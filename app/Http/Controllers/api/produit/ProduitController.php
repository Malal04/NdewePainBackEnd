<?php

namespace App\Http\Controllers\Api\produit;

use App\Http\Controllers\Controller;
use App\Http\Requests\produit\ProduitRequest;
use App\Http\Requests\produit\UproduitRequest;
use App\Models\Produit;
use App\Services\produit\ProduitService;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    
    protected $produitService;

    public function __construct(ProduitService $produitService)
    {
        $this->produitService = $produitService;
    }

    /**
     * Liste des produits avec pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 16);
        return $this->produitService->all($perPage);
    }

    /**
     * Liste des produits avec pagination
     */
    public function indexPagination()
    {
        return $this->produitService->indexPagination();
    }

    /**
     * Récupérer un produit avec suggestions
     */
    public function showWithRelated($id, $limit = 10)
    {
        return $this->produitService->showWithRelated($id, $limit);
    }

    /**
     * Filtrer les produits
     */
    public function filter(ProduitRequest $request)
    {
        return $this->produitService->filterProducts($request);
    }

    /**
     * Créer un produit
     */
    public function store(ProduitRequest $request)
    {
        return $this->produitService->store($request);
    }

    /**
     * Récupérer un produit par ID
     */
    public function show($id)
    {
        return $this->produitService->show($id);
    }

    /**
     * Mettre à jour un produit
     */
    public function update(UproduitRequest $request, $id)
    {
        return $this->produitService->update($request, $id);
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        return $this->produitService->destroy($id);
    }

    /**
     * Changer le statut d’un produit (active <-> inactive)
     */
    public function toggleStatus($id)
    {
        return $this->produitService->toggleStatus($id);
    }

    /**
     * Recherche de produits par nom ou description
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $perPage = $request->get('per_page', 10);

        return $this->produitService->search($query, $perPage);
    }

    /**
     * Récupérer un produit par slug
     */
    public function getBySlug($slug)
    {
        return $this->produitService->getBySlug($slug);
    }

}
