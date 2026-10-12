<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\CreateReapir;
use App\Http\Requests\Superadmin\UpdateReapir;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\Repair;
use Illuminate\Http\Request;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'new');

        $query = Repair::with(['buyer', 'craftsman']);

        if ($tab === 'new') {
            $query->where('status', 'pending');
        } elseif ($tab === 'accepted') {
            $query->where('status', 'accepted');
        } elseif ($tab === 'allocated') {
            $query->where('status', 'allocated')->where('craftsman_status', '!=', 'pending_approval');
        } elseif ($tab === 'craftsman_approval') {
            $query->where('craftsman_status', 'pending_approval');
        } elseif ($tab === 'approval') {
            $query->where('craftsman_status', 'approved_by_superadmin')->whereNull('item_sent_by');
        } elseif ($tab === 'buyer_approval') {
            // In Buyer Approval when item_sent_by is filled but order status is not yet completed
            $query->where('craftsman_status', 'approved_by_superadmin')->whereNotNull('item_sent_by')->where('status', '!=', 'completed');
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($tab === 'rejected') {
            $query->where('status', 'rejected');
        }

        $repairs = $query->latest()->paginate(15);
        $craftsmen = Craftman::all();

        $returnedNames = Repair::whereNotNull('item_returned_by')->distinct()->pluck('item_returned_by');
        $collectedNames = Repair::whereNotNull('item_collected_by')->distinct()->pluck('item_collected_by');
        $sentNames = Repair::whereNotNull('item_sent_by')->distinct()->pluck('item_sent_by');
        $acceptedNames = Repair::whereNotNull('item_accepted_by')->distinct()->pluck('item_accepted_by');

        return view('super-admin.repairs.index', compact(
            'repairs',
            'tab',
            'craftsmen',
            'returnedNames',
            'collectedNames',
            'sentNames',
            'acceptedNames'
        ));
    }

    // Final Completion from Buyer Approval Tab
    public function markBuyerCompleted(Request $request, Repair $repair)
    {
        $request->validate([
            'item_sent_by' => ['required', 'string', 'max:255'],
            'item_accepted_by' => ['required', 'string', 'max:255'],
        ]);

        $repair->update([
            'status' => 'completed',
            'item_sent_by' => $request->item_sent_by,
            'item_accepted_by' => $request->item_accepted_by,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Order marked as completed successfully and moved to Completed tab.');
    }

    // Superadmin clicks Approve in Craftsman Approval -> Moves to Approval Tab (to fill Item Sent By)
    public function approveCompletion(Request $request, Repair $repair)
    {
        $request->validate([
            'item_returned_by' => ['required', 'string', 'max:255'],
            'item_collected_by' => ['required', 'string', 'max:255'],
        ]);

        $repair->update([
            'craftsman_status' => 'approved_by_superadmin',
            'item_returned_by' => $request->item_returned_by,
            'item_collected_by' => $request->item_collected_by,
        ]);

        return back()->with('success', 'Craftsman completion approved. Please fill Item Sent By in the Approval tab.');
    }

    // Superadmin fills Item Sent By in Approval tab -> Moves to Buyer Approval
    public function storeItemSentBy(Request $request, Repair $repair)
    {
        $request->validate([
            'item_sent_by' => ['required', 'string', 'max:255'],
        ]);

        $repair->update([
            'item_sent_by' => $request->item_sent_by,
        ]);

        return back()->with('success', 'Item Sent By recorded. Moved to Buyer Approval tab.');
    }

    // Final Step: Mark fully completed (from Buyer Approval tab)
    public function markFullyCompleted(Repair $repair)
    {
        $repair->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Order marked as fully completed and closed.');
    }

    // Single Complete Order
    public function complete(Request $request, Repair $repair)
    {
        $request->validate([
            'item_returned_by' => ['required', 'string', 'max:255'],
            'item_collected_by' => ['required', 'string', 'max:255'],
        ]);

        $repair->update([
            'status' => 'completed',
            'item_returned_by' => $request->item_returned_by,
            'item_collected_by' => $request->item_collected_by,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Repair order marked as completed successfully.');
    }

    // Bulk Complete Orders
    public function bulkComplete(Request $request)
    {
        $request->validate([
            'repair_ids' => ['required', 'array'],
            'repair_ids.*' => ['exists:repairs,id'],
            'item_returned_by' => ['required', 'string', 'max:255'],
            'item_collected_by' => ['required', 'string', 'max:255'],
        ]);

        Repair::whereIn('id', $request->repair_ids)->update([
            'status' => 'completed',
            'item_returned_by' => $request->item_returned_by,
            'item_collected_by' => $request->item_collected_by,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Selected repair orders completed successfully.');
    }

    // Accept Order
    public function accept(Repair $repair)
    {
        $repair->update(['status' => 'accepted']);
        return back()->with('success', 'Repair order accepted successfully.');
    }

    // Reject Order
    public function reject(Repair $repair)
    {
        $repair->update(['status' => 'rejected']);
        return back()->with('success', 'Repair order rejected.');
    }

    // Allocate to Craftsman
    public function allocate(Request $request, Repair $repair)
    {
        $request->validate([
            'craftman_code' => ['required', 'string', 'exists:craftmen,craftman_code']
        ]);

        $repair->update([
            'status' => 'allocated',
            'craftman_code' => $request->craftman_code
        ]);

        return back()->with('success', 'Repair order allocated to craftman successfully.');
    }

    public function create()
    {
        $buyers = Buyer::all();

        // Fetch unique previously used names directly from the repairs table for dropdowns
        $deliveredNames = Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        $receivedNames = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');

        return view('super-admin.repairs.create', compact('buyers', 'deliveredNames', 'receivedNames'));
    }

    public function store(CreateReapir $request)
    {
        Repair::create($request->validated());

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record created successfully.');
    }

    public function show(Repair $repair)
    {
        return view('super-admin.repairs.show', compact('repair'));
    }

    public function edit(Repair $repair)
    {
        $buyers = Buyer::all();

        $deliveredNames = Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        $receivedNames = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');

        return view('super-admin.repairs.edit', compact('repair', 'buyers', 'deliveredNames', 'receivedNames'));
    }

    public function update(UpdateReapir $request, Repair $repair)
    {
        $repair->update($request->validated());

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record updated successfully.');
    }

    public function destroy(Repair $repair)
    {
        $repair->delete();

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record deleted successfully.');
    }
}
