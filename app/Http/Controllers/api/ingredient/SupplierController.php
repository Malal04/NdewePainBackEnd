<?php

namespace App\Http\Controllers\Api\ingredient;

use App\Http\Controllers\Controller;
use App\Http\Requests\ingredient\SupplierRequest;
use App\Models\Supplier;
use App\Services\ingredient\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $service;

    public function __construct(SupplierService $service)
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
    
    public function store(SupplierRequest $request) 
    {
        return $this->service->store($request); 
    }
    
    public function show(Supplier $supplier) 
    {
        return $this->service->show($supplier); 
    }
    
    public function update(SupplierRequest $request, Supplier $supplier) 
    {
        return $this->service->update($request, $supplier); 
    }
    
    public function destroy(Supplier $supplier) 
    {
        return $this->service->destroy($supplier); 
    }
    
    public function search($search) 
    {
        return $this->service->search($search); 
    }

}
