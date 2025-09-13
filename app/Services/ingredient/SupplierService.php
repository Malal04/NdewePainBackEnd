<?php

namespace App\Services\ingredient;

use App\Http\Requests\ingredient\SupplierRequest;
use App\Models\Supplier;
use App\Http\Resources\ingredient\SupplierResource;

class SupplierService
{
    public function index()
    {
        $suppliers = Supplier::all();
        return response()->json([
            'status' => true,
            'message' => 'Liste des fournisseurs récupérée avec succès.',
            'data' => SupplierResource::collection($suppliers),
        ]);
    }

    public function indexPagination()
    {
        $suppliers = Supplier::paginate(3);

        return response()->json([
            'status' => true,
            'data' => SupplierResource::collection($suppliers),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'per_page'     => $suppliers->perPage(),
                'total'        => $suppliers->total(),
            ],
        ]);
    }

    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Fournisseur ajouté avec succès.',
            'data' => new SupplierResource($supplier),
        ], 201);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Fournisseur mis à jour avec succès.',
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'status' => true,
            'message' => 'Fournisseur supprimé avec succès.',
        ]);
    }

    public function search($search, $perPage = 5)
    {
        $suppliers = Supplier::with('ingredient')
            ->where('nom', 'like', "%{$search}%")
            ->orWhere('contact_person', 'like', "%{$search}%")
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Recherche des fournisseurs effectuée avec succès.',
            'data' => SupplierResource::collection($suppliers),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'per_page'     => $suppliers->perPage(),
                'total'        => $suppliers->total(),
            ],
        ]);
    }

    public function show(Supplier $supplier)
    {
        return response()->json([
            'status' => true,
            'message' => 'Fournisseur récupéré avec succès.',
            'data' => new SupplierResource($supplier),
        ]);
    }

}
