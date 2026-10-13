<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatGroupParticipant extends Model
{
    use HasFactory;

    protected $table = 'chat_group_participants';

    protected $fillable = [
        'chat_group_id',
        'user_type',
        'user_id',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
