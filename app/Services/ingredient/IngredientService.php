<?php

namespace App\Services\ingredient;

use App\Http\Requests\ingredient\IngredientRequest;
use App\Models\Ingredient;
use App\Http\Resources\ingredient\IngredientResource;

class IngredientService
{
    public function index()
    {
        $ingredients = Ingredient::with('supplier')->get();
        return response()->json([
            'status' => true,
            'message' => 'Liste des ingrédients récupérée avec succès.',
            'data' => IngredientResource::collection($ingredients),
        ]);
    }

    public function indexPagination()
    {
        $ingredients = Ingredient::with('supplier')->paginate(3);

        return response()->json([
            'status' => true,
            'data' => IngredientResource::collection($ingredients),
            'meta' => [
                'current_page' => $ingredients->currentPage(),
                'last_page'    => $ingredients->lastPage(),
                'per_page'     => $ingredients->perPage(),
                'total'        => $ingredients->total(),
            ],
        ]);
    }

    public function store(IngredientRequest $request)
    {
        $ingredient = Ingredient::create($request->validated());
        $ingredient->refreshStatut();

        return response()->json([
            'status' => true,
            'message' => 'Ingrédient ajouté avec succès.',
            'data' => new IngredientResource($ingredient->load('supplier')),
        ], 201);
    }

    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        $ingredient->update($request->validated());
        $ingredient->refreshStatut();

        return response()->json([
            'status' => true,
            'message' => 'Ingrédient mis à jour avec succès.',
            'data' => new IngredientResource($ingredient->load('supplier')),
        ]);
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json([
            'status' => true,
            'message' => 'Ingrédient supprimé avec succès.',
        ]);
    }

    public function toggleStatus(Ingredient $ingredient)
    {
        $ingredient->statut = !$ingredient->statut;
        $ingredient->save();

        return response()->json([
            'status' => true,
            'message' => 'Statut de l\'ingrédient modifié avec succès.',
            'data' => new IngredientResource($ingredient->load('supplier')),
        ]);
    }

    public function search($search)
    {
        $ingredients = Ingredient::with('supplier')
            ->where('nom', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%")
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Recherche des ingrédients effectuée avec succès.',
            'data' => IngredientResource::collection($ingredients),
        ]);
    }

    public function show(Ingredient $ingredient)
    {
        return response()->json([
            'status' => true,
            'message' => 'Ingrédient récupéré avec succès.',
            'data' => new IngredientResource(
                $ingredient->load('supplier')
            ),
        ]);
    }


}
