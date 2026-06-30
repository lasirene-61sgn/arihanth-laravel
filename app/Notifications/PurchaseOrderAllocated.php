<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\PurchaseOrder;
use App\Channels\CustomFcmChannel;

class PurchaseOrderAllocated extends Notification
{
    protected $purchaseOrder;
    protected $customMessage;

    public function __construct(PurchaseOrder $purchaseOrder, $customMessage = null)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->customMessage = $customMessage;
    }

    public function via(object $notifiable): array
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'New Purchase Order Allocated',
            'body'  => $this->getMessage(),
            'data'  => [
                'purchase_order_id' => (string) $this->purchaseOrder->id,
                'click_action'      => route('craftsman.purchase-order.index', ['tab' => 'allocated']),
            ],
        ];
    }

    protected function getMessage(): string
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        return "Purchase Order #{$this->purchaseOrder->purchase_order_code} has been allocated to you.";
    }
}
