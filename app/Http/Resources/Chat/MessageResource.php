<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'body' => $this->body,
            'is_sender' => ($this->sender_id == $user->id && $this->sender_type == get_class($user)),
            'sender_name' => $this->sender->full_name ?? $this->sender->business_name ?? $this->sender->name ?? 'Unknown',
            'attachments' => $this->attachments->map(function ($at) {
                return [
                    'id' => $at->id,
                    'url' => asset('storage/' . $at->file_path),
                    'file_name' => $at->file_name,
                    'file_type' => $at->file_type,
                    'mime_type' => $at->mime_type,
                ];
            }),
            'is_read' => (bool)$this->is_read,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
