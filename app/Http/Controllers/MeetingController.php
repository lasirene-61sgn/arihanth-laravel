<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CyberDeep\LaravelAgoraTokenGenerator\Services\Agora;
use App\Notifications\MeetingStatusNotification;
use Illuminate\Support\Facades\Cache;
use App\Models\ProcessOwner;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class MeetingController extends Controller
{
    /**
     * Dashboard: List all scheduled video calls
     */
    public function index()
    {
        // Superadmin sees everything, Admin sees only their own
        if (Auth::user()->hasRole('superadmin')) {
            $meetings = Meeting::with(['host', 'participant'])->latest()->get();
        } else {
            $meetings = Meeting::where('host_id', Auth::id())
                                ->where('host_type', get_class(Auth::user()))
                                ->with(['participant'])
                                ->get();
        }

        $buyers = Buyer::orderBy('name')->get();
        $craftsmen = Craftman::orderBy('name')->get();
        $processOwners = ProcessOwner::where('id', '!=', Auth::id())->orderBy('full_name')->get();

        return view('admin.meetings.index', compact('meetings', 'buyers', 'craftsmen', 'processOwners'));
    }

    /**
     * Store: Superadmin/Admin schedules a new call
     */
    public function store(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'participant_type' => 'required', // E.g., 'App\Models\Buyer'
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        Meeting::create([
            'host_id' => Auth::id(),
            'host_type' => get_class(Auth::user()),
            'participant_id' => $request->participant_id,
            'participant_type' => $request->participant_type,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'status' => 'approved',
        ]);

        return back()->with('success', 'Jewelry consultation scheduled!');
    }

    /**
     * Join: The actual video room logic
     */
    public function join($room_id)
    {
        $meeting = Meeting::where('room_id', $room_id)->firstOrFail();

        // Cannot join a meeting that hasn't been approved yet
        if ($meeting->status === 'requested') {
            abort(403, 'This meeting is still pending approval. Please wait for the admin to confirm.');
        }

        // Cannot join a cancelled meeting
        if ($meeting->status === 'cancelled') {
            abort(403, 'This meeting has been cancelled.');
        }

        // Resolve the authenticated user from whichever guard is active
        // (set by the AuthenticateAnyGuard middleware)
        $user = auth()->user();

        // Standard Check — must be the host or the participant (polymorphic ID + type match)
        $isHost = ($meeting->host_id == $user->id && $meeting->host_type == get_class($user));
        $isParticipant = ($meeting->participant_id == $user->id && $meeting->participant_type == get_class($user));

        // Let Super Admin bypass the check
        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) 
                     || (isset($user->role) && ($user->role === 'superadmin' || $user->role === 'super_admin'));
                     
        $isAdmin = ($user instanceof \App\Models\ProcessOwner);

        if (!$isHost && !$isParticipant && !$isSuperAdmin && !$isAdmin) {
            abort(403, 'You do not have permission to join this specific consultation.');
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

        // Set started_at when host or superadmin joins
        if (($isHost || $isSuperAdmin) && is_null($meeting->started_at)) {
            $meeting->update(['started_at' => now()]);
        }

        // Generate Agora Token
        $appId = env('AGORA_APP_ID');
        $agoraToken = null;
        
        if ($appId) {
            $agoraToken = Agora::make($user->id)
                ->channel($room_id)
                ->uId($user->id) // Using DB User ID as Agora UID
                ->join(false) // Both parties need to publish video/audio
                ->audioOnly(false)
                ->token();

            // Send notification to the other party that this user has joined
            \Log::info('Join Notification Check', [
                'isParticipant' => $isParticipant,
                'isHost' => $isHost,
                'isSuperAdmin' => $isSuperAdmin,
                'user_role' => $user->role ?? 'none',
                'user_id' => $user->id
            ]);

            $cacheKey = "meeting_joined_notified_{$meeting->id}_" . get_class($user) . "_{$user->id}";

            // Atomic lock to prevent duplicate notifications from double-clicking
            if (\Illuminate\Support\Facades\Cache::lock($cacheKey, 60)->get()) {
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
                    // Generate token for the user to be notified
                    $targetToken = Agora::make($userToNotify->id)
                        ->channel($room_id)
                        ->uId($userToNotify->id)
                        ->join(false)
                        ->audioOnly(false)
                        ->token();

                    if ($user instanceof \App\Models\ProcessOwner) {
                        $code = $user->user_code ?? '';
                        $name = $user->full_name ?? ($user->name ?? 'Admin');
                        
                        $isSuper = isset($user->role) && ($user->role === 'superadmin' || $user->role === 'super_admin');
                        if ($isSuper) {
                            $roleLabel = 'Superadmin';
                        } else {
                            $roleLabel = $user->category ? $user->category : 'Admin';
                        }
                        
                        $parts = array_filter([$code, $name, $roleLabel]);
                        $callerName = implode(' - ', $parts);
                    } else {
                        $callerName = '';
                        $code = $user->bp_code ?? ($user->craftman_code ?? ($user->craftsman_code ?? ''));
                        if ($code) {
                            $callerName = $code . ' - ';
                        }
                        $callerName .= $user->name ?? ($user->full_name ?? ($user->business_name ?? 'Participant'));
                    }

                    // 1. Send FCM notification
                    $userToNotify->notify(new MeetingStatusNotification($meeting, 'joined', [
                        'app_id'       => $appId,
                        'token'        => $targetToken,
                        'channel_name' => $room_id,
                        'uid'          => $userToNotify->id,
                        'caller_name'  => $callerName
                    ]));

                    // 2. Write to Firebase RTDB active_calls ONLY if joiner is NOT an admin.
                    if (!($user instanceof \App\Models\ProcessOwner)) {
                        $this->writeRtdbActiveCall($meeting, $user);
                    }
                }
            }

            // Broadcast that the meeting was answered (non-fatal if Pusher is down)
            try {
                broadcast(new \App\Events\MeetingAnsweredEvent($meeting->id, $user->id));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Pusher broadcast failed (non-fatal): ' . $e->getMessage());
            }
        }

        return view('video.room', compact('meeting', 'agoraToken', 'appId'));
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
            $callerName = $caller->name ?? $caller->full_name ?? 'Buyer';

            $category = null;
            if ($meeting->participant_type === \App\Models\ProcessOwner::class) {
                $participant = \App\Models\ProcessOwner::find($meeting->participant_id);
                if ($participant) {
                    $category = $participant->category;
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
                $resolvedPath = storage_path($envCredentials);
                if (file_exists($resolvedPath)) {
                    $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $resolvedPath);
                } elseif (file_exists($envCredentials)) {
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
}
