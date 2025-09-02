<?php

namespace App\Http\Resources\adresse;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AdresseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();

        $data = [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'ligne_adresse' => $this->ligne_adresse,
            'ville'         => $this->ville,
            'code_postal'   => $this->code_postal,
            'pays'          => $this->pays,
            'est_principale'=> (bool) $this->est_principale,
            'type'          => $this->type,
            'mode_livraison'=> $this->mode_livraison,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];

        if ($user && in_array($user->role, ['admin', 'gerant', 'employe'])) {
            $data['user'] = [
                'id'    => $this->user->id ?? null,
                'nom'   => $this->user->nom ?? null,
                'email' => $this->user->email ?? null,
                'phone' => $this->user->phone ?? null,
                'role'  => $this->user->role ?? null,
            ];
        }

        return $data;
    }
}
