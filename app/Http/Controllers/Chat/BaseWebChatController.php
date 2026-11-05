<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Conversation;
use App\Models\ChatGroup;
use App\Models\ChatGroupParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageStatus;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\KeyUser;
use App\Models\CraftsmanStaff;
use App\Models\ProcessOwner;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\MessageDeleted;

abstract class BaseWebChatController extends Controller
{
    protected string $panel = 'super-admin';
    protected string $guard = 'super_admin';

    /**
     * Resolve the currently authenticated user from active session guards.
     */
    protected function getAuthUser()
    {
        if (Auth::guard($this->guard)->check()) {
            return Auth::guard($this->guard)->user();
        }

        foreach (['super_admin', 'admin', 'buyer', 'craftsman', 'key_user', 'craftsman_staff', 'web'] as $g) {
            if (Auth::guard($g)->check()) {
                return Auth::guard($g)->user();
            }
        }

        return Auth::user();
    }

    /**
     * Check if user is Super Admin
     */
    protected function isSuperAdmin($user): bool
    {
        if (!$user) return false;
        return (isset($user->role) && in_array(strtolower($user->role), ['superadmin', 'super_admin']))
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || str_contains(strtolower(get_class($user)), 'superadmin');
    }

    /**
     * Check if user is Admin / SuperAdmin
     */
    protected function isAdminUser($user): bool
    {
        if (!$user) return false;
        if ($this->isSuperAdmin($user)) return true;
        if ($user instanceof ProcessOwner) return true;
        return in_array(strtolower($user->role ?? ''), ['admin', 'super_admin', 'superadmin', 'process_owner']);
    }

    /**
     * Convert model class to human readable type string
     */
    protected function getModelTypeString($user): string
    {
        if (!$user) return 'unknown';
        if ($user instanceof ProcessOwner) {
            return $this->isSuperAdmin($user) ? 'super-admin' : 'admin';
        }
        if ($user instanceof Buyer) return 'buyer';
        if ($user instanceof Craftman) return 'craftsman';
        if ($user instanceof CraftsmanStaff) return 'craftsman_staff';
        if ($user instanceof KeyUser) return 'key_user';
        if ($user instanceof User) return 'user';
        return strtolower(class_basename($user));
    }

    /**
     * Resolve model class from string
     */
    protected function resolveModelClass(?string $type): ?string
    {
        if (!$type) return null;
        $normalized = strtolower(str_replace(['_', '-'], '', trim($type)));
        $map = [
            'buyer'          => Buyer::class,
            'craftsman'      => Craftman::class,
            'craftman'       => Craftman::class,
            'craftsmanstaff' => CraftsmanStaff::class,
            'staff'          => CraftsmanStaff::class,
            'keyuser'        => KeyUser::class,
            'admin'          => ProcessOwner::class,
            'superadmin'     => ProcessOwner::class,
            'processowner'   => ProcessOwner::class,
            'user'           => User::class,
        ];
        return $map[$normalized] ?? null;
    }

    /**
     * Format user summary for frontend
     */
    protected function formatUserSummary($user): array
    {
        if (!$user) {
            return [
                'id'     => null,
                'name'   => 'Unknown',
                'type'   => 'unknown',
                'badge'  => 'Unknown',
                'code'   => null,
                'avatar' => null,
            ];
        }

        $type = $this->getModelTypeString($user);
        $code = null;
        $name = 'User';
        $avatar = null;

        if ($user instanceof Buyer) {
            $code = $user->bp_code;
            $name = $user->name ?? ($user->full_name ?? ($user->business_name ?? 'Buyer'));
            $avatar = $user->image_url ?? ($user->image ? asset('storage/' . $user->image) : null);
        } elseif ($user instanceof Craftman) {
            $code = $user->craftman_code;
            $name = $user->name ?? ($user->full_name ?? ($user->business_name ?? 'Craftsman'));
            $avatar = $user->image ? asset('storage/' . $user->image) : null;
        } elseif ($user instanceof KeyUser) {
            $code = $user->user_code;
            $name = $user->full_name ?? ($user->name ?? 'Key User');
            $avatar = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;
        } elseif ($user instanceof CraftsmanStaff) {
            $code = $user->staff_code;
            $name = $user->name ?? 'Staff';
            $avatar = $user->image_url ?? null;
        } elseif ($user instanceof ProcessOwner) {
            $code = $user->user_code;
            $name = $user->full_name ?? ($user->name ?? ($this->isSuperAdmin($user) ? 'Super Admin' : 'Admin'));
            $avatar = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;
        } else {
            $name = $user->name ?? ($user->full_name ?? 'User');
        }

        return [
            'id'         => (int) $user->id,
            'name'       => $name,
            'type'       => $type,
            'short_type' => strtolower(class_basename(get_class($user))),
            'class_name' => get_class($user),
            'badge'      => ucfirst(str_replace(['_', '-'], ' ', $type)),
            'code'       => $code,
            'avatar'     => $avatar,
        ];
    }

    /**
     * GET /{panel}/chat/conversations
     * Returns recent 1-on-1 conversations and groups for the logged-in user
     */
    public function getConversations(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $userId = (int) $user->id;
        $userClass = get_class($user);

        // Fetch direct conversations ONLY where the authenticated user is a direct participant
        $conversations = Conversation::where(function ($q) use ($userId, $userClass) {
            $q->where(function ($sq) use ($userId, $userClass) {
                $sq->where('sender_id', $userId)->where('sender_type', $userClass);
            })->orWhere(function ($sq) use ($userId, $userClass) {
                $sq->where('receiver_id', $userId)->where('receiver_type', $userClass);
            });
        })
        ->with(['sender', 'receiver', 'latestMessage.attachments'])
        ->orderByDesc('last_message_at')
        ->limit(100)
        ->get();

        $data = $conversations->map(function ($conv) use ($userId, $userClass) {
            $isSender = ($conv->sender_id == $userId && $conv->sender_type == $userClass);
            $otherUser = $isSender ? $conv->receiver : $conv->sender;

            // Unread count
            $unreadCount = Message::where('conversation_id', $conv->id)
                ->where('is_read', false)
                ->where(function ($q) use ($userId, $userClass) {
                    $q->where('sender_id', '!=', $userId)
                      ->orWhere('sender_type', '!=', $userClass);
                })
                ->count();

            $latestMsg = $conv->latestMessage;

            return [
                'id'              => (int) $conv->id,
                'is_group'        => false,
                'other_party'     => $this->formatUserSummary($otherUser),
                'last_message'    => $latestMsg ? [
                    'body'       => $latestMsg->body ?: ($latestMsg->attachments->first()?->file_name ? '📎 Attachment' : ''),
                    'created_at' => $latestMsg->created_at?->diffForHumans() ?? '',
                    'timestamp'  => $latestMsg->created_at?->toIso8601String() ?? '',
                    'is_read'    => (bool) $latestMsg->is_read,
                    'is_mine'    => ($latestMsg->sender_id == $userId && $latestMsg->sender_type == $userClass),
                ] : null,
                'last_message_at' => $conv->last_message_at ? $conv->last_message_at->toIso8601String() : null,
                'unread_count'    => $unreadCount,
            ];
        });

        // Also fetch any group chats the user belongs to
        $groups = ChatGroup::whereHas('participants', function ($pq) use ($userId, $userClass) {
            $pq->where('user_id', $userId)->where('user_type', $userClass);
        })
        ->orWhere(function ($gq) use ($userId, $userClass) {
            $gq->where('creator_id', $userId)->where('creator_type', $userClass);
        })
        ->with(['latestMessage.attachments'])
        ->get()
        ->map(function ($grp) use ($userId, $userClass) {
            $latestMsg = $grp->latestMessage;
            return [
                'id'              => (int) $grp->id,
                'is_group'        => true,
                'other_party'     => [
                    'id'     => (int) $grp->id,
                    'name'   => $grp->name,
                    'type'   => 'group',
                    'badge'  => 'Group',
                    'code'   => 'GRP-' . $grp->id,
                    'avatar' => $grp->image ? asset('storage/' . $grp->image) : null,
                ],
                'last_message'    => $latestMsg ? [
                    'body'       => $latestMsg->body ?: '📎 Attachment',
                    'created_at' => $latestMsg->created_at?->diffForHumans() ?? '',
                    'timestamp'  => $latestMsg->created_at?->toIso8601String() ?? '',
                    'is_read'    => (bool) $latestMsg->is_read,
                    'is_mine'    => ($latestMsg->sender_id == $userId && $latestMsg->sender_type == $userClass),
                ] : null,
                'last_message_at' => $latestMsg?->created_at?->toIso8601String() ?? $grp->created_at->toIso8601String(),
                'unread_count'    => 0,
            ];
        });

        $merged = $data->concat($groups)->sortByDesc('last_message_at')->values();

        return response()->json([
            'success' => true,
            'data'    => $merged,
        ]);
    }

    /**
     * GET /{panel}/chat/all-conversations (Super Admin Only)
     * Monitor all ongoing conversations across the system
     */
    public function getAllConversations(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Super Admin access required.'], 403);
        }

        $query = Conversation::with(['sender', 'receiver', 'latestMessage.attachments'])
            ->orderByDesc('last_message_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHasMorph('sender', [Buyer::class, Craftman::class, ProcessOwner::class, KeyUser::class], function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")->orWhere('full_name', 'like', "%{$search}%");
                })->orWhereHasMorph('receiver', [Buyer::class, Craftman::class, ProcessOwner::class, KeyUser::class], function ($rq) use ($search) {
                    $rq->where('name', 'like', "%{$search}%")->orWhere('full_name', 'like', "%{$search}%");
                });
            });
        }

        $conversations = $query->paginate(30);

        $data = $conversations->getCollection()->transform(function ($conv) {
            $latestMsg = $conv->latestMessage;
            return [
                'id'              => (int) $conv->id,
                'is_group'        => false,
                'party_1'         => $this->formatUserSummary($conv->sender),
                'party_2'         => $this->formatUserSummary($conv->receiver),
                'last_message'    => $latestMsg ? [
                    'body'       => $latestMsg->body ?: '📎 Attachment',
                    'created_at' => $latestMsg->created_at?->diffForHumans() ?? '',
                    'timestamp'  => $latestMsg->created_at?->toIso8601String() ?? '',
                ] : null,
                'last_message_at' => $conv->last_message_at ? $conv->last_message_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $conversations,
        ]);
    }

    /**
     * GET /{panel}/chat/contacts
     * Returns eligible contacts matching the exact role isolation rules
     */
    public function getContacts(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $isAdmin = $this->isAdminUser($user);
        $search = trim((string) $request->input('search', ''));

        // Contact queries
        $buyerQuery = Buyer::query();
        $craftsmanQuery = Craftman::query();
        $keyUserQuery = KeyUser::query();
        $staffQuery = CraftsmanStaff::query();
        $adminQuery = ProcessOwner::query();

        if ($search !== '') {
            $buyerQuery->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('bp_code', 'like', "%{$search}%"));
            $craftsmanQuery->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('craftman_code', 'like', "%{$search}%"));
            $keyUserQuery->where(fn($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('user_code', 'like', "%{$search}%"));
            $staffQuery->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('staff_code', 'like', "%{$search}%"));
            $adminQuery->where(fn($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('user_code', 'like', "%{$search}%"));
        }

        // Apply Strict Role Isolation
        if ($isAdmin) {
            $adminQuery->where('id', '!=', $user->id);
        } elseif ($user instanceof Buyer) {
            $buyerQuery->whereRaw('0 = 1');
            $craftsmanQuery->whereRaw('0 = 1');
            $staffQuery->whereRaw('0 = 1');
            $keyUserQuery->where('bp_code', $user->bp_code);
        } elseif ($user instanceof KeyUser) {
            $buyerQuery->where('bp_code', $user->bp_code);
            $craftsmanQuery->whereRaw('0 = 1');
            $staffQuery->whereRaw('0 = 1');
            $keyUserQuery->where('bp_code', $user->bp_code)->where('id', '!=', $user->id);
        } elseif ($user instanceof Craftman) {
            $buyerQuery->whereRaw('0 = 1');
            $keyUserQuery->whereRaw('0 = 1');
            $craftsmanQuery->whereRaw('0 = 1');
            $staffQuery->where('craftsman_id', $user->id);
        } elseif ($user instanceof CraftsmanStaff) {
            $buyerQuery->whereRaw('0 = 1');
            $keyUserQuery->whereRaw('0 = 1');
            $craftsmanQuery->where('id', $user->craftsman_id);
            $staffQuery->where('craftsman_id', $user->craftsman_id)->where('id', '!=', $user->id);
        } else {
            $buyerQuery->whereRaw('0 = 1');
            $craftsmanQuery->whereRaw('0 = 1');
            $keyUserQuery->whereRaw('0 = 1');
            $staffQuery->whereRaw('0 = 1');
        }

        $allContacts = collect();
        $allContacts = $allContacts->concat($adminQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
        $allContacts = $allContacts->concat($buyerQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
        $allContacts = $allContacts->concat($craftsmanQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
        $allContacts = $allContacts->concat($keyUserQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
        $allContacts = $allContacts->concat($staffQuery->get()->map(fn($u) => $this->formatUserSummary($u)));

        $allContacts = $allContacts->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return response()->json([
            'success' => true,
            'data'    => $allContacts,
        ]);
    }

    /**
     * POST /{panel}/chat/conversations
     * Start or find a 1-on-1 conversation with a contact
     */
    public function startConversation(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'receiver_id'   => ['required', 'integer'],
            'receiver_type' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $receiverClass = $this->resolveModelClass($request->receiver_type);
        if (!$receiverClass) {
            return response()->json(['success' => false, 'message' => 'Invalid receiver type.'], 422);
        }

        $receiver = $receiverClass::find($request->receiver_id);
        if (!$receiver) {
            return response()->json(['success' => false, 'message' => 'Receiver not found.'], 404);
        }

        $userId = (int) $user->id;
        $userClass = get_class($user);
        $receiverId = (int) $receiver->id;

        // Prevent chatting with self
        if ($userId === $receiverId && $userClass === $receiverClass) {
            return response()->json(['success' => false, 'message' => 'Cannot start conversation with yourself.'], 422);
        }

        // Find existing conversation
        $conversation = Conversation::where(function ($q) use ($userId, $userClass, $receiverId, $receiverClass) {
            $q->where('sender_id', $userId)->where('sender_type', $userClass)
              ->where('receiver_id', $receiverId)->where('receiver_type', $receiverClass);
        })->orWhere(function ($q) use ($userId, $userClass, $receiverId, $receiverClass) {
            $q->where('sender_id', $receiverId)->where('sender_type', $receiverClass)
              ->where('receiver_id', $userId)->where('receiver_type', $userClass);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'sender_id'       => $userId,
                'sender_type'     => $userClass,
                'receiver_id'     => $receiverId,
                'receiver_type'   => $receiverClass,
                'last_message_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => (int) $conversation->id,
                'is_group'    => false,
                'other_party' => $this->formatUserSummary($receiver),
            ],
        ]);
    }

    /**
     * GET /{panel}/chat/conversations/{id}/messages
     * Retrieve messages for a conversation, format attachments, and mark unread as read
     */
    public function getMessages(Request $request, $id)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $userId = (int) $user->id;
        $userClass = get_class($user);
        $isSuperAdmin = $this->isSuperAdmin($user);

        // Check if conversation or group
        $conversation = Conversation::with(['sender', 'receiver'])->find($id);
        $group = null;

        if (!$conversation) {
            $group = ChatGroup::with(['creator'])->find($id);
            if (!$group) {
                return response()->json(['success' => false, 'message' => 'Conversation or group not found.'], 404);
            }
        }

        // Authorization check (SuperAdmin can view all, while everyone else can only view their own)
        if (!$isSuperAdmin) {
            if ($conversation) {
                $isParticipant = ($conversation->sender_id == $userId && $conversation->sender_type == $userClass)
                    || ($conversation->receiver_id == $userId && $conversation->receiver_type == $userClass);
                if (!$isParticipant) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized – not your conversation.'], 403);
                }
            } elseif ($group) {
                if (!$group->hasParticipant($user) && !($group->creator_id == $userId && $group->creator_type == $userClass)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized – not a group member.'], 403);
                }
            }
        }

        // Mark unread messages as read
        if ($conversation) {
            Message::where('conversation_id', $conversation->id)
                ->where('is_read', false)
                ->where(function ($q) use ($userId, $userClass) {
                    $q->where('sender_id', '!=', $userId)
                      ->orWhere('sender_type', '!=', $userClass);
                })
                ->update(['is_read' => true]);
        }

        // Fetch messages
        $messagesQuery = $conversation
            ? Message::where('conversation_id', $conversation->id)
            : Message::where('chat_group_id', $group->id);

        $messages = $messagesQuery->with(['sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get();

        $formatted = $messages->map(function ($msg) use ($userId, $userClass, $isSuperAdmin) {
            $isMine = ($msg->sender_id == $userId && $msg->sender_type == $userClass);

            $attachments = $msg->attachments->map(function ($att) {
                return [
                    'id'        => (int) $att->id,
                    'file_name' => $att->file_name,
                    'file_path' => $att->file_path,
                    'file_type' => $att->file_type,
                    'mime_type' => $att->mime_type,
                    'url'       => asset('storage/' . $att->file_path),
                ];
            });

            return [
                'id'          => (int) $msg->id,
                'body'        => $msg->body,
                'is_mine'     => $isMine,
                'can_delete'  => $isSuperAdmin, // SuperAdmin ONLY
                'is_read'     => (bool) $msg->is_read,
                'sender'      => $this->formatUserSummary($msg->sender),
                'attachments' => $attachments,
                'created_at'  => $msg->created_at->format('h:i A'),
                'date'        => $msg->created_at->format('d M Y'),
                'timestamp'   => $msg->created_at->toIso8601String(),
            ];
        });

        $otherParty = null;
        if ($conversation) {
            $isSender = ($conversation->sender_id == $userId && $conversation->sender_type == $userClass);
            $otherParty = $this->formatUserSummary($isSender ? $conversation->receiver : $conversation->sender);
        } elseif ($group) {
            $otherParty = [
                'id'     => (int) $group->id,
                'name'   => $group->name,
                'type'   => 'group',
                'badge'  => 'Group',
                'code'   => 'GRP-' . $group->id,
                'avatar' => $group->image ? asset('storage/' . $group->image) : null,
            ];
        }

        return response()->json([
            'success'     => true,
            'other_party' => $otherParty,
            'data'        => $formatted,
        ]);
    }

    /**
     * POST /{panel}/chat/conversations/{id}/messages
     * Send a new message, save attachment to DB & storage, broadcast via Pusher
     */
    public function sendMessage(Request $request, $id)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $userId = (int) $user->id;
        $userClass = get_class($user);
        $isSuperAdmin = $this->isSuperAdmin($user);

        $conversation = Conversation::find($id);
        $group = null;

        if (!$conversation) {
            $group = ChatGroup::find($id);
            if (!$group) {
                return response()->json(['success' => false, 'message' => 'Conversation or group not found.'], 404);
            }
        }

        // Authorization check (SuperAdmin can send in any monitored chat, while everyone else can only in their own)
        if (!$isSuperAdmin) {
            if ($conversation) {
                $isParticipant = ($conversation->sender_id == $userId && $conversation->sender_type == $userClass)
                    || ($conversation->receiver_id == $userId && $conversation->receiver_type == $userClass);
                if (!$isParticipant) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized – not your conversation.'], 403);
                }
            } elseif ($group) {
                if (!$group->hasParticipant($user) && !($group->creator_id == $userId && $group->creator_type == $userClass)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized – not a group member.'], 403);
                }
            }
        }

        $validator = Validator::make($request->all(), [
            'body'       => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:51200'], // 50MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (empty(trim((string) $request->body)) && !$request->hasFile('attachment')) {
            return response()->json(['success' => false, 'message' => 'Message body or attachment is required.'], 422);
        }

        DB::beginTransaction();
        try {
            $message = Message::create([
                'conversation_id' => $conversation ? $conversation->id : null,
                'chat_group_id'   => $group ? $group->id : null,
                'sender_id'       => $userId,
                'sender_type'     => $userClass,
                'body'            => trim((string) $request->body),
                'is_read'         => false,
            ]);

            // Handle file attachment
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
                $originalName = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension());

                $fileType = 'document';
                if (str_starts_with($mimeType, 'image/')) {
                    $fileType = 'image';
                } elseif (str_starts_with($mimeType, 'audio/')) {
                    $fileType = 'voice';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $fileType = 'video';
                }

                $path = $file->store('chat/attachments', 'public');

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path'  => $path,
                    'file_name'  => $originalName,
                    'file_type'  => $fileType,
                    'mime_type'  => $mimeType,
                ]);
            }

            if ($conversation) {
                $conversation->update(['last_message_at' => now()]);
            }

            DB::commit();

            // Broadcast via Pusher (real-time message sending to others)
            try {
                broadcast(new MessageSent($message))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('Pusher broadcast MessageSent failed: ' . $e->getMessage());
            }

            $message->load(['sender', 'attachments']);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'data'    => [
                    'id'          => (int) $message->id,
                    'body'        => $message->body,
                    'is_mine'     => true,
                    'can_delete'  => $isSuperAdmin,
                    'is_read'     => false,
                    'sender'      => $this->formatUserSummary($message->sender),
                    'attachments' => $message->attachments->map(fn($a) => [
                        'id'        => (int) $a->id,
                        'file_name' => $a->file_name,
                        'file_path' => $a->file_path,
                        'file_type' => $a->file_type,
                        'mime_type' => $a->mime_type,
                        'url'       => asset('storage/' . $a->file_path),
                    ]),
                    'created_at'  => $message->created_at->format('h:i A'),
                    'date'        => $message->created_at->format('d M Y'),
                    'timestamp'   => $message->created_at->toIso8601String(),
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Send web chat message error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /{panel}/chat/messages/{id}
     * STRICT PERMISSION: Super Admin ONLY can delete messages!
     */
    public function deleteMessage(Request $request, $id)
    {
        $user = $this->getAuthUser();
        if (!$this->isSuperAdmin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin has permission to delete chat messages.'
            ], 403);
        }

        $message = Message::with('attachments')->findOrFail($id);
        $convId = $message->conversation_id;
        $groupId = $message->chat_group_id;
        $msgId = $message->id;

        // Delete physical attachment files
        foreach ($message->attachments as $att) {
            if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                Storage::disk('public')->delete($att->file_path);
            }
        }

        $message->delete();

        // Broadcast deletion via Pusher so mobile and web remove it instantly in real-time
        try {
            broadcast(new MessageDeleted($msgId, $convId, $groupId))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Pusher broadcast MessageDeleted failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully by Super Admin.',
            'data'    => [
                'message_id'      => $msgId,
                'conversation_id' => $convId,
                'chat_group_id'   => $groupId,
            ],
        ]);
    }
}

