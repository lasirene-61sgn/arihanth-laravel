<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'creator_id', 'creator_type', 'is_global_admin_group'];

    public function creator()
    {
        return $this->morphTo();
    }

    public function participants()
    {
        return $this->hasMany(ChatGroupParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
