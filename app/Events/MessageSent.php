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
            // Frontend unifies listening under private-conversation.{id}
            $channels[] = new PrivateChannel('conversation.' . $this->message->chat_group_id);
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
            $streamUrl = $att->stream_url ?? url('/api/common/chat/media/' . $att->id . '/stream');
            return [
                'id'         => (int) $att->id,
                'file_name'  => $att->file_name,
                'file_path'  => $att->file_path,
                'file_type'  => $att->file_type,
                'mime_type'  => $att->mime_type ?: ($att->file_type === 'audio' ? 'audio/mp4' : 'application/octet-stream'),
                'full_url'   => $att->full_url,
                'stream_url' => $streamUrl,
            ];
        })->values()->toArray();

        // Location detection (Live Location / Shared Google Maps Location)
        $isLocation = false;
        $latitude = null;
        $longitude = null;
        $mapUrl = null;
        $isLiveLocation = false;

        $rawBody = (string) $this->message->body;
        if (preg_match('/(?:maps\.google\.com\/(?:maps)?\?q=|@)(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i', $rawBody, $locMatches)) {
            $isLocation = true;
            $latitude = (float) $locMatches[1];
            $longitude = (float) $locMatches[2];
            $mapUrl = "https://maps.google.com/?q={$latitude},{$longitude}";
            $isLiveLocation = str_contains(strtolower($rawBody), 'live');
        } elseif (preg_match('/^location:(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i', trim($rawBody), $locMatches)) {
            $isLocation = true;
            $latitude = (float) $locMatches[1];
            $longitude = (float) $locMatches[2];
            $mapUrl = "https://maps.google.com/?q={$latitude},{$longitude}";
            $isLiveLocation = false;
        }

        $audioAtt = $this->message->attachments->firstWhere('file_type', 'audio');
        $videoAtt = $this->message->attachments->firstWhere('file_type', 'video');
        $imageAtt = $this->message->attachments->firstWhere('file_type', 'image');
        $docAtt   = $this->message->attachments->firstWhere('file_type', 'document');

        $primaryAtt = $audioAtt ?? $videoAtt ?? $imageAtt ?? $docAtt;

        if ($isLocation) {
            $mediaType = $isLiveLocation ? 'live_location' : 'location';
        } else {
            $mediaType = $primaryAtt ? $primaryAtt->file_type : 'text';
        }

        $audioUrl = $audioAtt ? ($audioAtt->stream_url ?? $audioAtt->full_url) : null;
        $videoUrl = $videoAtt ? ($videoAtt->stream_url ?? $videoAtt->full_url) : null;
        $imageUrl = $imageAtt ? $imageAtt->full_url : null;
        $fileUrl  = $docAtt ? $docAtt->full_url : null;

        $mediaUrl = null;
        if ($isLocation) {
            $mediaUrl = $mapUrl;
        } elseif ($primaryAtt) {
            $mediaUrl = ($primaryAtt->file_type === 'audio' || $primaryAtt->file_type === 'video')
                ? ($primaryAtt->stream_url ?? $primaryAtt->full_url)
                : $primaryAtt->full_url;
        }

        $body = $this->message->body;
        if ($isLocation) {
            $body = $mapUrl;
        } elseif ($primaryAtt) {
            $currentTrimmed = trim((string)$body);
            $fileName = trim((string)$primaryAtt->file_name);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $shouldReplaceWithUrl = empty($currentTrimmed) || ($currentTrimmed === $fileName);
            if (!$shouldReplaceWithUrl && !empty($ext) && str_ends_with(strtolower($currentTrimmed), '.' . $ext) && !str_contains($currentTrimmed, ' ') && !str_starts_with($currentTrimmed, 'http')) {
                $shouldReplaceWithUrl = true;
            }

            if ($shouldReplaceWithUrl) {
                $body = $mediaUrl;
            }
        }

        return [
            'id'               => (int) $this->message->id,
            'conversation_id'  => $this->message->conversation_id ? (int) $this->message->conversation_id : ($this->message->chat_group_id ? (int) $this->message->chat_group_id : null),
            'chat_group_id'    => $this->message->chat_group_id ? (int) $this->message->chat_group_id : null,
            'is_group'         => !empty($this->message->chat_group_id),
            'chat_type'        => !empty($this->message->chat_group_id) ? 'group' : 'direct',
            'sender_id'        => (int) $this->message->sender_id,
            'sender_type'      => $this->message->sender_type,
            'sender_name'      => $senderName,
            'sender_code'      => $senderCode,
            'sender_avatar'    => $senderAvatar,
            'body'             => $body,
            'text'             => $body,
            'message'          => $body,
            'content'          => $body,
            'media_type'       => $mediaType,
            'media_url'        => $mediaUrl,
            'audio_url'        => $audioUrl,
            'audio_stream_url' => $audioAtt ? ($audioAtt->stream_url ?? null) : null,
            'video_url'        => $videoUrl,
            'image_url'        => $imageUrl,
            'document_url'     => $fileUrl,
            'file_url'         => $fileUrl,
            'file_name'        => $primaryAtt ? $primaryAtt->file_name : null,
            'mime_type'        => $primaryAtt ? ($primaryAtt->mime_type ?: ($primaryAtt->file_type === 'audio' ? 'audio/mp4' : null)) : null,
            'is_live_location' => $isLiveLocation,
            'latitude'         => $latitude,
            'longitude'        => $longitude,
            'map_url'          => $mapUrl,
            'location'         => $isLocation ? [
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'map_url'   => $mapUrl,
                'is_live'   => $isLiveLocation,
            ] : null,
            'attachments'      => $attachments,
            'created_at'       => optional($this->message->created_at)->toISOString() ?? now()->toISOString(),
        ];
    }
}