<?php

namespace App\Http\Resources\ingredient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ingredient\IngredientResource;
use App\Http\Resources\ingredient\SupplierResource;

class IngredientOrderResource extends JsonResource
{
    
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ingredient' => new IngredientResource($this->whenLoaded('ingredient')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'quantite' => $this->quantite,
            'statut' => $this->statut,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
