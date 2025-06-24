<?php

namespace App\Http\Resources;

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
            'created_at' => $this->created_at,
        ];
    }
}
