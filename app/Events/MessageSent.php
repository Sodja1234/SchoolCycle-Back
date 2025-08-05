<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        // Le message doit déjà être chargé avec ses relations
        $message->load('senderUser', 'receiverUser');
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Canal attendu : chat.{conversation_id}
        return new PrivateChannel('chat.' . $this->message->conversation);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'conversation' => $this->message->conversation,
            'sender' => [
                'id' => $this->message->senderUser->id ?? null,
                'name' => $this->message->senderUser->name ?? 'Inconnu'
            ],
            'receiver' => [
                'id' => $this->message->receiverUser->id ?? null,
                'name' => $this->message->receiverUser->name ?? 'Inconnu'
            ],
            'is_read' => $this->message->is_read,
            'created_at' => $this->message->created_at->format('H:i'),
            'updated_at' => $this->message->updated_at->format('H:i'),
        ];
    }
}
