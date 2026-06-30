<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyerMeetingController extends Controller
{
    /**
     * Display a list of meetings scheduled for this Buyer
     */
    public function index()
    {
        $meetings = Meeting::where('participant_id', Auth::id())
            ->where('participant_type', 'App\Models\Buyer')
            ->with(['host'])
            ->latest()
            ->get();

        // Get all admins/super-admins the buyer can request a meeting with
        // Status is tinyInteger: 1 = active, 0 = inactive
        $admins = ProcessOwner::where('status', 1)
            ->where('role', '!=', 'super_admin')
            ->get();
        $superAdmins = ProcessOwner::where('status', 1)
            ->where('role', 'super_admin')
            ->get();

        return view('buyer.meetings.index', compact('meetings', 'admins', 'superAdmins'));
    }

    /**
     * Buyer requests a meeting with a Super Admin or Admin
     * The meeting is created as "requested" status — admin must still join/approve
     */
    public function store(Request $request)
    {
        $request->validate([
            'host_id' => 'required|exists:process_owners,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        Meeting::create([
            'host_id' => $request->host_id,
            'host_type' => ProcessOwner::class,
            'participant_id' => Auth::id(),
            'participant_type' => get_class(Auth::user()),
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your meeting request has been sent! The admin will confirm shortly.');
    }
}