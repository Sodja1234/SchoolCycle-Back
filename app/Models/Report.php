<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
     use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'announcement_id',
        'motif',
        'detail',
        'created_at'
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