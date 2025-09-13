<?php

namespace App\Http\Resources\panier;

use App\Http\Resources\adresse\AdresseResource;
use App\Http\Resources\auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandeResource extends JsonResource
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
            'code_commande' => $this->code_commande,
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->user),
            'mode_livraison' => $this->mode_livraison,
            'sous_total' => $this->sous_total,
            'frais_livraison' => $this->frais_livraison,
            'remise' => $this->remise,
            'total' => $this->total,
            'plage_horaire' => $this->plage_horaire,
            'statut_commande' => $this->statut_commande,
            'articles' => ArticleCommandeResource::collection($this->whenLoaded('articlesCommande')),
            'paiement' => new PaiementResource($this->whenLoaded('paiement')),
            'adresse' => $this->whenLoaded('adresse') ? [
                'id' => $this->adresse->id,
                'ligne_adresse' => $this->adresse->ligne_adresse,
                'ville' => $this->adresse->ville,
            ] : null,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
