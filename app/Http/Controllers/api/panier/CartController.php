<?php

namespace App\Http\Controllers\Api\panier;

use App\Http\Controllers\Controller;
use App\Http\Requests\panier\AddToCartRequest;
use App\Http\Requests\panier\UpdateCartItemRequest;
use App\Http\Resources\panier\CartResource;
use App\Http\Resources\panier\CommandeResource;
use App\Models\Cart;
use App\Models\Commande;
use App\Services\panier\CartService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function show(Request $request)
    {
        $userId = $request->user()->id;
        $cart = $this->cartService->getOrCreateCart($userId);
        $cart->load('items.produit');
        $totals = $this->cartService->totals($cart);

        return (new CartResource($cart))->additional([
            'meta' => [
                'status' => true,
                'totals' => $totals
            ]
        ]);
    }

    public function add(AddToCartRequest $request)
    {
        try {
            $userId = $request->user()->id;
            $item = $this->cartService->addItem(
                $userId,
                $request->produit_id,
                $request->quantite
            );

            $cart = $this->cartService->getOrCreateCart($userId);
            $cart->load('items.produit');
            $totals = $this->cartService->totals($cart);

            return (new CartResource($cart))->additional([
                'meta' => [
                    'status' => true,
                    'message' => 'Produit ajouté',
                    'totals' => $totals
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(UpdateCartItemRequest $request, $itemId)
    {
        $userId = $request->user()->id;
        $this->cartService->updateItemQuantity(
            $userId,
            (int)$itemId,
            $request->quantite
        );

        $cart = $this->cartService->getOrCreateCart($userId);
        $cart->load('items.produit');
        $totals = $this->cartService->totals($cart);

        return (new CartResource($cart))->additional([
            'meta' => [
                'status' => true,
                'message' => 'Quantité mise à jour',
                'totals' => $totals
            ]
        ]);
    }

    public function remove(Request $request, $itemId)
    {
        $userId = $request->user()->id;
        $this->cartService->removeItem(
            $userId,
            (int)$itemId
        );

        $cart = $this->cartService->getOrCreateCart($userId);
        $cart->load('items.produit');
        $totals = $this->cartService->totals($cart);

        return (new CartResource($cart))->additional([
            'meta' => [
                'status' => true,
                'message' => 'Article supprimé',
                'totals' => $totals
            ]
        ]);
    }

    public function clear(Request $request)
    {
        $userId = $request->user()->id;
        $this->cartService->clearCart($userId);

        $cart = $this->cartService->getOrCreateCart($userId);
        $cart->load('items.produit');
        $totals = $this->cartService->totals($cart);

        return (new CartResource($cart))->additional([
            'meta' => [
                'status' => true,
                'message' => 'Panier vidé',
                'totals' => $totals
            ]
        ]);
    }

    public function applyPromo(Request $request)
    {
        $request->validate(['promo_code' => 'required|string']);
        $userId = $request->user()->id;

        $result = $this->cartService->applyPromoCode(
            $userId,
            $request->promo_code
        );

        $cart = $this->cartService->getOrCreateCart($userId);
        $cart->load('items.produit');
        $totals = $this->cartService->totals($cart);

        return (new CartResource($cart))->additional([
            'meta' => [
                'status' => $result['status'],
                'message' => $result['message'],
                'totals' => $totals
            ]
        ]);
    }

    /**
     * Définir le mode de livraison pour le panier en cours
     */
    public function setDelivery(Request $request)
    {
        $request->validate([
            'mode_livraison' => 'required|string|in:livraison,retrait',
            'frais_livraison' => 'nullable|numeric|min:0'
        ]);

        $userId = Auth::id();
        $mode = $request->input('mode_livraison');
        $frais = $request->input('frais_livraison');

        $result = $this->cartService->setDeliveryMode(
            $userId,
            $mode,
            $frais
        );

        if (!$result['status']) {
            return response()->json([
                'status' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => $result['message'],
            'cart' => $result['cart'],
            'totals' => $this->cartService->totals($result['cart'])
        ]);
    }

    // public function confirm(Request $request)
    // {
    //     Log::info('[CartController@confirm] Payload reçu', $request->all());
    //     Log::info('[CartController@confirm] User ID', ['user_id' => Auth::id()]);
    
    //     try {
    //         $request->validate([
    //             'methode_paiement' => 'required|in:carte,paiement_a_la_livraison,wave,orange_money',
    //         ]);
    
    //         $commande = $this->cartService->confirmOrder(
    //             Auth::id(),
    //             $request->methode_paiement
    //         );
    
    //         Log::info(' [CartController@confirm] Commande créée', $commande->toArray());
    
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Commande créée avec succès.',
    //             'commande' => $commande->loadMissing(['items.produit', 'paiement'])
    //         ]);
    
    //     } catch (\Throwable $e) {
    //         Log::error('[CartController@confirm] Erreur', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Erreur serveur lors de la confirmation de la commande.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function confirm(Request $request)
    {
        Log::info('[CartController@confirm] Payload reçu', $request->all());
        Log::info('[CartController@confirm] User ID', ['user_id' => Auth::id()]);

        try {
            $request->validate([
                'methode_paiement' => 'required|in:carte,paiement_a_la_livraison,wave,orange_money',
            ]);

            $commande = $this->cartService->confirmOrder(
                Auth::id(),
                $request->methode_paiement
            );

            // Charger les bonnes relations
            $commande->loadMissing(['articlesCommande.produit', 'paiement', 'adresse']);

            Log::info('[CartController@confirm] Commande créée', $commande->toArray());

            return response()->json([
                'status' => true,
                'message' => 'Commande créée avec succès.',
                'commande' => new CommandeResource($commande)
            ]);

        } catch (\Throwable $e) {
            Log::error('[CartController@confirm] Erreur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Erreur serveur lors de la confirmation de la commande.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste toutes les commandes de l’utilisateur connecté
     */
    public function mesCommandes(Request $request)
    {
        $userId = Auth::id();
        $commandes = Commande::with(['articlesCommande.produit', 'paiement', 'adresse'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return CommandeResource::collection($commandes)->additional([
            'status' => true,
            'message' => 'Liste des commandes récupérée.'
        ]);
    }

    /**
     * Liste toutes les commandes (réservé admin/employé)
     */
    public function allCommandes(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, [  'admin','gerant','employe'])) {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé.'
            ], 403);
        }

        $commandes = Commande::with(['articlesCommande.produit', 'paiement', 'adresse'])
            ->latest()
            ->get();

        return CommandeResource::collection($commandes)->additional([
            'status' => true,
            'message' => 'Toutes les commandes récupérées.'
        ]);
    }

    /**
     * Détail d’une commande précise
     */
 /**
 * Détail d’une commande précise
 */
    public function detailCommande($id)
    {
        $user = Auth::user();
        if ($user->role === 'client') {
            $commande = Commande::with(['articlesCommande.produit', 'paiement', 'adresse'])
                ->where('user_id', $user->id)
                ->findOrFail($id);
        } else {
            $commande = Commande::with(['articlesCommande.produit', 'paiement', 'adresse'])
                ->findOrFail($id);
        }
        return (new CommandeResource($commande))->additional([
            'status' => true,
            'message' => 'Détail de la commande.'
        ]);
    } 

    /**
     * Changer le statut d’une commande (admin/gerant/employe uniquement)
     */
    public function updateStatutCommande(Request $request, $id)
    {
        $request->validate([
            'statut_commande' => 'required|in:en_attente,confirmee,en_cours,livree,annulee'
        ]);

        $user = Auth::user();

        if (! in_array($user->role, ['admin','gerant','employe'])) {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé.'
            ], 403);
        }

        $commande = Commande::findOrFail($id);
        $commande->statut_commande = $request->statut_commande;
        $commande->save();

        return (new CommandeResource($commande->load(['articlesCommande.produit', 'paiement', 'adresse'])))
            ->additional([
                'status' => true,
                'message' => "Statut de la commande mis à jour : {$commande->statut_commande}"
        ]);
    }


}