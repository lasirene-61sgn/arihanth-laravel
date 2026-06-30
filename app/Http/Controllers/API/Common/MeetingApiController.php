<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CyberDeep\LaravelAgoraTokenGenerator\Services\Agora;
use App\Notifications\MeetingStatusNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class MeetingApiController extends Controller
{
    /**
     * Generate an Agora Token for a specific meeting room.
     * This is intended for Mobile App clients.
     */
    public function getAgoraToken(Request $request, $room_id)
    {
        $user = Auth::user();

        $meeting = Meeting::where('room_id', $room_id)->first();

        if (!$meeting) {
            return response()->json(['success' => false, 'message' => 'Meeting not found'], 404);
        }

        if ($meeting->status === 'requested') {
            return response()->json(['success' => false, 'message' => 'Meeting is still pending approval'], 403);
        }

        if ($meeting->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Meeting has been cancelled'], 403);
        }

        // Standard Check — must be the host or the participant (polymorphic ID + type match)
        $isHost = ($meeting->host_id == $user->id && $meeting->host_type == get_class($user));
        $isParticipant = ($meeting->participant_id == $user->id && $meeting->participant_type == get_class($user));

        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (isset($user->role) && ($user->role === 'superadmin' || $user->role === 'super_admin'));

        if (!$isHost && !$isParticipant && !$isSuperAdmin) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to join this consultation'], 403);
        }

        $appId = env('AGORA_APP_ID');
        if (!$appId) {
            return response()->json(['success' => false, 'message' => 'Agora App ID is not configured on the server'], 500);
        }

        try {
            $agoraToken = Agora::make($user->id)
                ->channel($room_id)
                ->uId($user->id)
                ->join(false)
                ->audioOnly(false)
                ->token();

            // Set started_at if not already set
            if (!$meeting->started_at) {
                $meeting->update(['started_at' => now()]);
            }

            // Send notification to the other party that this user has joined
            $userToNotify = null;
                
                if ($isParticipant) {
                    $userToNotify = $meeting->host;
                } elseif ($isHost) {
                    $userToNotify = $meeting->participant;
                } elseif ($isSuperAdmin) {
                    // SuperAdmin override: find the Buyer/Craftsman
                    if ($meeting->host && !($meeting->host instanceof \App\Models\ProcessOwner)) {
                        $userToNotify = $meeting->host;
                    } else {
                        $userToNotify = $meeting->participant;
                    }
                }

                $cacheKey = "meeting_joined_notified_{$meeting->id}_" . get_class($user) . "_{$user->id}";

                if (\Illuminate\Support\Facades\Cache::add($cacheKey, true, now()->addHours(4))) {
                    if ($userToNotify && method_exists($userToNotify, 'notify')) {
                        // Generate token for the user to be notified
                        $targetToken = Agora::make($userToNotify->id)
                            ->channel($room_id)
                            ->uId($userToNotify->id)
                            ->join(false)
                            ->audioOnly(false)
                            ->token();

                        $userToNotify->notify(new MeetingStatusNotification($meeting, 'joined', [
                            'app_id' => $appId,
                            'token' => $targetToken,
                            'channel_name' => $room_id,
                            'uid' => $userToNotify->id,
                            'caller_name' => $this->getFormattedCallerName($user)
                        ]));

                        // If a Buyer/Craftsman joined, ring the Admin web panel
                        if (!($user instanceof \App\Models\ProcessOwner)) {
                            try {
                                $callerName = $this->getFormattedCallerName($user);
                                
                                $category = null;
                                if ($meeting->participant_type === \App\Models\ProcessOwner::class) {
                                    $pAdmin = \App\Models\ProcessOwner::find($meeting->participant_id);
                                    if ($pAdmin) $category = $pAdmin->category;
                                } elseif ($meeting->host_type === \App\Models\ProcessOwner::class) {
                                    $hAdmin = \App\Models\ProcessOwner::find($meeting->host_id);
                                    if ($hAdmin) $category = $hAdmin->category;
                                }

                                broadcast(new \App\Events\MeetingIncomingEvent($meeting->id, $room_id, $callerName, $category));
                            } catch (\Exception $e) {
                                Log::warning('Pusher broadcast failed (non-fatal): ' . $e->getMessage());
                            }
                            
                            $this->writeRtdbActiveCall($meeting, $user);
                        }
                    }
                }

            return response()->json([
                'success' => true,
                'data' => [
                    'app_id' => $appId,
                    'channel_name' => $room_id,
                    'token' => $agoraToken,
                    'uid' => $user->id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to generate token: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Notify the other party that a user has joined the meeting.
     */
    public function notifyJoin(Request $request, $room_id)
    {
        $user = Auth::user();
        $meeting = Meeting::where('room_id', $room_id)->first();

        if (!$meeting) {
            return response()->json(['success' => false, 'message' => 'Meeting not found'], 404);
        }

        $isHost = ($meeting->host_id == $user->id && $meeting->host_type == get_class($user));
        $isParticipant = ($meeting->participant_id == $user->id && $meeting->participant_type == get_class($user));
        
        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (isset($user->role) && ($user->role === 'superadmin' || $user->role === 'super_admin'));

        $isAdmin = ($user instanceof \App\Models\ProcessOwner);

        if (!$isHost && !$isParticipant && !$isSuperAdmin && !$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // If a different Admin/Superadmin joins, claim the meeting so the mobile app knows who they are talking to
        if ($isAdmin && !$isHost && $meeting->host_type === \App\Models\ProcessOwner::class) {
            $meeting->update([
                'host_id' => $user->id,
                'status' => 'approved'
            ]);
            $meeting->load('host');
            $isHost = true;
        }

        if ($isAdmin && !$isParticipant && $meeting->participant_type === \App\Models\ProcessOwner::class) {
            $meeting->update([
                'participant_id' => $user->id,
                'status' => 'approved'
            ]);
            $meeting->load('participant');
            $isParticipant = true;
        }

        // Set started_at if not already set
        if (!$meeting->started_at) {
            $meeting->update(['started_at' => now()]);
        }

        $appId = env('AGORA_APP_ID');

        // Prevent duplicate notifications entirely for 4 hours
        $cacheKey = "meeting_joined_notified_{$meeting->id}_" . get_class($user) . "_{$user->id}";
        if (!\Illuminate\Support\Facades\Cache::add($cacheKey, true, now()->addHours(4))) {
            return response()->json(['success' => true, 'message' => 'Notification already sent recently']);
        }

        // Determine who to notify
        $userToNotify = null;
        if ($isParticipant) {
            $userToNotify = $meeting->host;
        } elseif ($isHost) {
            $userToNotify = $meeting->participant;
        } elseif ($isSuperAdmin || $isAdmin) {
            if ($meeting->host && !($meeting->host instanceof \App\Models\ProcessOwner)) {
                $userToNotify = $meeting->host;
            } else {
                $userToNotify = $meeting->participant;
            }
        }

        if ($userToNotify && method_exists($userToNotify, 'notify')) {
            try {
                $targetToken = Agora::make($userToNotify->id)
                    ->channel($room_id)
                    ->uId($userToNotify->id)
                    ->join(false)
                    ->audioOnly(false)
                    ->token();

                $userToNotify->notify(new MeetingStatusNotification($meeting, 'joined', [
                    'app_id' => $appId,
                    'token' => $targetToken,
                    'channel_name' => $room_id,
                    'uid' => $userToNotify->id,
                    'caller_name' => $this->getFormattedCallerName($user)
                ]));
            } catch (\Exception $e) {
                \Log::warning("FCM Mobile Notification failed: " . $e->getMessage());
            }

            // If a Buyer/Craftsman joined, trigger RTDB to ring the admin panel
            if (!($user instanceof \App\Models\ProcessOwner)) {
                $this->writeRtdbActiveCall($meeting, $user);
            }
        }

        return response()->json(['success' => true, 'message' => 'Notification processed successfully']);
    }

    /**
     * Display a listing of meetings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Fetch Meetings based on Role
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $meetingsCollection = Meeting::with(['host', 'participant'])->latest()->get();
        } else {
            $meetingsCollection = Meeting::where(function ($q) use ($user) {
                $q->where('host_id', $user->id)->where('host_type', get_class($user));
            })->orWhere(function ($q) use ($user) {
                $q->where('participant_id', $user->id)->where('participant_type', get_class($user));
            })->with(['host', 'participant'])->latest()->get();
        }

        $currentUserClass = get_class($user);
        $currentUserId = $user->id;
        $isAdminLoggedIn = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        // 2. Convert to Array immediately to manipulate raw JSON structural data safely
        $meetingsArray = $meetingsCollection->toArray();

        // 3. Map through the collection array
        $transformedMeetings = array_map(function ($meeting) use ($currentUserId, $currentUserClass, $isAdminLoggedIn) {

            // Maintain your existing creator metadata logic
            if (!empty($meeting['host'])) {
                $host = $meeting['host'];
                if ($meeting['host_type'] === \App\Models\Buyer::class) {
                    $meeting['creator_business_name'] = $host['business_name'] ?? null;
                    $meeting['creator_code'] = $host['bp_code'] ?? ($host['user_code'] ?? null);
                } elseif ($meeting['host_type'] === \App\Models\Craftman::class) {
                    $meeting['creator_business_name'] = $host['business_name'] ?? null;
                    $meeting['creator_code'] = $host['craftsman_code'] ?? null;
                } elseif ($meeting['host_type'] === \App\Models\ProcessOwner::class) {
                    $meeting['creator_business_name'] = null;
                    $meeting['creator_code'] = $host[''] ?? null;
                }
            }

            // 4. EXTRACT AND FORMAT PROFILE STRINGS
            $hostFormatted = '';
            $participantFormatted = '';

            // Build formatted string for Host profile
            if (!empty($meeting['host'])) {
                if ($meeting['host_type'] === \App\Models\Buyer::class) {
                    $code = $meeting['host']['bp_code'] ?? '';
                    $name = $meeting['host']['name'] ?? ($meeting['host']['full_name'] ?? '');
                    $hostFormatted = trim("$code - $name");
                } elseif ($meeting['host_type'] === \App\Models\Craftman::class) {
                    $code = $meeting['host']['craftsman_code'] ?? '';
                    $name = $meeting['host']['name'] ?? ($meeting['host']['full_name'] ?? '');
                    $hostFormatted = trim("$code - $name");
                } elseif ($meeting['host_type'] === \App\Models\ProcessOwner::class) {
                    $name = $meeting['host']['full_name'] ?? ($meeting['host']['name'] ?? 'Admin');
                    
                    $role = $meeting['host']['role'] ?? '';
                    $isSuper = ($role === 'superadmin' || $role === 'super_admin');
                    
                    if ($isSuper) {
                        $hostFormatted = "Superadmin - " . $name;
                    } else {
                        $category = $meeting['host']['category'] ?? null;
                        $hostFormatted = $category ? "$category - $name" : "Admin - $name";
                    }
                }
            }

            // Build formatted string for Participant profile
            if (!empty($meeting['participant'])) {
                if ($meeting['participant_type'] === \App\Models\Buyer::class) {
                    $code = $meeting['participant']['bp_code'] ?? '';
                    $name = $meeting['participant']['name'] ?? ($meeting['participant']['full_name'] ?? '');
                    $participantFormatted = trim("$code - $name");
                } elseif ($meeting['participant_type'] === \App\Models\Craftman::class) {
                    $code = $meeting['participant']['craftsman_code'] ?? '';
                    $name = $meeting['participant']['name'] ?? ($meeting['participant']['full_name'] ?? '');
                    $participantFormatted = trim("$code - $name");
                } elseif ($meeting['participant_type'] === \App\Models\ProcessOwner::class) {
                    $name = $meeting['participant']['full_name'] ?? ($meeting['participant']['name'] ?? 'Admin');
                    
                    $role = $meeting['participant']['role'] ?? '';
                    $isSuper = ($role === 'superadmin' || $role === 'super_admin');
                    
                    if ($isSuper) {
                        $participantFormatted = "Superadmin - " . $name;
                    } else {
                        $category = $meeting['participant']['category'] ?? null;
                        $participantFormatted = $category ? "$category - $name" : "Admin - $name";
                    }
                }
            }

            // 5. SMART SWAP LOGIC BASED ON THE LOGGED-IN PANEL
            // The frontend relies on 'host.full_name' always containing the PARTNER'S name!
            
            $isUserDbHost = ($meeting['host_type'] === $currentUserClass && $meeting['host_id'] == $currentUserId);

            if ($isAdminLoggedIn) {
                // Admin Panel view:
                if ($isUserDbHost) {
                    // Admin created it, so partner is the participant
                    $meeting['host']['full_name'] = $participantFormatted;
                    $meeting['participant']['full_name'] = $hostFormatted;
                } else {
                    // Buyer created it, so partner is the host
                    $meeting['host']['full_name'] = $hostFormatted;
                    $meeting['participant']['full_name'] = $participantFormatted;
                }
            } else {
                // Buyer / Craftsman Panel view:
                if ($meeting['host_type'] === \App\Models\ProcessOwner::class) {
                    // Admin created it, so partner is the host
                    $meeting['host']['full_name'] = $hostFormatted;
                    $meeting['participant']['full_name'] = $participantFormatted;
                } else {
                    // Buyer created it, so partner is the participant
                    $meeting['host']['full_name'] = $participantFormatted;
                    $meeting['participant']['full_name'] = $hostFormatted;
                }
            }

            return $meeting;
        }, $meetingsArray);

        return response()->json(['success' => true, 'data' => $transformedMeetings]);
    }

    /**
     * Get participants based on the requested category, or get allowed categories.
     */
    public function getParticipants(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (isset($user->role) && ($user->role === 'superadmin' || $user->role === 'super_admin'));
        
        $isAdmin = ($user instanceof \App\Models\ProcessOwner && $user->role === 'admin');

        // If no category is provided, return the allowed categories for the user's role
        if (!$request->has('category') || empty($request->input('category'))) {
            if (!$isSuperAdmin && !$isAdmin) {
                return response()->json(['success' => true, 'allowed_categories' => ['admin', 'superadmin']]);
            } else {
                return response()->json(['success' => true, 'allowed_categories' => ['admin', 'superadmin', 'buyer', 'craftsman']]);
            }
        }

        $category = $request->input('category'); // admin, superadmin, buyer, craftsman

        // Rule: Buyers and Craftsmen can only view Admin and Superadmin
        if (!$isSuperAdmin && !$isAdmin) {
            if (!in_array($category, ['admin', 'superadmin'])) {
                return response()->json(['success' => false, 'message' => 'You can only view Admin and Superadmin participants.'], 403);
            }
        }

        $data = [];

        if ($category === 'admin') {
            $data = \App\Models\ProcessOwner::where('role', 'admin')->where('id', '!=', $user->id)->get(['id', 'full_name', 'category', 'user_code']);
        } elseif ($category === 'superadmin') {
            $data = \App\Models\ProcessOwner::whereIn('role', ['superadmin', 'super_admin'])->where('id', '!=', $user->id)->get(['id', 'full_name', 'user_code']);
        } elseif ($category === 'buyer') {
            $data = \App\Models\Buyer::select('id', \Illuminate\Support\Facades\DB::raw('COALESCE(business_name, name) as full_name'), 'bp_code as user_code')->get();
        } elseif ($category === 'craftsman') {
            $data = \App\Models\Craftman::select('id', \Illuminate\Support\Facades\DB::raw('COALESCE(business_name, name) as full_name'), 'craftman_code as user_code')->get();
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid category.'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store a new meeting.
     */
    public function store(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'participant_type' => 'nullable|in:buyer,craftsman,admin,super-admin',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer',
        ]);

        $user = Auth::user();

        $typeMap = [
            'buyer' => \App\Models\Buyer::class,
            'craftsman' => \App\Models\Craftman::class,
            'admin' => \App\Models\ProcessOwner::class,
            'super-admin' => \App\Models\ProcessOwner::class,
        ];

        $participantId = $request->participant_id;
        $participantType = null;

        // If participant_type is provided, use it
        if ($request->filled('participant_type')) {
            $participantType = $typeMap[$request->participant_type] ?? null;
        }

        $isSenderAdmin = ($user instanceof \App\Models\ProcessOwner);

        // If not provided, try to infer it
        if (!$participantType) {
            if ($isSenderAdmin) {
                // Admin creates: search both tables by code
                $buyer = \App\Models\Buyer::where('bp_code', $participantId)->first();
                if ($buyer) {
                    $participantType = \App\Models\Buyer::class;
                    $participantId = $buyer->id;
                } else {
                    $craftsman = \App\Models\Craftman::where('craftman_code', $participantId)->first();
                    if ($craftsman) {
                        $participantType = \App\Models\Craftman::class;
                        $participantId = $craftsman->id;
                    }
                }
            } else {
                // Buyer/Craftsman creates: must be Admin
                $participantType = \App\Models\ProcessOwner::class;

                // Try looking up by category first
                $category = \App\Models\AdminCategory::where('name', $participantId)->first();
                if ($category) {
                    $placeholderAdmin = \App\Models\ProcessOwner::where('category', $category->name)->first();
                    if ($placeholderAdmin) {
                        $participantId = $placeholderAdmin->id;
                    } else {
                        return response()->json(['success' => false, 'message' => 'No admins available in this category.'], 404);
                    }
                } else {
                    // Fallback to user_code
                    $admin = \App\Models\ProcessOwner::where('user_code', $participantId)->first();
                    if ($admin) {
                        $participantId = $admin->id;
                    } else {
                        // If it's a numeric ID, try finding by ID
                        if (is_numeric($participantId) && \App\Models\ProcessOwner::find($participantId)) {
                            $participantId = (int) $participantId;
                        } else {
                            return response()->json(['success' => false, 'message' => 'Admin or Category not found.'], 404);
                        }
                    }
                }
            }
        } else {
            // If type WAS provided, do the specific lookup
            if ($participantType === \App\Models\Buyer::class) {
                $buyer = \App\Models\Buyer::where('bp_code', $participantId)->first();
                if (!$buyer) return response()->json(['success' => false, 'message' => 'Buyer not found with that BP code.'], 404);
                $participantId = $buyer->id;
            } elseif ($participantType === \App\Models\Craftman::class) {
                $craftsman = \App\Models\Craftman::where('craftman_code', $participantId)->first();
                if (!$craftsman) return response()->json(['success' => false, 'message' => 'Craftsman not found with that code.'], 404);
                $participantId = $craftsman->id;
            } elseif ($participantType === \App\Models\ProcessOwner::class) {
                if ($request->participant_type === 'admin') {
                    // Admin (by Category)
                    $categoryName = strtolower(str_replace(' ', '', $participantId));
                    $placeholderAdmin = \App\Models\ProcessOwner::whereRaw('LOWER(REPLACE(category, " ", "")) = ?', [$categoryName])->first();
                    if ($placeholderAdmin) {
                        $participantId = $placeholderAdmin->id;
                    } else {
                        // Fallback to searching category name directly
                        $placeholderAdmin = \App\Models\ProcessOwner::where('category', 'like', "%{$participantId}%")->first();
                        if ($placeholderAdmin) {
                            $participantId = $placeholderAdmin->id;
                        } else {
                            return response()->json(['success' => false, 'message' => 'No admins available in this category.'], 404);
                        }
                    }
                } elseif ($request->participant_type === 'super-admin') {
                    // SuperAdmin (by user_code or ID)
                    $admin = \App\Models\ProcessOwner::where('user_code', $participantId)->first();
                    if ($admin) {
                        $participantId = $admin->id;
                    } else {
                        if (is_numeric($participantId)) {
                            $admin = \App\Models\ProcessOwner::find($participantId);
                            if ($admin) {
                                $participantId = $admin->id;
                            } else {
                                return response()->json(['success' => false, 'message' => 'SuperAdmin not found.'], 404);
                            }
                        } else {
                            return response()->json(['success' => false, 'message' => 'SuperAdmin not found.'], 404);
                        }
                    }
                }
            }
        }

        // If still no type found after trying to infer (for Admin case)
        if (!$participantType) {
            return response()->json(['success' => false, 'message' => 'Could not determine participant type from the provided code.'], 400);
        }

        $isParticipantAdmin = ($participantType === \App\Models\ProcessOwner::class);

        // Removed Rule 1: Admins and Superadmins can now create meetings with other Admins/Superadmins

        // Rule 2: Buyers and Craftsmen can only create meetings with Admins
        if (!$isSenderAdmin && !$isParticipantAdmin) {
            return response()->json(['success' => false, 'message' => 'Buyers and Craftsmen can only create meetings with Admins.'], 403);
        }

        // Determine status
        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin());
        $isAdmin = ($user instanceof \App\Models\ProcessOwner && $user->role === 'admin');

        // As per your request: Admin creates -> approved, Buyer creates -> pending
        $status = ($isSuperAdmin || $isAdmin) ? 'approved' : 'pending';

        $meeting = Meeting::create([
            'host_id' => $user->id,
            'host_type' => get_class($user),
            'participant_id' => $participantId,
            'participant_type' => $participantType,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'status' => $status,
        ]);

        // Load participant to get the code
        $meeting->load('participant');
        $participantCode = null;
        if ($meeting->participant instanceof \App\Models\Buyer) {
            $participantCode = $meeting->participant->bp_code;
        } elseif ($meeting->participant instanceof \App\Models\Craftman) {
            $participantCode = $meeting->participant->craftman_code;
        } elseif ($meeting->participant instanceof \App\Models\ProcessOwner) {
            $participantCode = $meeting->participant->user_code;
        }

        // Convert to array and add the code
        $responseData = $meeting->toArray();
        $responseData['participant_code'] = $participantCode;

        return response()->json(['success' => true, 'message' => 'Meeting created successfully', 'data' => $responseData], 201);
    }

    /**
     * Approve a meeting.
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        // Only Admins can approve
        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin());
        $isAdmin = ($user instanceof \App\Models\ProcessOwner && $user->role === 'admin');

        if (!$isSuperAdmin && !$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Admins can approve meetings.'], 403);
        }

        $meeting = Meeting::findOrFail($id);

        // Only allow approving if it is in 'pending' status
        if ($meeting->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This meeting cannot be approved. Current status: ' . $meeting->status], 400);
        }

        // Add atomic lock to prevent duplicate processing on double-click
        $lockKey = "meeting_approved_lock_{$meeting->id}";
        if (!\Illuminate\Support\Facades\Cache::lock($lockKey, 60)->get()) {
            return response()->json(['success' => false, 'message' => 'This meeting is already being processed.'], 409);
        }

        $meeting->update(['status' => 'approved']);

        // Send notification to host
        if ($meeting->host && method_exists($meeting->host, 'notify')) {
            $meeting->host->notify(new MeetingStatusNotification($meeting, 'approved'));
        }

        return response()->json(['success' => true, 'message' => 'Meeting approved successfully', 'data' => $meeting]);
    }

    /**
     * Cancel a meeting.
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        $meeting = Meeting::findOrFail($id);

        // Check permission: Only host or participant or SuperAdmin can cancel
        $isHost = ($meeting->host_id == $user->id && $meeting->host_type == get_class($user));
        $isParticipant = ($meeting->participant_id == $user->id && $meeting->participant_type == get_class($user));
        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin());

        if (!$isHost && !$isParticipant && !$isSuperAdmin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to cancel this meeting.'], 403);
        }

        $oldStatus = $meeting->status;
        $meeting->update(['status' => 'cancelled']);

        // Send notification to host if cancelled by someone else (Admin/SuperAdmin)
        if (!$isHost && $meeting->host && method_exists($meeting->host, 'notify')) {
            $meeting->host->notify(new MeetingStatusNotification($meeting, 'cancelled'));
        }

        return response()->json(['success' => true, 'message' => 'Meeting cancelled successfully', 'data' => $meeting]);
    }

    /**
     * Write to Firebase Realtime Database active_calls/{meetingId}
     * Authenticated via service account OAuth2 token — works regardless of RTDB security rules.
     * This triggers the admin panel's onValue() ringing modal listener immediately.
     */
    private function writeRtdbActiveCall($meeting, $caller)
    {
        try {
            $dbUrl      = env('FIREBASE_DATABASE_URL', 'https://arihanth-1938c-default-rtdb.firebaseio.com');
            $callerName = $this->getFormattedCallerName($caller);

            $category = null;
            if ($meeting->participant_type === \App\Models\ProcessOwner::class) {
                $participant = \App\Models\ProcessOwner::find($meeting->participant_id);
                if ($participant) {
                    $category = $participant->category;
                }
            } elseif ($meeting->host_type === \App\Models\ProcessOwner::class) {
                $host = \App\Models\ProcessOwner::find($meeting->host_id);
                if ($host) {
                    $category = $host->category;
                }
            }

            $payload = [
                'status'      => 'ringing',
                'meeting_id'  => $meeting->id,
                'room_id'     => $meeting->room_id,
                'caller_name' => $callerName,
                'caller_id'   => $caller->id,
                'category'    => $category,
                'timestamp'   => now()->timestamp,
            ];

            // Get OAuth2 access token from service account (same as FCM uses)
            $accessToken = $this->getRtdbAccessToken();

            $endpoint = rtrim($dbUrl, '/') . '/active_calls/' . $meeting->id . '.json';

            // Append auth token as query param if we have one
            if ($accessToken) {
                $endpoint .= '?access_token=' . urlencode($accessToken);
            }

            $client = new Client(['timeout' => 5]);
            $response = $client->put($endpoint, [
                'json'        => $payload,
                'http_errors' => false,
            ]);

            $status = $response->getStatusCode();
            if ($status >= 200 && $status < 300) {
                Log::info('RTDB active_calls written for meeting #' . $meeting->id);
            } else {
                Log::warning('RTDB write returned HTTP ' . $status . ': ' . $response->getBody()->getContents());
            }
        } catch (\Exception $e) {
            Log::warning('RTDB write failed: ' . $e->getMessage());
        }
    }

    /**
     * Get a short-lived OAuth2 access token using the Firebase service account.
     * Reuses the same credentials resolution logic as FirebaseService.
     */
    private function getRtdbAccessToken(): ?string
    {
        try {
            $scopes = [
                'https://www.googleapis.com/auth/firebase',
                'https://www.googleapis.com/auth/userinfo.email',
            ];

            $envCredentials = env('FIREBASE_CREDENTIALS') ?? env('FIREBASE_SERVICE_ACCOUNT');
            $credentials    = null;

            if ($envCredentials) {
                // 1. Treat env value as relative to project root (most common: "storage/app/...")
                if (file_exists(base_path($envCredentials))) {
                    $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, base_path($envCredentials));
                }
                // 2. Treat env value as relative to storage/ (e.g. "app/firebase-service-account.json")
                elseif (file_exists(storage_path($envCredentials))) {
                    $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, storage_path($envCredentials));
                }
                // 3. Treat env value as an absolute path
                elseif (file_exists($envCredentials)) {
                    $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $envCredentials);
                }
            }

            if (!$credentials) {
                $fallback = storage_path('app/firebase-service-account.json');
                if (file_exists($fallback)) {
                    $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $fallback);
                }
            }

            if (!$credentials) {
                return null;
            }

            $token = $credentials->fetchAuthToken();
            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::warning('RTDB access token fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a formatted caller name (Code - Name) for notifications.
     */
    private function getFormattedCallerName($caller)
    {
        $code = '';
        $name = '';

        if ($caller instanceof \App\Models\Buyer) {
            $code = $caller->bp_code ?? '';
            $name = $caller->name ?? ($caller->full_name ?? ($caller->business_name ?? 'Buyer'));
        } elseif ($caller instanceof \App\Models\Craftman) {
            $code = $caller->craftman_code ?? ($caller->craftsman_code ?? '');
            $name = $caller->name ?? ($caller->full_name ?? ($caller->business_name ?? 'Craftsman'));
        } elseif ($caller instanceof \App\Models\ProcessOwner) {
            $name = $caller->full_name ?? ($caller->name ?? 'Admin');
            
            $isSuper = isset($caller->role) && ($caller->role === 'superadmin' || $caller->role === 'super_admin');
            if ($isSuper) {
                return "Superadmin - " . $name;
            } else {
                return "AJPL";
            }
        } else {
            return method_exists($caller, 'getNameAttribute') ? $caller->name : ($caller->full_name ?? ($caller->business_name ?? 'Participant'));
        }

        if ($code && $name) {
            return "$code - $name";
        }
        
        return $code ?: ($name ?: 'Participant');
    }
}
