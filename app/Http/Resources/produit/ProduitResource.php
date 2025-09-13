<?php

namespace App\Http\Resources\produit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\produit\CategorieResource;

class ProduitResource extends JsonResource
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
            'prix_unitaire'=> $this->prix_unitaire,
            'photo_url'   => $this->photo_url ? asset('storage/'.$this->photo_url) : null,
            'status'      => $this->status,
            'allergenes'  => $this->allergenes,
            'stock'       => $this->stock,
            'categorie'   => new CategorieResource(
                $this->whenLoaded('categorie')
            ),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
