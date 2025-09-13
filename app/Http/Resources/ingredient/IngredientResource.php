<?php

namespace App\Http\Resources\ingredient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ingredient\SupplierResource;

class IngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'nom'           => $this->nom,
            'quantite'      => $this->quantite,
            'seuil_reappro' => $this->seuil_reappro,
            'unite'         => $this->unite,
            'statut'        => $this->statut,
            'supplier'      => new SupplierResource(
                $this->whenLoaded('supplier')
            ),
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
