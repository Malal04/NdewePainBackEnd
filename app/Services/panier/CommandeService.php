<?php

namespace App\Services\panier;

use App\Models\Commande;
use App\Models\Promotion;
use App\Models\Stock;
use App\Models\StockHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CommandeService
{

    /**
     * Confirme une commande : crée commande + articles + paiement
     */
    public function confirmOrder(array $data): Commande
    {
        $user = Auth::user();
        if (!$user) {
            throw ValidationException::withMessages(['user' => ['Veuillez vous connecter.']]);
        }
        return DB::transaction(function () use ($data, $user) {
            $cart = $user->cart()->with('items')->first();
            if (!$cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => ['Votre panier est vide.']]);
            }
            // Totaux
            $sousTotal = $cart->items->sum('total');
            $remise = 0;
            if ($cart->promo_code) {
                $promo = Promotion::where('code_promo', $cart->promo_code)
                    ->where('status', 'active')->first();
                if ($promo) {
                    $remise = $promo->type_remise === 'pourcentage'
                        ? round($sousTotal * ($promo->valeur_remise / 100), 2)
                        : round(min($promo->valeur_remise, $sousTotal), 2);
                }
            }
            $fraisLivraison = $data['frais_livraison'] ?? 2000;
            $total = max(0, $sousTotal + $fraisLivraison - $remise);
            // Création commande
            $commande = Commande::create([
                'user_id' => $user->id,
                'mode_livraison' => $data['mode_livraison'] ?? 'livraison',
                'sous_total' => $sousTotal,
                'frais_livraison' => $fraisLivraison,
                'remise' => $remise,
                'total' => $total,
                'statut_commande' => 'en_attente',
                'adresse_id' => $data['adresse_id'] ?? null,
            ]);
            // Articles
            foreach ($cart->items as $item) {
                $stock = Stock::where('produit_id', $item->produit_id)->lockForUpdate()->first();
                if (!$stock || $stock->quantite_actuelle < $item->quantite) {
                    throw ValidationException::withMessages([
                        'stock' => ["Stock insuffisant pour {$item->produit->nom}."]
                    ]);
                }
                // Update stock
                $stock->decrement('quantite_actuelle', $item->quantite);
                $stock->refreshStatut();
                StockHistory::create([
                    'produit_id' => $item->produit_id,
                    'type_mouvement' => StockHistory::SORTIE,
                    'quantite' => $item->quantite,
                    'user_id' => $user->id,
                    'commentaire' => "Commande #{$commande->id}"
                ]);
                $commande->articlesCommande()->create([
                    'produit_id' => $item->produit_id,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->prix_unitaire,
                    'total' => $item->total,
                    'personnalisation' => $item->personnalisation,
                ]);
            }
            // Paiement
            $commande->paiement()->create([
                'methode' => $data['methode_paiement'] ?? 'livraison',
                'statut' => 'en_attente',
                'montant' => $total,
            ]);
            // Vider le panier
            $cart->items()->delete();
            $cart->update(['promo_code' => null]);
            Session::forget('promo_code');
            return $commande->load(
                [
                    'articlesCommande.produit',
                    'paiement'
                ]
            );
        });
    }

}
