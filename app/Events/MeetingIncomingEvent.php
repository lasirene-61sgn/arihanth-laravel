<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingIncomingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meeting_id;
    public $room_id;
    public $caller_name;
    public $category;

    /**
     * Create a new event instance.
     */
    public function __construct($meeting_id, $room_id, $caller_name, $category = null)
    {
        $this->meeting_id = $meeting_id;
        $this->room_id = $room_id;
        $this->caller_name = $caller_name;
        $this->category = $category;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-meetings'),
        ];
    }
    
    public function broadcastAs()
    {
        return 'MeetingIncomingEvent';
    }
}
