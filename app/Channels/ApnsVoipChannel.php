<?php

namespace App\Channels;

use App\Services\ApnsVoipService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ApnsVoipChannel
{
    protected ApnsVoipService $apnsService;

    public function __construct(ApnsVoipService $apnsService)
    {
        $this->apnsService = $apnsService;
    }

    /**
     * Send the notification via Apple APNs VoIP service.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $voipToken = $notifiable->voip_token ?? null;

        if (empty($voipToken)) {
            return;
        }

        // Get payload: either through toVoip() or fallback to toFcm()['data']
        $payload = [];
        if (method_exists($notification, 'toVoip')) {
            $payload = $notification->toVoip($notifiable);
        } elseif (method_exists($notification, 'toFcm')) {
            $fcmData = $notification->toFcm($notifiable);
            $payload = $fcmData['data'] ?? [];
            if (!empty($fcmData['title']) && empty($payload['title'])) {
                $payload['title'] = $fcmData['title'];
            }
        }

        if (empty($payload)) {
            return;
        }

        $result = $this->apnsService->sendVoipNotification($voipToken, $payload);

        // If Apple returns BadDeviceToken or Unregistered, automatically clear dead token
        if (isset($result['reason']) && in_array($result['reason'], ['BadDeviceToken', 'Unregistered'], true)) {
            try {
                $notifiable->update(['voip_token' => null]);
                Log::info('Cleared stale VoIP token for ' . get_class($notifiable) . ' #' . $notifiable->id);
            } catch (\Exception $e) {
                Log::warning('Could not clear stale VoIP token: ' . $e->getMessage());
            }
        }
    }
}
