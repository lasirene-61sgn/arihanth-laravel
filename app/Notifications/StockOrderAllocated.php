<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\StockOrder;
use App\Channels\CustomFcmChannel;

class StockOrderAllocated extends Notification
{
    protected $stockOrder;
    protected $customMessage;

    public function __construct(StockOrder $stockOrder, $customMessage = null)
    {
        $this->stockOrder = $stockOrder;
        $this->customMessage = $customMessage;
    }

    public function via(object $notifiable): array
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'New Stock Order Allocated',
            'body'  => $this->getMessage(),
            'data'  => [
                'stock_order_id' => (string) $this->stockOrder->id,
                'click_action'   => 'stock_order_list', // Mobile app can use this to navigate
            ],
        ];
    }

    protected function getMessage(): string
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        return "Stock Order #{$this->stockOrder->order_number} has been allocated to you.";
    }
}
