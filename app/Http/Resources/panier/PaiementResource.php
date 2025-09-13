<?php

namespace App\Http\Resources\panier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
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
            'commande_id' => $this->commande_id,
            'methode' => $this->methode,
            'statut' => $this->statut,
            'transaction_id' => $this->transaction_id,
            'montant' => $this->montant,
        ];
    }
}
