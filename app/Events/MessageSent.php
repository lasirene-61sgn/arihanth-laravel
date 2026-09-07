<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');
    }

    public function broadcastOn(): array
    {
        $conversation = \App\Models\Conversation::find($this->message->conversation_id);
        $channels = [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];

        if ($conversation) {
            $receiverId = $conversation->sender_id === $this->message->sender_id ? $conversation->receiver_id : $conversation->sender_id;
            $receiverType = $conversation->sender_type === $this->message->sender_type ? $conversation->receiver_type : $conversation->sender_type;

            $shortType = strtolower(class_basename($receiverType));
            $channels[] = new PrivateChannel('user.' . $shortType . '.' . $receiverId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => (int) $this->message->id,
            'conversation_id' => (int) $this->message->conversation_id,
            'sender_id'       => (int) $this->message->sender_id,
            'sender_type'     => $this->message->sender_type,
            'body'            => $this->message->body,
            'sender_name'     => $this->message->sender->name ?? 'User',
            'created_at'      => optional($this->message->created_at)->toISOString() ?? now()->toISOString(),
        ];
    }
}