<?php

namespace App\Http\Resources\stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\stock\StockHistoryResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'produit_id'       => $this->produit_id,
            'produit'          => $this->whenLoaded('produit', fn () => [
                'id'   => $this->produit->id,
                'nom'  => $this->produit->nom,
                'slug' => $this->produit->slug,
            ]),
            'quantite_actuelle'=> $this->quantite_actuelle,
            'seuil_minimum'    => $this->seuil_minimum,
            'statut'           => $this->statut,
            'updated_at'       => $this->updated_at,
        ];
    }
}
