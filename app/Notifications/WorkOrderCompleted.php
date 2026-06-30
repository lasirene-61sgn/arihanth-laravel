<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\WorkOrder;
use App\Channels\CustomFcmChannel;

class WorkOrderCompleted extends Notification
{
    protected $workOrder;
    protected $customMessage;

    public function __construct(WorkOrder $workOrder, $customMessage = null)
    {
        $this->workOrder = $workOrder;
        $this->customMessage = $customMessage;
    }

    public function via(object $notifiable): array
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable): array
    {
        $isBuyerSide = $notifiable instanceof \App\Models\Buyer
            || $notifiable instanceof \App\Models\KeyUser
            || $notifiable instanceof \App\Models\User;

        $clickAction = $isBuyerSide
            ? route('buyer.work-order.index', ['tab' => 'completed'])
            : route('admin.work-order.index', ['tab' => 'completed']);

        return [
            'title' => 'Work Order Completed',
            'body'  => $this->getMessage($isBuyerSide),
            'data'  => [
                'work_order_id' => (string) $this->workOrder->id,
                'click_action'  => $clickAction,
            ],
        ];
    }

    protected function getMessage(bool $isBuyerSide = false): string
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        return $isBuyerSide
            ? "Your Work Order #{$this->workOrder->work_order_number} has been completed and approved."
            : "Work Order #{$this->workOrder->work_order_number} has been completed by the craftsman.";
    }
}
