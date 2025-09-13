<?php

namespace App\Services\panier;

use App\Models\ArticleCommande;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Promotion;
use App\Models\Stock;
use App\Models\StockHistory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CartService
{
    
    /**
     * Retourne (ou crée) le panier de l'utilisateur.
     *
     * @param int|null $userId
     * @return Cart
     */
    public function getOrCreateCart(?int $userId): Cart
    {
        if ($userId) {
            return Cart::firstOrCreate(['user_id' => $userId]);
        }

        // pour simplifier : si pas d'user, crée panier sans user (session token non géré)
        return Cart::firstOrCreate(['user_id' => null]);
    }

    /**
     * Ajoute un produit au panier (ou met à jour la quantité si existe).
     *
     * @param int|null $userId
     * @param int $produitId
     * @param int $quantite
     * @return CartItem
     * @throws Exception
     */
    public function addItem(
        ?int $userId,
        int $produitId,
        int $quantite): CartItem
    {
        if ($quantite <= 0) {
            throw new Exception("La quantité doit être supérieure à 0.");
        }

        return DB::transaction(function () use ($userId, $produitId, $quantite) {
            $cart = $this->getOrCreateCart($userId);
            /** @var Produit $produit */
            $produit = Produit::with('stock')->findOrFail($produitId);

            $stock = Stock::where('produit_id', $produitId)->lockForUpdate()->first();
            if (!$stock) {
                throw new Exception("Stock non trouvé pour le produit.");
            }

            // Si l'article existe déjà, on calcule la quantité totale souhaitée
            $existing = $cart->items()->where('produit_id', $produitId)->first();
            $nouvelleQuantiteTotale = $quantite + ($existing ? $existing->quantite : 0);

            if ($stock->quantite_actuelle < $nouvelleQuantiteTotale) {
                throw new Exception("Quantité insuffisante en stock. Disponible: {$stock->quantite_actuelle}");
            }

            // Créer ou update item
            $item = $cart->items()->updateOrCreate(
                ['produit_id' => $produitId],
                [
                    'quantite' => $nouvelleQuantiteTotale,
                    'prix_unitaire' => $produit->prix_unitaire,
                    'total' => $produit->prix_unitaire * $nouvelleQuantiteTotale,
                ]
            );

            // Réduire le stock pour la différence (si existed)
            $delta = $nouvelleQuantiteTotale - ($existing ? $existing->quantite : 0);
            if ($delta > 0) {
                $stock->quantite_actuelle -= $delta;
                $stock->save();
                $stock->refreshStatut();

                StockHistory::create([
                    'produit_id' => $produitId,
                    'type_mouvement' => StockHistory::SORTIE ?? 'sortie',
                    'quantite' => $delta,
                    'user_id' => Auth::id(),
                    'commentaire' => "Réduction stock pour ajout panier (cart_id: {$cart->id})"
                ]);
            }

            return $item;
        });
    }

    /**
     * Met à jour la quantité d'un item.
     *
     * @param int|null $userId
     * @param int $itemId
     * @param int $quantite
     * @return CartItem
     * @throws Exception
     */
    public function updateItemQuantity(
        ?int $userId,
        int $itemId,
        int $quantite): CartItem
    {
        if ($quantite < 0) {
            throw new Exception("Quantité invalide.");
        }

        return DB::transaction(function () use ($userId, $itemId, $quantite) {
            $cart = $this->getOrCreateCart($userId);
            $item = $cart->items()->where('id', $itemId)->first();

            if (!$item) {
                return [
                    'status' => false,
                    'message' => 'Article du panier introuvable.'
                ];
            }

            /** @var Stock $stock */
            $stock = Stock::where('produit_id', $item->produit_id)->lockForUpdate()->first();
            if (!$stock) {
                // throw new Exception("Stock non trouvé.");
                return [
                    'status' => false,
                    'message' => 'Stock non trouvé.'
                ];
            }

            $produit = Produit::findOrFail($item->produit_id);

            $currentQty = $item->quantite;
            $delta = $quantite - $currentQty;

            if ($delta > 0 && $stock->quantite_actuelle < $delta) {
                return [
                    'status' => false,
                    'message' => 'Quantité insuffisante en stock. Disponible: {$stock->quantite_actuelle}'
                ];
            }

            // Update item (si quantite == 0 on supprime)
            if ($quantite === 0) {
                $item->delete();
            } else {
                $item->update([
                    'quantite' => $quantite,
                    'total' => $produit->prix_unitaire * $quantite,
                ]);
            }

            // Appliquer changement sur le stock
            if ($delta !== 0) {
                $stock->quantite_actuelle -= $delta;
                $stock->save();
                $stock->refreshStatut();

                StockHistory::create([
                    'produit_id' => $item->produit_id,
                    'type_mouvement' => $delta > 0 ? 'sortie' : 'entree',
                    'quantite' => abs($delta),
                    'user_id' => Auth::id(),
                    'commentaire' => "Mise à jour du panier (cart_id: {$cart->id}, item_id: {$itemId})"
                ]);
            }

            return $item;
        });
    }

    /**
     * Supprime un item du panier et remet la quantité au stock.
     *
     * @param int|null $userId
     * @param int $itemId
     * @return void
     * @throws Exception
     */
    public function removeItem(?int $userId, int $itemId): void
    {
        DB::transaction(function () use ($userId, $itemId) {
            $cart = $this->getOrCreateCart($userId);
            $item = $cart->items()->where('id', $itemId)->first();

            if (!$item) {
                return [
                    'status' => false,
                    'message' => 'Article du panier introuvable.'
                ];
            }

            $stock = Stock::where('produit_id', $item->produit_id)->lockForUpdate()->first();
            if (!$stock) {
                return [
                    'status' => false,
                    'message' => 'Stock non trouvé.'
                ];
            }

            // Remet la quantité au stock
            $stock->quantite_actuelle += $item->quantite;
            $stock->save();
            $stock->refreshStatut();

            StockHistory::create([
                'produit_id' => $item->produit_id,
                'type_mouvement' => 'entree',
                'quantite' => $item->quantite,
                'user_id' => Auth::id(),
                'commentaire' => "Suppression item panier (cart_id: {$cart->id}, item_id: {$item->id})"
            ]);

            $item->delete();
        });
    }

    /**
     * Vide le panier et remet toutes les quantités en stock.
     *
     * @param int|null $userId
     * @return void
     */
    public function clearCart(?int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $cart = $this->getOrCreateCart($userId);
            $items = $cart->items()->get();

            foreach ($items as $item) {
                $stock = Stock::where('produit_id', $item->produit_id)->lockForUpdate()->first();
                if ($stock) {
                    $stock->quantite_actuelle += $item->quantite;
                    $stock->save();
                    $stock->refreshStatut();

                    StockHistory::create([
                        'produit_id' => $item->produit_id,
                        'type_mouvement' => 'entree',
                        'quantite' => $item->quantite,
                        'user_id' => Auth::id(),
                        'commentaire' => "Vider panier (cart_id: {$cart->id})"
                    ]);
                }
                $item->delete();
            }
        });
    }

    public function applyPromoCode(
        ?int $userId,
        string $promoCode
    ): array {
        $cart = $this->getOrCreateCart($userId);
    
        $promo = Promotion::with('produits')
            ->where('code_promo', $promoCode)
            ->where('status', 'active')
            ->where(function ($q) {
                $today = now()->toDateString();
                $q->whereNull('date_debut')->orWhere('date_debut', '<=', $today);
            })
            ->where(function ($q) {
                $today = now()->toDateString();
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', $today);
            })
            ->first();
    
        if (!$promo) {
            return ['status' => false, 'message' => 'Code promo invalide ou expiré.'];
        }
    
        if ($promo->type_remise === 'bogo') {
            $eligibleIds = $promo->produits->pluck('id')->toArray();
            $hasEligible = false;
    
            foreach ($cart->items as $item) {
                if (empty($eligibleIds) || in_array($item->produit_id, $eligibleIds, true)) {
                    $hasEligible = true;
                    break;
                }
            }
    
            if (! $hasEligible) {
                return [
                    'status' => false,
                    'message' => 'Ce code promo n’est pas applicable aux produits présents dans le panier.'
                ];
            }
        }
    
        $cart->promo_code = $promo->code_promo;
        $cart->save();
    
        return ['status' => true, 'message' => 'Code promo appliqué.'];
    }

    public function setDeliveryMode(
        ?int $userId,
        string $mode,
        ?float $fraisLivraison = null
    ): array {
        $cart = $this->getOrCreateCart($userId);

        if (! in_array($mode, ['livraison', 'retrait'])) {
            return ['status' => false, 'message' => 'Mode de livraison invalide.'];
        }

        $cart->mode_livraison = $mode;
        $cart->frais_livraison = $fraisLivraison ?? ($mode === 'livraison' ? 5.00 : 0.00);
        $cart->save();

        return [
            'status' => true,
            'message' => "Mode de livraison défini : {$mode}",
            'cart' => $cart
        ];
    }
    
    /**
     * Calcule le total du panier.
     *
     * @param Cart $cart
     * @return array
     */
    public function totals(Cart $cart): array
    {
        
        $sousTotal = (float) $cart->items()->sum('total');
        $fraisLivraison = (float) ($cart->frais_livraison ?? 0);
        $remise = 0.0;

        if ($cart->promo_code) {
            $promo = Promotion::with('produits')->where('code_promo', $cart->promo_code)->first();
            if ($promo && $promo->status === 'active') {
                $type = $promo->type_remise;

                if ($type === 'pourcentage') {
                    $val = (float) ($promo->valeur_remise ?? 0);
                    $remise = round($sousTotal * ($val / 100.0), 2);
                } elseif ($type === 'montant') {
                    $val = (float) ($promo->valeur_remise ?? 0);
                    $remise = round(min($val, $sousTotal), 2);
                } elseif ($type === 'bogo') {
                    $eligibleIds = $promo->produits->pluck('id')->toArray(); 
                    $bogoDiscount = 0.0;
                    foreach ($cart->items as $item) {
                        if (empty($eligibleIds) || in_array($item->produit_id, $eligibleIds, true)) {
                            $freeUnits = intdiv((int)$item->quantite, 2);
                            $bogoDiscount += $freeUnits * (float)$item->prix_unitaire;
                        }
                    }
                    $remise = round(min($bogoDiscount, $sousTotal), 2);
                } elseif ($type === 'gratuit_livraison') {
                    $fraisLivraison = 0.0;
                }
            }
        }

        $total = max(0, $sousTotal + $fraisLivraison - $remise);

        return [
            'status' => true,
            'sous_total' => round($sousTotal, 2),
            'frais_livraison' => round($fraisLivraison, 2),
            'remise' => round($remise, 2),
            'total' => round($total, 2),
        ];

    }

    public function confirmOrder(
        ?int $userId, 
        string $methodePaiement
    ): Commande {
        return DB::transaction(function () use ($userId, $methodePaiement) {
            $cart = Cart::with('items.produit')->where('user_id', $userId)->firstOrFail();
            if ($cart->items->isEmpty()) {
                throw new Exception("Votre panier est vide.");
            }

            $commande = Commande::create([
                'user_id' => $userId,
                'code_commande' => strtoupper(Str::random(10)),
                'mode_livraison' => $cart->mode_livraison ?? 'retrait',
                'sous_total' => $cart->items->sum('total'),
                'frais_livraison' => $cart->frais_livraison ?? 0,
                'remise' => 0, 
                'total' => $cart->items->sum('total') + ($cart->frais_livraison ?? 0),
                'statut_commande' => 'en_attente',
            ]);

            foreach ($cart->items as $item) {
                ArticleCommande::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $item->produit_id,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->prix_unitaire,
                    'total' => $item->total,
                    'personnalisation' => $item->personnalisation,
                ]);
            }

            Paiement::create([
                'commande_id' => $commande->id,
                'methode' => $methodePaiement,
                'statut' => 'paye',
                'montant' => $commande->total,
            ]);

            $cart->items()->delete();
            $cart->update([
                'promo_code' => null,
                'remise_appliquee' => 0,
                'frais_livraison' => 0,
                'mode_livraison' => null
            ]);

            return $commande;
        });
    }

}
