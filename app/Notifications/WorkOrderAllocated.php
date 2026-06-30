<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\WorkOrder;

use App\Channels\CustomFcmChannel;

class WorkOrderAllocated extends Notification
{

    protected $workOrder;
    protected $customMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(WorkOrder $workOrder, $customMessage = null)
    {
        $this->workOrder = $workOrder;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CustomFcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'work_order_number' => $this->workOrder->work_order_number,
            'message' => $this->getMessage(),
            'type' => 'allocation'
        ];
    }
    
    public function toFcm($notifiable)
    {
        return [
            'title' => 'New Work Order Allocated',
            'body' => $this->getMessage(),
            'data' => [
                'work_order_id' => (string)$this->workOrder->id,
                'click_action' => route('craftsman.work-order.index', ['tab' => 'new-orders'])
            ]
        ];
    }

    protected function getMessage()
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }
        return "Work Order #{$this->workOrder->work_order_number} has been allocated to you.";
    }
}
