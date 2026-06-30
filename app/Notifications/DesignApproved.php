<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Product;
use App\Channels\CustomFcmChannel;

class DesignApproved extends Notification
{
    protected $product;
    protected $adminName;

    public function __construct(Product $product, $adminName = 'Admin')
    {
        $this->product   = $product;
        $this->adminName = $adminName;
    }

    public function via(object $notifiable): array
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable): array
    {
        $isCraftsman = $notifiable instanceof \App\Models\Craftman;

        $clickAction = $isCraftsman
            ? route('craftsman.design.index', ['tab' => 'accepted'])
            : route('buyer.design.index', ['tab' => 'accepted']);

        return [
            'title' => 'Design Approved',
            'body'  => "Your design {$this->product->product_name} has been approved by {$this->adminName}.",
            'data'  => [
                'product_id'   => (string) $this->product->id,
                'design_code'  => (string) $this->product->design_code,
                'click_action' => $clickAction,
            ],
        ];
    }
}
