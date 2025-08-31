<?php

namespace App\Http\Resources\auth;

use App\Http\Resources\adresse\AdresseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nom'             => $this->nom,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'role'            => $this->role,
            'profile'         => $this->profile ? asset('storage/' . $this->profile) : null,
            'accountState'    => $this->accountState,
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'      => $this->updated_at?->format('Y-m-d H:i:s'),
            'adresses'        => AdresseResource::collection(
                $this->whenLoaded('addresses')
            ),
        ];
    }
}
