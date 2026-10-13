<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Conversation extends Model
{
    protected $fillable = [
        'sender_id', 
        'receiver_id',
        'sender_type',
        'receiver_type',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function receiver(): MorphTo
    {
        return $this->morphTo();
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
