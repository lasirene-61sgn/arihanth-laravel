<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['web', 'auth:web,admin,super_admin,craftsman,key_user,buyer,craftsman_staff']]);

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if (!$user) {
        return false;
    }

    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }

    // Allow SuperAdmin
    if (
        (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
        (isset($user->role) && in_array($user->role, ['superadmin', 'super_admin'])) ||
        str_contains(strtolower(get_class($user)), 'superadmin')
    ) {
        return true;
    }

    $userClass = get_class($user);
    $userId = (int) $user->id;

    // Compare both ID and model class type
    $isSender = ((int) $conversation->sender_id === $userId) && 
                (!isset($conversation->sender_type) || $conversation->sender_type === $userClass);

    $isReceiver = ((int) $conversation->receiver_id === $userId) && 
                  (!isset($conversation->receiver_type) || $conversation->receiver_type === $userClass);

    return $isSender || $isReceiver;
}, ['guards' => ['web', 'admin', 'super_admin', 'craftsman', 'key_user', 'buyer', 'craftsman_staff']]);

Broadcast::channel('group.{chatGroupId}', function ($user, $chatGroupId) {
    if (!$user) return false;

    // Allow SuperAdmin
    if (
        (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
        (isset($user->role) && in_array($user->role, ['superadmin', 'super_admin'])) ||
        str_contains(strtolower(get_class($user)), 'superadmin')
    ) {
        return true;
    }

    $group = \App\Models\ChatGroup::find($chatGroupId);
    if (!$group) return false;
    
    // Global admin group: Allow any ProcessOwner admin
    if ($group->is_global_admin_group) {
        return $user instanceof \App\Models\ProcessOwner && in_array($user->role, ['admin', 'super_admin']);
    }

    // Otherwise, check participants table
    return \App\Models\ChatGroupParticipant::where('chat_group_id', $chatGroupId)
        ->where('user_id', $user->id)
        ->where('user_type', get_class($user))
        ->exists();
}, ['guards' => ['web', 'admin', 'super_admin', 'craftsman', 'key_user', 'buyer', 'craftsman_staff']]);