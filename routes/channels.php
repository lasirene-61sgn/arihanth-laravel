<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if (!$user) {
        return false;
    }

    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }

    // Always allow SuperAdmin
    if (
        (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
        (isset($user->role) && in_array($user->role, ['superadmin', 'super_admin'])) ||
        str_contains(strtolower(get_class($user)), 'superadmin')
    ) {
        return true;
    }

    // Allow if user is either participant in the conversation
    return ((int) $conversation->sender_id === (int) $user->id) || 
           ((int) $conversation->receiver_id === (int) $user->id);
});