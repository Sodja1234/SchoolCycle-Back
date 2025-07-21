<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ChatStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;
    public $action;

    /**
     * Crée une nouvelle instance d’événement
     */
    public function __construct(Chat $chat, string $action = 'updated')
    {
        // On charge les relations nécessaires si elles ne sont pas encore chargées
        $chat->loadMissing('announcement');
        $this->chat = $chat;
        $this->action = $action;
    }

    /**
     * Les canaux sur lesquels diffuser l'événement
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chat->id),
            new PrivateChannel('user.' . $this->chat->created_by),
            new PrivateChannel('user.' . $this->chat->announcement?->created_by),
        ];
    }

    /**
     * Nom de l'événement côté client
     */
    public function broadcastAs(): string
    {
        return 'chat.status.changed';
    }

    /**
     * Données envoyées avec l’événement
     */
    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chat->id,
            'action' => $this->action,
            'is_closed' => $this->chat->is_closed,
            'closed_at' => optional($this->chat->closed_at)->format('H:i'),
            'close_to' => $this->chat->close_to,
            'announcement_id' => $this->chat->posted_by,
            'created_by' => $this->chat->created_by,
            'updated_at' => $this->chat->updated_at->format('H:i'),
        ];
    }
}
