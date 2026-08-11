<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Repair;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RepairController extends Controller
{
    public function index()
    {
        $buyerId = Auth::guard('buyer')->id();
        $repairs = Repair::where('buyer_id', $buyerId)->latest()->paginate(10);
        return view('buyer.repairs.index', compact('repairs'));
    }

    public function show($id)
    {
        $repair = Repair::with('craftsman')->where('buyer_id', Auth::guard('buyer')->id())->findOrFail($id);
        return view('buyer.repairs.show', compact('repair'));
    }

    public function create()
    {
        return view('buyer.repairs.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'repair_details' => 'nullable|string',
            'sample_details' => 'nullable|string',
            'item_given_to' => 'nullable|string|max:255',
            'image_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'order_no' => 'nullable|string',
            'repair' => 'nullable|string',
            'ref' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image_proof')) {
            $image = $request->file('image_proof');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/repairs'), $imageName);
            $imagePath = 'images/repairs/' . $imageName;
        }

        Repair::create([
            'buyer_id' => Auth::guard('buyer')->id(),
            'repair_date' => now()->toDateString(),
            'product_name' => $request->product_name,
            'weight' => $request->weight,
            'repair_details' => $request->repair_details,
            'sample_details' => $request->sample_details,
            'item_given_to' => $request->item_given_to,
            'image_proof' => $imagePath,
            'order_no' => $request->order_no,
            'repair' => $request->repair,
            'status' => 'Pending',
            'created_by' => Auth::guard('buyer')->id(),
            'creator_type' => 'buyer',
        ]);

        return redirect()->route('buyer.repairs.index')->with('success', 'Repair order created successfully.');
    }

    public function acceptCompleted($id)
    {
        $repair = Repair::where('buyer_id', Auth::guard('buyer')->id())->findOrFail($id);
        $repair->update([
            'status' => 'Buyer_Accepted',
            'buyer_accepted_at' => now(),
        ]);
        return redirect()->route('buyer.repairs.index')->with('success', 'Repair accepted successfully.');
    }

    public function rejectCompleted(Request $request, $id)
    {
        $repair = Repair::where('buyer_id', Auth::guard('buyer')->id())->findOrFail($id);
        $repair->update([
            'status' => 'Buyer_Rejected',
            'reject_reason' => $request->reject_reason,
        ]);
        return redirect()->route('buyer.repairs.index')->with('success', 'Repair rejected.');
    }
}
