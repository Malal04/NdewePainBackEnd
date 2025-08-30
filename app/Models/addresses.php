<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class addresses extends Model
{
    protected $fillable = [
        'user_id',
        'ligne_adresse',
        'ville',
        'code_postal',
        'pays',
        'est_principale',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
