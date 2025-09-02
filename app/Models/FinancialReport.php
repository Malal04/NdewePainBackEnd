<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'periode',
        'date_debut',
        'date_fin',
        'revenus_totaux',
        'depenses_totales',
        'profit_total',
    ];

    protected $casts = [
        'revenus_totaux' => 'decimal:2',
        'depenses_totales' => 'decimal:2',
        'profit_total' => 'decimal:2',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
