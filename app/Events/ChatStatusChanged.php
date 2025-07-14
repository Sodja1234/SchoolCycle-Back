<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;
    public $action; // 'closed', 'reopened', etc.

    /**
     * Create a new event instance.
     */
    public function __construct(Chat $chat, string $action = 'updated')
    {
        $this->chat = $chat;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chat->id),
            new PrivateChannel('user.' . $this->chat->created_by),
            new PrivateChannel('user.' . $this->chat->announcement->created_by),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chat->id,
            'action' => $this->action,
            'is_closed' => $this->chat->is_closed,
            'closed_at' => $this->chat->closed_at?->toISOString(),
            'close_to' => $this->chat->close_to?->toISOString(),
            'announcement_id' => $this->chat->posted_by,
            'created_by' => $this->chat->created_by,
            'updated_at' => $this->chat->updated_at->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'chat.status.changed';
    }
} 