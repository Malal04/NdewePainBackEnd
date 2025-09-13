<?php

namespace App\Http\Controllers\Api\stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\stock\StockMoveRequest;
use App\Http\Requests\stock\StockRequest;
use App\Models\Stock;
use App\Services\stock\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected $service;

    public function __construct(StockService $service)
    {
        $this->service = $service;
    }

       /** Liste paginée */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /** Détails */
    public function show(Stock $stock)
    {
        return $this->service->show($stock);
    }

    /** Création */
    public function store(StockRequest $request)
    {
        return $this->service->store($request);
    }

    /** Mise à jour */
    public function update(StockRequest $request, Stock $stock)
    {
        return $this->service->update($request, $stock);
    }

    /** Suppression */
    public function destroy(Stock $stock)
    {
        return $this->service->destroy($stock);
    }

    /** Mouvement de stock (entrée, sortie, ajustement) */
    public function move(StockMoveRequest $request)
    {
        return $this->service->move($request);
    }

    /** Historique des mouvements d’un produit */
    public function history($produitId, Request $request)
    {
        return $this->service->history($produitId, $request);
    }
     
}
