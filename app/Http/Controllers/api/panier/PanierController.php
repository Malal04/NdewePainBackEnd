<?php

namespace App\Http\Controllers\Api\panier;

use App\Http\Controllers\Controller;
use App\Http\Requests\panier\CommandeRequest;
use App\Http\Resources\panier\CommandeResource;
use App\Services\panier\PanierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanierController extends Controller
{
    protected $panierService;

    public function __construct(PanierService $panierService)
    {
        $this->panierService = $panierService;
    }

    public function voir()
    {
        try {
            $data = $this->panierService->getCart();
            return response()->json([
                'status' => true,
                'message' => 'Panier récupéré avec succès.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    public function addItem(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1'
        ]);

        try {
            $this->panierService->addItemToCart($request->produit_id, $request->quantite);
            return response()->json(['message' => 'Produit ajouté au panier'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantite' => 'required|integer|min:1'
        ]);

        try {
            $this->panierService->updateItemQuantity($itemId, $request->quantite);
            return response()->json(['message' => 'Quantité mise à jour'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function removeItem($itemId)
    {
        try {
            $this->panierService->removeItemFromCart($itemId);
            return response()->json(['message' => 'Produit supprimé du panier'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'mode_livraison' => 'required|string|in:livraison,ramassage',
            'methode_paiement' => 'required|string|in:carte,paiement_a_la_livraison,wave,orange_money',
        ]);

        try {
            return $this->panierService->checkout($request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }



    // /**
    //  * Voir le contenu du panier + totaux
    //  */
    // public function voir()
    // {
    //     try {
    //         $data = $this->panierService->getCheckoutData();
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Panier récupéré avec succès.',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    // /**
    //  * Ajouter un produit au panier
    //  */
    // public function ajouter(Request $request)
    // {
    //     $request->validate([
    //         'produit_id' => 'required|integer|exists:produits,id',
    //         'quantite' => 'required|integer|min:1',
    //         'personnalisation' => 'nullable|string|max:255',
    //     ]);

    //     try {
    //         $data = $this->panierService->addToCart(
    //             $request->produit_id,
    //             $request->quantite,
    //             $request->personnalisation
    //         );

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Produit ajouté au panier.',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    // /**
    //  * Modifier la quantité d’un produit dans le panier
    //  */
    // public function modifierQuantite(Request $request)
    // {
    //     $request->validate([
    //         'produit_id' => 'required|integer|exists:produits,id',
    //         'quantite' => 'required|integer|min:0',
    //     ]);

    //     try {
    //         $data = $this->panierService->updateQuantity(
    //             $request->produit_id, $request->quantite
    //         );

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Quantité mise à jour.',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    // /**
    //  * Supprimer un produit du panier
    //  */
    // public function supprimer(Request $request)
    // {
    //     $request->validate([
    //         'produit_id' => 'required|integer|exists:produits,id',
    //     ]);

    //     try {
    //         $data = $this->panierService->removeFromCart($request->produit_id);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Produit supprimé du panier.',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    // /**
    //  * Vider complètement le panier
    //  */
    // public function vider()
    // {
    //     try {
    //         $data = $this->panierService->clearCart();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Panier vidé.',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    // /**
    //  * Appliquer un code promo
    //  */
    // public function appliquerPromo(Request $request)
    // {
    //     $request->validate([
    //         'promo_code' => 'required|string',
    //     ]);

    //     try {
    //         $result = $this->panierService->applyPromoCode($request->promo_code);
    //         return response()->json($result);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    // /**
    //  * Confirmer la commande
    //  */
    // public function confirmer(CommandeRequest $request)
    // {
    //     try {
    //         $commande = $this->panierService->confirmOrder($request->validated());

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Commande confirmée avec succès.',
    //             'commande' => $commande
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

}
