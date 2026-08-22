<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroupParticipant extends Model
{
    use HasFactory;

    protected $fillable = ['chat_group_id', 'user_id', 'user_type'];

    public function chatGroup()
    {
        return $this->belongsTo(ChatGroup::class);
    }

    public function user()
    {
        return $this->morphTo();
    }
}
