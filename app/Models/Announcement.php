<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @OA\Schema(
 *     schema="Announcement",
 *     type="object",
 *     title="Annonce",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Sac à dos blanc"),
 *     @OA\Property(property="description", type="string", example="Sac à dos blanc pour ecolier, avec trois poche "),
 *     @OA\Property(property="price", type="number", format="float", example=750.50),
 *     @OA\Property(property="operation type", type="string",  example="evente"),
 *     @OA\Property(property="state", type="string",  example="new"),
 *     @OA\Property(property="category", type="object",
 *         @OA\Property(property="id", type="integer", example=3),
 *         @OA\Property(property="name", type="string", example="Immobilier")
 *     ),
 *     @OA\Property(property="photos", type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="url", type="string", example="http://example.com/photo.jpg")
 *         )
 *     ),
 *     @OA\Property(property="user", type="object",
 *         @OA\Property(property="id", type="integer", example=4),
 *         @OA\Property(property="name", type="string", example="Alain Katayi")
 *     )
 * )
 */

class Announcement extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable =[
        'title',
        'description',
        'category_id',
        'operation_type',
        'state',
        'price',
        'is_completed',
        'is_cancelled',
        'exchange_location_address',
        'exchange_location_lng',
        'exchange_location_lat',
        'created_by',

    ];
    public function user():BelongsTo{
        return $this->belongsTo(User::class,'created_by');
    }
    public function category():BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function favorites(){
        return $this->hasMany(Favorite::class);
    }

    public function photos(){
        return $this->hasMany(Photo::class);
    }

    public  function chats()
    {
        return $this->hasMany(Chat::class, 'posted_by');
    }

}
