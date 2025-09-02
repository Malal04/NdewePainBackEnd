<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionProduit extends Model
{
    use HasFactory;

    protected $table = 'promotion_produit';

    protected $fillable = [
        'promotion_id',
        'produit_id',
    ];
}
