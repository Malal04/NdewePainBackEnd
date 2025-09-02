<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'contact_person',
        'email',
        'telephone',
        'adresse',
    ];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function orders()
    {
        return $this->hasMany(IngredientOrder::class);
    }
}
