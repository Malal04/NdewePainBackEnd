<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'quantite',
        'unite',
        'seuil_reappro',
        'supplier_id',
        'statut',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'seuil_reappro' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orders()
    {
        return $this->hasMany(IngredientOrder::class);
    }

    /**
     * Met à jour le statut en fonction de la quantité et du seuil
     */
    public function refreshStatut(): void
    {
        if ($this->quantite <= 0) {
            $this->statut = 'out';
        } elseif ($this->quantite <= $this->seuil_reappro) {
            $this->statut = 'low';
        } else {
            $this->statut = 'ok';
        }
        $this->save();
    }
}
