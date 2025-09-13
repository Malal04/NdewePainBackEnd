<?php

namespace App\Services\produit;

use App\Http\Requests\produit\ProduitRequest;
use App\Http\Requests\produit\UproduitRequest;
use App\Http\Resources\produit\ProduitResource;
use App\Models\Produit;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProduitService
{

    /**
     * Lister tous les produits avec pagination
     */
    public function all($perPage = 16)
    {
        $user = Auth::user();

        $query = Produit::with('categorie');

        
        if ($user && $user->role === 'client') {
            $query->where('status', 'active');
        }

        $produits = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Liste des produits récupérée avec succès.',
            'data' => ProduitResource::collection($produits),
            'meta' => [
                'current_page' => $produits->currentPage(),
                'last_page'    => $produits->lastPage(),
                'per_page'     => $produits->perPage(),
                'total'        => $produits->total(),
            ],
        ]);
    }

    /**
     * Liste des produits avec pagination
     */
    public function indexPagination()
    {
        $produits = Produit::all();
        
        return response()->json([
            'status' => true,
            'message' => 'Liste des produits récupérée avec succès.',
            'data' => ProduitResource::collection($produits),
        ]);
    }

    /**
     * Lister et filtrer les produits avec pagination
     */
    public function filterProducts(ProduitRequest $request, $perPage = 16)
    {   
        $user = Auth::user();

        $query = Produit::query();

        if ($user && $user->role === 'client') {
            $query->where('status', 'active');
        }
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        if ($request->filled('dietary')) {
            $dietary = $request->get('dietary');
            $query->whereJsonContains('allergenes', $dietary);
        }

        if ($request->filled('ingredients')) {
            foreach ($request->ingredients as $ingredient) {
                $query->whereJsonContains('allergenes', $ingredient);
            }
        }

        if ($request->filled('exclude_allergens')) {
            foreach ($request->exclude_allergens as $allergen) {
                $query->whereJsonDoesntContain('allergenes', $allergen);
            }
        }

        if ($request->filled('price_min')) {
            $query->where('prix_unitaire', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('prix_unitaire', '<=', $request->price_max);
        }

        $sortBy = $request->get('sort_by', 'popularity'); 
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('prix_unitaire', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('prix_unitaire', 'desc');
                break;
            case 'latest':
                $query->latest();
                break;
            default:
                $query->orderBy('created_at', 'desc'); 
        }

        $produits = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Liste des produits filtrés récupérée avec succès.',
            'data' => ProduitResource::collection($produits),
            'meta' => [
                'current_page' => $produits->currentPage(),
                'last_page'    => $produits->lastPage(),
                'per_page'     => $produits->perPage(),
                'total'        => $produits->total(),
            ],
        ]);
    }

    /**
     * Créer un produit
     */
    public function store(ProduitRequest $request)
    {
        try {
            $slug = Str::slug($request->nom);

            // Vérification du slug existant
            if (Produit::where('slug', $slug)->exists()) {
                $slug .= '-' . uniqid();
            }

            // Création du produit
            $produit = Produit::create([
                'categorie_id'=> $request->categorie_id,
                'nom'         => $request->nom,
                'slug'        => $slug,
                'description' => $request->description,
                'prix_unitaire'=> $request->prix_unitaire,
                'photo_url'   => $request->photo_url,
                'stock'       => $request->stock ?? 0,
                'status'      => $request->status ?? 'active',
                'allergenes'  => $request->allergenes,
            ]);

            // Gestion du fichier photo
            if ($request->hasFile('photo_url')) {
                $path = $request->file('photo_url')->store('uploads/produits', 'public');
                $produit->photo_url = $path;
                $produit->save(); 
            }

            return response()->json([
                'status' => true,
                'message' => 'Produit créé avec succès.',
                'data' => new ProduitResource($produit->load('categorie')),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la création du produit.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer un produit
     */
    public function show($id)
    {
        $produit = Produit::with('categorie')->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Produit récupéré avec succès.',
            'data' => new ProduitResource($produit),
        ]);
    }

    /**
     * Récupérer un produit avec suggestions
     */
    public function showWithRelated($id, $limit = 10)
    {
        $produit = Produit::with('categorie')->findOrFail($id);

        // Suggestions : même catégorie, exclure le produit courant
        $related = Produit::with('categorie')
            ->where('categorie_id', $produit->categorie_id)
            ->where('id', '!=', $produit->id)
            ->inRandomOrder()
            ->take($limit)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Produit et suggestions récupérés avec succès.',
            'data' => [
                'produit'  => new ProduitResource($produit),
                'related'  => ProduitResource::collection($related),
            ],
        ]);
    }


    /**
     * Mettre à jour un produit
     */
    public function update(UproduitRequest $request, $id)
    {
        try {
            $produit = Produit::findOrFail($id);

            // Mise à jour du slug si le nom change
            if ($request->has('nom') && $request->nom !== $produit->nom) {
                $slug = Str::slug($request->nom);

                if (Produit::where('slug', $slug)->where('id', '!=', $produit->id)->exists()) {
                    $slug .= '-' . uniqid();
                }
                $produit->slug = $slug;
            }

            // Mise à jour des champs
            $produit->categorie_id = $request->categorie_id ?? $produit->categorie_id;
            $produit->nom          = $request->nom ?? $produit->nom;
            $produit->description  = $request->description ?? $produit->description;
            $produit->prix_unitaire= $request->prix_unitaire ?? $produit->prix_unitaire;
            $produit->stock        = $request->stock ?? $produit->stock;
            $produit->status       = $request->status ?? $produit->status;
            $produit->allergenes   = $request->allergenes ?? $produit->allergenes;

            // Gestion de l'image
            if ($request->hasFile('photo_url')) {
                // Suppression de l'ancienne image si existe
                if ($produit->photo_url && Storage::disk('public')->exists($produit->photo_url)) {
                    Storage::disk('public')->delete($produit->photo_url);
                }

                $path = $request->file('photo_url')->store('uploads/produits', 'public');
                $produit->photo_url = $path;
            }

            $produit->save();

            return response()->json([
                'status' => true,
                'message' => 'Produit mis à jour avec succès.',
                'data' => new ProduitResource($produit->load('categorie')),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la mise à jour du produit.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un produit
    */
    public function destroy($id)
    {
        $produit = Produit::findOrFail($id);
        $produit->delete();

        return response()->json([
            'status' => true,
            'message' => 'Produit supprimé avec succès.',
        ]);
    }

    /**
     * Changer le statut (active <-> inactive)
     */
    public function toggleStatus($id)
    {
        $produit = Produit::findOrFail($id);

        $produit->status = $produit->status === 'active' ? 'inactive' : 'active';
        $produit->save();

        return response()->json([
            'status' => true,
            'message' => 'Statut du produit mis à jour.',
            'data' => new ProduitResource($produit->load('categorie')),
        ]);
    }

    /**
     * Recherche produit par nom / description
     */
    public function search($query, $perPage = 10)
    {
        $produits = Produit::with('categorie')
            ->where('nom', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%")
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Résultats de la recherche.',
            'data' => ProduitResource::collection($produits),
            'meta' => [
                'current_page' => $produits->currentPage(),
                'last_page'    => $produits->lastPage(),
                'per_page'     => $produits->perPage(),
                'total'        => $produits->total(),
            ],
        ]);
    }

    /**
     * Récupérer un produit par slug
     */
    public function getBySlug($slug)
    {
        $produit = Produit::with('categorie')->where('slug', $slug)->first();

        return response()->json([
            'status' => true,
            'message' => 'Produit récupéré avec succès.',
            'data' => new ProduitResource($produit),
        ]);
    }

   
}
