<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'type', 
        'text',
    ];

    public function chat()
    {
        return $this->belongsTo(SupportChat::class, 'chat_id');
    }
}
