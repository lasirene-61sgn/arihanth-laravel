<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'chat_group_id',
        'sender_id',
        'sender_type',
        'body',
        'is_read'
    ];

    /**
     * Get the conversation that owns the message.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the chat group that owns the message.
     */
    public function chatGroup()
    {
        return $this->belongsTo(ChatGroup::class);
    }

    /**
     * Get the sender of the message (Polymorphic).
     */
    public function sender()
    {
        return $this->morphTo();
    }

    /**
     * Get the statuses (read/delivered) for this message.
     */
    public function statuses()
    {
        return $this->hasMany(MessageStatus::class);
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }
}