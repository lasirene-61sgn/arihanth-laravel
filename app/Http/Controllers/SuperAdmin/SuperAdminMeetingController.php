<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;

class SuperAdminMeetingController extends Controller
{
    /**
     * View ALL meetings across the whole system
     */
    public function index()
    {
        $meetings = Meeting::with(['host', 'participant'])->latest()->paginate(10);
        $buyers = Buyer::orderBy('name')->get();
        $craftsmen = Craftman::orderBy('name')->get();
        $processOwners = ProcessOwner::where('id', '!=', auth()->id())->orderBy('full_name')->get();
        
        return view('super-admin.meetings.index', compact('meetings', 'buyers', 'craftsmen', 'processOwners'));
    }

    /**
     * Superadmin can schedule a call for themselves or others
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
            'host_id' => auth()->id(),
            'host_type' => get_class(auth()->user()),
            'participant_id' => $request->participant_id,
            'participant_type' => $request->participant_type,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Meeting created by Superadmin.');
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

        $user = auth()->user();
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

        $user = auth()->user();

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

        $user = auth()->user();

        // Send push notification only to the other party
        if ($meeting->host_id != $user->id || $meeting->host_type != get_class($user)) {
            if ($meeting->host && method_exists($meeting->host, 'notify')) {
                $meeting->host->notify(new \App\Notifications\MeetingStatusNotification($meeting, 'cancelled'));
            }
        }
        
        if ($meeting->participant_id != $user->id || $meeting->participant_type != get_class($user)) {
            if ($meeting->participant && method_exists($meeting->participant, 'notify')) {
                $meeting->participant->notify(new \App\Notifications\MeetingStatusNotification($meeting, 'cancelled'));
            }
        }

        return back()->with('success', 'Meeting cancelled.');
    }

    /**
     * Delete a meeting
     */
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();

        return back()->with('success', 'Meeting deleted successfully.');
    }
}
