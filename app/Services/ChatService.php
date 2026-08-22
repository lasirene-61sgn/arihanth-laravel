<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\ProcessOwner;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ChatService
{
    /**
     * Get conversations for a user.
     */
    public function getConversations($user)
    {
        return Conversation::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->where('sender_type', get_class($user));
        })->orWhere(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)->where('receiver_type', get_class($user));
        })->with(['sender', 'receiver', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get monitoring data for Super Admin.
     */
    public function getMonitoringData($user)
    {
        if (!$this->isSuperAdmin($user)) return [];

        $all_conversations = Conversation::with(['sender', 'receiver', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->orderBy('updated_at', 'desc')->get();

        return ProcessOwner::where('role', 'admin')->get()->filter(function($admin) use ($all_conversations, $user) {
            $admin->buyer_convos = $all_conversations->filter(function($convo) use ($admin, $user) {
                $isAdminInvolved = ($convo->sender_id == $admin->id && $convo->sender_type == ProcessOwner::class) || 
                                   ($convo->receiver_id == $admin->id && $convo->receiver_type == ProcessOwner::class);
                $isSuperAdminInvolved = ($convo->sender_id == $user->id && $convo->sender_type == get_class($user)) || 
                                        ($convo->receiver_id == $user->id && $convo->receiver_type == get_class($user));
                $otherType = ($convo->sender_id == $admin->id && $convo->sender_type == ProcessOwner::class) ? $convo->receiver_type : $convo->sender_type;
                
                return $isAdminInvolved && !$isSuperAdminInvolved && $otherType === Buyer::class;
            });

            $admin->craftsman_convos = $all_conversations->filter(function($convo) use ($admin, $user) {
                $isAdminInvolved = ($convo->sender_id == $admin->id && $convo->sender_type == ProcessOwner::class) || 
                                   ($convo->receiver_id == $admin->id && $convo->receiver_type == ProcessOwner::class);
                $isSuperAdminInvolved = ($convo->sender_id == $user->id && $convo->sender_type == get_class($user)) || 
                                        ($convo->receiver_id == $user->id && $convo->receiver_type == get_class($user));
                $otherType = ($convo->sender_id == $admin->id && $convo->sender_type == ProcessOwner::class) ? $convo->receiver_type : $convo->sender_type;
                
                return $isAdminInvolved && !$isSuperAdminInvolved && $otherType === Craftman::class;
            });

            $admin->convo_count = $admin->buyer_convos->count() + $admin->craftsman_convos->count();
            return $admin->convo_count > 0;
        });
    }

    /**
     * Get messages for a conversation.
     */
    public function getMessages(Conversation $conversation, $user, $includeBase64 = false)
    {
        if (!$this->isParticipant($conversation, $user)) {
            throw new \Exception('Unauthorized', 403);
        }

        $messages = $conversation->messages()->with(['sender', 'attachments', 'statuses.user'])->oldest()->get();

        if ($includeBase64) {
            foreach ($messages as $msg) {
                foreach ($msg->attachments as $at) {
                    if ($at->file_type === 'voice') {
                        try {
                            $content = Storage::disk('public')->get($at->file_path);
                            $at->base64_data = 'data:' . ($at->mime_type ?: 'audio/webm') . ';base64,' . base64_encode($content);
                        } catch (\Exception $e) {
                            $at->base64_data = null;
                        }
                    }
                }
            }
        }

        // Mark as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $messages;
    }

    /**
     * Store a new message.
     */
    public function storeMessage($data, $user)
    {
        $conversation = Conversation::findOrFail($data['conversation_id']);
        
        if (!$this->isParticipant($conversation, $user)) {
            throw new \Exception('Unauthorized', 403);
        }

        return DB::transaction(function () use ($data, $conversation, $user) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'sender_type' => get_class($user),
                'body' => $data['body'] ?? '',
            ]);

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $this->saveAttachment($message, $file);
                }
            }

            $conversation->touch();
            return $message->load(['sender', 'attachments']);
        });
    }

    /**
     * Search users for chat.
     */
    public function searchUsers($query, $user)
    {
        if (strlen($query) < 2) return $this->getSuggestedContacts($user);

        $results = [];
        $isStaff = ($user instanceof ProcessOwner);

        if ($isStaff) {
            $buyers = Buyer::where('business_name', 'like', "%$query%")
                ->orWhere('name', 'like', "%$query%")
                ->orWhere('bp_code', 'like', "%$query%")
                ->limit(10)->get();
            foreach ($buyers as $b) {
                $results[] = [
                    'id' => $b->id, 
                    'name' => $b->business_name . ' (' . ($b->bp_code ?? 'Buyer') . ')', 
                    'type' => 'buyer',
                    'code' => $b->bp_code
                ];
            }

            $craftsmen = Craftman::where('business_name', 'like', "%$query%")
                ->orWhere('name', 'like', "%$query%")
                ->orWhere('craftman_code', 'like', "%$query%")
                ->limit(10)->get();
            foreach ($craftsmen as $c) {
                $results[] = [
                    'id' => $c->id, 
                    'name' => $c->business_name . ' (' . ($c->craftman_code ?? 'Craftsman') . ')', 
                    'type' => 'craftsman',
                    'code' => $c->craftman_code
                ];
            }
        } else {
            $admins = ProcessOwner::where(function ($q) use ($query) {
                $q->where('full_name', 'like', "%$query%")
                    ->orWhere('user_code', 'like', "%$query%");
            })->whereIn('role', ['admin', 'super_admin'])->limit(10)->get();

            foreach ($admins as $a) {
                $results[] = [
                    'id' => $a->id,
                    'name' => $a->full_name . ' (' . ucfirst($a->role) . ')',
                    'type' => $a->role === 'super_admin' ? 'super-admin' : 'admin',
                    'code' => $a->user_code
                ];
            }
        }

        return $results;
    }

    /**
     * Delete a message.
     */
    public function deleteMessage($id, $user)
    {
        $message = Message::with('attachments')->findOrFail($id);
        
        $isSuperAdmin = $this->isSuperAdmin($user);
        $isOwner = ($message->sender_id == $user->id && $message->sender_type == get_class($user));

        if (!$isSuperAdmin && !$isOwner) {
            throw new \Exception('Unauthorized. Only Super Admin or the sender can delete messages.', 403);
        }

        return DB::transaction(function () use ($message) {
            foreach ($message->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }
            $messageId = $message->id;
            $conversationId = $message->conversation_id;
            $groupId = $message->chat_group_id;
            
            $message->delete();
            
            broadcast(new \App\Events\MessageDeleted($messageId, $conversationId, $groupId))->toOthers();
            return true;
        });
    }

    /**
     * Delete an entire conversation.
     */
    public function deleteConversation($id, $user)
    {
        $conversation = Conversation::with('messages.attachments')->findOrFail($id);
        
        $isSuperAdmin = $this->isSuperAdmin($user);
        $isParticipant = $this->isParticipant($conversation, $user);

        if (!$isSuperAdmin && !$isParticipant) {
            throw new \Exception('Unauthorized. Only Super Admin or participants can delete conversations.', 403);
        }

        return DB::transaction(function () use ($conversation) {
            foreach ($conversation->messages as $message) {
                foreach ($message->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                    $attachment->delete();
                }
                $message->delete();
            }
            $conversation->delete();
            return true;
        });
    }
    public function getSuggestedContacts($user)
    {
        $results = [];
        if (!($user instanceof ProcessOwner)) {
            $admins = ProcessOwner::whereIn('role', ['admin', 'super_admin'])
                ->where('status', 1)->get()->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->full_name . ' (' . ucfirst($a->role) . ')',
                    'type' => $a->role === 'super_admin' ? 'super-admin' : 'admin',
                    'code' => $a->user_code
                ]);
            return $admins->toArray();
        } else {
            $buyers = Buyer::latest()->get()->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->business_name . ' (Buyer)',
                'type' => 'buyer',
                'bp_code' => $b->bp_code,
                'code' => $b->bp_code
            ]);
            $craftsmen = Craftman::latest()->get()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->business_name . ' (Craftsman)',
                'type' => 'craftsman',
                'craftman_code' => $c->craftman_code,
                'code' => $c->craftman_code
            ]);
            return array_merge($buyers->toArray(), $craftsmen->toArray());
        }
    }

    /**
     * Start or get a conversation.
     */
    public function startConversation($receiverId, $receiverTypeString, $user)
    {
        $typeMap = [
            'buyer' => Buyer::class,
            'craftsman' => Craftman::class,
            'admin' => ProcessOwner::class,
            'super-admin' => ProcessOwner::class,
        ];

        $receiverType = $typeMap[$receiverTypeString] ?? ProcessOwner::class;
        $senderType = get_class($user);

        // Rule: Buyer and Craftsman cannot chat with another Buyer or Craftsman
        $isSenderClient = ($senderType === Buyer::class || $senderType === Craftman::class);
        $isReceiverClient = ($receiverType === Buyer::class || $receiverType === Craftman::class);

        if ($isSenderClient && $isReceiverClient) {
            throw new \Exception('Buyers and Craftsmen cannot chat with each other. They can only chat with Admins.', 403);
        }

        $conversation = Conversation::where(function ($q) use ($user, $receiverId, $receiverType) {
            $q->where('sender_id', $user->id)->where('sender_type', get_class($user))
                ->where('receiver_id', $receiverId)->where('receiver_type', $receiverType);
        })->orWhere(function ($q) use ($user, $receiverId, $receiverType) {
            $q->where('sender_id', $receiverId)->where('sender_type', $receiverType)
                ->where('receiver_id', $user->id)->where('receiver_type', get_class($user));
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'sender_id' => $user->id,
                'sender_type' => get_class($user),
                'receiver_id' => $receiverId,
                'receiver_type' => $receiverType,
                'last_message_at' => now(),
            ]);
        }

        return $conversation;
    }

    /**
     * Helper: Check if user is Super Admin.
     */
    public function isSuperAdmin($user)
    {
        if (!($user instanceof ProcessOwner)) return false;
        return ($user->role === 'super_admin' || ($user->user_code && str_starts_with($user->user_code, 'SA')));
    }

    /**
     * Helper: Check if user is participant in conversation.
     */
    public function isParticipant(Conversation $conversation, $user)
    {
        if ($this->isSuperAdmin($user)) return true;

        return ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user)) ||
               ($conversation->receiver_id == $user->id && $conversation->receiver_type == get_class($user));
    }

    /**
     * Helper: Save attachment.
     */
    private function saveAttachment($message, $file)
    {
        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType();
        $type = 'document';

        $isVoiceMime = str_contains($mime, 'audio') || str_contains($mime, 'video/webm');
        $isVoiceExt = str_ends_with(strtolower($originalName), '.webm') || str_ends_with(strtolower($originalName), '.ogg') || str_ends_with(strtolower($originalName), '.mp3');
        
        if ($isVoiceMime || $isVoiceExt) {
            $type = 'voice';
            $mime = 'audio/webm';
        } elseif (str_contains($mime, 'image')) {
            $type = 'image';
        }

        $path = $file->store('chat_attachments', 'public');

        return MessageAttachment::create([
            'message_id' => $message->id,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_type' => $type,
            'mime_type' => $mime
        ]);
    }
}
