<?php

namespace App\Http\Controllers\Api\produit;

use App\Http\Controllers\Controller;
use App\Http\Requests\produit\CategorieRequest;
use App\Services\produit\CategorieService;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    protected $categorieService;

    public function __construct(CategorieService $categorieService)
    {
        $this->categorieService = $categorieService;
    }

    // Lister toutes les catégories
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        return $this->categorieService->all($perPage);
    }

    // Liste paginée des catégories
    public function indexDefault()
    {
        return $this->categorieService->indexDefault();
    }

    // Créer une catégorie
    public function store(CategorieRequest $request)
    {
        return $this->categorieService->store($request);
    }

    // Afficher une catégorie
    public function show($id)
    {
        return $this->categorieService->show($id);
    }

    // Mettre à jour une catégorie
    public function update(CategorieRequest $request, $id)
    {
        return $this->categorieService->update($request, $id);
    }

    // Supprimer une catégorie
    public function destroy($id)
    {
        return $this->categorieService->destroy($id);
    }

    // Changer le statut (active <-> inactive)
    public function toggleStatus($id)
    {
        return $this->categorieService->toggleStatus($id);
    }

    /**
     * Recherche de catégories par nom ou description
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $perPage = $request->get('per_page', 10);

        return $this->categorieService->search($query, $perPage);
    }

    // Récupérer une catégorie par slug
    public function getBySlug($slug)
    {
        return $this->categorieService->getBySlug($slug);
    }

}
