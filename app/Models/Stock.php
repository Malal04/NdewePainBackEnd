<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'produit_id',
        'quantite_actuelle',
        'seuil_minimum',
        'statut',
    ];

    protected $casts = [
        'quantite_actuelle' => 'integer',
        'seuil_minimum' => 'integer',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // Met à jour le statut en fonction de la quantité
    public function refreshStatut(): void
    {
        if ($this->quantite_actuelle <= 0) {
            $this->statut = 'out_of_stock';
        } elseif ($this->quantite_actuelle <= $this->seuil_minimum) {
            $this->statut = 'low_stock';
        } else {
            $this->statut = 'in_stock';
        }
        $this->save();
    }
}
