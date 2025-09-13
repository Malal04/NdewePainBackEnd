<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'status',
    ];

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'chat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
