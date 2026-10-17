<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_path',
        'file_name',
        'file_type',
        'mime_type'
    ];

    protected $appends = [
        'full_url',
        'stream_url'
    ];

    /**
     * Get the message that owns the attachment.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Accessor to get the full URL of the attachment.
     * Usage: $attachment->full_url
     */
    public function getFullUrlAttribute()
    {
        if (empty($this->file_path)) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Accessor to get the dedicated HTTP 206 byte-range streaming URL for iOS AVPlayer / Web Audio.
     * Usage: $attachment->stream_url
     */
    public function getStreamUrlAttribute()
    {
        if (empty($this->file_path)) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return url('/api/common/chat/media/' . $this->id . '/stream');
    }
}