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

        if (!$this->hasChatPermission($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have chat permissions enabled. Please contact Super Admin.'
            ], 403);
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
    /**
     * Get eligible contacts for the authenticated user to chat with or add to groups.
     * Enforces all role restrictions and supports search, category filtering, and pagination.
     *
     * Query Parameters:
     * - search: string (search by name, user code, bp_code, email, or mobile)
     * - type: string ('all', 'buyers', 'craftsmen', 'key_users', 'craftsman_staff', 'admins')
     * - page: int (default: 1)
     * - per_page: int (default: 20, max: 100)
     * - grouped: bool (optional: true returns grouped dictionary)
     */
    public function getEligibleContacts(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if (!$this->hasChatPermission($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have chat permissions enabled. Please contact Super Admin.'
            ], 403);
        }

        $search = trim((string) $request->input('search', ''));
        $filterType = strtolower(trim((string) $request->input('type', 'all')));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
        $isGrouped = $request->boolean('grouped');

        $isSuperAdmin = $this->isSuperAdmin($user);
        $isAdmin = ($user instanceof ProcessOwner);

        // 1. Buyer Query
        $buyerQuery = Buyer::query();
        if ($search !== '') {
            $buyerQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('bp_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // 2. Craftsman Query
        $craftsmanQuery = Craftman::query();
        if ($search !== '') {
            $craftsmanQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('craftman_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // 3. Key User Query
        $keyUserQuery = KeyUser::query();
        if ($search !== '') {
            $keyUserQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('user_code', 'like', "%{$search}%")
                  ->orWhere('bp_code', 'like', "%{$search}%")
                  ->orWhere('email_id', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        // 4. Craftsman Staff Query
        $staffQuery = CraftsmanStaff::query();
        if ($search !== '') {
            $staffQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('staff_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // 5. Admin (ProcessOwner) Query
        $adminQuery = ProcessOwner::query();
        if ($search !== '') {
            $adminQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('user_code', 'like', "%{$search}%")
                  ->orWhere('email_id', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        // Apply Strict Role Isolation:
        // - Super Admin & Admin: Can contact anyone across the platform
        // - Buyer: Can ONLY see their own Key Users + Admins/Superadmins (NO Craftsmen, NO Craftsman Staff, NO other Buyers)
        // - Craftsman: Can ONLY see their own Craftsman Staff + Admins/Superadmins (NO other Craftsmen, NO Buyers, NO Key Users)
        // - Key User: Can ONLY see their parent Buyer + fellow Key Users under same bp_code + Admins/Superadmins
        // - Craftsman Staff: Can ONLY see their parent Craftsman + fellow Staff under same craftsman_id + Admins/Superadmins
        if ($isAdmin) {
            // Super Admin & Admin can contact anyone across the platform
            $adminQuery->where('id', '!=', $user->id);
        } elseif ($user instanceof Buyer) {
            // Buyer: their own Key Users + Admins/Superadmins ONLY
            $buyerQuery->whereRaw('0 = 1');
            $craftsmanQuery->whereRaw('0 = 1');
            $staffQuery->whereRaw('0 = 1');
            $keyUserQuery->where('bp_code', $user->bp_code);
        } elseif ($user instanceof KeyUser) {
            // Key User: their parent Buyer + fellow Key Users under same BP code + Admins/Superadmins ONLY
            $buyerQuery->where('bp_code', $user->bp_code);
            $craftsmanQuery->whereRaw('0 = 1');
            $staffQuery->whereRaw('0 = 1');
            $keyUserQuery->where('bp_code', $user->bp_code)->where('id', '!=', $user->id);
        } elseif ($user instanceof Craftman) {
            // Craftsman: their own staff + Admins/Superadmins ONLY
            $buyerQuery->whereRaw('0 = 1');
            $keyUserQuery->whereRaw('0 = 1');
            $craftsmanQuery->whereRaw('0 = 1');
            $staffQuery->where('craftsman_id', $user->id);
        } elseif ($user instanceof CraftsmanStaff) {
            // Staff: their parent Craftsman + fellow staff under same workshop + Admins/Superadmins ONLY
            $buyerQuery->whereRaw('0 = 1');
            $keyUserQuery->whereRaw('0 = 1');
            $craftsmanQuery->where('id', $user->craftsman_id);
            $staffQuery->where('craftsman_id', $user->craftsman_id)->where('id', '!=', $user->id);
        } else {
            // Generic user: only Admins
            $buyerQuery->whereRaw('0 = 1');
            $craftsmanQuery->whereRaw('0 = 1');
            $keyUserQuery->whereRaw('0 = 1');
            $staffQuery->whereRaw('0 = 1');
        }

        // Calculate counts for frontend filter tabs
        $counts = [
            'all'             => 0,
            'buyers'          => (clone $buyerQuery)->count(),
            'craftsmen'       => (clone $craftsmanQuery)->count(),
            'key_users'       => (clone $keyUserQuery)->count(),
            'craftsman_staff' => (clone $staffQuery)->count(),
            'admins'          => (clone $adminQuery)->count(),
        ];
        $counts['all'] = $counts['buyers'] + $counts['craftsmen'] + $counts['key_users'] + $counts['craftsman_staff'] + $counts['admins'];

        // Option: return grouped format if requested
        if ($isGrouped) {
            return response()->json([
                'success' => true,
                'data' => [
                    'my_role'         => $this->getModelTypeString($user),
                    'admins'          => $adminQuery->get()->map(fn($u) => $this->formatUserSummary($u))->values(),
                    'buyers'          => $buyerQuery->get()->map(fn($u) => $this->formatUserSummary($u))->values(),
                    'craftsmen'       => $craftsmanQuery->get()->map(fn($u) => $this->formatUserSummary($u))->values(),
                    'key_users'       => $keyUserQuery->get()->map(fn($u) => $this->formatUserSummary($u))->values(),
                    'craftsman_staff' => $staffQuery->get()->map(fn($u) => $this->formatUserSummary($u))->values(),
                ],
                'filter_counts' => $counts,
            ]);
        }

        // Flat paginated list format
        $normalizedType = match ($filterType) {
            'buyer', 'buyers'                     => 'buyers',
            'craftman', 'craftsman', 'craftsmen' => 'craftsmen',
            'key_user', 'key_users', 'keyuser'    => 'key_users',
            'staff', 'craftsman_staff'            => 'craftsman_staff',
            'admin', 'admins'                     => 'admins',
            default                               => 'all',
        };

        if ($normalizedType === 'buyers') {
            $paginator = $buyerQuery->orderBy('name')->paginate($perPage, ['*'], 'page', $page);
            $items = collect($paginator->items())->map(fn($u) => $this->formatUserSummary($u))->values();
            $total = $paginator->total();
        } elseif ($normalizedType === 'craftsmen') {
            $paginator = $craftsmanQuery->orderBy('name')->paginate($perPage, ['*'], 'page', $page);
            $items = collect($paginator->items())->map(fn($u) => $this->formatUserSummary($u))->values();
            $total = $paginator->total();
        } elseif ($normalizedType === 'key_users') {
            $paginator = $keyUserQuery->orderBy('full_name')->paginate($perPage, ['*'], 'page', $page);
            $items = collect($paginator->items())->map(fn($u) => $this->formatUserSummary($u))->values();
            $total = $paginator->total();
        } elseif ($normalizedType === 'craftsman_staff') {
            $paginator = $staffQuery->orderBy('name')->paginate($perPage, ['*'], 'page', $page);
            $items = collect($paginator->items())->map(fn($u) => $this->formatUserSummary($u))->values();
            $total = $paginator->total();
        } elseif ($normalizedType === 'admins') {
            $paginator = $adminQuery->orderBy('full_name')->paginate($perPage, ['*'], 'page', $page);
            $items = collect($paginator->items())->map(fn($u) => $this->formatUserSummary($u))->values();
            $total = $paginator->total();
        } else {
            // 'all': Combine eligible categories
            $allContacts = collect();

            if ($counts['admins'] > 0) {
                $allContacts = $allContacts->concat($adminQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
            }
            if ($counts['buyers'] > 0) {
                $allContacts = $allContacts->concat($buyerQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
            }
            if ($counts['craftsmen'] > 0) {
                $allContacts = $allContacts->concat($craftsmanQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
            }
            if ($counts['key_users'] > 0) {
                $allContacts = $allContacts->concat($keyUserQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
            }
            if ($counts['craftsman_staff'] > 0) {
                $allContacts = $allContacts->concat($staffQuery->get()->map(fn($u) => $this->formatUserSummary($u)));
            }

            // Natural case-insensitive alphabetical sort by display name
            $allContacts = $allContacts->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

            $total = $allContacts->count();
            $items = $allContacts->slice(($page - 1) * $perPage, $perPage)->values();
        }

        $lastPage = (int) max(1, ceil($total / $perPage));

        return response()->json([
            'success'       => true,
            'data'          => $items,
            'pagination'    => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => $lastPage,
                'from'         => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to'           => min($total, $page * $perPage),
                'has_more'     => $page < $lastPage,
            ],
            'filter_counts' => $counts,
            'active_filter' => $normalizedType,
        ]);
    }

    /**
     * Unified Login-Wise Recent Chats & Recent Messages API with Pagination.
     * Endpoints:
     *   - GET /api/common/chat/recent-chats
     *   - GET /api/common/chat/recent-messages
     *   - GET /api/common/chat/conversations
     */
    public function getRecentChats(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
    }

    if (!$this->hasChatPermission($user)) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have chat permissions enabled. Please contact Super Admin.'
        ], 403);
    }

    $search = trim((string) $request->input('search', ''));
    $page = max(1, (int) $request->input('page', 1));
    $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
    $unreadOnly = filter_var($request->input('unread_only', false), FILTER_VALIDATE_BOOLEAN);
    $typeFilter = strtolower(trim((string) $request->input('type', 'all')));

    $filteredDirectChats = [];
    $totalDirectUnread = 0;

    // 1. Process Direct 1-to-1 Conversations (unless user specifically asked for only groups)
    if ($typeFilter !== 'groups' && $typeFilter !== 'group') {
        $conversations = Conversation::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->where('sender_type', get_class($user));
            })->orWhere(function ($q) use ($user) {
                $q->where('receiver_id', $user->id)->where('receiver_type', get_class($user));
            })
            ->with(['sender', 'receiver', 'latestMessage.attachments'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        // Deduplicate conversations by partner first to prevent duplicate rows & inflated counts
        $uniqueConversations = [];
        $seenPartnerKeys = [];
        foreach ($conversations as $c) {
            $isSender = ($c->sender_id == $user->id && $c->sender_type == get_class($user));
            $partner = $isSender ? $c->receiver : $c->sender;
            if (!$partner) {
                continue;
            }
            $partnerKey = get_class($partner) . ':' . $partner->id;
            if (isset($seenPartnerKeys[$partnerKey])) {
                continue; // Skip duplicate conversation record with the same partner
            }
            $seenPartnerKeys[$partnerKey] = true;
            $uniqueConversations[] = $c;
        }

        $uniqueConversationIds = array_map(fn($c) => $c->id, $uniqueConversations);

        if (!empty($uniqueConversationIds)) {
            $totalDirectUnread = (int) Message::whereIn('conversation_id', $uniqueConversationIds)
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', '!=', $user->id)
                      ->orWhere('sender_type', '!=', get_class($user));
                })
                ->where('is_read', false)
                ->count();
        }

        foreach ($uniqueConversations as $c) {
            $isSender = ($c->sender_id == $user->id && $c->sender_type == get_class($user));
            $partner = $isSender ? $c->receiver : $c->sender;
            if (!$partner) {
                continue;
            }

            $partnerSummary = $this->formatUserSummary($partner);

            // Filter by role/type if requested
            if ($typeFilter !== 'all' && $typeFilter !== '' && $typeFilter !== 'direct' && $typeFilter !== 'conversations') {
                $pType = strtolower($partnerSummary['type'] ?? '');
                if ($typeFilter === 'admins' || $typeFilter === 'admin') {
                    if (!in_array($pType, ['admin', 'super_admin', 'superadmin', 'process_owner'])) continue;
                } elseif ($typeFilter === 'buyers' || $typeFilter === 'buyer') {
                    if ($pType !== 'buyer') continue;
                } elseif ($typeFilter === 'craftsmen' || $typeFilter === 'craftsman') {
                    if ($pType !== 'craftsman') continue;
                } elseif ($typeFilter === 'key_users' || $typeFilter === 'key_user') {
                    if ($pType !== 'key_user') continue;
                } elseif ($typeFilter === 'craftsman_staff' || $typeFilter === 'staff') {
                    if ($pType !== 'craftsman_staff') continue;
                }
            }

            // Search filter
            if ($search !== '') {
                $searchLower = strtolower($search);
                $nameMatch  = str_contains(strtolower($partnerSummary['name'] ?? ''), $searchLower);
                $codeMatch  = str_contains(strtolower($partnerSummary['code'] ?? ''), $searchLower);
                $emailMatch = str_contains(strtolower($partnerSummary['email'] ?? ''), $searchLower);
                $phoneMatch = str_contains(strtolower($partnerSummary['mobile'] ?? ''), $searchLower);
                $msgMatch   = $c->latestMessage && str_contains(strtolower($c->latestMessage->body ?? ''), $searchLower);
                if (!$nameMatch && !$codeMatch && !$emailMatch && !$phoneMatch && !$msgMatch) {
                    continue;
                }
            }

            $unreadCount = Message::where('conversation_id', $c->id)
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', '!=', $user->id)
                      ->orWhere('sender_type', '!=', get_class($user));
                })
                ->where('is_read', false)
                ->count();

            if ($unreadOnly && $unreadCount === 0) {
                continue;
            }

            $recentMessageData = $c->latestMessage ? $this->formatMessageSummary($c->latestMessage) : null;
            $sortTime = $c->last_message_at ? $c->last_message_at->timestamp : ($c->updated_at ? $c->updated_at->timestamp : 0);

            $filteredDirectChats[] = [
                'unique_id'         => 'direct_' . $c->id,
                'id'                => (int) $c->id,
                'conversation_id'   => (int) $c->id,
                'is_group'          => false,
                'chat_type'         => 'direct',
                'channel_name'      => 'private-conversation.' . $c->id,
                'name'              => $partnerSummary['name'] ?? '',
                'avatar_url'        => $partnerSummary['avatar_url'] ?? null,
                'partner'           => $partnerSummary,
                'last_message'      => $recentMessageData,
                'unread_count'      => (int) $unreadCount,
                'last_message_at'   => optional($c->last_message_at)->toISOString(),
                'created_at'        => optional($c->created_at)->toISOString(),
                '_sort_time'        => $sortTime,
            ];
        }
    }

    // 2. Process Chat Groups (included in all, or when requested via type=groups)
    $filteredGroups = [];
    $totalGroupUnread = 0;

    $allowedGroupTypes = ['all', 'groups', 'group', ''];
    if (in_array($typeFilter, $allowedGroupTypes)) {
        $isSuperAdmin = $this->isSuperAdmin($user);

        if ($isSuperAdmin && $request->boolean('all')) {
            $groups = ChatGroup::with(['creator', 'participants.user', 'latestMessage.attachments'])
                ->orderByDesc('updated_at')
                ->get();
        } else {
            $groupIds = ChatGroupParticipant::where('user_id', $user->id)
                ->where('user_type', get_class($user))
                ->pluck('chat_group_id')
                ->toArray();

            $createdGroupIds = ChatGroup::where('creator_id', $user->id)
                ->where('creator_type', get_class($user))
                ->pluck('id')
                ->toArray();

            $allGroupIds = array_unique(array_merge($groupIds, $createdGroupIds));
            $groupQuery = ChatGroup::whereIn('id', $allGroupIds);

            if ($user instanceof ProcessOwner) {
                $groupQuery->orWhere('is_global_admin_group', true);
            }

            $groups = $groupQuery->with(['creator', 'participants.user', 'latestMessage.attachments'])
                ->orderByDesc('updated_at')
                ->get();
        }

        $seenGroupIds = [];
        foreach ($groups as $g) {
            if (isset($seenGroupIds[$g->id])) {
                continue;
            }
            $seenGroupIds[$g->id] = true;

            // Search filter on group
            if ($search !== '') {
                $searchLower = strtolower($search);
                $nameMatch = str_contains(strtolower($g->name ?? ''), $searchLower);
                $msgMatch = $g->latestMessage && str_contains(strtolower($g->latestMessage->body ?? ''), $searchLower);
                if (!$nameMatch && !$msgMatch) {
                    continue;
                }
            }

            $unreadCount = Message::where('chat_group_id', $g->id)
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', '!=', $user->id)
                      ->orWhere('sender_type', '!=', get_class($user));
                })
                ->where('is_read', false)
                ->count();

            if ($unreadOnly && $unreadCount === 0) {
                continue;
            }

            $totalGroupUnread += (int) $unreadCount;
            $recentMessageData = $g->latestMessage ? $this->formatMessageSummary($g->latestMessage) : null;
            $lastActivityAt = $g->latestMessage ? $g->latestMessage->created_at : $g->updated_at;
            $sortTime = $lastActivityAt ? $lastActivityAt->timestamp : ($g->updated_at ? $g->updated_at->timestamp : 0);

            $groupSummary = [
                'unique_id'          => 'group_' . $g->id,
                'id'                 => (int) $g->id,
                'conversation_id'    => (int) $g->id,
                'chat_group_id'      => (int) $g->id,
                'is_group'           => true,
                'chat_type'          => 'group',
                'channel_name'       => 'private-conversation.' . $g->id,
                'name'               => $g->name,
                'avatar_url'         => $g->avatar ?? null,
                'partner'            => [
                    'id'         => (int) $g->id,
                    'name'       => $g->name,
                    'avatar_url' => $g->avatar ?? null,
                    'type'       => 'group',
                    'badge'      => 'Group',
                ],
                'creator'            => $this->formatUserSummary($g->creator),
                'participants_count' => (int) $g->participants->count(),
                'last_message'       => $recentMessageData,
                'unread_count'       => (int) $unreadCount,
                'last_message_at'    => optional($lastActivityAt)->toISOString(),
                'updated_at'         => optional($g->updated_at)->toISOString(),
                'created_at'         => optional($g->created_at)->toISOString(),
                '_sort_time'         => $sortTime,
            ];

            $filteredGroups[] = $groupSummary;
        }
    }

    // 3. Unify, sort and paginate
    if ($typeFilter === 'groups' || $typeFilter === 'group') {
        $unifiedChats = $filteredGroups;
    } elseif ($typeFilter === 'direct' || $typeFilter === 'conversations') {
        $unifiedChats = $filteredDirectChats;
    } else {
        $unifiedChats = array_merge($filteredDirectChats, $filteredGroups);
    }

    // Strictly deduplicate unified chats by unique_id
    $dedupedUnified = [];
    $seenKeys = [];
    foreach ($unifiedChats as $item) {
        $key = $item['unique_id'] ?? (($item['is_group'] ? 'group_' : 'direct_') . $item['id']);
        if (!isset($seenKeys[$key])) {
            $seenKeys[$key] = true;
            $dedupedUnified[] = $item;
        }
    }
    $unifiedChats = $dedupedUnified;

    usort($unifiedChats, fn($a, $b) => ($b['_sort_time'] ?? 0) <=> ($a['_sort_time'] ?? 0));

    // Strip temporary _sort_time key
    foreach ($unifiedChats as &$item) {
        unset($item['_sort_time']);
    }
    unset($item);

    $cleanGroups = array_map(function ($g) {
        unset($g['_sort_time']);
        return $g;
    }, $filteredGroups);

    $cleanDirect = array_map(function ($d) {
        unset($d['_sort_time']);
        return $d;
    }, $filteredDirectChats);

    $total = count($unifiedChats);
    $lastPage = (int) max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $paginatedData = array_slice($unifiedChats, $offset, $perPage);
    $totalUnreadCount = $totalDirectUnread + $totalGroupUnread;

    return response()->json([
        'success'            => true,
        'total_unread_count' => (int) $totalUnreadCount,
        'data'               => $paginatedData,
        'pagination'         => [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($total, $page * $perPage),
            'has_more'     => $page < $lastPage,
        ],
    ]);
}

    /**
     * Backward-compatible aliases
     */
    public function getConversations(Request $request)
    {
        return $this->getRecentChats($request);
    }

    public function getRecentMessages(Request $request)
    {
        return $this->getRecentChats($request);
    }

    /**
     * Start or fetch existing direct 1-to-1 conversation or chat group.
     * Unified endpoint: gracefully handles both direct chats and groups by ID, target_type or flags.
     */
    public function startConversation(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Auto-decode JSON if Content-Type was omitted in Postman/Client
        $rawContent = $request->getContent();
        if (!empty($rawContent)) {
            $json = json_decode($rawContent, true);
            if (is_array($json)) {
                $request->merge($json);
            }
        }

        $typeParam = strtolower(trim((string)($request->input('target_type') ?? $request->input('recipient_type') ?? $request->input('type') ?? $request->input('chat_type') ?? '')));
        $isGroupFlag = $request->boolean('is_group') || in_array($typeParam, ['group', 'groups']);

        // 1. Resolve Group if chat_group_id is given, or if marked as group
        $groupId = $request->input('chat_group_id') ?? $request->input('group_id');
        if (!$groupId && $isGroupFlag) {
            $groupId = $request->input('target_id') ?? $request->input('id') ?? $request->input('conversation_id');
        }

        // If no explicit group flag, check if given ID belongs to a ChatGroup
        if (!$groupId && $request->filled('conversation_id')) {
            $possibleGroup = ChatGroup::find($request->input('conversation_id'));
            if ($possibleGroup) {
                $groupId = $possibleGroup->id;
                $isGroupFlag = true;
            }
        }
        if (!$groupId && $request->filled('id') && empty($typeParam)) {
            $possibleGroup = ChatGroup::find($request->input('id'));
            if ($possibleGroup) {
                $groupId = $possibleGroup->id;
                $isGroupFlag = true;
            }
        }

        if ($groupId && ($isGroupFlag || ChatGroup::where('id', $groupId)->exists())) {
            $group = ChatGroup::with(['creator', 'participants.user', 'latestMessage.attachments'])->find($groupId);
            if (!$group) {
                return response()->json(['success' => false, 'message' => "Chat Group #{$groupId} not found."], 404);
            }

            if (!$group->hasParticipant($user) && !$this->isSuperAdmin($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized - You are not a member of this chat group.'], 403);
            }

            $groupSummary = $this->formatGroupSummary($group, $user, true);
            $partnerObj = [
                'id'         => (int) $group->id,
                'name'       => $group->name,
                'avatar_url' => $group->avatar ?? null,
                'type'       => 'group',
                'badge'      => 'Group',
            ];

            return response()->json([
                'success'         => true,
                'message'         => 'Group conversation fetched successfully.',
                'is_group'        => true,
                'chat_type'       => 'group',
                'id'              => (int) $group->id,
                'conversation_id' => (int) $group->id,
                'chat_group_id'   => (int) $group->id,
                'partner'         => $partnerObj,
                'data'            => array_merge([
                    'id'              => (int) $group->id,
                    'conversation_id' => (int) $group->id,
                    'chat_group_id'   => (int) $group->id,
                    'is_group'        => true,
                    'chat_type'       => 'group',
                    'name'            => $group->name,
                    'partner'         => $partnerObj,
                ], $groupSummary)
            ], 200);
        }

        // 2. Check if an existing direct conversation ID was passed
        $convId = $request->input('conversation_id') ?? ($request->filled('id') && empty($typeParam) ? $request->input('id') : null);
        if ($convId) {
            $existingConv = Conversation::with(['sender', 'receiver', 'latestMessage.attachments'])->find($convId);
            if ($existingConv) {
                $isParticipant = ($existingConv->sender_id == $user->id && $existingConv->sender_type == get_class($user))
                    || ($existingConv->receiver_id == $user->id && $existingConv->receiver_type == get_class($user))
                    || $this->isSuperAdmin($user);

                if ($isParticipant) {
                    $partner = ($existingConv->sender_id == $user->id && $existingConv->sender_type == get_class($user))
                        ? $existingConv->receiver
                        : $existingConv->sender;
                    $partnerSummary = $partner ? $this->formatUserSummary($partner) : null;

                    return response()->json([
                        'success'         => true,
                        'message'         => 'Conversation fetched successfully.',
                        'is_group'        => false,
                        'chat_type'       => 'direct',
                        'id'              => (int) $existingConv->id,
                        'conversation_id' => (int) $existingConv->id,
                        'partner'         => $partnerSummary,
                        'data'            => [
                            'id'              => (int) $existingConv->id,
                            'conversation_id' => (int) $existingConv->id,
                            'is_group'        => false,
                            'chat_type'       => 'direct',
                            'partner'         => $partnerSummary,
                            'last_message_at' => optional($existingConv->last_message_at)->toISOString(),
                            'latest_message'  => $existingConv->latestMessage ? $this->formatMessageSummary($existingConv->latestMessage) : null,
                        ]
                    ], 200);
                }
            }
        }

        // 3. Start or fetch 1-to-1 conversation with target user
        $targetId = $request->input('target_id') ?? $request->input('recipient_id') ?? $request->input('user_id');
        $targetType = $typeParam ?: ($request->input('target_type') ?? $request->input('recipient_type') ?? $request->input('type'));

        if (!$targetId || !$targetType) {
            return response()->json([
                'success' => false,
                'message' => 'Both target_id (or recipient_id) and target_type (or recipient_type) are required.'
            ], 422);
        }

        $targetClass = $this->resolveModelClass($targetType);

        if (!$targetClass) {
            return response()->json(['success' => false, 'message' => 'Invalid target user type.'], 400);
        }

        $target = $this->resolveTargetModel($targetClass, $targetId);
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

        // Send optional initial message if provided (text, image, video, audio, document, or live location)
        $initialMessage = null;
        $hasInitialLocation = ($request->filled('latitude') && $request->filled('longitude'))
            || ($request->filled('lat') && ($request->filled('lng') || $request->filled('long')))
            || $request->filled('location')
            || $request->filled('map_url')
            || $request->filled('live_location');

        $hasInitialMedia = $request->hasFile('attachments') 
            || $request->hasFile('image') 
            || $request->hasFile('images') 
            || $request->hasFile('video') 
            || $request->hasFile('videos') 
            || $request->hasFile('audio') 
            || $request->hasFile('audios') 
            || $request->hasFile('voice') 
            || $request->hasFile('voice_note') 
            || $request->hasFile('document') 
            || $request->hasFile('documents') 
            || $request->hasFile('doc') 
            || $request->hasFile('docs') 
            || $request->hasFile('file') 
            || $request->hasFile('files') 
            || $request->hasFile('media') 
            || $request->filled('video_url') 
            || $request->filled('audio_url') 
            || $request->filled('voice_url')
            || $request->filled('document_url')
            || $request->filled('doc_url')
            || $hasInitialLocation
            || (is_string($request->input('video')) && filter_var($request->input('video'), FILTER_VALIDATE_URL))
            || (is_string($request->input('audio')) && filter_var($request->input('audio'), FILTER_VALIDATE_URL))
            || (is_string($request->input('voice')) && filter_var($request->input('voice'), FILTER_VALIDATE_URL))
            || (!empty($request->input('attachments')) && is_array($request->input('attachments')));

        if ($request->filled('body') || $hasInitialMedia) {
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
     * Resolve whether an ID corresponds to a ChatGroup or a 1-to-1 Conversation.
     * Smartly disambiguates even when IDs overlap based on participant membership & recent activity.
     */
    private function resolveConversationOrGroup($id, $user, Request $request): ?array
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        $isGroupHint = null;
        if ($request->has('is_group')) {
            $isGroupHint = filter_var($request->input('is_group'), FILTER_VALIDATE_BOOLEAN);
        }
        $typeHint = strtolower(trim((string) ($request->input('chat_type') ?? $request->input('type') ?? '')));
        $explicitGroupId = $request->input('chat_group_id') ?? $request->input('group_id');

        // 1. Explicit group hints
        if ($isGroupHint === true || $typeHint === 'group' || !empty($explicitGroupId)) {
            $groupId = !empty($explicitGroupId) ? (int) $explicitGroupId : $id;
            $group = ChatGroup::find($groupId);
            if ($group) {
                return ['type' => 'group', 'model' => $group];
            }
        }

        // 2. Explicit direct conversation hints
        if ($isGroupHint === false || in_array($typeHint, ['direct', 'conversation', 'single', 'individual'])) {
            $conv = Conversation::find($id);
            if ($conv) {
                return ['type' => 'conversation', 'model' => $conv];
            }
        }

        // 3. No explicit hint provided: check both models
        $group = ChatGroup::find($id);
        $conv = Conversation::find($id);

        if ($group && !$conv) {
            return ['type' => 'group', 'model' => $group];
        }

        if ($conv && !$group) {
            return ['type' => 'conversation', 'model' => $conv];
        }

        // Both exist with this same ID: disambiguate by checking user participation
        if ($group && $conv) {
            $isGroupMember = $group->hasParticipant($user);
            $isConvMember = ($conv->sender_id == $user->id && $conv->sender_type == get_class($user))
                || ($conv->receiver_id == $user->id && $conv->receiver_type == get_class($user));

            if ($isGroupMember && !$isConvMember) {
                return ['type' => 'group', 'model' => $group];
            }

            if ($isConvMember && !$isGroupMember) {
                return ['type' => 'conversation', 'model' => $conv];
            }

            // If user is participant in BOTH (or SuperAdmin in both), compare latest activity
            $convActiveAt = $conv->last_message_at ?? $conv->updated_at;
            $groupActiveAt = $group->updated_at ?? $group->created_at;

            if ($groupActiveAt && $convActiveAt) {
                return ($groupActiveAt >= $convActiveAt)
                    ? ['type' => 'group', 'model' => $group]
                    : ['type' => 'conversation', 'model' => $conv];
            }

            return ['type' => 'conversation', 'model' => $conv];
        }

        // 4. Neither exists with ID $id as primary key:
        // Check if $id was passed as partner's user ID for a direct conversation
        $targetTypeStr = $request->input('target_type') 
            ?? $request->input('partner_type') 
            ?? $request->input('recipient_type') 
            ?? $request->input('user_type');
        $targetClass = $targetTypeStr ? $this->resolveModelClass($targetTypeStr) : null;

        $partnerQuery = Conversation::where(function ($q) use ($user, $id, $targetClass) {
            $q->where('sender_id', $user->id)->where('sender_type', get_class($user))
              ->where('receiver_id', $id);
            if ($targetClass) {
                $q->where('receiver_type', $targetClass);
            }
        })->orWhere(function ($q) use ($user, $id, $targetClass) {
            $q->where('receiver_id', $user->id)->where('receiver_type', get_class($user))
              ->where('sender_id', $id);
            if ($targetClass) {
                $q->where('sender_type', $targetClass);
            }
        });

        $partnerConv = $partnerQuery->orderByDesc('last_message_at')->first();

        if ($partnerConv) {
            return ['type' => 'conversation', 'model' => $partnerConv];
        }

        return null;
    }

    /**
     * Get messages in a direct 1-to-1 conversation or chat group.
     * Unified endpoint: works for both 1-to-1 conversations and groups by ID.
     */
    public function getConversationMessages(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            if (!$this->hasChatPermission($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have chat permissions enabled. Please contact Super Admin.'
                ], 403);
            }

            $resolved = $this->resolveConversationOrGroup($id, $user, $request);
            if (!$resolved) {
                return response()->json([
                    'success' => false,
                    'message' => "Conversation or Group #{$id} not found."
                ], 404);
            }

            $page = max(1, (int) $request->input('page', 1));
            $perPage = max(1, min(100, (int) $request->input('per_page', 50)));
            $order = strtolower(trim((string) $request->input('order', 'asc')));
            $fromOldest = filter_var($request->input('from_oldest', false), FILTER_VALIDATE_BOOLEAN)
                || filter_var($request->input('from_start', false), FILTER_VALIDATE_BOOLEAN);

            // 1. GROUP CHAT MESSAGES
            if ($resolved['type'] === 'group') {
                $group = $resolved['model'];

                if (!$group->hasParticipant($user) && !$this->isSuperAdmin($user)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized to view this group chat.'], 403);
                }

                // Mark incoming group messages as read
                try {
                    Message::where('chat_group_id', $group->id)
                        ->where(function ($q) use ($user) {
                            $q->where('sender_id', '!=', $user->id)
                              ->orWhere('sender_type', '!=', get_class($user));
                        })
                        ->where('is_read', false)
                        ->update(['is_read' => true]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to mark group messages as read: ' . $e->getMessage());
                }

                $totalCount = (int) Message::where('chat_group_id', $group->id)->count();
                $lastPage = (int) max(1, ceil($totalCount / $perPage));

                if ($order === 'desc') {
                    $messages = Message::where('chat_group_id', $group->id)
                        ->with(['attachments', 'sender'])
                        ->orderBy('id', 'desc')
                        ->paginate($perPage);

                    $formattedMessages = [];
                    foreach ($messages->items() as $m) {
                        $formattedMessages[] = $this->formatMessageSummary($m);
                    }
                } elseif ($fromOldest) {
                    $messages = Message::where('chat_group_id', $group->id)
                        ->with(['attachments', 'sender'])
                        ->orderBy('id', 'asc')
                        ->paginate($perPage);

                    $formattedMessages = [];
                    foreach ($messages->items() as $m) {
                        $formattedMessages[] = $this->formatMessageSummary($m);
                    }
                } else {
                    if ($page > $lastPage) {
                        $formattedMessages = [];
                    } else {
                        $calculatedOffset = $totalCount - ($page * $perPage);
                        if ($calculatedOffset < 0) {
                            $limit = $perPage + $calculatedOffset;
                            $offset = 0;
                        } else {
                            $offset = $calculatedOffset;
                            $limit = $perPage;
                        }

                        $items = Message::where('chat_group_id', $group->id)
                            ->with(['attachments', 'sender'])
                            ->orderBy('id', 'asc')
                            ->offset($offset)
                            ->limit($limit)
                            ->get();

                        $formattedMessages = $items->map(fn($m) => $this->formatMessageSummary($m))->values()->toArray();
                    }
                }

                $groupSummary = $this->formatGroupSummary($group, $user, true);
                $partnerSummary = [
                    'id'        => (int) $group->id,
                    'name'      => $group->name,
                    'full_name' => $group->name,
                    'label'     => $group->name,
                    'code'      => 'GROUP-' . $group->id,
                    'type'      => 'group',
                    'role'      => 'group',
                    'avatar'    => null,
                    'image'     => null,
                    'image_url' => null,
                ];

                Log::info("[CHAT GET GROUP MESSAGES] Group #{$group->id} requested by User " . get_class($user) . " #{$user->id}", [
                    'group_id'       => (int) $group->id,
                    'page'           => $page,
                    'total_messages' => $totalCount,
                    'returned_count' => count($formattedMessages),
                ]);

                return response()->json([
                    'success'         => true,
                    'conversation_id' => (int) $group->id,
                    'id'              => (int) $group->id,
                    'is_group'        => true,
                    'chat_type'       => 'group',
                    'group'           => $groupSummary,
                    'partner'         => $partnerSummary,
                    'user'            => $partnerSummary,
                    'messages'        => $formattedMessages,
                    'data'            => $formattedMessages,
                    'pagination'      => [
                        'current_page' => $page,
                        'last_page'    => $lastPage,
                        'per_page'     => $perPage,
                        'total'        => $totalCount,
                        'has_more'     => $page < $lastPage,
                    ]
                ]);
            }

            // 2. DIRECT 1-TO-1 CONVERSATION MESSAGES
            $conversation = $resolved['model'];
            $isParticipant = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
                || ($conversation->receiver_id == $user->id && $conversation->receiver_type == get_class($user));

            if (!$isParticipant && !$this->isSuperAdmin($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this conversation.'
                ], 403);
            }

            // Mark incoming messages as read
            try {
                Message::where('conversation_id', $conversation->id)
                    ->where(function ($q) use ($user) {
                        $q->where('sender_id', '!=', $user->id)
                          ->orWhere('sender_type', '!=', get_class($user));
                    })
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            } catch (\Throwable $e) {
                Log::warning('Failed to mark messages as read: ' . $e->getMessage());
            }

            $totalCount = (int) Message::where('conversation_id', $conversation->id)->count();
            $lastPage = (int) max(1, ceil($totalCount / $perPage));

            if ($order === 'desc') {
                $messages = Message::where('conversation_id', $conversation->id)
                    ->with(['attachments', 'sender'])
                    ->orderBy('id', 'desc')
                    ->paginate($perPage);

                $formattedMessages = [];
                foreach ($messages->items() as $m) {
                    $formattedMessages[] = $this->formatMessageSummary($m);
                }
            } elseif ($fromOldest) {
                $messages = Message::where('conversation_id', $conversation->id)
                    ->with(['attachments', 'sender'])
                    ->orderBy('id', 'asc')
                    ->paginate($perPage);

                $formattedMessages = [];
                foreach ($messages->items() as $m) {
                    $formattedMessages[] = $this->formatMessageSummary($m);
                }
            } else {
                if ($page > $lastPage) {
                    $formattedMessages = [];
                } else {
                    $calculatedOffset = $totalCount - ($page * $perPage);
                    if ($calculatedOffset < 0) {
                        $limit = $perPage + $calculatedOffset;
                        $offset = 0;
                    } else {
                        $offset = $calculatedOffset;
                        $limit = $perPage;
                    }

                    $items = Message::where('conversation_id', $conversation->id)
                        ->with(['attachments', 'sender'])
                        ->orderBy('id', 'asc')
                        ->offset($offset)
                        ->limit($limit)
                        ->get();

                    $formattedMessages = $items->map(fn($m) => $this->formatMessageSummary($m))->values()->toArray();
                }
            }

            $partner = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
                ? $conversation->receiver
                : $conversation->sender;

            $partnerSummary = $partner ? $this->formatUserSummary($partner) : null;

            Log::info("[CHAT GET MESSAGES] Conversation #{$conversation->id} requested by User " . get_class($user) . " #{$user->id}", [
                'conversation_id' => (int) $conversation->id,
                'page'            => $page,
                'total_messages'  => $totalCount,
                'returned_count'  => count($formattedMessages),
                'first_id'        => !empty($formattedMessages) ? $formattedMessages[0]['id'] : null,
                'last_id'         => !empty($formattedMessages) ? end($formattedMessages)['id'] : null,
            ]);

            return response()->json([
                'success'         => true,
                'conversation_id' => (int) $conversation->id,
                'id'              => (int) $conversation->id,
                'is_group'        => false,
                'chat_type'       => 'direct',
                'partner'         => $partnerSummary,
                'user'            => $partnerSummary,
                'group'           => null,
                'messages'        => $formattedMessages,
                'data'            => $formattedMessages,
                'pagination'      => [
                    'current_page' => $page,
                    'last_page'    => $lastPage,
                    'per_page'     => $perPage,
                    'total'        => $totalCount,
                    'has_more'     => $page < $lastPage,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('getConversationMessages error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * Send message in a direct conversation or group chat.
     * Unified endpoint: works for both 1-to-1 conversations and groups by ID.
     */
    public function sendConversationMessage(Request $request, $id)
    {
        try {
            // Auto-decode JSON if Content-Type was omitted in Postman/Client
            $rawContent = $request->getContent();
            if (!empty($rawContent)) {
                $json = json_decode($rawContent, true);
                if (is_array($json)) {
                    $request->merge($json);
                }
            }

            $request->validate([
                'body'             => 'nullable|string',
                'message'          => 'nullable|string',
                'text'             => 'nullable|string',
                'content'          => 'nullable|string',
                'attachments.*'    => 'nullable|file|max:102400',
                'image'            => 'nullable',
                'images.*'         => 'nullable|file|max:51200',
                'video'            => 'nullable',
                'videos.*'         => 'nullable|file|max:102400',
                'audio'            => 'nullable',
                'audios.*'         => 'nullable|file|max:51200',
                'voice'            => 'nullable',
                'voice_note'       => 'nullable',
                'document'         => 'nullable',
                'documents.*'      => 'nullable|file|max:102400',
                'doc'              => 'nullable',
                'docs.*'           => 'nullable|file|max:102400',
                'file'             => 'nullable',
                'files.*'          => 'nullable|file|max:102400',
                'video_url'        => 'nullable|string',
                'audio_url'        => 'nullable|string',
                'voice_url'        => 'nullable|string',
                'document_url'     => 'nullable|string',
                'doc_url'          => 'nullable|string',
                'latitude'         => 'nullable',
                'longitude'        => 'nullable',
                'lat'              => 'nullable',
                'lng'              => 'nullable',
                'long'             => 'nullable',
                'location'         => 'nullable',
                'live_location'    => 'nullable',
                'is_live_location' => 'nullable',
                'map_url'          => 'nullable|string',
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            if (!$this->hasChatPermission($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have chat permissions enabled. Please contact Super Admin.'
                ], 403);
            }

            $resolved = $this->resolveConversationOrGroup($id, $user, $request);
            if (!$resolved) {
                return response()->json([
                    'success' => false,
                    'message' => "Conversation or Group #{$id} not found."
                ], 404);
            }

            $bodyText = $request->input('body') 
                ?? $request->input('message') 
                ?? $request->input('text') 
                ?? $request->input('content')
                ?? $request->input('reply')
                ?? $request->input('input')
                ?? $request->input('action_input')
                ?? (!empty($rawContent) && !str_starts_with(trim($rawContent), '{') ? trim($rawContent) : null);

            $hasLocation = ($request->filled('latitude') && $request->filled('longitude'))
                || ($request->filled('lat') && ($request->filled('lng') || $request->filled('long')))
                || $request->filled('location')
                || $request->filled('map_url')
                || $request->filled('live_location');

            $hasText = !is_null($bodyText) && trim((string)$bodyText) !== '';
            $hasMedia = $request->hasFile('attachments') 
                || $request->hasFile('image') 
                || $request->hasFile('images') 
                || $request->hasFile('video') 
                || $request->hasFile('videos') 
                || $request->hasFile('audio') 
                || $request->hasFile('audios') 
                || $request->hasFile('voice') 
                || $request->hasFile('voice_note') 
                || $request->hasFile('document') 
                || $request->hasFile('documents') 
                || $request->hasFile('doc') 
                || $request->hasFile('docs') 
                || $request->hasFile('file') 
                || $request->hasFile('files') 
                || $request->hasFile('media') 
                || $request->filled('video_url') 
                || $request->filled('audio_url') 
                || $request->filled('voice_url')
                || $request->filled('document_url')
                || $request->filled('doc_url')
                || $hasLocation
                || (is_string($request->input('video')) && filter_var($request->input('video'), FILTER_VALIDATE_URL))
                || (is_string($request->input('audio')) && filter_var($request->input('audio'), FILTER_VALIDATE_URL))
                || (is_string($request->input('voice')) && filter_var($request->input('voice'), FILTER_VALIDATE_URL))
                || (!empty($request->input('attachments')) && is_array($request->input('attachments')));

            if (!$hasText && !$hasMedia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message body, a media attachment (video, audio, document, image), or live location is required.',
                    'debug'   => [
                        'received_keys' => array_keys($request->all()),
                        'supported_media_keys' => ['video', 'audio', 'document', 'live_location', 'latitude', 'longitude', 'attachments', 'image', 'file', 'video_url', 'audio_url', 'document_url'],
                        'hint' => 'Send text/URL via "body", upload files via "video", "audio", "document", or send live location via "latitude" & "longitude".'
                    ]
                ], 422);
            }

            // 1. SEND GROUP MESSAGE
            if ($resolved['type'] === 'group') {
                $group = $resolved['model'];
                if (!$group->hasParticipant($user) && !$this->isSuperAdmin($user)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized to send messages in this group.'], 403);
                }

                $message = $this->storeMessageRecord(
                    conversationId: null,
                    chatGroupId: $group->id,
                    user: $user,
                    request: $request
                );

                $group->touch(); // updates updated_at

                // Broadcast WebSocket (Pusher) event to group channel and conversation channel
                try {
                    $broadcastEvent = new MessageSent($message);
                    $eventChannels = array_map(fn($c) => $c->name, $broadcastEvent->broadcastOn());
                    Log::info("[PUSHER GROUP BROADCAST] Sending message #{$message->id} to Group #{$group->id}:", [
                        'group_id'   => $group->id,
                        'channels'   => $eventChannels,
                        'event_name' => $broadcastEvent->broadcastAs(),
                        'sender'     => get_class($user) . ' #' . $user->id,
                    ]);
                    broadcast($broadcastEvent);
                    Log::info("[PUSHER GROUP BROADCAST] Group message #{$message->id} successfully broadcasted to Pusher!");
                } catch (\Throwable $e) {
                    Log::error('[PUSHER GROUP BROADCAST FAILED]: ' . $e->getMessage());
                }

                // Send FCM Push notifications to all group participants (except sender)
                $senderSummary = $this->formatUserSummary($user);
                if (!empty($message->body)) {
                    $bodyPreview = $message->body;
                } elseif ($message->attachments->isNotEmpty()) {
                    $firstAtt = $message->attachments->first();
                    $bodyPreview = match ($firstAtt->file_type) {
                        'audio'    => '🎤 Voice / Audio message',
                        'video'    => '🎥 Video message',
                        'image'    => '📷 Photo',
                        'document' => '📄 Document: ' . $firstAtt->file_name,
                        default    => 'Sent media attachment',
                    };
                } else {
                    $bodyPreview = 'New message';
                }
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
                                    'conversation_id' => (string) $group->id,
                                    'chat_group_id'   => (string) $group->id,
                                    'is_group'        => 'true',
                                    'group_name'      => (string) $group->name,
                                    'sender_name'     => (string) $senderSummary['name'],
                                ]
                            ));
                        } catch (\Throwable $e) {
                            Log::warning('FCM group participant push failed (non-fatal): ' . $e->getMessage());
                        }
                    }
                }

                $formattedMessage = $this->formatMessageSummary($message);

                return response()->json([
                    'success'         => true,
                    'is_group'        => true,
                    'chat_type'       => 'group',
                    'conversation_id' => (int) $group->id,
                    'chat_group_id'   => (int) $group->id,
                    'message'         => 'Group message sent successfully.',
                    'data'            => $formattedMessage
                ], 201);
            }

            // 2. SEND DIRECT 1-TO-1 CONVERSATION MESSAGE
            $conversation = $resolved['model'];
            $isParticipant = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
                || ($conversation->receiver_id == $user->id && $conversation->receiver_type == get_class($user));

            if (!$isParticipant && !$this->isSuperAdmin($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized to send message here.'], 403);
            }

            $message = $this->storeMessageRecord(
                conversationId: $conversation->id,
                chatGroupId: null,
                user: $user,
                request: $request
            );

            $conversation->update(['last_message_at' => now()]);

            // Broadcast WebSocket (Pusher) event
            try {
                $broadcastEvent = new MessageSent($message);
                $eventChannels = array_map(fn($c) => $c->name, $broadcastEvent->broadcastOn());
                $eventPayload = $broadcastEvent->broadcastWith();

                Log::info("[PUSHER CHAT BROADCAST] Sending message #{$message->id} to Pusher:", [
                    'conversation_id' => $conversation->id,
                    'event_name'      => $broadcastEvent->broadcastAs(),
                    'channels'        => $eventChannels,
                    'sender'          => get_class($user) . ' #' . $user->id,
                    'payload_preview' => [
                        'id'          => $eventPayload['id'] ?? null,
                        'body'        => $eventPayload['body'] ?? '',
                        'sender_name' => $eventPayload['sender_name'] ?? '',
                        'attachments' => count($eventPayload['attachments'] ?? []),
                        'created_at'  => $eventPayload['created_at'] ?? '',
                    ]
                ]);

                broadcast($broadcastEvent);

                Log::info("[PUSHER CHAT BROADCAST] Message #{$message->id} successfully broadcasted to Pusher Cloud!");
            } catch (\Throwable $e) {
                Log::error("[PUSHER CHAT BROADCAST FAILED]: " . $e->getMessage(), [
                    'line'  => $e->getLine(),
                    'file'  => basename($e->getFile()),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Send FCM Push Notification to the partner
            $partner = ($conversation->sender_id == $user->id && $conversation->sender_type == get_class($user))
                ? $conversation->receiver
                : $conversation->sender;

            if ($partner && method_exists($partner, 'notify')) {
                try {
                    $senderSummary = $this->formatUserSummary($user);
                    if (!empty($message->body)) {
                        $bodyPreview = $message->body;
                    } elseif ($message->attachments->isNotEmpty()) {
                        $firstAtt = $message->attachments->first();
                        $bodyPreview = match ($firstAtt->file_type) {
                            'audio'    => '🎤 Voice / Audio message',
                            'video'    => '🎥 Video message',
                            'image'    => '📷 Photo',
                            'document' => '📄 Document: ' . $firstAtt->file_name,
                            default    => 'Sent media attachment',
                        };
                    } else {
                        $bodyPreview = 'New message';
                    }

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
                'success'         => true,
                'is_group'        => false,
                'chat_type'       => 'direct',
                'conversation_id' => (int) $conversation->id,
                'message'         => 'Message sent successfully.',
                'data'            => $this->formatMessageSummary($message)
            ], 201);
        } catch (\Throwable $e) {
            Log::error('sendConversationMessage error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => basename($e->getFile()),
            ], 500);
        }
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

        if (!$this->hasChatPermission($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have chat permissions enabled. Please contact Super Admin.'
            ], 403);
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
        return $this->getRecentChats($request->merge(['type' => 'groups']));
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
     * Get messages in a group chat (Delegates to unified getConversationMessages).
     */
    public function getGroupMessages(Request $request, $id)
    {
        $request->merge(['is_group' => true]);
        return $this->getConversationMessages($request, $id);
    }

    /**
     * Send group message (Delegates to unified sendConversationMessage).
     */
    public function sendGroupMessage(Request $request, $id)
    {
        $request->merge(['is_group' => true]);
        return $this->sendConversationMessage($request, $id);
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
     * Check if a user has permission to use the chat module.
     * Respects 'chat' and 'messages' permissions assigned by Super Admin.
     */
    private function hasChatPermission($user): bool
    {
        if (!$user) return false;

        // Super Admin & Process Owners (Admins) always have full access
        if ($this->isSuperAdmin($user) || $user instanceof ProcessOwner) {
            return true;
        }

        // Key User: check their permissions, or fallback to parent Buyer
        if ($user instanceof KeyUser) {
            if ($user->permissions === null) {
                if ($user->buyer) {
                    return $this->hasChatPermission($user->buyer);
                }
                return true;
            }
            return $user->hasPermission('chat') || $user->hasPermission('messages');
        }

        // Craftsman Staff: check their permissions, or fallback to parent Craftsman
        if ($user instanceof CraftsmanStaff) {
            if ($user->permissions === null) {
                if ($user->craftsman) {
                    return $this->hasChatPermission($user->craftsman);
                }
                return true;
            }
            return $user->hasPermission('chat') || $user->hasPermission('messages');
        }

        // Buyer, Craftsman, or any model with hasPermission
        if (method_exists($user, 'hasPermission')) {
            // If user's permissions column is null (default unconfigured account), allow
            if ($user->permissions === null) {
                return true;
            }
            return $user->hasPermission('chat') || $user->hasPermission('messages');
        }

        return true;
    }

    /**
     * Validate whether two users have permission to communicate directly.
     */
    private function validateCommunicationPermission($user, $target): array
    {
        // 1. Verify current user has chat permission enabled
        if (!$this->hasChatPermission($user)) {
            return [
                'allowed' => false,
                'reason'  => 'You do not have chat permissions enabled. Please contact Super Admin.'
            ];
        }

        // 2. Verify target contact has chat permission enabled
        if (!$this->hasChatPermission($target)) {
            return [
                'allowed' => false,
                'reason'  => 'The selected contact does not have chat permissions enabled.'
            ];
        }

        // 3. Superadmin or Admin can communicate with anyone
        if ($user instanceof ProcessOwner || $target instanceof ProcessOwner) {
            return ['allowed' => true];
        }

        // 4. Buyer communication rules: Only their own Key Users or Admins
        if ($user instanceof Buyer) {
            if ($target instanceof KeyUser && strtoupper(trim((string)$target->bp_code)) === strtoupper(trim((string)$user->bp_code))) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Buyers can only communicate directly with their own Key Users or Admins.'
            ];
        }

        // 5. Craftsman communication rules: Only their own Craftsman Staff or Admins
        if ($user instanceof Craftman) {
            if ($target instanceof CraftsmanStaff && (int) $target->craftsman_id === (int) $user->id) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Craftsmen can only communicate directly with their own staff or Admins.'
            ];
        }

        // 6. Key User communication rules: Only their parent Buyer, fellow Key Users under same BP code, or Admins
        if ($user instanceof KeyUser) {
            if ($target instanceof Buyer && strtoupper(trim((string)$user->bp_code)) === strtoupper(trim((string)$target->bp_code))) {
                return ['allowed' => true];
            }
            if ($target instanceof KeyUser && strtoupper(trim((string)$user->bp_code)) === strtoupper(trim((string)$target->bp_code))) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Key users can only communicate directly with their parent Buyer, colleagues, or Admins.'
            ];
        }

        // 7. Craftsman Staff communication rules: Only their parent Craftsman, fellow staff in same workshop, or Admins
        if ($user instanceof CraftsmanStaff) {
            if ($target instanceof Craftman && (int) $user->craftsman_id === (int) $target->id) {
                return ['allowed' => true];
            }
            if ($target instanceof CraftsmanStaff && (int) $user->craftsman_id === (int) $target->craftsman_id) {
                return ['allowed' => true];
            }
            return [
                'allowed' => false,
                'reason'  => 'Craftsman staff can only communicate directly with their parent Craftsman, workshop colleagues, or Admins.'
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
            return ['id' => null, 'name' => 'Unknown', 'type' => 'unknown', 'label' => 'Unknown', 'badge' => 'Unknown', 'agora_user_id' => null];
        }

        $type = $this->getModelTypeString($user);
        $code = null;
        $name = 'User';
        $avatar = null;
        $email = null;
        $mobile = null;
        $badge = 'User';

        if ($user instanceof Buyer) {
            $code = $user->bp_code;
            $name = $user->name ?? ($user->full_name ?? ($user->business_name ?? 'Buyer'));
            $avatar = $user->image_url ?? ($user->image ? asset('storage/' . $user->image) : null);
            $email = $user->email ?? $user->business_email;
            $mobile = $user->mobile;
            $badge = 'Buyer';
        } elseif ($user instanceof Craftman) {
            $code = $user->craftman_code;
            $name = $user->name ?? ($user->full_name ?? ($user->business_name ?? 'Craftsman'));
            $avatar = $user->image ? asset('storage/' . $user->image) : null;
            $email = $user->email ?? $user->business_email;
            $mobile = $user->mobile;
            $badge = 'Craftsman';
        } elseif ($user instanceof KeyUser) {
            $code = $user->user_code;
            $name = $user->full_name ?? ($user->name ?? 'Key User');
            $avatar = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;
            $email = $user->email_id;
            $mobile = $user->mobile_no;
            $badge = 'Key User';
        } elseif ($user instanceof CraftsmanStaff) {
            $code = $user->staff_code;
            $name = $user->name ?? 'Staff';
            $avatar = $user->image_url ?? ($user->image ? asset('storage/' . $user->image) : null);
            $email = $user->email;
            $mobile = $user->mobile;
            $badge = 'Craftsman Staff';
        } elseif ($user instanceof ProcessOwner) {
            $code = $user->user_code;
            $name = $user->full_name ?? ($user->name ?? 'Admin');
            $avatar = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;
            $email = $user->email_id;
            $mobile = $user->mobile_no;
            $badge = in_array(strtolower($user->role ?? ''), ['super_admin', 'superadmin']) ? 'Super Admin' : 'Admin';
        } elseif ($user instanceof User) {
            $name = $user->name ?? 'User';
            $email = $user->email ?? null;
            $mobile = $user->phone ?? null;
            $badge = 'User';
        }

        return [
            'id'            => (int) $user->id,
            'type'          => $type,
            'model_type'    => get_class($user),
            'agora_user_id' => $this->getAgoraUserId($user),
            'code'          => $code,
            'name'          => $name,
            'label'         => trim(($code ? "{$code} - " : "") . $name),
            'avatar_url'    => $avatar,
            'email'         => $email,
            'mobile'        => $mobile,
            'badge'         => $badge,
        ];
    }

    /**
     * Format a message for API response with full attachment URLs and media convenience keys.
     */
    private function formatMessageSummary(Message $message): array
    {
        $senderSummary = $this->formatUserSummary($message->sender);

        $attachments = $message->attachments->map(function ($att) {
            $streamUrl = $att->stream_url ?? url('/api/common/chat/media/' . $att->id . '/stream');
            $ext = strtolower(pathinfo($att->file_name ?? $att->file_path, PATHINFO_EXTENSION));
            $mimeType = $att->mime_type;
            if (empty($mimeType) || $mimeType === 'application/octet-stream' || str_ends_with($mimeType, '/*')) {
                $mimeType = match ($ext) {
                    'm4a'   => 'audio/mp4',
                    'mp3'   => 'audio/mpeg',
                    'wav'   => 'audio/wav',
                    'aac'   => 'audio/aac',
                    'opus'  => 'audio/opus',
                    'mp4'   => 'video/mp4',
                    'mov'   => 'video/quicktime',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png'   => 'image/png',
                    'pdf'   => 'application/pdf',
                    'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'xls'   => 'application/vnd.ms-excel',
                    'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'doc'   => 'application/msword',
                    default => ($att->file_type === 'audio' ? 'audio/mp4' : ($att->file_type === 'video' ? 'video/mp4' : 'application/octet-stream')),
                };
            }

            return [
                'id'         => (int) $att->id,
                'file_name'  => $att->file_name,
                'file_path'  => $att->file_path,
                'file_type'  => $att->file_type,
                'mime_type'  => $mimeType,
                'full_url'   => $att->full_url,
                'stream_url' => $streamUrl,
            ];
        })->values()->toArray();

        // Location detection (Live Location / Shared Google Maps Location)
        $isLocation = false;
        $latitude = null;
        $longitude = null;
        $mapUrl = null;
        $isLiveLocation = false;

        $rawBody = (string) $message->body;
        if (preg_match('/(?:maps\.google\.com\/(?:maps)?\?q=|@)(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i', $rawBody, $locMatches)) {
            $isLocation = true;
            $latitude = (float) $locMatches[1];
            $longitude = (float) $locMatches[2];
            $mapUrl = "https://maps.google.com/?q={$latitude},{$longitude}";
            $isLiveLocation = str_contains(strtolower($rawBody), 'live');
        } elseif (preg_match('/^location:(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i', trim($rawBody), $locMatches)) {
            $isLocation = true;
            $latitude = (float) $locMatches[1];
            $longitude = (float) $locMatches[2];
            $mapUrl = "https://maps.google.com/?q={$latitude},{$longitude}";
            $isLiveLocation = false;
        }

        // Extract primary media URLs for direct top-level access in Flutter/Web
        $audioAtt = $message->attachments->firstWhere('file_type', 'audio');
        $videoAtt = $message->attachments->firstWhere('file_type', 'video');
        $imageAtt = $message->attachments->firstWhere('file_type', 'image');
        $docAtt   = $message->attachments->firstWhere('file_type', 'document');

        $primaryAtt = $audioAtt ?? $videoAtt ?? $imageAtt ?? $docAtt;

        if ($isLocation) {
            $mediaType = $isLiveLocation ? 'live_location' : 'location';
        } else {
            $mediaType = $primaryAtt ? $primaryAtt->file_type : 'text';
        }

        $audioUrl = $audioAtt ? ($audioAtt->stream_url ?? $audioAtt->full_url) : null;
        $videoUrl = $videoAtt ? ($videoAtt->stream_url ?? $videoAtt->full_url) : null;
        $imageUrl = $imageAtt ? $imageAtt->full_url : null;
        $fileUrl  = $docAtt ? $docAtt->full_url : null;

        $mediaUrl = null;
        if ($isLocation) {
            $mediaUrl = $mapUrl;
        } elseif ($primaryAtt) {
            $mediaUrl = ($primaryAtt->file_type === 'audio' || $primaryAtt->file_type === 'video')
                ? ($primaryAtt->stream_url ?? $primaryAtt->full_url)
                : $primaryAtt->full_url;
        }

        $body = $message->body;

        if ($isLocation) {
            $body = $mapUrl;
        } elseif ($primaryAtt) {
            $currentTrimmed = trim((string)$body);
            $fileName = trim((string)$primaryAtt->file_name);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // If body is empty, OR matches the bare filename, OR ends with a media extension without spaces
            $shouldReplaceWithUrl = empty($currentTrimmed) || ($currentTrimmed === $fileName);
            if (!$shouldReplaceWithUrl && !empty($ext) && str_ends_with(strtolower($currentTrimmed), '.' . $ext) && !str_contains($currentTrimmed, ' ') && !str_starts_with($currentTrimmed, 'http')) {
                $shouldReplaceWithUrl = true;
            }

            if ($shouldReplaceWithUrl) {
                $body = $mediaUrl;
            }
        }

        $primaryExt = $primaryAtt ? strtolower(pathinfo($primaryAtt->file_name ?? $primaryAtt->file_path, PATHINFO_EXTENSION)) : '';
        $primaryMime = $primaryAtt ? ($primaryAtt->mime_type ?: match ($primaryExt) {
            'm4a'   => 'audio/mp4',
            'mp3'   => 'audio/mpeg',
            'wav'   => 'audio/wav',
            'aac'   => 'audio/aac',
            'mp4'   => 'video/mp4',
            'pdf'   => 'application/pdf',
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => ($primaryAtt->file_type === 'audio' ? 'audio/mp4' : ($primaryAtt->file_type === 'video' ? 'video/mp4' : null)),
        }) : null;

        return [
            'id'               => (int) $message->id,
            'conversation_id'  => $message->conversation_id ? (int) $message->conversation_id : ($message->chat_group_id ? (int) $message->chat_group_id : null),
            'chat_group_id'    => $message->chat_group_id ? (int) $message->chat_group_id : null,
            'is_group'         => !empty($message->chat_group_id),
            'chat_type'        => !empty($message->chat_group_id) ? 'group' : 'direct',
            'sender'           => $senderSummary,
            'body'             => $body,
            'text'             => $body,
            'message'          => $body,
            'content'          => $body,
            'media_type'       => $mediaType,
            'media_url'        => $mediaUrl,
            'audio_url'        => $audioUrl,
            'audio_stream_url' => $audioAtt ? ($audioAtt->stream_url ?? null) : null,
            'video_url'        => $videoUrl,
            'image_url'        => $imageUrl,
            'document_url'     => $fileUrl,
            'file_url'         => $fileUrl,
            'file_name'        => $primaryAtt ? $primaryAtt->file_name : null,
            'mime_type'        => $primaryMime,
            'is_live_location' => $isLiveLocation,
            'latitude'         => $latitude,
            'longitude'        => $longitude,
            'map_url'          => $mapUrl,
            'location'         => $isLocation ? [
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'map_url'   => $mapUrl,
                'is_live'   => $isLiveLocation,
            ] : null,
            'attachments'      => $attachments,
            'is_read'          => (bool) $message->is_read,
            'created_at'       => optional($message->created_at)->toISOString(),
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
            'conversation_id'       => (int) $group->id,
            'chat_group_id'         => (int) $group->id,
            'is_group'              => true,
            'chat_type'             => 'group',
            'channel_name'          => 'private-conversation.' . $group->id,
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
     * Automatically handles video, audio, document, and live location.
     */
    private function storeMessageRecord(?int $conversationId, ?int $chatGroupId, $user, Request $request): Message
    {
        $bodyContent = $request->input('body') 
            ?? $request->input('message') 
            ?? $request->input('text') 
            ?? $request->input('content') 
            ?? $request->input('reply') 
            ?? $request->input('input') 
            ?? $request->input('action_input') 
            ?? '';

        // Check for incoming location coordinates
        $lat = $request->input('latitude') ?? $request->input('lat');
        $lng = $request->input('longitude') ?? $request->input('lng') ?? $request->input('long');
        if (is_null($lat) && is_array($request->input('location'))) {
            $lat = $request->input('location.latitude') ?? $request->input('location.lat');
            $lng = $request->input('location.longitude') ?? $request->input('location.lng');
        }

        // Support JSON string passed inside body (e.g. {"text": "...", "latitude": ..., "image": ...})
        if (is_string($bodyContent) && str_starts_with(trim($bodyContent), '{') && str_ends_with(trim($bodyContent), '}')) {
            $decodedJson = json_decode(trim($bodyContent), true);
            if (is_array($decodedJson)) {
                if (isset($decodedJson['text']) || isset($decodedJson['message']) || isset($decodedJson['content']) || isset($decodedJson['body'])) {
                    $bodyContent = $decodedJson['text'] ?? $decodedJson['message'] ?? $decodedJson['content'] ?? $decodedJson['body'];
                }
                if (is_null($lat) && (isset($decodedJson['latitude']) || isset($decodedJson['lat']))) {
                    $lat = $decodedJson['latitude'] ?? $decodedJson['lat'];
                    $lng = $decodedJson['longitude'] ?? $decodedJson['lng'] ?? $decodedJson['long'] ?? null;
                } elseif (is_null($lat) && isset($decodedJson['location']) && is_array($decodedJson['location'])) {
                    $lat = $decodedJson['location']['latitude'] ?? $decodedJson['location']['lat'] ?? null;
                    $lng = $decodedJson['location']['longitude'] ?? $decodedJson['location']['lng'] ?? null;
                }
                if (!empty($decodedJson['image']) && is_string($decodedJson['image'])) {
                    $request->merge(['image_url' => $decodedJson['image']]);
                }
                if (!empty($decodedJson['video']) && is_string($decodedJson['video'])) {
                    $request->merge(['video_url' => $decodedJson['video']]);
                }
                if (!empty($decodedJson['audio']) && is_string($decodedJson['audio'])) {
                    $request->merge(['audio_url' => $decodedJson['audio']]);
                }
                if (!empty($decodedJson['voice']) && is_string($decodedJson['voice'])) {
                    $request->merge(['audio_url' => $decodedJson['voice']]);
                }
                if (!empty($decodedJson['document']) && is_string($decodedJson['document'])) {
                    $request->merge(['document_url' => $decodedJson['document']]);
                }
                if (!empty($decodedJson['doc']) && is_string($decodedJson['doc'])) {
                    $request->merge(['document_url' => $decodedJson['doc']]);
                }
                if (!empty($decodedJson['file']) && is_string($decodedJson['file'])) {
                    $request->merge(['file_url' => $decodedJson['file']]);
                }
            }
        }

        $isLiveLocation = filter_var($request->input('live_location', false), FILTER_VALIDATE_BOOLEAN)
            || filter_var($request->input('is_live_location', false), FILTER_VALIDATE_BOOLEAN)
            || str_contains(strtolower((string)$bodyContent), 'live');

        if (!is_null($lat) && !is_null($lng) && is_numeric($lat) && is_numeric($lng)) {
            $prefix = $isLiveLocation ? "📍 Live Location:\n" : "📍 Location:\n";
            $mapLink = "https://maps.google.com/?q={$lat},{$lng}";
            if (!empty($bodyContent) && !str_contains((string)$bodyContent, 'maps.google.com')) {
                $bodyContent = trim((string)$bodyContent) . "\n" . $mapLink;
            } else {
                $bodyContent = $mapLink;
            }
        }

        $messageData = [
            'sender_id'   => $user->id,
            'sender_type' => get_class($user),
            'body'        => (string) $bodyContent,
            'is_read'     => false,
        ];

        if (!is_null($conversationId)) {
            $messageData['conversation_id'] = $conversationId;
        }

        if (!is_null($chatGroupId)) {
            $messageData['chat_group_id'] = $chatGroupId;
        }

        $message = Message::create($messageData);

        // 1. Process all multipart file uploads (image, video, audio, voice, document, attachments, etc.)
        $fileKeys = [
            'attachments',
            'image', 'images',
            'video', 'videos',
            'audio', 'audios',
            'voice', 'voice_note',
            'document', 'documents',
            'doc', 'docs',
            'file', 'files',
            'media'
        ];
        $filesToProcess = [];

        foreach ($fileKeys as $key) {
            if ($request->hasFile($key)) {
                $files = $request->file($key);
                if (is_array($files)) {
                    $filesToProcess = array_merge($filesToProcess, $files);
                } elseif ($files instanceof \Illuminate\Http\UploadedFile) {
                    $filesToProcess[] = $files;
                }
            }
        }

        // Deduplicate files by hash to prevent multiple form keys uploading the same file multiple times
        $seenHashes = [];
        $uniqueFiles = [];
        foreach ($filesToProcess as $file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) continue;
            $hash = md5_file($file->getRealPath()) ?: ($file->getClientOriginalName() . '_' . $file->getSize());
            if (isset($seenHashes[$hash])) continue;
            $seenHashes[$hash] = true;
            $uniqueFiles[] = $file;
        }

        foreach ($uniqueFiles as $file) {
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION));
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
            $fileType = $this->determineFileType($mimeType, $ext);

            // Ensure proper audio/video/document MIME type
            if ($fileType === 'audio') {
                $mimeType = match ($ext) {
                    'm4a'   => 'audio/mp4',
                    'mp3'   => 'audio/mpeg',
                    'wav'   => 'audio/wav',
                    'aac'   => 'audio/aac',
                    'opus'  => 'audio/opus',
                    'amr'   => 'audio/amr',
                    default => ($mimeType ?: 'audio/mp4'),
                };
            } elseif ($fileType === 'video') {
                $mimeType = match ($ext) {
                    'mp4'   => 'video/mp4',
                    'mov'   => 'video/quicktime',
                    'webm'  => 'video/webm',
                    default => ($mimeType ?: 'video/mp4'),
                };
            } elseif ($fileType === 'document') {
                $mimeType = match ($ext) {
                    'pdf'   => 'application/pdf',
                    'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'xls'   => 'application/vnd.ms-excel',
                    'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'doc'   => 'application/msword',
                    'txt'   => 'text/plain',
                    'csv'   => 'text/csv',
                    'zip'   => 'application/zip',
                    default => ($mimeType ?: 'application/octet-stream'),
                };
            }

            $path = $file->store('chat_attachments', 'public');

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_path'  => $path,
                'file_name'  => $originalName,
                'file_type'  => $fileType,
                'mime_type'  => $mimeType,
            ]);
        }

        // 2. Process any direct URL inputs for video, audio, voice, document, or files in JSON body
        $urlInputs = [
            'video_url'    => 'video',
            'audio_url'    => 'audio',
            'voice_url'    => 'audio',
            'image_url'    => 'image',
            'document_url' => 'document',
            'doc_url'      => 'document',
            'file_url'     => 'document',
        ];

        foreach ($urlInputs as $inputKey => $defaultType) {
            $urlVal = $request->input($inputKey);
            if (is_string($urlVal) && filter_var($urlVal, FILTER_VALIDATE_URL)) {
                $ext = strtolower(pathinfo(parse_url($urlVal, PHP_URL_PATH), PATHINFO_EXTENSION));
                $fileType = !empty($ext) ? $this->determineFileType('', $ext) : $defaultType;
                $mimeType = match ($ext) {
                    'm4a'   => 'audio/mp4',
                    'mp3'   => 'audio/mpeg',
                    'wav'   => 'audio/wav',
                    'aac'   => 'audio/aac',
                    'mp4'   => 'video/mp4',
                    'pdf'   => 'application/pdf',
                    default => "{$fileType}/*",
                };

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path'  => $urlVal,
                    'file_name'  => basename(parse_url($urlVal, PHP_URL_PATH)) ?: "{$defaultType}_file",
                    'file_type'  => $fileType,
                    'mime_type'  => $mimeType,
                ]);
            }
        }

        // Direct string URLs passed in 'video', 'audio', 'voice', 'image', 'document', 'doc'
        foreach (['video', 'audio', 'voice', 'image', 'document', 'doc'] as $directKey) {
            $val = $request->input($directKey);
            if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) {
                $defaultType = ($directKey === 'voice') ? 'audio' : ($directKey === 'doc' ? 'document' : $directKey);
                $ext = strtolower(pathinfo(parse_url($val, PHP_URL_PATH), PATHINFO_EXTENSION));
                $fileType = !empty($ext) ? $this->determineFileType('', $ext) : $defaultType;
                $mimeType = match ($ext) {
                    'm4a'   => 'audio/mp4',
                    'mp3'   => 'audio/mpeg',
                    'wav'   => 'audio/wav',
                    'aac'   => 'audio/aac',
                    'mp4'   => 'video/mp4',
                    'pdf'   => 'application/pdf',
                    default => "{$fileType}/*",
                };

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path'  => $val,
                    'file_name'  => basename(parse_url($val, PHP_URL_PATH)) ?: "{$directKey}_file",
                    'file_type'  => $fileType,
                    'mime_type'  => $mimeType,
                ]);
            }
        }

        // 3. Process array of JSON attachments if passed in body
        $jsonAttachments = $request->input('attachments');
        if (is_array($jsonAttachments) && !$request->hasFile('attachments')) {
            foreach ($jsonAttachments as $jAtt) {
                if (is_array($jAtt) && !empty($jAtt['url'])) {
                    $u = $jAtt['url'];
                    $ext = strtolower(pathinfo(parse_url($u, PHP_URL_PATH), PATHINFO_EXTENSION));
                    $fType = $jAtt['type'] ?? (!empty($ext) ? $this->determineFileType('', $ext) : 'document');
                    $mMime = $jAtt['mime_type'] ?? match ($ext) {
                        'm4a'   => 'audio/mp4',
                        'mp3'   => 'audio/mpeg',
                        'wav'   => 'audio/wav',
                        'aac'   => 'audio/aac',
                        'mp4'   => 'video/mp4',
                        'pdf'   => 'application/pdf',
                        default => "{$fType}/*",
                    };

                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'file_path'  => $u,
                        'file_name'  => $jAtt['name'] ?? (basename(parse_url($u, PHP_URL_PATH)) ?: 'attachment'),
                        'file_type'  => $fType,
                        'mime_type'  => $mMime,
                    ]);
                }
            }
        }

        // 4. Auto-detect if "body" itself is a direct media URL (sent in body only)
        if (is_string($bodyContent) && filter_var(trim($bodyContent), FILTER_VALIDATE_URL)) {
            $trimmedUrl = trim($bodyContent);
            $ext = strtolower(pathinfo(parse_url($trimmedUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!empty($ext)) {
                $fType = $this->determineFileType('', $ext);
                if (in_array($fType, ['audio', 'video', 'image', 'document'])) {
                    $exists = MessageAttachment::where('message_id', $message->id)
                        ->where('file_path', $trimmedUrl)
                        ->exists();

                    if (!$exists) {
                        $mimeType = match ($ext) {
                            'm4a'   => 'audio/mp4',
                            'mp3'   => 'audio/mpeg',
                            'wav'   => 'audio/wav',
                            'aac'   => 'audio/aac',
                            'opus'  => 'audio/opus',
                            'mp4'   => 'video/mp4',
                            'mov'   => 'video/quicktime',
                            'jpg', 'jpeg' => 'image/jpeg',
                            'png'   => 'image/png',
                            'pdf'   => 'application/pdf',
                            default => "{$fType}/*",
                        };

                        MessageAttachment::create([
                            'message_id' => $message->id,
                            'file_path'  => $trimmedUrl,
                            'file_name'  => basename(parse_url($trimmedUrl, PHP_URL_PATH)) ?: "{$fType}_file",
                            'file_type'  => $fType,
                            'mime_type'  => $mimeType,
                        ]);
                    }
                }
            }
        }

        // 5. Update DB body with the actual media stream/storage URL if body was empty or just a raw filename
        $firstAttachment = $message->attachments()->first();
        if ($firstAttachment) {
            $curBody = trim((string)$message->body);
            $fileName = trim((string)$firstAttachment->file_name);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $isBareFilename = ($curBody === '' || $curBody === $fileName);
            if (!$isBareFilename && !empty($ext) && str_ends_with(strtolower($curBody), '.' . $ext) && !str_contains($curBody, ' ') && !str_starts_with($curBody, 'http')) {
                $isBareFilename = true;
            }

            if ($isBareFilename) {
                $mediaUrl = ($firstAttachment->file_type === 'audio' || $firstAttachment->file_type === 'video')
                    ? ($firstAttachment->stream_url ?? $firstAttachment->full_url)
                    : $firstAttachment->full_url;

                $message->update([
                    'body' => $mediaUrl
                ]);
            }
        }

        return $message->fresh(['attachments', 'sender']);
    }

    /**
     * Determine file type classification (image, video, audio, document).
     */
    private function determineFileType(string $mime, string $ext): string
    {
        $mime = strtolower(trim($mime));
        $ext = strtolower(trim($ext, '. '));

        if (str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif', 'svg', 'bmp', 'tiff'])) {
            return 'image';
        }

        if (str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'mov', 'avi', '3gp', '3g2', 'webm', 'mkv', 'flv', 'wmv', 'm4v', 'mpeg', 'mpg'])) {
            return 'video';
        }

        if (str_starts_with($mime, 'audio/') || in_array($ext, ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'opus', 'amr', 'caf', 'wma', 'flac', '3ga', 'weba'])) {
            return 'audio';
        }

        return 'document';
    }

    /**
     * Stream multimedia attachments with HTTP 206 Byte-Range support.
     * Essential for iOS AVPlayer (DarwinAudioError fix), Safari, and HTML5 audio/video.
     * Includes ngrok interstitial bypass header.
     * Route: GET /api/common/chat/media/{id}/stream
     */
    public function streamMedia(Request $request, $id)
    {
        // 1. Resolve attachment by ID if numeric, otherwise by file_path or file_name
        if (is_numeric($id)) {
            $attachment = MessageAttachment::find((int) $id);
        } else {
            $attachment = MessageAttachment::where('file_path', 'like', "%{$id}%")
                ->orWhere('file_name', $id)
                ->first();
        }

        $diskPath = null;
        $fileName = 'media_file';
        $fileType = 'audio';
        $mimeType = null;

        if ($attachment) {
            $fileName = $attachment->file_name;
            $fileType = $attachment->file_type;
            $mimeType = $attachment->mime_type;

            // Direct external absolute URL stored in attachment
            if (str_starts_with($attachment->file_path, 'http://') || str_starts_with($attachment->file_path, 'https://')) {
                return redirect()->away($attachment->file_path);
            }

            $diskPath = storage_path('app/public/' . $attachment->file_path);
            if (!file_exists($diskPath)) {
                $diskPath = public_path('storage/' . $attachment->file_path);
            }
        } else {
            // Direct file search in storage/app/public/chat_attachments
            $cleanId = basename($id);
            $possiblePath = storage_path('app/public/chat_attachments/' . $cleanId);
            if (file_exists($possiblePath)) {
                $diskPath = $possiblePath;
                $fileName = $cleanId;
            } else {
                $possiblePublicPath = public_path('storage/chat_attachments/' . $cleanId);
                if (file_exists($possiblePublicPath)) {
                    $diskPath = $possiblePublicPath;
                    $fileName = $cleanId;
                }
            }
        }

        if (!$diskPath || !file_exists($diskPath)) {
            return response()->json([
                'success' => false,
                'message' => "Media attachment '{$id}' not found on server disk."
            ], 404);
        }

        $fileSize = filesize($diskPath);
        $ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));

        // Determine precise MIME type
        if (empty($mimeType) || $mimeType === 'application/octet-stream' || str_ends_with($mimeType, '/*')) {
            $mimeType = match ($ext) {
                'm4a'   => 'audio/mp4',
                'mp3'   => 'audio/mpeg',
                'wav'   => 'audio/wav',
                'aac'   => 'audio/aac',
                'ogg'   => 'audio/ogg',
                'opus'  => 'audio/opus',
                'amr'   => 'audio/amr',
                'mp4'   => 'video/mp4',
                'mov'   => 'video/quicktime',
                'webm'  => 'video/webm',
                'jpg', 'jpeg' => 'image/jpeg',
                'png'   => 'image/png',
                default => 'application/octet-stream',
            };
        }

        $start = 0;
        $end = $fileSize - 1;
        $statusCode = 200;

        // Inspect HTTP Range header (iOS AVPlayer sends "bytes=0-1" or "bytes=0-")
        $rangeHeader = $request->header('Range') ?? ($request->server('HTTP_RANGE') ?? null);

        if (!empty($rangeHeader) && preg_match('/bytes=(\d+)-(\d*)[\/,\s]?/i', $rangeHeader, $matches)) {
            $start = (int) $matches[1];
            if (!empty($matches[2])) {
                $end = min((int) $matches[2], $fileSize - 1);
            }
            $statusCode = 206; // Partial Content for iOS AVPlayer
        }

        $length = ($end - $start) + 1;

        $headers = [
            'Content-Type'                  => $mimeType,
            'Content-Length'                => (string) $length,
            'Accept-Ranges'                 => 'bytes',
            'Cache-Control'                 => 'public, max-age=604800, no-transform',
            'Access-Control-Allow-Origin'   => '*',
            'Access-Control-Allow-Methods'  => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers'  => 'Range, Authorization, Content-Type, ngrok-skip-browser-warning',
            'Access-Control-Expose-Headers' => 'Content-Range, Accept-Ranges, Content-Length',
            'ngrok-skip-browser-warning'    => '1',
            'Content-Disposition'           => 'inline; filename="' . basename($fileName) . '"',
        ];

        if ($statusCode === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        return response()->stream(function () use ($diskPath, $start, $length) {
            $fp = fopen($diskPath, 'rb');
            if ($fp === false) return;

            fseek($fp, $start);
            $chunkSize = 1024 * 64; // 64KB chunk buffer
            $remaining = $length;

            while (!feof($fp) && $remaining > 0) {
                $bytesToRead = min($chunkSize, $remaining);
                $chunk = fread($fp, $bytesToRead);
                if ($chunk === false) break;
                echo $chunk;
                flush();
                $remaining -= strlen($chunk);
            }
            fclose($fp);
        }, $statusCode, $headers);
    }

    /**
     * Check WebSocket (Reverb) server status and return client connection configuration for iOS/Frontend.
     */
    public function getWebsocketConfig(Request $request)
    {
        $broadcaster = config('broadcasting.default', env('BROADCAST_CONNECTION', 'pusher'));

        if ($broadcaster === 'pusher') {
            $appKey = config('broadcasting.connections.pusher.key', env('PUSHER_APP_KEY', '9bcfabd864b6ce776339'));
            $cluster = config('broadcasting.connections.pusher.options.cluster', env('PUSHER_APP_CLUSTER', 'ap2'));

            return response()->json([
                'success' => true,
                'data'    => [
                    'broadcaster'        => 'pusher',
                    'is_running'         => true,
                    'app_key'            => $appKey,
                    'cluster'            => $cluster,
                    'host'               => "ws-{$cluster}.pusher.com",
                    'port'               => 443,
                    'scheme'             => 'https',
                    'use_tls'            => true,
                    'ws_url'             => "wss://ws-{$cluster}.pusher.com:443/app/{$appKey}",
                    'auth_endpoint'      => url('/api/broadcasting/auth'),
                    'alt_auth_endpoint'  => url('/broadcasting/auth'),
                    'channels'           => [
                        'conversation_pattern' => 'private-conversation.{conversation_id}',
                        'group_pattern'        => 'private-group.{chat_group_id}',
                        'user_pattern'         => 'private-user.{user_type}.{user_id}',
                    ],
                    'events'             => [
                        'message_sent'    => 'message.sent',
                        'message_deleted' => 'message.deleted',
                    ],
                    'guide'              => [
                        'flutter'     => 'Connect using pusher_channels_flutter package with apiKey and cluster.',
                        'web'         => 'Connect using laravel-echo and pusher-js with broadcaster: "pusher".',
                        'voice_video' => 'Connect to Agora RTC using /api/common/chat/agora-token for live audio/video calls.',
                    ]
                ]
            ]);
        }

        // Reverb fallback
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
                'broadcaster'        => $broadcaster,
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
                    'text_chat'  => 'Connect to WebSocket to listen to conversation.{id} and group.{id} channels for real-time text & media messages.',
                    'voice_video'=> 'Connect to Agora RTC using /api/common/chat/agora-token for live audio/video calls.',
                ]
            ]
        ]);
    }
}
