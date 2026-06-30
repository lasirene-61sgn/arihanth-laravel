<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Repair;
use App\Channels\CustomFcmChannel;

class RepairCompleted extends Notification
{
    protected $repair;
    protected $customMessage;

    public function __construct(Repair $repair, $customMessage = null)
    {
        $this->repair = $repair;
        $this->customMessage = $customMessage;
    }

    public function via(object $notifiable): array
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Repair Completed',
            'body'  => $this->getMessage(),
            'data'  => [
                'repair_id'    => (string) $this->repair->id,
                'click_action' => 'repair_list',
            ],
        ];
    }

    protected function getMessage(): string
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        return "Repair for product {$this->repair->product_name} has been completed.";
    }
}
