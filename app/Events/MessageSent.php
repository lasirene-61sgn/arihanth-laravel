<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'attachments']);
    }

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->message->conversation_id) {
            $channels[] = new PrivateChannel('conversation.' . $this->message->conversation_id);

            $conversation = \App\Models\Conversation::find($this->message->conversation_id);
            if ($conversation) {
                $receiverId = ($conversation->sender_id == $this->message->sender_id && $conversation->sender_type == $this->message->sender_type)
                    ? $conversation->receiver_id
                    : $conversation->sender_id;

                $receiverType = ($conversation->sender_id == $this->message->sender_id && $conversation->sender_type == $this->message->sender_type)
                    ? $conversation->receiver_type
                    : $conversation->sender_type;

                if ($receiverType && $receiverId) {
                    $shortType = strtolower(class_basename($receiverType));
                    $channels[] = new PrivateChannel('user.' . $shortType . '.' . $receiverId);
                }
            }
        }

        if ($this->message->chat_group_id) {
            $channels[] = new PrivateChannel('group.' . $this->message->chat_group_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $sender = $this->message->sender;
        $senderName = 'User';
        $senderAvatar = null;
        $senderCode = null;

        if ($sender) {
            if ($sender instanceof \App\Models\Buyer) {
                $senderName = $sender->name ?? ($sender->full_name ?? ($sender->business_name ?? 'Buyer'));
                $senderCode = $sender->bp_code ?? null;
                $senderAvatar = $sender->image_url ?? null;
            } elseif ($sender instanceof \App\Models\Craftman) {
                $senderName = $sender->name ?? ($sender->full_name ?? ($sender->business_name ?? 'Craftsman'));
                $senderCode = $sender->craftman_code ?? null;
                $senderAvatar = $sender->image ? asset('storage/' . $sender->image) : null;
            } elseif ($sender instanceof \App\Models\KeyUser) {
                $senderName = $sender->full_name ?? ($sender->name ?? 'Key User');
                $senderCode = $sender->user_code ?? null;
                $senderAvatar = $sender->profile_picture ?? null;
            } elseif ($sender instanceof \App\Models\CraftsmanStaff) {
                $senderName = $sender->name ?? 'Staff';
                $senderCode = $sender->staff_code ?? null;
                $senderAvatar = $sender->image_url ?? null;
            } elseif ($sender instanceof \App\Models\ProcessOwner) {
                $senderName = $sender->full_name ?? ($sender->name ?? 'Admin');
                $senderCode = $sender->user_code ?? null;
            } else {
                $senderName = $sender->name ?? ($sender->full_name ?? 'User');
            }
        }

        $attachments = $this->message->attachments->map(function ($att) {
            return [
                'id'        => (int) $att->id,
                'file_name' => $att->file_name,
                'file_path' => $att->file_path,
                'file_type' => $att->file_type,
                'mime_type' => $att->mime_type,
                'full_url'  => $att->full_url,
            ];
        })->values()->toArray();

        return [
            'id'              => (int) $this->message->id,
            'conversation_id' => $this->message->conversation_id ? (int) $this->message->conversation_id : null,
            'chat_group_id'   => $this->message->chat_group_id ? (int) $this->message->chat_group_id : null,
            'sender_id'       => (int) $this->message->sender_id,
            'sender_type'     => $this->message->sender_type,
            'sender_name'     => $senderName,
            'sender_code'     => $senderCode,
            'sender_avatar'   => $senderAvatar,
            'body'            => $this->message->body,
            'attachments'     => $attachments,
            'created_at'      => optional($this->message->created_at)->toISOString() ?? now()->toISOString(),
        ];
    }
}