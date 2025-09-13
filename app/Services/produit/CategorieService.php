<?php

namespace App\Services\produit;

use App\Models\Categorie;
use App\Http\Resources\produit\CategorieResource;
use Illuminate\Support\Str;
use App\Http\Requests\produit\CategorieRequest;
use Exception;
use Illuminate\Support\Facades\Auth;

class CategorieService
{

    /**
     * Lister toutes les catégories avec pagination
     */
    public function all($perPage = 20)
    {
        $user = Auth::user();

        $categories = Categorie::paginate($perPage);

        if ($user && $user->role === 'client') {
            $categories = $categories->where('status', 'active');
        }

        return response()->json([
            'status' => true,
            'message' => 'Liste des catégories récupérée avec succès.',
            'data' => CategorieResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ],
        ]);
    }

    /**
     * Liste paginée des catégories
     */
    public function indexDefault()
    {
        $user = Auth::user();

        $categories = Categorie::all();

        if ($user && $user->role === 'client') {
            $categories = $categories->where('status', 'active');
        }

        return response()->json([
            'status' => true,
            'message' => 'Liste des catégories récupérée avec succès.',
            'data' => CategorieResource::collection($categories),
        ]);
    }

    /**
     * Créer une nouvelle catégorie
     */
    public function store(CategorieRequest $request)
    {
        try {
            $slug = Str::slug($request->nom);

            // Vérifier si le slug existe déjà
            if (Categorie::where('slug', $slug)->exists()) {
                $slug .= '-' . uniqid();
            }

            $categorie = Categorie::create([
                'nom'        => $request->nom,
                'slug'       => $slug,
                'description'=> $request->description,
                'status'     => $request->status ?? 'active',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Catégorie créée avec succès.',
                'data' => new CategorieResource($categorie),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la création de la catégorie.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer une catégorie par ID
     */
    public function show($id)
    {
        $categorie = Categorie::findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Catégorie récupérée avec succès.',
            'data' => new CategorieResource($categorie),
        ]);
    }

    /**
     * Mettre à jour une catégorie
     */
    public function update(CategorieRequest $request, $id)
    {
        try {
            $categorie = Categorie::findOrFail($id);

            $slug = Str::slug($request->nom ?? $categorie->nom);

            if (Categorie::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug .= '-' . uniqid();
            }

            $categorie->update([
                'nom'        => $request->nom ?? $categorie->nom,
                'slug'       => $slug,
                'description'=> $request->description ?? $categorie->description,
                'status'     => $request->status ?? $categorie->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Catégorie mise à jour avec succès.',
                'data' => new CategorieResource($categorie),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer une catégorie
     */
    public function destroy($id)
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();

        return response()->json([
            'status' => true,
            'message' => 'Catégorie supprimée avec succès.',
        ]);
    }

    /**
     * Changer le statut (active <-> inactive)
     */
    public function toggleStatus($id)
    {
        $categorie = Categorie::findOrFail($id);

        $categorie->status = $categorie->status === 'active' ? 'inactive' : 'active';
        $categorie->save();

        return response()->json([
            'status' => true,
            'message' => 'Statut de la catégorie mis à jour.',
            'data' => new CategorieResource($categorie),
        ]);
    }

    /**
     * Recherche par nom ou description
     */
    public function search($query, $perPage = 20)
    {
        $categories = Categorie::where('nom', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%")
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Résultats de la recherche.',
            'data' => CategorieResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ],
        ]);
    }

    /**
     * Récupérer une catégorie par slug
     */
    public function getBySlug($slug)
    {
        $categorie = Categorie::where('slug', $slug)->first();

        return response()->json([
            'status' => true,
            'message' => 'Catégorie récupérée avec succès.',
            'data' => new CategorieResource($categorie),
        ]);
    }

}
