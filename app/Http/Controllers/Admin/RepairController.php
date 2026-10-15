<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateRepair;
use App\Http\Requests\Admin\UpdateRepair;
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

        $query = Repair::with(['buyer', 'craftman']);

        if ($tab === 'new') {
            $query->where('status', 'pending');
        } elseif ($tab === 'accepted') {
            $query->where('status', 'accepted');
        } elseif ($tab === 'allocated') {
            $query->where('status', 'allocated')
                  ->where(function ($q) {
                      $q->whereNull('craftsman_status')
                        ->orWhere('craftsman_status', '!=', 'pending_approval');
                  });
        } elseif ($tab === 'craftsman_approval') {
            $query->where('craftsman_status', 'pending_approval');
        } elseif ($tab === 'approval') {
            $query->where('craftsman_status', 'approved_by_superadmin')
                  ->whereNull('item_sent_by');
        } elseif ($tab === 'buyer_approval') {
            $query->where('craftsman_status', 'approved_by_superadmin')
                  ->whereNotNull('item_sent_by')
                  ->where('status', '!=', 'completed');
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

        return view('admin.repairs.index', compact(
            'repairs', 'tab', 'craftsmen',
            'returnedNames', 'collectedNames', 'sentNames', 'acceptedNames'
        ));
    }

    public function create()
    {
        $buyers = Buyer::all();
        $deliveredNames = Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        $receivedNames = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');

        return view('admin.repairs.create', compact('buyers', 'deliveredNames', 'receivedNames'));
    }

    public function store(CreateRepair $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('repair_image')) {
            $validatedData['repair_image'] = $request->file('repair_image')->store('repairs', 'public');
        }

        Repair::create($validatedData);

        return redirect()->route('admin.repairs.index')
            ->with('success', 'Repair/Sample record created successfully.');
    }

    public function show(Repair $repair)
    {
        $repair->load(['buyer', 'craftman']);
        return view('admin.repairs.show', compact('repair'));
    }

    public function edit(Repair $repair)
    {
        $buyers = Buyer::all();
        $deliveredNames = Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        $receivedNames = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');

        return view('admin.repairs.edit', compact('repair', 'buyers', 'deliveredNames', 'receivedNames'));
    }

    public function update(UpdateRepair $request, Repair $repair)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('repair_image')) {
            if ($repair->repair_image && Storage::disk('public')->exists($repair->repair_image)) {
                Storage::disk('public')->delete($repair->repair_image);
            }
            $validatedData['repair_image'] = $request->file('repair_image')->store('repairs', 'public');
        } else {
            $validatedData['repair_image'] = $repair->repair_image;
        }

        $repair->update($validatedData);

        return redirect()->route('admin.repairs.index')
            ->with('success', 'Repair/Sample record updated successfully.');
    }

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

        return redirect()->route('admin.repairs.index')
            ->with('success', 'Repair/Sample record deleted successfully.');
    }

    // --- Workflow Action Methods ---

    public function accept(Repair $repair)
    {
        $repair->update(['status' => 'accepted']);
        return back()->with('success', 'Order accepted successfully.');
    }

    public function reject(Repair $repair)
    {
        $repair->update(['status' => 'rejected']);
        return back()->with('success', 'Order rejected.');
    }

    public function allocate(Request $request, Repair $repair)
    {
        $request->validate([
            'craftman_code' => ['required', 'exists:craftsmen,craftman_code'],
        ]);

        $repair->update([
            'status' => 'allocated',
            'craftman_code' => $request->craftman_code,
        ]);

        return back()->with('success', 'Order allocated to craftsman successfully.');
    }

    // Single Complete in Allocated Tab -> Moves to Craftsman Approval Tab
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
            'craftsman_status' => 'pending_approval',
            'item_returned_by' => $request->item_returned_by,
            'item_collected_by' => $request->item_collected_by,
            'weight' => $request->filled('weight') ? $request->weight : $repair->weight,
            'craftsman_voucher_image' => $voucherPath,
            'craftsman_completion_proof_image' => $proofPath,
        ]);

        return back()->with('success', 'Order marked as completed and sent to Craftsman Approval tab.');
    }

    // Bulk Complete in Allocated Tab -> Moves to Craftsman Approval Tab
    public function bulkComplete(Request $request)
    {
        $request->validate([
            'repair_ids' => ['required', 'array', 'min:1'],
            'repair_ids.*' => ['exists:repairs,id'],
            'item_returned_by' => ['required', 'string', 'max:255'],
            'item_collected_by' => ['required', 'string', 'max:255'],
        ]);

        Repair::whereIn('id', $request->repair_ids)->update([
            'craftsman_status' => 'pending_approval',
            'item_returned_by' => $request->item_returned_by,
            'item_collected_by' => $request->item_collected_by,
        ]);

        return back()->with('success', 'Selected orders sent to Craftsman Approval tab.');
    }

    // Craftsman Approval Tab -> Moves to Approval Tab
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

    // Approval Tab -> Moves to Buyer Approval Tab
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

    // Buyer Approval Tab -> Final Completion
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
}