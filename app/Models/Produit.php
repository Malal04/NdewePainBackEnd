<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;
    protected $table = 'produits';

    protected $fillable = [
        'categorie_id',
        'nom',
        'slug',
        'description',
        'prix_unitaire',
        'photo_url',
        'stock',
        'status',
        'allergenes',
    ];

    protected $casts = [
        'allergenes' => 'array',
        'prix_unitaire' => 'decimal:2',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    /**
     * Relation Many-to-Many avec Promotion
     */
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_produit')
                    ->withTimestamps();
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function productionTasks()
    {
        return $this->hasMany(ProductionTask::class);
    }


}
