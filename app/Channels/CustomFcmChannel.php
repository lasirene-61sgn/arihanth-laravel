<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class CustomFcmChannel
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $token = $notifiable->routeNotificationForFcm();

        if (!$token) {
            Log::info('FCM skipped: no token for ' . get_class($notifiable) . ' #' . $notifiable->id);
            return;
        }

        $data = $notification->toFcm($notifiable);

        $result = $this->firebaseService->sendNotification(
            $token,
            $data['title'],
            $data['body'],
            $data['data'] ?? []
        );

        // If the token is UNREGISTERED (stale/expired), clear it from the DB
        // so we don't keep hitting FCM with a dead token.
        if (is_array($result) && ($result['unregistered'] ?? false)) {
            try {
                $notifiable->update(['fcm_token' => null]);
                Log::info('Cleared stale FCM token for ' . get_class($notifiable) . ' #' . $notifiable->id);
            } catch (\Exception $e) {
                Log::warning('Could not clear FCM token: ' . $e->getMessage());
            }
        }
    }
}
