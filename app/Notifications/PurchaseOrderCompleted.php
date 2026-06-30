<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\PurchaseOrder;
use App\Channels\CustomFcmChannel;

class PurchaseOrderCompleted extends Notification
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
        $isCraftsman = $notifiable instanceof \App\Models\Craftman;

        $clickAction = $isCraftsman
            ? route('craftsman.purchase-order.index', ['tab' => 'completed'])
            : route('admin.purchase-order.index', ['tab' => 'completed']);

        return [
            'title' => 'Purchase Order Completed',
            'body'  => $this->getMessage($isCraftsman),
            'data'  => [
                'purchase_order_id' => (string) $this->purchaseOrder->id,
                'click_action'      => $clickAction,
            ],
        ];
    }

    protected function getMessage(bool $isCraftsman = false): string
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        return $isCraftsman
            ? "Purchase Order #{$this->purchaseOrder->purchase_order_code} has been approved by Admin."
            : "Purchase Order #{$this->purchaseOrder->purchase_order_code} has been completed by the craftsman.";
    }
}
