<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\CreateReapir;
use App\Http\Requests\Superadmin\UpdateReapir;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function complete(Request $request, Repair $repair)
    {
        $request->validate([
            'item_returned_by' => ['required', 'string', 'max:255'],
            'item_collected_by' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric'],
            'craftsman_voucher_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'craftsman_completion_proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $voucherPath = $repair->craftsman_voucher_image;
        if ($request->hasFile('craftsman_voucher_image')) {
            if ($voucherPath && Storage::disk('public')->exists($voucherPath)) {
                Storage::disk('public')->delete($voucherPath);
            }
            $voucherPath = $request->file('craftsman_voucher_image')->store('repairs/vouchers', 'public');
        }

        $proofPath = $repair->craftsman_completion_proof_image;
        if ($request->hasFile('craftsman_completion_proof_image')) {
            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }
            $proofPath = $request->file('craftsman_completion_proof_image')->store('repairs/proofs', 'public');
        }

        $repair->update([
            'craftsman_status' => 'pending_approval', // <-- Moves to Craftsman Approval tab instead of completed
            'item_returned_by' => $request->item_returned_by,
            'item_collected_by' => $request->item_collected_by,
            'weight' => $request->filled('weight') ? $request->weight : $repair->weight,
            'craftsman_voucher_image' => $voucherPath,
            'craftsman_completion_proof_image' => $proofPath,
        ]);

        return back()->with('success', 'Order marked as completed by Superadmin and sent to Craftsman Approval tab.');
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
        $validatedData = $request->validated();

        // Handle file upload before creating the record
        if ($request->hasFile('repair_image')) {
            $validatedData['repair_image'] = $request->file('repair_image')->store('repairs', 'public');
        }

        // Create the record with all validated data including the image path
        Repair::create($validatedData);

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
        $validatedData = $request->validated();

        // Handle file upload if a new image is provided
        if ($request->hasFile('repair_image')) {
            // Delete old image if it exists
            if ($repair->repair_image && Storage::disk('public')->exists($repair->repair_image)) {
                Storage::disk('public')->delete($repair->repair_image);
            }

            // Store the new image and add path to validated data
            $validatedData['repair_image'] = $request->file('repair_image')->store('repairs', 'public');
        } else {
            // Keep existing image if no new file is uploaded
            $validatedData['repair_image'] = $repair->repair_image;
        }

        // Update the record
        $repair->update($validatedData);

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record updated successfully.');
    }

    // Final Completion from Superadmin Buyer Approval Tab
    public function markBuyerCompleted(Request $request, Repair $repair)
    {
        $request->validate([
            'item_sent_by' => ['required', 'string', 'max:255'],
            'item_accepted_by' => ['required', 'string', 'max:255'],
            'buyer_voucher_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'buyer_completion_proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $voucherPath = $repair->buyer_voucher_image;
        if ($request->hasFile('buyer_voucher_image')) {
            if ($voucherPath && Storage::disk('public')->exists($voucherPath)) {
                Storage::disk('public')->delete($voucherPath);
            }
            $voucherPath = $request->file('buyer_voucher_image')->store('repairs/buyer_vouchers', 'public');
        }

        $proofPath = $repair->buyer_completion_proof_image;
        if ($request->hasFile('buyer_completion_proof_image')) {
            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }
            $proofPath = $request->file('buyer_completion_proof_image')->store('repairs/buyer_proofs', 'public');
        }

        $repair->update([
            'status' => 'completed',
            'item_sent_by' => $request->item_sent_by,
            'item_accepted_by' => $request->item_accepted_by,
            'buyer_voucher_image' => $voucherPath,
            'buyer_completion_proof_image' => $proofPath,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Order marked as completed successfully and moved to Completed tab.');
    }

    // Updated destroy method to clean up all 5 possible images
    public function destroy(Repair $repair)
    {
        $imageFields = [
            'repair_image',
            'craftsman_voucher_image',
            'craftsman_completion_proof_image',
            'buyer_voucher_image',
            'buyer_completion_proof_image',
        ];

        foreach ($imageFields as $field) {
            if ($repair->$field && Storage::disk('public')->exists($repair->$field)) {
                Storage::disk('public')->delete($repair->$field);
            }
        }

        $repair->delete();

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record deleted successfully.');
    }
}
