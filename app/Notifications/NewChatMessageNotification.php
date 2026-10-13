<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\CustomFcmChannel;
use App\Models\Message;

class NewChatMessageNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $title;
    protected $body;
    protected $extraData;

    public function __construct(Message $message, string $title, string $body, array $extraData = [])
    {
        $this->message = $message;
        $this->title = $title;
        $this->body = $body;
        $this->extraData = $extraData;
    }

    public function via($notifiable)
    {
        return [CustomFcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $conversationId = (string) ($this->message->conversation_id ?? '');
        $chatGroupId = (string) ($this->message->chat_group_id ?? '');

        $replyUrl = $conversationId !== ''
            ? url('/api/common/chat/conversations/' . $conversationId . '/messages')
            : url('/api/common/chat/groups/' . $chatGroupId . '/messages');

        $data = array_merge([
            'type'              => 'chat_message',
            'message_id'        => (string) $this->message->id,
            'conversation_id'   => $conversationId,
            'chat_group_id'     => $chatGroupId,
            'sender_id'         => (string) $this->message->sender_id,
            'sender_type'       => (string) $this->message->sender_type,
            'action'            => 'reply',
            'action_type'       => 'inline_reply',
            'allow_input'       => 'true',
            'input_placeholder' => 'Type a reply...',
            'category'          => 'CHAT_MESSAGE_REPLY',
            'reply_url'         => $replyUrl,
            'reply_method'      => 'POST',
        ], $this->extraData);

        return [
            'title' => $this->title,
            'body'  => $this->body,
            'data'  => $data,
        ];
    }
}
