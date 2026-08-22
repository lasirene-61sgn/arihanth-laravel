<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageDelivered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $userId;
    public $userType;
    public $deliveredAt;
    public $conversationId;

    public function __construct(Message $message, $userId, $userType, $deliveredAt)
    {
        $this->messageId = $message->id;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->deliveredAt = $deliveredAt;
        $this->conversationId = $message->conversation_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }
    
    public function broadcastAs()
    {
        return 'message.delivered';
    }
}
