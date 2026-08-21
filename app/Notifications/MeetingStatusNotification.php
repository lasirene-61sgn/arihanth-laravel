<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\CustomFcmChannel;
use App\Models\Meeting;
use Illuminate\Support\Facades\Log;

class MeetingStatusNotification extends Notification
{
    use Queueable;

    protected $meeting;
    protected $status;
    protected $agoraDetails;

    public function __construct(Meeting $meeting, $status, $agoraDetails = [])
    {
        $this->meeting = $meeting;
        $this->status = $status;
        $this->agoraDetails = $agoraDetails;
    }

    public function via($notifiable)
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        // 1. Memory Guard: Prevent duplicate runs during a single execution cycle
        static $processedNotifications = [];

        // Create a unique fingerprint based on User ID, Meeting ID, and Status
        $fingerprint = md5($notifiable->id . '_' . $this->meeting->id . '_' . $this->status);

        if (isset($processedNotifications[$fingerprint])) {
            Log::warning('FCM Duplicate Aborted: toFcm() called multiple times for same event.', [
                'user_id'    => $notifiable->id,
                'meeting_id' => $this->meeting->id,
                'status'     => $this->status
            ]);
            
            // Return an empty array or signal payload to abort processing
            return [];
        }

        // Mark this payload fingerprint as built
        $processedNotifications[$fingerprint] = true;

        // 2. Build the payload
        $title = 'Meeting Update';
        $body = '';

        $effectiveStatus = $this->status;
        if ($this->status === 're_ring') {
            $effectiveStatus = 'approved';
        }

        if ($this->status === 're_ring') {
            $title = 'Incoming Call';
            $body = 'Incoming video call...';
        } elseif ($this->status === 'approved') {
            $body = 'Your meeting has been accepted.';
        } elseif ($this->status === 'cancelled') {
            $body = 'Your meeting has been rejected.';
        } elseif ($this->status === 'joined') {
            $body = 'Your meeting partner has joined the call.';
        } else {
            $body = 'Your meeting status has been updated to ' . $this->status;
        }

        $data = [
            'meeting_id'  => (string) $this->meeting->id,
            'room_id'     => $this->meeting->room_id,
            'type'        => '1',
            'isVideo'     => 'true',
            'call_type'   => 'video',
            'is_video'    => 'true',
        ];

        if ($effectiveStatus !== 'joined') {
            $data['status'] = $effectiveStatus;
        }

        // Add specific action for joined status
        if ($this->status === 'joined' || $this->status === 're_ring') {
            $data['action'] = 'join_meeting';
        }

        if (!empty($this->agoraDetails)) {
            foreach ($this->agoraDetails as $key => $value) {
                $data[$key] = (string) $value;
            }
        }
        
        // Ensure UID is set for join_meeting action if not provided in agoraDetails
        if ($this->status === 'joined' && !isset($data['uid'])) {
             $data['uid'] = (string) $notifiable->id;
        }

        // Ensure caller_name is always included
        if (!isset($data['caller_name'])) {
            $isHost = ($this->meeting->host_id == $notifiable->id && $this->meeting->host_type == get_class($notifiable));
            $otherParty = $isHost ? $this->meeting->participant : $this->meeting->host;
            $data['caller_name'] = $this->getFormattedCallerName($otherParty);
        }

        return [
            'title' => $title,
            'body'  => $body,
            'data'  => $data
        ];
    }

    /**
     * Get a formatted caller name (Code - Name) for notifications.
     */
    private function getFormattedCallerName($caller)
    {
        if (!$caller) return 'Participant';

        $code = '';
        $name = '';

        if ($caller instanceof \App\Models\Buyer) {
            $code = $caller->bp_code ?? '';
            $name = $caller->name ?? ($caller->full_name ?? ($caller->business_name ?? 'Buyer'));
        } elseif ($caller instanceof \App\Models\Craftman) {
            $code = $caller->craftman_code ?? ($caller->craftsman_code ?? '');
            $name = $caller->name ?? ($caller->full_name ?? ($caller->business_name ?? 'Craftsman'));
        } elseif ($caller instanceof \App\Models\ProcessOwner) {
            $code = $caller->user_code ?? '';
            $name = $caller->full_name ?? ($caller->name ?? 'Admin');
            
            $isSuper = isset($caller->role) && ($caller->role === 'superadmin' || $caller->role === 'super_admin');
            if ($isSuper) {
                $roleLabel = 'Superadmin';
            } else {
                $roleLabel = $caller->category ? $caller->category : 'Admin';
            }
            
            $parts = array_filter([$code, $name, $roleLabel]);
            return implode(' - ', $parts);
        } else {
            return method_exists($caller, 'getNameAttribute') ? $caller->name : ($caller->full_name ?? ($caller->business_name ?? 'Participant'));
        }

        if ($code && $name) {
            return "$code - $name";
        }
        
        return $code ?: ($name ?: 'Participant');
    }
}