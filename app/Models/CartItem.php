<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
        'total',
        'personnalisation'
    ];

    protected $casts = [
        'personnalisation' => 'array'
    ];

    protected static function booted()
    {
        static::saving(function (CartItem $item) {
            if ($item->prix_unitaire && $item->quantite) {
                $item->total = $item->prix_unitaire * $item->quantite;
            }
        });
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
    
}
