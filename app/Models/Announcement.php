<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Category;

class Announcement extends Model
{
    protected $fillable =[
        'title',
        'description',
        'operation_type',
        'price',
        'is_completed',
        'is_cancelled',
        'exchange_location_address',
        'exchange_location_lng',
        'exchange_location_lat',
        'create_by',
        
    ];
    public function users():BelongsTo{
        return $this->belongsTo(User::class);
    }

}
