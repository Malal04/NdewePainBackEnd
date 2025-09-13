<?php

namespace App\Http\Resources\ingredient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'nom'            => $this->nom,
            'contact_person' => $this->contact_person,
            'email'          => $this->email,
            'telephone'      => $this->telephone,
            'adresse'        => $this->adresse,
            'created_at'     => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
