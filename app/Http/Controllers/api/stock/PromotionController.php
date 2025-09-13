<?php

namespace App\Http\Controllers\Api\stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\stock\PromotionRequest;
use App\Http\Requests\stock\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Services\stock\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $service;

    public function __construct(PromotionService $service)
    {
        $this->service = $service;
    }

    /**
     * Liste des promotions
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        return $this->service->index($perPage);
    }

    /**
     * Crée une nouvelle promotion
     */
    public function store(PromotionRequest $request)
    {
        return $this->service->store($request);
    }

    /**
     * Détails d’une promotion
     */
    public function show(Promotion $promotion)
    {
        return $this->service->show($promotion);
    }

    /**
     * Met à jour une promotion
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        return $this->service->update($request, $promotion);
    }


    /**
     * Supprime une promotion
     */
    public function destroy(Promotion $promotion)
    {
        return $this->service->destroy($promotion);
    }
    
    public function toggleStatus(Promotion $promotion)
    {
        return $this->service->toggleStatus($promotion);
    }
}
