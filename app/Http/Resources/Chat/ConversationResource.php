<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        
        // Determine the "other" user
        $isSender = ($this->sender_id == $user->id && $this->sender_type == get_class($user));
        $otherUser = $isSender ? $this->receiver : $this->sender;
        
        $lastMessage = $this->messages->first(); // messages are loaded with limit 1 in index

        return [
            'id' => $this->id,
            'other_user' => [
                'id' => $otherUser->id ?? null,
                'name' => $otherUser->full_name ?? $otherUser->business_name ?? $otherUser->name ?? 'Unknown',
                'type' => class_basename($otherUser),
                'avatar' => null, // Add avatar logic if available
            ],
            'last_message' => $lastMessage ? [
                'body' => $lastMessage->body,
                'is_read' => (bool)$lastMessage->is_read,
                'created_at' => $lastMessage->created_at->diffForHumans(),
            ] : null,
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
