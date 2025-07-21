<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageRessource extends JsonResource
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
            'id' => $this->id,
            'conversation' => $this->conversation,
            'sender' => [
                'id' => $this->sender,
                'name' => $this->senderUser ? $this->senderUser->name : 'Utilisateur supprimé'
            ],
            'receiver' => [
                'id' => $this->receiver,
                'name' => $this->receiverUser ? $this->receiverUser->name : 'Utilisateur supprimé'
            ],
            'content' => $this->content,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('H:i') : null,
        ];
    }
}
