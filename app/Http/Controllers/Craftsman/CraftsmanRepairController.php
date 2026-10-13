<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CraftsmanRepairController extends Controller
{
    public function index(Request $request)
    {
        // Assuming you store authenticated craftsman code in session or auth user
        // e.g., $craftmanCode = auth()->user()->craftman_code; or session('craftman_code');
        $craftmanCode = session('craftman_code', 'CA001'); // Replace with your auth logic

        $tab = $request->get('tab', 'assigned'); // assigned, in_process, pending_approval, completed

        $query = Repair::with('buyer')->where('craftman_code', $craftmanCode);

        if ($tab === 'assigned') {
            $query->where('status', 'allocated')->where('craftsman_status', 'pending_acceptance');
        } elseif ($tab === 'in_process') {
            $query->where('status', 'allocated')->where('craftsman_status', 'in_process');
        } elseif ($tab === 'pending_approval') {
            $query->where('craftsman_status', 'pending_approval');
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        }

        $repairs = $query->latest()->paginate(15);

        return view('craftsman.repairs.index', compact('repairs', 'tab'));
    }

    // Craftsman Accepts Order (Moves to In Process)
    public function acceptOrder(Repair $repair)
    {
        $repair->update(['craftsman_status' => 'in_process']);
        return back()->with('success', 'Repair order accepted and moved to In Process.');
    }

    public function markComplete(Request $request, Repair $repair)
    {
        $request->validate([
            'item_returned_by' => ['required', 'string', 'max:255'],
            'item_collected_by' => ['required', 'string', 'max:255'],
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
            'craftsman_voucher_image' => $voucherPath,
            'craftsman_completion_proof_image' => $proofPath,
        ]);

        return back()->with('success', 'Repair marked as completed. Sent to Superadmin for approval.');
    }
}
