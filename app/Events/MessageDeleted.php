<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $conversationId;
    public $chatGroupId;

    public function __construct($messageId, $conversationId, $chatGroupId = null)
    {
        $this->messageId = $messageId;
        $this->conversationId = $conversationId;
        $this->chatGroupId = $chatGroupId;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        if ($this->conversationId) {
            $channels[] = new PrivateChannel('conversation.' . $this->conversationId);
        }
        if ($this->chatGroupId) {
            $channels[] = new PrivateChannel('group.' . $this->chatGroupId);
        }
        return $channels;
    }
    
    public function broadcastAs()
    {
        return 'message.deleted';
    }
}
