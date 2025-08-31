<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    protected $fillable = [
        'user_id',
        'ligne_adresse',
        'ville',
        'code_postal',
        'pays',
        'est_principale',
        'type',
        'mode_livraison',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
