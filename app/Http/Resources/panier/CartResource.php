<?php

namespace App\Http\Resources\panier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $items = $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'produit_id' => $item->produit_id,
                'nom_produit' => optional($item->produit)->nom,
                'promo_code' => $this->promo_code,
                'slug' => optional($item->produit)->slug,
                'prix_unitaire' => (float) $item->prix_unitaire,
                'quantite' => (int) $item->quantite,
                'total' => (float) $item->total,
                'personnalisation' => $item->personnalisation ? (array) $item->personnalisation : null,
                'photo_url' => optional($item->produit)->photo_url,
                'stock_disponible' => optional($item->produit->stock)->quantite_actuelle ?? null,
            ];
        });

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items' => $items,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
