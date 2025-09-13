<?php

namespace App\Services\stock;

use App\Http\Requests\stock\PromotionRequest;
use App\Http\Requests\stock\UpdatePromotionRequest;
use App\Http\Resources\stock\PromotionResource;
use App\Models\Promotion;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    use ApiResponse;

    public function index($perPage = 15)
    {
        $promotions = Promotion::with('produits')->paginate($perPage);

        return $this->success(
            PromotionResource::collection($promotions),
            'Liste des promotions',
            200,
            $this->meta($promotions)
        );
    }

    public function store($request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $produits = $data['produit_ids'] ?? [];
            unset($data['produit_ids']);

            $promotion = Promotion::create($data);
            $promotion->produits()->sync($produits);

            return $this->success(
                new PromotionResource($promotion->load('produits')),
                'Promotion créée avec succès',
                201
            );
        });
    }

    public function show(Promotion $promotion)
    {
        return $this->success(
            new PromotionResource($promotion->load('produits')),
            "Détails de la promotion #{$promotion->id}"
        );
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        return DB::transaction(function () use ($request, $promotion) {
            $data = $request->validated();
            $produits = $data['produit_ids'] ?? [];
            unset($data['produit_ids']);

            $promotion->update($data);
            $promotion->produits()->sync($produits);

            return $this->success(
                new PromotionResource($promotion->load('produits')),
                "Promotion mise à jour"
            );
        });
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return $this->success(null, "Promotion supprimée");
    }

    public function toggleStatus(Promotion $promotion)
    {
        $promotion->status = $promotion->status === 'active' ? 'inactive' : 'active';
        $promotion->save();

        return $this->success(
            new PromotionResource($promotion),
            "Statut de la promotion #{$promotion->id} mis à jour"
        );
    }


    /**
     * Helper meta pagination
     */
    private function meta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }

}
