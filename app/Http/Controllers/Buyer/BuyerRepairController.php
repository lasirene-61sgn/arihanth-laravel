<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;

class BuyerRepairController extends Controller
{
    public function index(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        $buyerId = $buyer ? $buyer->id : session('buyer_id', 1);
        $currentBuyer = $buyer ?? Buyer::find($buyerId);

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
        $buyer = Auth::guard('buyer')->user();
        $currentBuyer = $buyer ?? Buyer::find(session('buyer_id', 1));

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
            'repair_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('repair_image')) {
            $imagePath = $request->file('repair_image')->store('repairs', 'public');
        }

        $buyer = Auth::guard('buyer')->user() ?? Buyer::find(session('buyer_id', 1));
        $buyerId = $buyer ? $buyer->id : session('buyer_id', 1);

        Repair::create([
            'date' => $request->date,
            'buyer_id' => $buyerId,
            'product_name' => $request->product_name,
            'order_number' => $request->order_number,
            'repair_or_sample' => $request->repair_or_sample,
            'weight' => $request->weight,
            'repair_details' => $request->repair_details,
            'repair_image' => $imagePath,
            'item_delivered_by' => $buyer ? ($buyer->name ?? $buyer->business_name) : 'Buyer',
            'item_received_by' => 'Pending',
            'status' => 'pending',
        ]);

        return redirect()->route('buyer.repairs.index')->with('success', 'Repair order created successfully.');
    }



    public function edit(Repair $repair)
    {
        if ($repair->status !== 'pending') {
            return redirect()->route('buyer.repairs.index')->with('error', 'You can only edit orders that are still pending.');
        }

        $buyer = Auth::guard('buyer')->user();
        $buyerId = $buyer ? $buyer->id : session('buyer_id', 1);
        $currentBuyer = $buyer ?? Buyer::find($buyerId);

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
            'repair_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $imagePath = $repair->repair_image;
        if ($request->hasFile('repair_image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('repair_image')->store('repairs', 'public');
        }

        $repair->update([
            'date' => $request->date,
            'product_name' => $request->product_name,
            'order_number' => $request->order_number,
            'repair_or_sample' => $request->repair_or_sample,
            'weight' => $request->weight,
            'repair_details' => $request->repair_details,
            'repair_image' => $imagePath,
        ]);

        return redirect()->route('buyer.repairs.index')->with('success', 'Repair order updated successfully.');
    }



    public function destroy(Repair $repair)
    {
        if ($repair->status !== 'pending') {
            return redirect()->route('buyer.repairs.index')->with('error', 'You can only delete orders that are still pending.');
        }

        if ($repair->repair_image && Storage::disk('public')->exists($repair->repair_image)) {
            Storage::disk('public')->delete($repair->repair_image);
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
