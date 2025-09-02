<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'produit_id',
        'quantite',
        'deadline',
        'assigned_to',
        'statut',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'deadline' => 'date',
    ];

    /**
     * La tâche appartient à un produit
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    /**
     * La tâche est assignée à un employé
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
