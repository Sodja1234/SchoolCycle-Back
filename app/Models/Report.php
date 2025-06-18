<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
     use HasFactory;

    protected $fillable = [
        'user_id',
        'announcement_id',
        'motif',
        'detail',
    ];

    // Relation avec l'utilisateur qui a signalé
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec l'annonce signalée
    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}