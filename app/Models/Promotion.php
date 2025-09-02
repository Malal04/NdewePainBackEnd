<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';

    protected $fillable = [
        'nom',
        'description',
        'type_remise',
        'valeur_remise',
        'code_promo',
        'conditions',
        'date_debut',
        'date_fin',
        'recurrence',
        'status',
    ];

    protected $casts = [
        'valeur_remise' => 'decimal:2',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    /**
     * Retourne un statut lisible (actif, inactif, à venir, expiré).
     */
    public function getStatutAttribute(): string
    {
        if ($this->status === 'inactive') {
            return 'inactif';
        }
        if ($this->date_debut && now()->lt($this->date_debut)) {
            return 'à venir';
        }
        if ($this->date_fin && now()->gt($this->date_fin)) {
            return 'expiré';
        }
        return 'actif';
    }

    /**
     * Relation Many-to-Many avec Produit
     */
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'promotion_produit')
                    ->withTimestamps();
    }

}
