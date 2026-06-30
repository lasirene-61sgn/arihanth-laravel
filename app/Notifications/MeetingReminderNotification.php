<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\CustomFcmChannel;
use App\Models\Meeting;

class MeetingReminderNotification extends Notification
{
    use Queueable;

    protected $meeting;
    protected $minutes;
    protected $token;
    protected $appId;

    public function __construct(Meeting $meeting, $minutes = 5, $token = null, $appId = null)
    {
        $this->meeting = $meeting;
        $this->minutes = $minutes;
        $this->token = $token;
        $this->appId = $appId;
    }

    public function via($notifiable)
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        // Determine the other party
        $isHost = ($this->meeting->host_id == $notifiable->id && $this->meeting->host_type == get_class($notifiable));
        $otherParty = $isHost ? $this->meeting->participant : $this->meeting->host;
        $callerName = $this->getFormattedCallerName($otherParty);

        return [
            'title' => 'Meeting Reminder',
            'body' => 'Your meeting is starting in ' . $this->minutes . ' minute' . ($this->minutes > 1 ? 's' : '') . '.',
            'data' => [
                'meeting_id' => (string) $this->meeting->id,
                'room_id' => $this->meeting->room_id,
                'action' => 'join_meeting',
                'app_id' => $this->appId,
                'channel_name' => $this->meeting->room_id,
                'caller_name' => $callerName,
                'token' => $this->token,
                'uid' => (string) $notifiable->id,
            ]
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
            
            // Format ProcessOwner clearly: "Code - Name (Role)"
            $formattedName = $name;
            if ($roleLabel) {
                $formattedName .= " ($roleLabel)";
            }

            if ($code && $formattedName) {
                return "$code - $formattedName";
            }
            return $code ?: ($formattedName ?: 'Admin');
        } else {
            return method_exists($caller, 'getNameAttribute') ? $caller->name : ($caller->full_name ?? ($caller->business_name ?? 'Participant'));
        }

        if ($code && $name) {
            return "$code - $name";
        }
        
        return $code ?: ($name ?: 'Participant');
    }
}