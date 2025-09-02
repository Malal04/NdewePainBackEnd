<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'produit_id',
        'categorie_id',
        'quantite_vendue',
        'revenu',
        'profit_margin',
    ];

    protected $casts = [
        'quantite_vendue' => 'integer',
        'revenu' => 'decimal:2',
        'profit_margin' => 'decimal:2',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
}
