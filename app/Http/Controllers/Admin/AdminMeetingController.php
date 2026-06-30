<?php

namespace App\Http\Controllers\Admin;

use App\Events\MeetingAnsweredEvent;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMeetingController extends Controller
{
    /**
     * View only the meetings hosted by this Admin + meetings requested by buyers/craftsmen
     */
    public function index()
    {
        // Meetings where admin is the host, OR meetings requested by buyers/craftsmen for this admin
        $meetings = Meeting::where(function ($query) {
            $query->where(function ($q) {
                $q->where('host_id', Auth::id())
                    ->where('host_type', get_class(Auth::user()));
            })->orWhere(function ($q) {
                $q->where('participant_id', Auth::id())
                    ->where('participant_type', get_class(Auth::user()));
            });
        })->with(['host', 'participant'])->latest()->get();

        $buyers = Buyer::orderBy('name')->get();
        $craftsmen = Craftman::orderBy('name')->get();
        $processOwners = ProcessOwner::where('id', '!=', Auth::id())->orderBy('full_name')->get();

        return view('admin.meetings.index', compact('meetings', 'buyers', 'craftsmen', 'processOwners'));
    }

    /**
     * Admin schedules a consultation with a Buyer or Craftsman
     */
    public function store(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'participant_type' => 'required',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer',
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

        return back()->with('success', 'Consultation scheduled.');
    }

    /**
     * Approve a meeting requested by a buyer or craftsman
     */
    public function approve(Request $request, Meeting $meeting)
    {
        if ($meeting->status !== 'pending') {
            return back()->with('error', 'This meeting cannot be approved.');
        }

        // Add atomic lock to prevent duplicate processing on double-click
        $lockKey = "meeting_approved_lock_{$meeting->id}";
        if (!\Illuminate\Support\Facades\Cache::lock($lockKey, 60)->get()) {
            return back()->with('success', 'Meeting request is already being processed.');
        }

        $user = Auth::user();
        $isAdmin = ($user instanceof \App\Models\ProcessOwner);

        // Auto-claim logic: If another admin approves this, they take over the meeting
        if ($isAdmin && $meeting->host_type === \App\Models\ProcessOwner::class && $meeting->host_id != $user->id) {
            $meeting->update(['host_id' => $user->id]);
            $meeting->load('host');
        } elseif ($isAdmin && $meeting->participant_type === \App\Models\ProcessOwner::class && $meeting->participant_id != $user->id) {
            $meeting->update(['participant_id' => $user->id]);
            $meeting->load('participant');
        }

        $meeting->update(['status' => 'approved']);

        $user = Auth::user();

        // Send push notification only to the other party
        if ($meeting->host_id != $user->id || $meeting->host_type != get_class($user)) {
            if ($meeting->host && method_exists($meeting->host, 'notify')) {
                $meeting->host->notify(new \App\Notifications\MeetingStatusNotification($meeting, 'approved'));
            }
        }
        
        if ($meeting->participant_id != $user->id || $meeting->participant_type != get_class($user)) {
            if ($meeting->participant && method_exists($meeting->participant, 'notify')) {
                $meeting->participant->notify(new \App\Notifications\MeetingStatusNotification($meeting, 'approved'));
            }
        }

        return back()->with('success', 'Meeting request approved.');
    }

    /**
     * Cancel a meeting
     */
    public function cancel(Request $request, Meeting $meeting)
    {
        $meeting->update(['status' => 'cancelled']);

        return back()->with('success', 'Meeting cancelled.');
    }

    public function answerMeeting(Request $request, $meeting_id)
{
    $adminId = Auth::id();

    try {
        $database = app('firebase.database');
        $callRef = $database->getReference('active_calls/' . $meeting_id);
        
        // Update the database record in the cloud
        $callRef->update([
            'status' => 'answered',
            'answered_by' => $adminId,
            'updated_at' => now()->toIso8601String()
        ]);

        // Update the MySQL Database so this Admin becomes the official participant
        $meeting = \App\Models\Meeting::find($meeting_id);
        if ($meeting && $meeting->participant_type === \App\Models\ProcessOwner::class) {
            // Only claim it if it's pending (meaning it was a category call that hasn't been claimed yet)
            // Or just always claim it if they answer. We'll just update it so they don't get 403 on join.
            $meeting->update([
                'participant_id' => $adminId,
                'status' => 'approved' // Automatically approve if they answer
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Firebase call status marked as answered.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
}
