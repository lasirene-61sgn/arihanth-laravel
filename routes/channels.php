<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

$allowedGuards = ['sanctum', 'web', 'admin', 'super_admin', 'craftsman', 'key_user', 'buyer', 'craftsman_staff'];

// Allow both Session-based web guards and Token-based Sanctum API guard (for iOS / mobile apps / Flutter)
Broadcast::routes(['middleware' => ['auth:sanctum,web,admin,super_admin,craftsman,key_user,buyer,craftsman_staff']]);
Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:sanctum,web,admin,super_admin,craftsman,key_user,buyer,craftsman_staff']]);
Broadcast::routes(['prefix' => 'api/common', 'middleware' => ['auth:sanctum,web,admin,super_admin,craftsman,key_user,buyer,craftsman_staff']]);
Broadcast::routes(['prefix' => 'api/common/chat', 'middleware' => ['auth:sanctum,web,admin,super_admin,craftsman,key_user,buyer,craftsman_staff']]);

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if (!$user) {
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

    $conversation = Conversation::find($conversationId);
    $userClass = get_class($user);
    $userId = (int) $user->id;

    if ($conversation) {
        $senderMatches = ((int) $conversation->sender_id === $userId) && 
            (!isset($conversation->sender_type) || $conversation->sender_type === $userClass || strtolower(class_basename($conversation->sender_type)) === strtolower(class_basename($userClass)));

        $receiverMatches = ((int) $conversation->receiver_id === $userId) && 
            (!isset($conversation->receiver_type) || $conversation->receiver_type === $userClass || strtolower(class_basename($conversation->receiver_type)) === strtolower(class_basename($userClass)));

        if ($senderMatches || $receiverMatches) {
            return true;
        }

        // If this is a direct conversation and user is not a participant, deny access immediately
        return false;
    }

    // If no direct conversation exists with this ID, check if it refers to a ChatGroup
    $group = \App\Models\ChatGroup::find($conversationId);
    if ($group) {
        if ($group->hasParticipant($user)) return true;
        if ((int)$group->creator_id === $userId && strtolower(class_basename($group->creator_type)) === strtolower(class_basename($userClass))) {
            return true;
        }
    }

    return false;
}, ['guards' => $allowedGuards]);

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
    
    if ($group->hasParticipant($user)) return true;
    if ((int)$group->creator_id === (int)$user->id && strtolower(class_basename($group->creator_type)) === strtolower(class_basename($user))) {
        return true;
    }

    return false;
}, ['guards' => $allowedGuards]);

Broadcast::channel('user.{type}.{id}', function ($user, $type, $id) {
    if (!$user) return false;
    $typeMap = [
        'buyer'           => \App\Models\Buyer::class,
        'craftsman'       => \App\Models\Craftman::class,
        'craftman'        => \App\Models\Craftman::class,
        'craftsman_staff' => \App\Models\CraftsmanStaff::class,
        'admin'           => \App\Models\ProcessOwner::class,
        'super-admin'     => \App\Models\ProcessOwner::class,
        'processowner'    => \App\Models\ProcessOwner::class,
        'key_user'        => \App\Models\KeyUser::class,
        'user'            => \App\Models\User::class,
    ];
    $expectedClass = $typeMap[strtolower($type)] ?? null;
    return (int)$user->id === (int)$id && get_class($user) === $expectedClass;
}, ['guards' => $allowedGuards]);