<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_report_id',
        'categorie',
        'montant',
        'date_depense',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'date',
    ];

    public function report()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id');
    }
}
