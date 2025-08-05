<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Papeterie et feuille"),
 *     @OA\Property(property="description", type="string", example="Feuille, papier,etc"),
 *     @OA\Property(property="photo", type="string", example="image.png"),
 * )
 */


class Category extends Model
{
    use  HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'name',
        'description',
        'photo'
    ];

    public function announcements(){
        return $this->hasMany(Announcement::class);
    }

    public function preferences(){
        return $this->belongsToMany(User::class,'preferences','category_id','created_by');
    }
}
