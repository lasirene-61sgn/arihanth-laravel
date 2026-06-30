<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingAnsweredEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meeting_id;
    public $answered_by_admin_id;

    /**
     * Create a new event instance.
     */
    public function __construct($meeting_id, $answered_by_admin_id)
    {
        $this->meeting_id = $meeting_id;
        $this->answered_by_admin_id = $answered_by_admin_id;
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
        return 'MeetingAnsweredEvent';
    }
}
