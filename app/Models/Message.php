<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'id',
        'conversation', // fait référence à la table chat
        'sender',
        'receiver',
        'content',
    ];

    public function chat()
    {
        return $this ->belongsTo(Chat::class, 'conversation');
    }

    public function senderUser()
    {
        return $this -> belongsTo(User::class, 'sender');
    }

    public function receiverUser()
    {
        return $this -> belongsTo(User::class, 'receiver');
    }
}
