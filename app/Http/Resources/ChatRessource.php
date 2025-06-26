<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' =>$this->created_by,
            'posted_by'=>[
                'id' => $this->announcement->user->id,
                'title' => $this->announcement->title,
                'created_by' => $this->announcement->user->name,
            ],
            'is_closed' => $this->is_closed,
            'closed_at' => $this->closed_at,
            'close_to' => $this->close_to,
            'messages' => MessageRessource::collection($this->messages)
        ];
    }
}
