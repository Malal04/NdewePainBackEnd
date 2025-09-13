<?php

namespace App\Http\Resources\stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockHistoryResource extends JsonResource
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
            'produit_id'    => $this->produit_id,
            'user_id'       => $this->user_id,
            'produit'       => $this->whenLoaded('produit', fn () => [
                'id'          => $this->produit->id,
                'nom'         => $this->produit->nom,
                'slug'        => $this->produit->slug,
            ]),
            'user'          => $this->whenLoaded('user', fn () => [
                'id'          => $this->user->id,
                'nom'         => $this->user->nom,
                'email'       => $this->user->email,
            ]),
            'stock'         => $this->whenLoaded('stock', fn () => [
                'id'              => $this->stock->id,
                'quantite_actuelle' => $this->stock->quantite_actuelle,
                'statut'          => $this->stock->statut,
                'seuil_minimum'   => $this->stock->seuil_minimum,
            ]),
            'type_mouvement'=> $this->type_mouvement,
            'quantite'      => $this->quantite,
            'commentaire'   => $this->commentaire,
            'created_at'    => $this->created_at,
        ];
    }
}
