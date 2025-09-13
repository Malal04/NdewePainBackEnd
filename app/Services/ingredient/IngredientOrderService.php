<?php

namespace App\Services\ingredient;

use App\Models\IngredientOrder;
use App\Http\Requests\ingredient\IngredientOrderRequest;
use App\Http\Resources\ingredient\IngredientOrderResource;

class IngredientOrderService
{

    public function index()
    {
        $orders = IngredientOrder::with(['ingredient', 'supplier'])->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des commandes récupérée avec succès.',
            'data' => IngredientOrderResource::collection($orders),
        ]);
    }

    public function indexPagination()
    {
        $orders = IngredientOrder::with('ingredient')
        ->with('supplier')
        ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => IngredientOrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function store(IngredientOrderRequest $request)
    {
        $order = IngredientOrder::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Commande créée avec succès.',
            'data' => new IngredientOrderResource(
                $order->load(['ingredient', 'supplier'])
            ),
        ], 201);
    }

    public function update(IngredientOrderRequest $request, IngredientOrder $order)
    {
        $order->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Commande mise à jour avec succès.',
            'data' => new IngredientOrderResource(
                $order->load(['ingredient', 'supplier'])
            ),
        ]);
    }

    public function destroy(IngredientOrder $order)
    {
        $order->delete();

        return response()->json([
            'status' => true,
            'message' => 'Commande supprimée avec succès.',
        ]);
    }

    /**
     * Marquer une commande comme livrée
     * et augmenter le stock de l’ingrédient
     */
    public function markAsDelivered(IngredientOrder $order)
    {
        if ($order->statut === 'livrer') {
            return response()->json([
                'status' => false,
                'message' => 'Commande déjà livrée.',
            ], 400);
        }

        $order->update(['statut' => 'livrer']);

        // Augmenter le stock de l’ingrédient
        $ingredient = $order->ingredient;
        $ingredient->quantite += $order->quantite;
        $ingredient->refreshStatut();

        return response()->json([
            'status' => true,
            'message' => 'Commande marquée comme livrée et stock mis à jour.',
            'data' => new IngredientOrderResource(
                $order->load(['ingredient', 'supplier'])
            ),
        ]);
    }

    public function markAsCancelled(IngredientOrder $order)
    {
        if ($order->statut === 'annuler') {
            return response()->json([
                'status' => false,
                'message' => 'Commande déjà annulée.',
            ], 400);
        }

        $order->update(['statut' => 'annuler']);

        return response()->json([
            'status' => true,
            'message' => 'Commande marquée comme annulée.',
            'data' => new IngredientOrderResource(
                $order->load(['ingredient', 'supplier'])
            ),
        ]);
    }

    public function search($search)
    {
        $orders = IngredientOrder::where('ingredient_id', 'like', "%{$search}%")
            ->orWhere('supplier_id', 'like', "%{$search}%")
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Recherche des commandes effectuée avec succès.',
            'data' => IngredientOrderResource::collection($orders),
        ]);
    }

    public function show(IngredientOrder $order)
    {
        return response()->json([
            'status' => true,
            'message' => 'Commande récupérée avec succès.',
            'data' => new IngredientOrderResource(
                $order->load(['ingredient', 'supplier'])
            ),
        ]);
    }

}
