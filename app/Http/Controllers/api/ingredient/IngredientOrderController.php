<?php

namespace App\Http\Controllers\Api\ingredient;

use App\Http\Controllers\Controller;
use App\Http\Requests\ingredient\IngredientOrderRequest;
use App\Models\IngredientOrder;
use App\Services\ingredient\IngredientOrderService;
use Illuminate\Http\Request;

class IngredientOrderController extends Controller
{
    protected $service;

    public function __construct(IngredientOrderService $service)
    {
        $this->service = $service;
    }

    public function index()      
    {
        return $this->service->index(); 
    }

    public function indexPagination()      
    {
        return $this->service->indexPagination(); 
    }

    public function store(IngredientOrderRequest $request) 
    {
        return $this->service->store($request); 
    }

    public function show(IngredientOrder $order) 
    {
        return $this->service->show($order); 
    }
    
    public function update(IngredientOrderRequest $request, IngredientOrder $order) 
    {
        return $this->service->update($request, $order); 
    }
    
    public function destroy(IngredientOrder $order) 
    {
        return $this->service->destroy($order); 
    }

    public function markAsDelivered(IngredientOrder $order) 
    {
        return $this->service->markAsDelivered($order); 
    }
    
    public function markAsCancelled(IngredientOrder $order) 
    {
        return $this->service->markAsCancelled($order); 
    }
    
    public function search($search) 
    {
        return $this->service->search($search); 
    }

}
