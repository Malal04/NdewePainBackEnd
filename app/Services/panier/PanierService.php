<?php

namespace App\Services\panier;

use App\Models\ArticleCommande;
use App\Models\Cart;
use App\Models\Commande;
use App\Models\Paiement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PanierService
{
 
    public function getCart()
    {
        // Récupérer ou créer le panier
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            $cart = Cart::create(['user_id' => Auth::id()]);
        }

        return $cart;
    }

    public function addItemToCart($produitId, $quantite)
    {
        $cart = $this->getCart();
        $cart->addItem($produitId, $quantite);
    }

    public function updateItemQuantity($itemId, $quantite)
    {
        $cart = $this->getCart();
        $cart->updateItemQuantity($itemId, $quantite);
    }

    public function removeItemFromCart($itemId)
    {
        $cart = $this->getCart();
        $cart->removeItem($itemId);
    }

    public function checkout(Request $request)
    {
        $cart = $this->getCart();
        $total = $cart->calculateTotal();

        DB::beginTransaction();
        try {
            // Créer la commande
            $commande = Commande::create([
                'user_id' => Auth::id(),
                'code_commande' => 'CMD-' . strtoupper(uniqid()),
                'mode_livraison' => $request->mode_livraison,
                'total' => $total,
                'statut_commande' => 'en_attente',
            ]);

            // Ajouter les articles de panier à la commande
            foreach ($cart->items as $item) {
                ArticleCommande::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $item->produit_id,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->prix_unitaire,
                    'total' => $item->total,
                ]);
            }

            // Enregistrer le paiement
            Paiement::create([
                'commande_id' => $commande->id,
                'methode' => $request->methode_paiement,
                'statut' => 'en_attente',
                'montant' => $total,
            ]);

            DB::commit();
            return response()->json(['message' => 'Commande créée avec succès!', 'commande' => $commande], 201);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Erreur lors de la création de la commande: ' . $e->getMessage());
        }
    }
}
