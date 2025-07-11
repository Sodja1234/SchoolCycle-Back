<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'id'         => $this->id,
            'motif'      => $this->motif,
            'detail'     => $this->detail,
            'user' => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
            'announcement' => [
                'id'    => $this->announcement->id,
                'title' => $this->announcement->title,
            ],
            'created_at' =>  Carbon::parse($this->created_at)->diffForHumans(),
        ];
    }
}
