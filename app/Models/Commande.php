<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Commande extends Model
{
    use HasFactory;

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_CONFIRMEE  = 'confirmee';
    const STATUT_EN_COURS   = 'en_cours';
    const STATUT_LIVREE     = 'livree';
    const STATUT_ANNULEE    = 'annulee';

    protected $fillable = [
        'user_id',
        'code_commande',
        'mode_livraison',
        'sous_total',
        'frais_livraison',
        'remise',
        'total',
        'plage_horaire',
        'statut_commande',
        'adresse_id'
    ];

    protected static function booted()
    {
        static::creating(function (Commande $commande) {
            if (empty($commande->code_commande)) {
                // Format : CMD-YYYYMMDD-HASH
                $commande->code_commande = 'CMD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function articlesCommande()
    {
        return $this->hasMany(ArticleCommande::class);
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class);
    }

    public function adresse()
    {
        return $this->belongsTo(Addresses::class, 'adresse_id');
    }
}
