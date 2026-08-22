<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'user_id', 'user_type', 'delivered_at', 'read_at'];

    public function user()
    {
        return $this->morphTo();
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
