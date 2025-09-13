<?php

namespace App\Http\Resources\produit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'slug'        => $this->slug,
            'description' => $this->description,
            'status'      => $this->status,
            'produits'    => $this->whenLoaded('produits', function () {
                return $this->produits->map(fn($produit) => [
                    'id'     => $produit->id,
                    'nom'    => $produit->nom,
                    'prix_unitaire'   => number_format($produit->prix, 2) . ' FCFA',
                    'status' => $produit->status,
                ]);
            }),
            'created_at'  => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
