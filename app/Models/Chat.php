<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'created_by',
        'posted_by',
        'is_closed',
        'closed_at',
        'close_to',

    ];

    public function user()
    {
        return $this -> belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this -> hasMany(Message::class, 'conversation');
    }
}
