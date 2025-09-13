<?php

namespace App\Http\Controllers\Api\ingredient;

use App\Http\Controllers\Controller;
use App\Http\Requests\ingredient\IngredientRequest;
use App\Models\Ingredient;
use App\Services\ingredient\IngredientService;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    protected $service;

    public function __construct(IngredientService $service)
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
    
    public function store(IngredientRequest $request) 
    {
        return $this->service->store($request); 
    }
    
    public function show(Ingredient $ingredient) 
    {
        return $this->service->show($ingredient); 
    }
    
    public function update(IngredientRequest $request, Ingredient $ingredient) 
    {
        return $this->service->update($request, $ingredient); 
    }
    
    public function destroy(Ingredient $ingredient) 
    {
        return $this->service->destroy($ingredient); 
    }
    
    public function toggleStatus(Ingredient $ingredient) 
    {
        return $this->service->toggleStatus($ingredient); 
    }
    
    public function search($search) 
    {
        return $this->service->search($search); 
    }

}
