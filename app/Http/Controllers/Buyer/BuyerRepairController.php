<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerRepairController extends Controller
{
    public function index(Request $request)
    {
        // Get the authenticated buyer (e.g., from auth or session)
        // Assuming auth()->user() represents the buyer or has a buyer relationship
        $buyerId = session('buyer_id', 1); // Replace with auth()->user()->id or your auth logic
        $currentBuyer = Buyer::find($buyerId);

        $tab = $request->get('tab', 'active');

        $query = Repair::with(['craftsman', 'buyer'])->where('buyer_id', $buyerId);

        if ($tab === 'active') {
            $query->where('status', '!=', 'completed')->where('craftsman_status', '!=', 'approved_by_superadmin');
        } elseif ($tab === 'approval') {
            $query->where('craftsman_status', 'approved_by_superadmin')->whereNotNull('item_sent_by')->where('status', '!=', 'completed');
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        }

        $repairs = $query->latest()->paginate(15);

        return view('buyer.repairs.index', compact('repairs', 'tab', 'currentBuyer'));
    }

    public function create()
    {
        $buyerId = session('buyer_id', 1);
        $currentBuyer = Buyer::find($buyerId);

        return view('buyer.repairs.create', compact('currentBuyer'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'product_name' => ['required', 'string', 'max:255'],
            'order_number' => ['required', 'string', 'max:255', 'unique:repairs,order_number'],
            'repair_or_sample' => ['required', 'in:repair,sample'],
            'weight' => ['nullable', 'numeric'],
            'repair_details' => ['nullable', 'string'],
        ]);

        Repair::create([
            'date' => $request->date,
            'buyer_id' => session('buyer_id', 1), // Automatically assigned to logged-in buyer
            'product_name' => $request->product_name,
            'order_number' => $request->order_number,
            'repair_or_sample' => $request->repair_or_sample,
            'weight' => $request->weight,
            'repair_details' => $request->repair_details,
            'status' => 'pending',
        ]);

        return redirect()->route('buyer.repairs.index')->with('success', 'Repair order created successfully.');
    }

    public function edit(Repair $repair)
    {
        if ($repair->status !== 'pending') {
            return redirect()->route('buyer.repairs.index')->with('error', 'You can only edit orders that are still pending.');
        }

        $buyerId = session('buyer_id', 1);
        $currentBuyer = Buyer::find($buyerId);

        return view('buyer.repairs.edit', compact('repair', 'currentBuyer'));
    }

    public function update(Request $request, Repair $repair)
    {
        if ($repair->status !== 'pending') {
            return redirect()->route('buyer.repairs.index')->with('error', 'You can only update orders that are still pending.');
        }

        $request->validate([
            'date' => ['required', 'date'],
            'product_name' => ['required', 'string', 'max:255'],
            'order_number' => ['required', 'string', 'max:255', 'unique:repairs,order_number,' . $repair->id],
            'repair_or_sample' => ['required', 'in:repair,sample'],
            'weight' => ['nullable', 'numeric'],
            'repair_details' => ['nullable', 'string'],
        ]);

        $repair->update($request->only([
            'date', 'product_name', 'order_number', 'repair_or_sample', 'weight', 'repair_details'
        ]));

        return redirect()->route('buyer.repairs.index')->with('success', 'Repair order updated successfully.');
    }

    public function destroy(Repair $repair)
    {
        if ($repair->status !== 'pending') {
            return redirect()->route('buyer.repairs.index')->with('error', 'You can only delete orders that are still pending.');
        }

        $repair->delete();

        return redirect()->route('buyer.repairs.index')->with('success', 'Repair order deleted successfully.');
    }

    public function acceptCompletion(Request $request, Repair $repair)
    {
        $request->validate([
            'item_accepted_by' => ['required', 'string', 'max:255'],
        ]);

        $repair->update([
            'status' => 'completed',
            'item_accepted_by' => $request->item_accepted_by,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Repair order accepted and marked as completed successfully.');
    }
}