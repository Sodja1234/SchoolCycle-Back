<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        Carbon::setLocale('fr');
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'description'=>$this->description,
            'operation_type'=>$this->operation_type,
            'price'=>$this->price,
            'state'=>$this->state,
            'is_completed'=>$this->is_completed,
            'is_cancelled'=>$this->is_cancelled,
            'exchange_location_address'=>$this->exchange_location_address,
            'exchange_location_lng'=>$this->exchange_location_lng,
            'exchange_location_lat'=>$this->exchange_location_lat,
            'category' => new CategoryResource($this->category),
            'photos'=>$this->photos,
            'created_by'=> new UserResource($this->user),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans()
        ];
    }
}
