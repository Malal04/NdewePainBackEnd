<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    // Stock status constants
    const IN_STOCK = 'in_stock';
    const LOW_STOCK = 'low_stock';
    const OUT_OF_STOCK = 'out_of_stock';

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
            $this->statut = Stock::OUT_OF_STOCK;
        } elseif ($this->quantite_actuelle <= $this->seuil_minimum) {
            $this->statut = Stock::LOW_STOCK;
        } else {
            $this->statut = Stock::IN_STOCK;
        }
        $this->save();
    }
}
