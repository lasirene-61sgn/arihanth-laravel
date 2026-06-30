<?php

namespace App\Traits;

use App\Models\Conversation;
use App\Models\Message;

trait HasMessages
{
    public function setConversations(){
        return $this->morphMany(Conversation::class, 'sender');
    }

    public function receivedConversations(){
        return $this->morphMany(Conversation::class, 'receiver');
    }

    public function allConversations(){
        return Conversation::where(function($q){
            $q->where('sender_id', $this->id)->where('sender_type', get_class($this));
        })->orwhere(function($q){
            $q->where('receiver_id', $this->id)->where('receiver_type', get_class($this));
        });
    }
}
