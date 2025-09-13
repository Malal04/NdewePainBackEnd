<?php

namespace App\Http\Resources\stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
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
            'nom' => $this->nom,
            'description' => $this->description,
            'type_remise' => $this->type_remise,
            'valeur_remise' => $this->valeur_remise,
            'code_promo' => $this->code_promo,
            'conditions' => $this->conditions,
            'date_debut' => optional($this->date_debut)->toDateString(),
            'date_fin' => optional($this->date_fin)->toDateString(),
            'recurrence' => $this->recurrence,
            'status' => $this->status,
            'is_currently_valid' => $this->is_currently_valid,
            'produits' => $this->whenLoaded('produits', fn() => $this->produits->pluck('id')),
            'created_at' => $this->created_at,
        ];
    }
}
