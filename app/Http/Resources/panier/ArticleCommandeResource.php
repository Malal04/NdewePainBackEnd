<?php

namespace App\Http\Resources\panier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCommandeResource extends JsonResource
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
            'produit' => [
                'id' => $this->produit_id,
                'nom' => $this->produit->nom ?? null,
                'prix_unitaire' => $this->prix_unitaire,
                'photo_url' => $this->produit->photo_url ?? null,
            ],
            'quantite' => $this->quantite,
            'prix_unitaire' => $this->prix_unitaire,
            'total' => $this->total,
            'personnalisation' => $this->personnalisation,
        ];
    }
}
