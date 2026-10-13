<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use CyberDeep\LaravelAgoraTokenGenerator\Services\Token\ChatTokenBuilder2;
use CyberDeep\LaravelAgoraTokenGenerator\Services\Token\RtmTokenBuilder2;
use CyberDeep\LaravelAgoraTokenGenerator\Services\Agora;
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
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Repair;
use App\Models\Meeting;
use App\Events\MessageSent;
use App\Events\MessageDeleted;
use App\Notifications\NewChatMessageNotification;

class ChatApiController extends Controller
{
    /**
     * Generate Agora Tokens for Chat / RTM / RTC.
     * Suitable for iOS AgoraChat and AgoraRtm SDKs.
     */
    public function getAgoraToken(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        if (!$appId || !$appCertificate) {
            return response()->json([
                'success' => false,
                'message' => 'Agora App ID or App Certificate is not configured in .env'
            ], 500);
        }

        $expire = (int) $request->input('expire', 86400); // 24 hours default
        if ($expire < 60) {
            $expire = 86400;
        }

        // Formatted unique identifier for Agora Chat / RTM (alphanumeric + underscore)
        $agoraUserId = $this->getAgoraUserId($user);

        try {
            // 1. Agora Chat User Token (V2)
            $chatUserToken = ChatTokenBuilder2::buildUserToken($appId, $appCertificate, $agoraUserId, $expire);

            // 2. Agora Chat App Token (V2)
            $chatAppToken = ChatTokenBuilder2::buildAppToken($appId, $appCertificate, $expire);

            // 3. Agora RTM Token (V2)
            $rtmToken = RtmTokenBuilder2::buildToken($appId, $appCertificate, $agoraUserId, $expire);

            // 4. Optional RTC Token (if channel_name is provided for voice/video in chat room)
            $rtcToken = null;
            $channelName = $request->input('channel_name');
            if ($channelName) {
                try {
                    $rtcToken = Agora::make($user->id)
                        ->channel($channelName)
                        ->uId((int) $user->id)
                        ->join(false)
                        ->audioOnly(false)
                        ->token();
                } catch (\Exception $e) {
                    Log::warning('RTC token generation failed in chat: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'app_id'          => $appId,
                    'agora_user_id'   => $agoraUserId,
                    'chat_user_token' => $chatUserToken,
                    'chat_app_token'  => $chatAppToken,
                    'rtm_token'       => $rtmToken,
                    'rtc_token'       => $rtcToken,
                    'channel_name'    => $channelName,
                    'expires_in'      => $expire,
                    'expires_at'      => now()->addSeconds($expire)->toISOString(),
                    'user'            => $this->formatUserSummary($user),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Agora chat token generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Agora token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get eligible contacts for the authenticated user to chat with or add to groups.
     * Enforces all role restrictions:
     * - Buyer: ONLY their own key users (bp_code match) + Admins/Superadmins
     * - Key User: their parent Buyer + fellow key users under same bp_code + Admins
     * - Craftsman: ONLY their own staff (craftsman_id match) + Admins/Superadmins
     * - Craftsman Staff: their parent Craftsman + fellow staff under same craftsman_id + Admins
     * - Admin/Super Admin: All participants across the system
     */
    public function getEligibleContacts(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $isSuperAdmin = $this->isSuperAdmin($user);
        $isAdmin = ($user instanceof ProcessOwner);

        $contacts = [
            'my_role'         => $this->getModelTypeString($user),
            'key_users'       => [],
            'craftsman_staff' => [],
            'buyers'          => [],
            'craftsmen'       => [],
            'admins'          => [],
        ];

        // 1. If Super Admin or Admin: can see everyone
        if ($isAdmin) {
            $contacts['admins'] = ProcessOwner::where('id', '!=', $user->id)
                ->get()
                ->map(fn($u) => $this->formatUserSummary($u));

            $contacts['buyers'] = Buyer::all()
                ->map(fn($u) => $this->formatUserSummary($u));

            $contacts['craftsmen'] = Craftman::all()
                ->map(fn($u) => $this->formatUserSummary($u));

            $contacts['key_users'] = KeyUser::all()
                ->map(fn($u) => $this->formatUserSummary($u));

            $contacts['craftsman_staff'] = CraftsmanStaff::all()
                ->map(fn($u) => $this->formatUserSummary($u));

            return response()->json(['success' => true, 'data' => $contacts]);
        }

        // Always provide Admins & Superadmins as available contacts
        $contacts['admins'] = ProcessOwner::all()
            ->map(fn($u) => $this->formatUserSummary($u));

        // 2. If Buyer: ONLY their own Key Users
        if ($user instanceof Buyer) {
            $contacts['key_users'] = KeyUser::where('bp_code', $user->bp_code)
                ->get()
                ->map(fn($u) => $this->formatUserSummary($u));

            return response()->json(['success' => true, 'data' => $contacts]);
        }

        // 3. If Key User: their parent Buyer, and colleagues under same BP code
        if ($user instanceof KeyUser) {
            $parentBuyer = Buyer::where('bp_code', $user->bp_code)->first();
            if ($parentBuyer) {
                $contacts['buyers'] = [$this->formatUserSummary($parentBuyer)];
            }

            $contacts['key_users'] = KeyUser::where('bp_code', $user->bp_code)
                ->where('id', '!=', $user->id)
                ->get()
                ->map(fn($u) => $this->formatUserSummary($u));

            return response()->json(['success' => true, 'data' => $contacts]);
        }

        // 4. If Craftsman: ONLY their own Craftsman Staff + Admins
        if ($user instanceof Craftman) {
            $contacts['craftsman_staff'] = CraftsmanStaff::where('craftsman_id', $user->id)
                ->get()
                ->map(fn($u) => $this->formatUserSummary($u));

            return response()->json(['success' => true, 'data' => $contacts]);
        }

        // 5. If Craftsman Staff: their parent Craftsman, and fellow staff under same workshop + Admins
        if ($user instanceof CraftsmanStaff) {
            $parentCraftsman = Craftman::find($user->craftsman_id);
            if ($parentCraftsman) {
                $contacts['craftsmen'] = [$this->formatUserSummary($parentCraftsman)];
            }

            $contacts['craftsman_staff'] = CraftsmanStaff::where('craftsman_id', $user->craftsman_id)
                ->where('id', '!=', $user->id)
                ->get()
                ->map(fn($u) => $this->formatUserSummary($u));

            return response()->json(['success' => true, 'data' => $contacts]);
        }

        return response()->json(['success' => true, 'data' => $contacts]);
    }

    /**
     * List direct 1-to-1 conversations for current user.
     */
    public function getConversations(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $conversations = Conversation::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->where('sender_type', get_class($user));
        })->orWhere(function ($q) use ($user) {
            $q->where('receiver_id', $user->id)->where('receiver_type', get_class($user));
        })
        ->with(['sender', 'receiver', 'latestMessage.attachments'])
        ->orderByDesc('last_message_at')
        ->orderByDesc('updated_at')
        ->get();

        $data = $conversations->map(function ($c) use ($user) {
            $isSender = ($c->sender_id == $user->id && $c->sender_type == get_class($user));
            $partner = $isSender ? $c->receiver : $c->sender;

            $unreadCount = Message::where('conversation_id', $c->id)
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', '!=', $user->id)
                      ->orWhere('sender_type', '!=', get_class($user));
                })
                ->where('is_read', false)
                ->count();

            return [
                'id'              => (int) $c->id,
                'partner'         => $this->formatUserSummary($partner),
                'last_message_at' => optional($c->last_message_at)->toISOString(),
                'latest_message'  => $c->latestMessage ? $this->formatMessageSummary($c->latestMessage) : null,
                'unread_count'    => (int) $unreadCount,
                'created_at'      => optional($c->created_at)->toISOString(),
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Start or fetch existing direct 1-to-1 conversation.
     */
    public function startConversation(Request $request)
    {
        $request->validate([
            'target_id'   => 'required',
            'target_type' => 'required|string',
            'body'        => 'nullable|string',
        ]);

        $user = Auth::user();
        $targetClass = $this->resolveModelClass($request->target_type);

        if (!$targetClass) {
            return response()->json(['success' => false, 'message' => 'Invalid target user type.'], 400);
        }

        $target = $this->resolveTargetModel($targetClass, $request->target_id);
        if (!$target) {
            return response()->json(['success' => false, 'message' => 'Target user not found.'], 404);
        }

        if ($target->id == $user->id && get_class($target) == get_class($user)) {
            return response()->json(['success' => false, 'message' => 'Cannot start a conversation with yourself.'], 400);
        }

        // Validate communication permissions between $user and $target
        $authCheck = $this->validateCommunicationPermission($user, $target);
        if (!$authCheck['allowed']) {
            return response()->json(['success' => false, 'message' => $authCheck['reason']], 403);
        }

        // Find existing or create new
        $conversation = Conversation::where(function ($q) use ($user, $target) {
            $q->where('sender_id', $user->id)->where('sender_type', get_class($user))
              ->where('receiver_id', $target->id)->where('receiver_type', get_class($target));
        })->orWhere(function ($q) use ($user, $target) {
            $q->where('sender_id', $target->id)->where('sender_type', get_class($target))
              ->where('receiver_id', $user->id)->where('receiver_type', get_class($user));
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'sender_id'       => $user->id,
                'sender_type'     => get_class($user),
                'receiver_id'     => $target->id,
                'receiver_type'   => get_class($target),
                'last_message_at' => now(),
            ]);
        }

        // Send optional initial message if provided
        $initialMessage = null;
        if ($request->filled('body') || $request->hasFile('attachments') || $request->hasFile('image') || $request->hasFile('video')) {
            $initialMessage = $this->storeMessageRecord(
                conversationId: $conversation->id,
                chatGroupId: null,
                user: $user,
                request: $request
            );
            $conversation->update(['last_message_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conversation initialized successfully.',
            'data'    => [
                'id'              => (int) $conversation->id,
                'partner'         => $this->formatUserSummary($target),
                'last_message_at' => optional($conversation->last_message_at)->toISOString(),
                'initial_message' => $initialMessage ? $this->formatMessageSummary($initialMessage) : null,
            ]
        ], 201);
    }

    /**
     * Get messages in a direct 1-to-1 conversation.
     */
    public function getConversationMessages(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        $isParticipant = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
            || ($conversation->receiver_id == $user->id && $conversation->receiver_type == get_class($user));

        if (!$isParticipant && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to view this conversation.'], 403);
        }

        // Mark incoming messages as read
        Message::where('conversation_id', $id)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', '!=', $user->id)
                  ->orWhere('sender_type', '!=', get_class($user));
            })
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $perPage = (int) $request->input('per_page', 50);
        $messages = Message::where('conversation_id', $id)
            ->with(['attachments', 'sender'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        $partner = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
            ? $conversation->receiver
            : $conversation->sender;

        return response()->json([
            'success' => true,
            'partner' => $this->formatUserSummary($partner),
            'data'    => $messages->map(fn($m) => $this->formatMessageSummary($m)),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
            ]
        ]);
    }

    /**
     * Send message with optional images/videos in a 1-to-1 conversation.
     */
    public function sendConversationMessage(Request $request, $id)
    {
        // Auto-decode JSON if Content-Type was omitted in Postman
        $rawContent = $request->getContent();
        if (!empty($rawContent)) {
            $json = json_decode($rawContent, true);
            if (is_array($json)) {
                $request->merge($json);
            }
        }

        $request->validate([
            'body'          => 'nullable|string',
            'message'       => 'nullable|string',
            'text'          => 'nullable|string',
            'content'       => 'nullable|string',
            'attachments.*' => 'nullable|file|max:51200',
            'image'         => 'nullable',
            'video'         => 'nullable',
        ]);

        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        $isParticipant = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
            || ($conversation->receiver_id == $user->id && $conversation->receiver_type == get_class($user));

        if (!$isParticipant && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to send message here.'], 403);
        }

        $bodyText = $request->input('body') 
            ?? $request->input('message') 
            ?? $request->input('text') 
            ?? $request->input('content')
            ?? (!empty($rawContent) && !str_starts_with(trim($rawContent), '{') ? trim($rawContent) : null);

        $hasText = !is_null($bodyText) && trim((string)$bodyText) !== '';
        $hasMedia = $request->hasFile('attachments') || $request->hasFile('image') || $request->hasFile('video') || $request->hasFile('file');

        if (!$hasText && !$hasMedia) {
            return response()->json([
                'success' => false,
                'message' => 'Message body or an attachment is required.',
                'debug'   => [
                    'received_keys' => array_keys($request->all()),
                    'hint' => 'In Postman, send a JSON body like {"body": "your message"} or form-data with key "body".'
                ]
            ], 422);
        }

        $message = $this->storeMessageRecord(
            conversationId: $conversation->id,
            chatGroupId: null,
            user: $user,
            request: $request
        );

        $conversation->update(['last_message_at' => now()]);

        // Broadcast Reverb WebSocket event
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Reverb broadcasting failed (non-fatal): ' . $e->getMessage());
        }

        // Send FCM Push Notification to the partner
        $partner = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
            ? $conversation->receiver
            : $conversation->sender;

        if ($partner && method_exists($partner, 'notify')) {
            try {
                $senderSummary = $this->formatUserSummary($user);
                $bodyPreview = $message->body ?: ($message->attachments->count() > 0 ? 'Sent media attachment' : 'New message');

                $partner->notify(new NewChatMessageNotification(
                    $message,
                    $senderSummary['label'],
                    $bodyPreview,
                    [
                        'conversation_id' => (string) $conversation->id,
                        'sender_code'     => (string) ($senderSummary['code'] ?? ''),
                        'sender_name'     => (string) $senderSummary['name'],
                    ]
                ));
            } catch (\Throwable $e) {
                Log::warning('FCM Notification error (non-fatal): ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data'    => $this->formatMessageSummary($message)
        ], 201);
    }

    /**
     * Create a Chat Group.
     * Enforces STRICT business rules:
     * - Buyer: Can ONLY add Key Users under their own bp_code! Cannot add other buyers' key users.
     * - Craftsman: Can ONLY add Craftsman Staff under their workshop, Admin, and Superadmin!
     * - Admin/Superadmin: Can create groups with permitted combinations.
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:100',
            'participants'         => 'required|array|min:1',
            'participants.*.id'   => 'required',
            'participants.*.type' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $isBuyer = ($user instanceof Buyer);
        $isCraftsman = ($user instanceof Craftman);
        $isAdmin = ($user instanceof ProcessOwner);

        // Validate participants strictly according to role
        $validatedParticipants = [];

        foreach ($request->participants as $index => $pData) {
            $pClass = $this->resolveModelClass($pData['type']);
            if (!$pClass) {
                return response()->json([
                    'success' => false,
                    'message' => "Participant at index {$index} has invalid type '{$pData['type']}'."
                ], 422);
            }

            $participantModel = $this->resolveTargetModel($pClass, $pData['id']);
            if (!$participantModel) {
                return response()->json([
                    'success' => false,
                    'message' => "Participant at index {$index} (ID: {$pData['id']}) not found."
                ], 404);
            }

            // Do not re-add creator
            if ($participantModel->id == $user->id && get_class($participantModel) == get_class($user)) {
                continue;
            }

            // 1. RULE: BUYER GROUP RESTRICTION
            // Buyer can make group under their key user ONLY, not other buyer key users!
            if ($isBuyer) {
                if ($participantModel instanceof KeyUser) {
                    if (strtoupper(trim($participantModel->bp_code)) !== strtoupper(trim($user->bp_code))) {
                        return response()->json([
                            'success' => false,
                            'message' => "Validation failed: Key User {$participantModel->user_code} ({$participantModel->full_name}) does not belong to your BP code ({$user->bp_code}). Buyers can only add key users under their own account."
                        ], 422);
                    }
                } elseif ($participantModel instanceof ProcessOwner) {
                    // Allowed: Admin / Superadmin for support
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Validation failed: Buyers can only create groups with their own Key Users and Admins."
                    ], 422);
                }
            }

            // 2. RULE: CRAFTSMAN GROUP RESTRICTION
            // Craftsman can make group under craftsman staff, admin, and Superadmin!
            if ($isCraftsman) {
                if ($participantModel instanceof CraftsmanStaff) {
                    if ((int) $participantModel->craftsman_id !== (int) $user->id) {
                        return response()->json([
                            'success' => false,
                            'message' => "Validation failed: Craftsman staff {$participantModel->staff_code} ({$participantModel->name}) does not belong to your workshop. Craftsmen can only add staff belonging to their workshop."
                        ], 422);
                    }
                } elseif ($participantModel instanceof ProcessOwner) {
                    // Allowed: Admin and Superadmin
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Validation failed: Craftsmen can only add their own Craftsman Staff, Admin, and Superadmin."
                    ], 422);
                }
            }

            $validatedParticipants[] = $participantModel;
        }

        if (empty($validatedParticipants)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide at least one valid participant.'
            ], 422);
        }

        // Create ChatGroup
        $group = ChatGroup::create([
            'name'                  => $request->name,
            'creator_type'          => get_class($user),
            'creator_id'            => $user->id,
            'is_global_admin_group' => false,
        ]);

        // Add creator as participant
        ChatGroupParticipant::firstOrCreate([
            'chat_group_id' => $group->id,
            'user_type'     => get_class($user),
            'user_id'       => $user->id,
        ]);

        // Add all validated participants
        foreach ($validatedParticipants as $p) {
            ChatGroupParticipant::firstOrCreate([
                'chat_group_id' => $group->id,
                'user_type'     => get_class($p),
                'user_id'       => $p->id,
            ]);
        }

        // Return group with full details
        return response()->json([
            'success' => true,
            'message' => 'Chat group created successfully.',
            'data'    => $this->formatGroupSummary($group->fresh(['participants.user', 'creator']))
        ], 201);
    }

    /**
     * List chat groups for the current user.
     */
    public function getGroups(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $isSuperAdmin = $this->isSuperAdmin($user);

        if ($isSuperAdmin && $request->boolean('all')) {
            $groups = ChatGroup::with(['creator', 'participants.user', 'latestMessage.attachments'])
                ->orderByDesc('updated_at')
                ->get();
        } else {
            // Find groups where user is a participant or creator
            $groupIds = ChatGroupParticipant::where('user_id', $user->id)
                ->where('user_type', get_class($user))
                ->pluck('chat_group_id')
                ->toArray();

            // Also check creator
            $createdGroupIds = ChatGroup::where('creator_id', $user->id)
                ->where('creator_type', get_class($user))
                ->pluck('id')
                ->toArray();

            $allIds = array_unique(array_merge($groupIds, $createdGroupIds));

            $query = ChatGroup::whereIn('id', $allIds);

            // If Admin, also include global admin groups
            if ($user instanceof ProcessOwner) {
                $query->orWhere('is_global_admin_group', true);
            }

            $groups = $query->with(['creator', 'participants.user', 'latestMessage.attachments'])
                ->orderByDesc('updated_at')
                ->get();
        }

        $data = $groups->map(fn($g) => $this->formatGroupSummary($g, $user));

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get specific group details with members.
     */
    public function getGroupDetails(Request $request, $id)
    {
        $user = Auth::user();
        $group = ChatGroup::with(['creator', 'participants.user'])->findOrFail($id);

        if (!$group->hasParticipant($user) && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to view this group.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatGroupSummary($group, $user, true)
        ]);
    }

    /**
     * Add member(s) to group.
     */
    public function addGroupParticipants(Request $request, $id)
    {
        $request->validate([
            'participants'         => 'required|array|min:1',
            'participants.*.id'   => 'required',
            'participants.*.type' => 'required|string',
        ]);

        $user = Auth::user();
        $group = ChatGroup::findOrFail($id);

        $isCreator = ($group->creator_id == $user->id && $group->creator_type == get_class($user));
        if (!$isCreator && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Only the group creator or Superadmin can add members.'], 403);
        }

        $creatorModel = $group->creator;
        $isCreatorBuyer = ($creatorModel instanceof Buyer);
        $isCreatorCraftsman = ($creatorModel instanceof Craftman);

        $added = [];

        foreach ($request->participants as $pData) {
            $pClass = $this->resolveModelClass($pData['type']);
            if (!$pClass) continue;

            $participantModel = $this->resolveTargetModel($pClass, $pData['id']);
            if (!$participantModel) continue;

            // Enforce Buyer restrictions
            if ($isCreatorBuyer) {
                if ($participantModel instanceof KeyUser) {
                    if (strtoupper(trim($participantModel->bp_code)) !== strtoupper(trim($creatorModel->bp_code))) {
                        return response()->json([
                            'success' => false,
                            'message' => "Key user {$participantModel->user_code} does not belong to buyer BP code {$creatorModel->bp_code}."
                        ], 422);
                    }
                } elseif (!($participantModel instanceof ProcessOwner)) {
                    return response()->json(['success' => false, 'message' => 'Buyers can only add their own Key Users or Admins.'], 422);
                }
            }

            // Enforce Craftsman restrictions
            if ($isCreatorCraftsman) {
                if ($participantModel instanceof CraftsmanStaff) {
                    if ((int) $participantModel->craftsman_id !== (int) $creatorModel->id) {
                        return response()->json([
                            'success' => false,
                            'message' => "Staff member {$participantModel->staff_code} does not belong to this craftsman's workshop."
                        ], 422);
                    }
                } elseif (!($participantModel instanceof ProcessOwner)) {
                    return response()->json(['success' => false, 'message' => 'Craftsmen can only add their own staff, Admin, and Superadmin.'], 422);
                }
            }

            ChatGroupParticipant::firstOrCreate([
                'chat_group_id' => $group->id,
                'user_type'     => get_class($participantModel),
                'user_id'       => $participantModel->id,
            ]);

            $added[] = $this->formatUserSummary($participantModel);
        }

        return response()->json([
            'success' => true,
            'message' => count($added) . ' participant(s) added successfully.',
            'data'    => $added
        ]);
    }

    /**
     * Remove member from group.
     */
    public function removeGroupParticipant(Request $request, $id, $participantId)
    {
        $user = Auth::user();
        $group = ChatGroup::findOrFail($id);

        $isCreator = ($group->creator_id == $user->id && $group->creator_type == get_class($user));
        $isSuperAdmin = $this->isSuperAdmin($user);

        $participantRecord = ChatGroupParticipant::where('chat_group_id', $group->id)
            ->where('id', $participantId)
            ->firstOrFail();

        $isSelfLeaving = ($participantRecord->user_id == $user->id && $participantRecord->user_type == get_class($user));

        if (!$isCreator && !$isSuperAdmin && !$isSelfLeaving) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to remove this participant.'], 403);
        }

        $participantRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'Participant removed from group successfully.'
        ]);
    }

    /**
     * Get messages in a group chat.
     */
    public function getGroupMessages(Request $request, $id)
    {
        $user = Auth::user();
        $group = ChatGroup::findOrFail($id);

        if (!$group->hasParticipant($user) && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to view this group chat.'], 403);
        }

        $perPage = (int) $request->input('per_page', 50);
        $messages = Message::where('chat_group_id', $id)
            ->with(['attachments', 'sender'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'group'   => [
                'id'   => (int) $group->id,
                'name' => $group->name,
            ],
            'data'    => $messages->map(fn($m) => $this->formatMessageSummary($m)),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
            ]
        ]);
    }

    /**
     * Send group message with optional images and videos.
     */
    public function sendGroupMessage(Request $request, $id)
    {
        $request->validate([
            'body'          => 'nullable|string',
            'attachments.*' => 'nullable|file|max:51200',
            'image'         => 'nullable|file|image|max:15360',
            'video'         => 'nullable|file|mimes:mp4,mov,avi,3gp,webm,mkv|max:51200',
        ]);

        $user = Auth::user();
        $group = ChatGroup::findOrFail($id);

        if (!$group->hasParticipant($user) && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to send messages in this group.'], 403);
        }

        $bodyText = $request->input('body') ?? $request->input('message') ?? $request->input('text') ?? $request->input('content');
        $hasText = !is_null($bodyText) && trim((string)$bodyText) !== '';
        $hasMedia = $request->hasFile('attachments') || $request->hasFile('image') || $request->hasFile('video') || $request->hasFile('file');

        if (!$hasText && !$hasMedia) {
            return response()->json(['success' => false, 'message' => 'Message body or an attachment is required.'], 422);
        }

        $message = $this->storeMessageRecord(
            conversationId: null,
            chatGroupId: $group->id,
            user: $user,
            request: $request
        );

        $group->touch(); // updates updated_at

        // Broadcast Reverb WebSocket event to group channel
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Reverb group broadcast failed (non-fatal): ' . $e->getMessage());
        }

        // Send FCM Push notifications to all group participants (except sender)
        $senderSummary = $this->formatUserSummary($user);
        $bodyPreview = $message->body ?: ($message->attachments->count() > 0 ? 'Sent media attachment' : 'New message');
        $notificationTitle = "{$group->name} • {$senderSummary['name']}";

        $participants = $group->participants()
            ->where(function ($q) use ($user) {
                $q->where('user_id', '!=', $user->id)
                  ->orWhere('user_type', '!=', get_class($user));
            })
            ->with('user')
            ->get();

        foreach ($participants as $pRecord) {
            $pUser = $pRecord->user;
            if ($pUser && method_exists($pUser, 'notify')) {
                try {
                    $pUser->notify(new NewChatMessageNotification(
                        $message,
                        $notificationTitle,
                        $bodyPreview,
                        [
                            'chat_group_id' => (string) $group->id,
                            'group_name'    => (string) $group->name,
                            'sender_name'   => (string) $senderSummary['name'],
                        ]
                    ));
                } catch (\Throwable $e) {
                    Log::warning('FCM group participant push failed (non-fatal): ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Group message sent successfully.',
            'data'    => $this->formatMessageSummary($message)
        ], 201);
    }

    /**
     * Delete a single message.
     * Permission: Super Admin can delete ANY message. Regular user can delete their OWN message.
     */
    public function deleteMessage(Request $request, $id)
    {
        $user = Auth::user();
        $message = Message::findOrFail($id);

        $isOwner = ($message->sender_id == $user->id && $message->sender_type == get_class($user));
        $isSuperAdmin = $this->isSuperAdmin($user);

        if (!$isOwner && !$isSuperAdmin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to delete this message.'], 403);
        }

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

        // Broadcast deletion
        try {
            broadcast(new MessageDeleted($msgId, $convId, $groupId))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('MessageDeleted broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.'
        ]);
    }

    /**
     * Delete entire conversation.
     * Permission: Super Admin, or conversation participant.
     */
    public function deleteConversation(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        $isParticipant = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
            || ($conversation->receiver_id == $user->id && $conversation->receiver_type == get_class($user));

        if (!$isParticipant && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to delete this conversation.'], 403);
        }

        // Delete all message attachment files
        $messages = Message::where('conversation_id', $conversation->id)->with('attachments')->get();
        foreach ($messages as $msg) {
            foreach ($msg->attachments as $att) {
                if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                    Storage::disk('public')->delete($att->file_path);
                }
            }
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully.'
        ]);
    }

    /**
     * Delete a chat group.
     * Permission: Super Admin or group creator.
     */
    public function deleteGroup(Request $request, $id)
    {
        $user = Auth::user();
        $group = ChatGroup::findOrFail($id);

        $isCreator = ($group->creator_id == $user->id && $group->creator_type == get_class($user));
        if (!$isCreator && !$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Only the group creator or Superadmin can delete this group.'], 403);
        }

        // Delete all message attachments
        $messages = Message::where('chat_group_id', $group->id)->with('attachments')->get();
        foreach ($messages as $msg) {
            foreach ($msg->attachments as $att) {
                if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                    Storage::disk('public')->delete($att->file_path);
                }
            }
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat group deleted successfully.'
        ]);
    }

    /**
     * Super Admin Overview:
     * Shows how many Buyers, Craftsmen, Key Users, Craftsman Staff, and Admins
     * have chat history, plus total conversations and messages counts.
     */
    public function superAdminOverview(Request $request)
    {
        $user = Auth::user();
        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Super Admin access required.'], 403);
        }

        // Count distinct participants who have chatted
        $distinctBuyers = Message::where('sender_type', Buyer::class)
            ->distinct('sender_id')
            ->count('sender_id');

        $distinctCraftsmen = Message::where('sender_type', Craftman::class)
            ->distinct('sender_id')
            ->count('sender_id');

        $distinctKeyUsers = Message::where('sender_type', KeyUser::class)
            ->distinct('sender_id')
            ->count('sender_id');

        $distinctCraftsmanStaff = Message::where('sender_type', CraftsmanStaff::class)
            ->distinct('sender_id')
            ->count('sender_id');

        $distinctAdmins = Message::where('sender_type', ProcessOwner::class)
            ->distinct('sender_id')
            ->count('sender_id');

        $totalConversations = Conversation::count();
        $totalGroups = ChatGroup::count();
        $totalMessages = Message::count();

        $totalImages = MessageAttachment::where('file_type', 'image')->count();
        $totalVideos = MessageAttachment::where('file_type', 'video')->count();
        $totalDocuments = MessageAttachment::whereIn('file_type', ['document', 'file'])->count();

        // Recent conversations with last activity
        $recentConversations = Conversation::with(['sender', 'receiver', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id'              => $c->id,
                    'party_1'         => $this->formatUserSummary($c->sender),
                    'party_2'         => $this->formatUserSummary($c->receiver),
                    'last_message_at' => optional($c->last_message_at)->toISOString(),
                    'latest_message'  => $c->latestMessage ? $c->latestMessage->body : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'stats' => [
                    'buyers_chatted_count'          => (int) $distinctBuyers,
                    'craftsmen_chatted_count'       => (int) $distinctCraftsmen,
                    'key_users_chatted_count'       => (int) $distinctKeyUsers,
                    'craftsman_staff_chatted_count' => (int) $distinctCraftsmanStaff,
                    'admins_chatted_count'          => (int) $distinctAdmins,
                    'total_direct_conversations'    => (int) $totalConversations,
                    'total_chat_groups'             => (int) $totalGroups,
                    'total_messages_sent'           => (int) $totalMessages,
                    'media_counts' => [
                        'images'    => (int) $totalImages,
                        'videos'    => (int) $totalVideos,
                        'documents' => (int) $totalDocuments,
                    ]
                ],
                'recent_conversations' => $recentConversations,
            ]
        ]);
    }

    /**
     * Super Admin: View all direct conversations across the system.
     */
    public function superAdminAllConversations(Request $request)
    {
        $user = Auth::user();
        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Super Admin access required.'], 403);
        }

        $query = Conversation::with(['sender', 'receiver', 'latestMessage.attachments'])
            ->orderByDesc('last_message_at');

        // Optional filter by participant role/type
        if ($request->filled('type')) {
            $modelClass = $this->resolveModelClass($request->type);
            if ($modelClass) {
                $query->where(function ($q) use ($modelClass) {
                    $q->where('sender_type', $modelClass)->orWhere('receiver_type', $modelClass);
                });
            }
        }

        $conversations = $query->paginate((int) $request->input('per_page', 20));

        $data = $conversations->map(function ($c) {
            return [
                'id'              => (int) $c->id,
                'party_1'         => $this->formatUserSummary($c->sender),
                'party_2'         => $this->formatUserSummary($c->receiver),
                'last_message_at' => optional($c->last_message_at)->toISOString(),
                'latest_message'  => $c->latestMessage ? $this->formatMessageSummary($c->latestMessage) : null,
                'messages_count'  => $c->messages()->count(),
                'created_at'      => optional($c->created_at)->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page'    => $conversations->lastPage(),
                'per_page'     => $conversations->perPage(),
                'total'        => $conversations->total(),
            ]
        ]);
    }

    /**
     * Chatbot Assistant Endpoint.
     * Answers common questions (Work Orders, POs, Repairs, Meetings, Support)
     * and can provide Agora consultation tokens.
     */
    public function chatbotMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $query = strtolower(trim($request->message));
        $userSummary = $this->formatUserSummary($user);

        $reply = '';
        $actionType = 'text';
        $actionData = null;
        $agoraData = null;

        // 1. Work Order queries
        if (str_contains($query, 'work order') || str_contains($query, 'wo ') || str_contains($query, 'order status')) {
            $woQuery = WorkOrder::query();

            if ($user instanceof Buyer) {
                $woQuery->where('bp_code', $user->bp_code);
            } elseif ($user instanceof KeyUser) {
                $woQuery->where('bp_code', $user->bp_code);
            } elseif ($user instanceof Craftman) {
                $woQuery->where('craftsman_code', $user->craftman_code);
            }

            $totalWo = (clone $woQuery)->count();
            $pendingWo = (clone $woQuery)->whereIn('status', ['pending', 'allocated'])->count();
            $completedWo = (clone $woQuery)->where('status', 'completed')->count();
            $latestOrders = (clone $woQuery)->latest()->limit(3)->get(['id', 'work_order_number', 'status', 'created_at']);

            $reply = "You have {$totalWo} total work order(s). Active/Pending: {$pendingWo}, Completed: {$completedWo}.";
            $actionType = 'work_orders';
            $actionData = [
                'total'         => $totalWo,
                'pending'       => $pendingWo,
                'completed'     => $completedWo,
                'latest_orders' => $latestOrders,
            ];
        }
        // 2. Purchase Order queries
        elseif (str_contains($query, 'purchase order') || str_contains($query, 'po ')) {
            $poQuery = PurchaseOrder::query();

            if ($user instanceof Buyer || $user instanceof KeyUser) {
                $poQuery->where('bp_code', $user->bp_code);
            } elseif ($user instanceof Craftman) {
                $poQuery->where('craftsman_code', $user->craftman_code);
            }

            $totalPo = (clone $poQuery)->count();
            $reply = "You currently have {$totalPo} purchase order(s) registered in your account.";
            $actionType = 'purchase_orders';
            $actionData = ['total' => $totalPo];
        }
        // 3. Repair queries
        elseif (str_contains($query, 'repair')) {
            $repairs = Repair::query();
            if ($user instanceof Buyer || $user instanceof KeyUser) {
                $repairs->where('bp_code', $user->bp_code);
            } elseif ($user instanceof Craftman) {
                $repairs->where('craftsman_id', $user->id);
            }
            $count = $repairs->count();
            $reply = "You have {$count} repair item(s) logged in the system.";
            $actionType = 'repairs';
            $actionData = ['count' => $count];
        }
        // 4. Meeting / Video Consultation / Agora call queries
        elseif (str_contains($query, 'meeting') || str_contains($query, 'video call') || str_contains($query, 'consultation') || str_contains($query, 'call')) {
            $meetings = Meeting::where(function ($q) use ($user) {
                $q->where('host_id', $user->id)->where('host_type', get_class($user));
            })->orWhere(function ($q) use ($user) {
                $q->where('participant_id', $user->id)->where('participant_type', get_class($user));
            })->latest()->limit(3)->get();

            $reply = "You have " . $meetings->count() . " upcoming / recent video consultation(s). You can connect to Agora video consultation directly through the Meetings tab.";
            $actionType = 'meetings';
            $actionData = $meetings;

            // Generate an instant Agora token for video support if requested
            $appId = env('AGORA_APP_ID');
            $appCert = env('AGORA_APP_CERTIFICATE');
            if ($appId && $appCert) {
                try {
                    $agoraUserId = $this->getAgoraUserId($user);
                    $room = 'bot_consultation_' . $user->id;
                    $token = Agora::make($user->id)->channel($room)->uId($user->id)->token();
                    $agoraData = [
                        'app_id'       => $appId,
                        'channel_name' => $room,
                        'token'        => $token,
                        'uid'          => $user->id,
                    ];
                } catch (\Throwable $e) {
                    // non-fatal
                }
            }
        }
        // 5. Contact / Support queries
        elseif (str_contains($query, 'contact') || str_contains($query, 'support') || str_contains($query, 'help') || str_contains($query, 'phone')) {
            $reply = "For immediate assistance, you can contact our Admin office directly at emailer@qppk7f.mailer91.com or send a direct message in the Chat section to the Administrator.";
            $actionType = 'support';
        }
        // 6. Default greeting & capabilities
        else {
            $reply = "Hello {$userSummary['name']}! I am your Arihanth Assistant. I can help you with:\n" .
                     "• Work Order Status\n" .
                     "• Purchase Orders\n" .
                     "• Repairs Tracking\n" .
                     "• Video Consultation & Agora Meetings\n" .
                     "• Connecting you with Admin or Workshop Staff.\n\n" .
                     "How can I assist you today?";
            $actionType = 'general';
        }

        return response()->json([
            'success'     => true,
            'reply'       => $reply,
            'action_type' => $actionType,
            'action_data' => $actionData,
            'agora'       => $agoraData,
            'timestamp'   => now()->toISOString(),
        ]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Format a safe string identifier for Agora Chat / RTM (e.g. "buyer_1", "admin_2").
     */
    private function getAgoraUserId($user): string
    {
        $shortType = $this->getModelTypeString($user);
        return strtolower($shortType) . '_' . $user->id;
    }

    /**
     * Determine if user has Super Admin privileges.
     */
    private function isSuperAdmin($user): bool
    {
        if (!$user) return false;

        if ($user instanceof ProcessOwner) {
            $role = strtolower($user->role ?? '');
            if (in_array($role, ['superadmin', 'super_admin'])) {
                return true;
            }
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Map model or string to friendly type ('buyer', 'craftsman', 'key_user', etc.)
     */
    private function getModelTypeString($model): string
    {
        $class = is_object($model) ? get_class($model) : $model;

        return match ($class) {
            Buyer::class          => 'buyer',
            Craftman::class       => 'craftsman',
            KeyUser::class        => 'key_user',
            CraftsmanStaff::class => 'craftsman_staff',
            ProcessOwner::class   => (isset($model->role) && in_array(strtolower($model->role), ['superadmin', 'super_admin'])) ? 'superadmin' : 'admin',
            User::class           => 'user',
            default               => strtolower(class_basename($class)),
        };
    }

    /**
     * Map friendly type string to Eloquent Model Class.
     */
    private function resolveModelClass(string $type): ?string
    {
        $map = [
            'buyer'           => Buyer::class,
            'craftsman'       => Craftman::class,
            'craftman'        => Craftman::class,
            'key_user'        => KeyUser::class,
            'key-user'        => KeyUser::class,
            'keyuser'         => KeyUser::class,
            'craftsman_staff' => CraftsmanStaff::class,
            'craftsman-staff' => CraftsmanStaff::class,
            'craftsmanstaff'  => CraftsmanStaff::class,
            'admin'           => ProcessOwner::class,
            'superadmin'      => ProcessOwner::class,
            'super-admin'     => ProcessOwner::class,
            'super_admin'     => ProcessOwner::class,
            'processowner'    => ProcessOwner::class,
            'user'            => User::class,
        ];

        return $map[strtolower(trim($type))] ?? null;
    }

    /**
     * Resolve target Eloquent model by ID or User Code.
     */
    private function resolveTargetModel(string $modelClass, $identifier)
    {
        if (is_numeric($identifier)) {
            $model = $modelClass::find($identifier);
            if ($model) return $model;
        }

        if ($modelClass === Buyer::class) {
            return Buyer::where('bp_code', $identifier)->first();
        } elseif ($modelClass === Craftman::class) {
            return Craftman::where('craftman_code', $identifier)->first();
        } elseif ($modelClass === KeyUser::class) {
            return KeyUser::where('user_code', $identifier)->first();
        } elseif ($modelClass === CraftsmanStaff::class) {
            return CraftsmanStaff::where('staff_code', $identifier)->first();
        } elseif ($modelClass === ProcessOwner::class) {
            return ProcessOwner::where('user_code', $identifier)->first();
        }

        return null;
    }

    /**
     * Validate whether two users have permission to communicate directly.
     */
    private function validateCommunicationPermission($user, $target): array
    {
        // Superadmin or Admin can communicate with anyone
        if ($user instanceof ProcessOwner || $target instanceof ProcessOwner) {
            return ['allowed' => true];
        }

        // Buyer permissions
        if ($user instanceof Buyer) {
            if ($target instanceof KeyUser && strtoupper(trim($target->bp_code)) === strtoupper(trim($user->bp_code))) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Buyers can only communicate directly with their own Key Users or Admins.'
            ];
        }

        // Key User permissions
        if ($user instanceof KeyUser) {
            if ($target instanceof Buyer && strtoupper(trim($user->bp_code)) === strtoupper(trim($target->bp_code))) {
                return ['allowed' => true];
            }
            if ($target instanceof KeyUser && strtoupper(trim($user->bp_code)) === strtoupper(trim($target->bp_code))) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Key users can only communicate directly with their parent Buyer, colleagues under the same BP code, or Admins.'
            ];
        }

        // Craftsman permissions
        if ($user instanceof Craftman) {
            if ($target instanceof CraftsmanStaff && (int) $target->craftsman_id === (int) $user->id) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Craftsmen can only communicate directly with their own staff or Admins.'
            ];
        }

        // Craftsman Staff permissions
        if ($user instanceof CraftsmanStaff) {
            if ($target instanceof Craftman && (int) $user->craftsman_id === (int) $target->id) {
                return ['allowed' => true];
            }
            if ($target instanceof CraftsmanStaff && (int) $user->craftsman_id === (int) $target->craftsman_id) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Staff can only communicate directly with their Craftsman, fellow staff members, or Admins.'
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Format a user's details for API response.
     */
    private function formatUserSummary($user): array
    {
        if (!$user) {
            return ['id' => null, 'name' => 'Unknown', 'type' => 'unknown', 'label' => 'Unknown'];
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
            $avatar = $user->profile_picture ?? null;
        } elseif ($user instanceof CraftsmanStaff) {
            $code = $user->staff_code;
            $name = $user->name ?? 'Staff';
            $avatar = $user->image_url ?? ($user->image ? asset('storage/' . $user->image) : null);
        } elseif ($user instanceof ProcessOwner) {
            $code = $user->user_code;
            $name = $user->full_name ?? ($user->name ?? 'Admin');
            $avatar = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;
        } elseif ($user instanceof User) {
            $name = $user->name ?? 'User';
        }

        return [
            'id'         => (int) $user->id,
            'type'       => $type,
            'model_type' => get_class($user),
            'code'       => $code,
            'name'       => $name,
            'label'      => trim(($code ? "{$code} - " : "") . $name),
            'avatar_url' => $avatar,
        ];
    }

    /**
     * Format a message for API response with full attachment URLs.
     */
    private function formatMessageSummary(Message $message): array
    {
        $senderSummary = $this->formatUserSummary($message->sender);

        $attachments = $message->attachments->map(function ($att) {
            return [
                'id'        => (int) $att->id,
                'file_name' => $att->file_name,
                'file_path' => $att->file_path,
                'file_type' => $att->file_type,
                'mime_type' => $att->mime_type,
                'full_url'  => $att->full_url,
            ];
        })->values()->toArray();

        return [
            'id'              => (int) $message->id,
            'conversation_id' => $message->conversation_id ? (int) $message->conversation_id : null,
            'chat_group_id'   => $message->chat_group_id ? (int) $message->chat_group_id : null,
            'sender'          => $senderSummary,
            'body'            => $message->body,
            'attachments'     => $attachments,
            'is_read'         => (bool) $message->is_read,
            'created_at'      => optional($message->created_at)->toISOString(),
        ];
    }

    /**
     * Format chat group details.
     */
    private function formatGroupSummary(ChatGroup $group, $currentUser = null, $includeMembers = false): array
    {
        $participantsCount = $group->participants()->count();
        $creatorSummary = $this->formatUserSummary($group->creator);

        $unreadCount = 0;
        if ($currentUser) {
            $unreadCount = Message::where('chat_group_id', $group->id)
                ->where(function ($q) use ($currentUser) {
                    $q->where('sender_id', '!=', $currentUser->id)
                      ->orWhere('sender_type', '!=', get_class($currentUser));
                })
                ->where('is_read', false)
                ->count();
        }

        $res = [
            'id'                    => (int) $group->id,
            'name'                  => $group->name,
            'creator'               => $creatorSummary,
            'is_global_admin_group' => (bool) $group->is_global_admin_group,
            'participants_count'    => (int) $participantsCount,
            'unread_count'          => (int) $unreadCount,
            'latest_message'        => $group->latestMessage ? $this->formatMessageSummary($group->latestMessage) : null,
            'created_at'            => optional($group->created_at)->toISOString(),
        ];

        if ($includeMembers) {
            $res['members'] = $group->participants->map(function ($p) {
                return array_merge($this->formatUserSummary($p->user), [
                    'participant_id' => (int) $p->id,
                    'joined_at'      => optional($p->created_at)->toISOString(),
                ]);
            })->values()->toArray();
        }

        return $res;
    }

    /**
     * Store message record and all uploaded multimedia files.
     */
    private function storeMessageRecord(?int $conversationId, ?int $chatGroupId, $user, Request $request): Message
    {
        $bodyContent = $request->input('body') ?? $request->input('message') ?? $request->input('text') ?? $request->input('content') ?? '';

        $message = Message::create([
            'conversation_id' => $conversationId,
            'chat_group_id'   => $chatGroupId,
            'sender_id'       => $user->id,
            'sender_type'     => get_class($user),
            'body'            => (string) $bodyContent,
            'is_read'         => false,
        ]);

        $filesToProcess = [];

        // 1. attachments[] array
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            if (is_array($files)) {
                $filesToProcess = array_merge($filesToProcess, $files);
            } else {
                $filesToProcess[] = $files;
            }
        }

        // 2. individual 'image' file
        if ($request->hasFile('image')) {
            $filesToProcess[] = $request->file('image');
        }

        // 3. individual 'video' file
        if ($request->hasFile('video')) {
            $filesToProcess[] = $request->file('video');
        }

        // 4. individual 'file' file
        if ($request->hasFile('file')) {
            $filesToProcess[] = $request->file('file');
        }

        foreach ($filesToProcess as $file) {
            if (!$file->isValid()) continue;

            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
            $fileType = $this->determineFileType($mimeType, $file->getClientOriginalExtension());

            $path = $file->store('chat_attachments', 'public');

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_path'  => $path,
                'file_name'  => $originalName,
                'file_type'  => $fileType,
                'mime_type'  => $mimeType,
            ]);
        }

        return $message->fresh(['attachments', 'sender']);
    }

    /**
     * Determine file type classification (image, video, audio, document).
     */
    private function determineFileType(string $mime, string $ext): string
    {
        $mime = strtolower($mime);
        $ext = strtolower($ext);

        if (str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'svg'])) {
            return 'image';
        }

        if (str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'mov', 'avi', '3gp', 'webm', 'mkv', 'flv'])) {
            return 'video';
        }

        if (str_starts_with($mime, 'audio/') || in_array($ext, ['mp3', 'wav', 'm4a', 'aac', 'ogg'])) {
            return 'audio';
        }

        return 'document';
    }

    /**
     * Check WebSocket (Reverb) server status and return client connection configuration for iOS/Frontend.
     */
    public function getWebsocketConfig(Request $request)
    {
        $host = env('REVERB_HOST', '127.0.0.1');
        $port = (int) env('REVERB_PORT', 8080);
        $scheme = env('REVERB_SCHEME', 'http');
        $appKey = env('REVERB_APP_KEY');

        // Check if port is reachable locally
        $isRunning = false;
        try {
            $connection = @fsockopen($host === 'localhost' ? '127.0.0.1' : $host, $port, $errno, $errstr, 1);
            if (is_resource($connection)) {
                $isRunning = true;
                fclose($connection);
            }
        } catch (\Throwable $e) {
            $isRunning = false;
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'broadcaster'        => config('broadcasting.default'),
                'is_running'         => $isRunning,
                'app_key'            => $appKey,
                'host'               => $host,
                'port'               => $port,
                'scheme'             => $scheme,
                'ws_url'             => ($scheme === 'https' ? 'wss://' : 'ws://') . $host . ':' . $port . '/app/' . $appKey,
                'auth_endpoint'      => url('/api/broadcasting/auth'),
                'alt_auth_endpoint'  => url('/broadcasting/auth'),
                'how_to_start'       => 'Run "php artisan reverb:start" on the server',
                'guide'              => [
                    'text_chat'  => 'Connect to WebSocket (Reverb) to listen to conversation.{id} and group.{id} channels for real-time text & media messages.',
                    'voice_video'=> 'Connect to Agora RTC using /api/common/chat/agora-token for live audio/video calls.',
                ]
            ]
        ]);
    }
}
