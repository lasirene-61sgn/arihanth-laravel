<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatGroup extends Model
{
    use HasFactory;

    protected $table = 'chat_groups';

    protected $fillable = [
        'name',
        'creator_type',
        'creator_id',
        'is_global_admin_group',
    ];

    protected $casts = [
        'is_global_admin_group' => 'boolean',
    ];

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChatGroupParticipant::class, 'chat_group_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'chat_group_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'chat_group_id')->latestOfMany();
    }

    public function hasParticipant($user): bool
    {
        if (!$user) return false;

        // Superadmin always has access
        if (
            (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
            (isset($user->role) && in_array($user->role, ['superadmin', 'super_admin']))
        ) {
            return true;
        }

        if ($this->is_global_admin_group) {
            return $user instanceof ProcessOwner && in_array($user->role, ['admin', 'superadmin', 'super_admin']);
        }

        return $this->participants()
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->exists();
    }
}
