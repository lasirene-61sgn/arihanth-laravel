<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Repair;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Support\Facades\Validator;
use App\Notifications\RepairAllocated;
use App\Notifications\RepairCompleted;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $query = Repair::with('buyer', 'craftsman');

        // ── Search (ID, Product Name, BP Code/Name, or Craftsman Code/Name) ──
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('allocated_craftsman_code', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function($bq) use ($search) {
                      $bq->where('bp_code', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('craftsman', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('craftman_code', 'like', "%{$search}%");
                  });
            });
        }

        // ── Filter by Status ──
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ── Filter by Buyer (BP Code) ──
        if ($request->filled('bp_code')) {
            $query->whereHas('buyer', function($q) use ($request) {
                $q->where('bp_code', $request->bp_code);
            });
        }

        // ── Filter by Craftsman ──
        if ($request->filled('craftsman_code')) {
            $query->where('allocated_craftsman_code', $request->craftsman_code);
        }

        // ── Filter by Date Range ──
        if ($request->filled('date_from')) {
            $query->whereDate('repair_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('repair_date', '<=', $request->date_to);
        }

        // ── Tab Groups Logic ──
        $activeTab = $request->get('tab', 'new');
        $statusMap = [
            'new'        => ['Pending', 'Accepted'],
            'allocated'  => ['Allocated'],
            'in_process' => ['In_Process'],
            'completed'  => ['Completed', 'Craftsman_Completed', 'Buyer_Accepted'],
            'rejected'   => ['Rejected_by_Admin', 'Craftsman_Rejected', 'Buyer_Rejected'],
        ];

        if ($activeTab !== 'all' && isset($statusMap[$activeTab])) {
            $query->whereIn('status', $statusMap[$activeTab]);
        }

        // Clone query for counts BEFORE pagination
        $countQuery = clone $query;
        // However, counts should ideally show totals for the whole set regardless of current tab, 
        // but respecting search/other filters.
        
        $baseCountQuery = Repair::query();
        // Re-apply search/date/buyer/craftsman filters to baseCountQuery for accurate tab counts
        if ($request->filled('search')) {
            $search = $request->search;
            $baseCountQuery->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('allocated_craftsman_code', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function($bq) use ($search) {
                      $bq->where('bp_code', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('craftsman', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('craftman_code', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('bp_code')) {
            $baseCountQuery->whereHas('buyer', function($q) use ($request) {
                $q->where('bp_code', $request->bp_code);
            });
        }
        if ($request->filled('craftsman_code')) {
            $baseCountQuery->where('allocated_craftsman_code', $request->craftsman_code);
        }
        if ($request->filled('date_from')) {
            $baseCountQuery->whereDate('repair_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $baseCountQuery->whereDate('repair_date', '<=', $request->date_to);
        }

        $counts = [
            'all'        => (clone $baseCountQuery)->count(),
            'new'        => (clone $baseCountQuery)->whereIn('status', $statusMap['new'])->count(),
            'allocated'  => (clone $baseCountQuery)->whereIn('status', $statusMap['allocated'])->count(),
            'in_process' => (clone $baseCountQuery)->whereIn('status', $statusMap['in_process'])->count(),
            'completed'  => (clone $baseCountQuery)->whereIn('status', $statusMap['completed'])->count(),
            'rejected'   => (clone $baseCountQuery)->whereIn('status', $statusMap['rejected'])->count(),
        ];

        $repairs = $query->latest()->paginate(10)->withQueryString();
        
        $buyers = Buyer::all();
        $craftsmen = Craftman::all();
        $statuses = [
            'Pending', 'Accepted', 'Allocated', 'Craftsman_Completed', 
            'Craftsman_Rejected', 'Completed', 'Rejected_by_Admin', 
            'Buyer_Accepted', 'Buyer_Rejected'
        ];

        return view('super-admin.repairs.index', compact('repairs', 'buyers', 'craftsmen', 'statuses', 'counts', 'activeTab'));
    }

    public function create()
    {
        $buyers = Buyer::all();
        $receivedByOptions = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');
        $receivedThroughOptions = Repair::whereNotNull('item_received_through')->distinct()->pluck('item_received_through');
        $deliveredByOptions = Repair::where('item_delivered_by_type', 'AJPL')->whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        return view('super-admin.repairs.create', compact('buyers', 'receivedByOptions', 'receivedThroughOptions', 'deliveredByOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'buyer_id' => 'required|exists:buyers,id',
            'product_name' => 'required|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'repair_details' => 'nullable|string',
            'sample_details' => 'nullable|string',
            'item_given_to' => 'nullable|string|max:255',
            'image_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'order_no' => 'nullable|string',
            'repair' => 'nullable|string',
            'ref' => 'nullable|string',
            'notes' => 'nullable',
            'item_received_by' => 'nullable|string|max:255',
            'item_received_through' => 'nullable|string|max:255',
            'item_delivered_by_type' => 'nullable|in:Self,AJPL',
            'item_delivered_by' => 'nullable|string|max:255',
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
            'buyer_id' => $request->buyer_id,
            'repair_date' => now()->toDateString(),
            'product_name' => $request->product_name,
            'weight' => $request->weight,
            'repair_details' => $request->repair_details,
            'sample_details' => $request->sample_details,
            'item_given_to' => $request->item_given_to,
            'image_proof' => $imagePath,
            'order_no' => $request->order_no,
            'repair' => $request->repair,
            'ref' => $request->ref,
            'notes' => $request->notes,
            'item_received_by' => $request->item_received_by,
            'item_received_through' => $request->item_received_through,
            'item_delivered_by_type' => $request->item_delivered_by_type,
            'item_delivered_by' => $request->item_delivered_by,
            'status' => 'Pending',
            'created_by' => auth()->id(),
            'creator_type' => 'super_admin',
            'creator_user_code' => auth()->user()->user_code ?? null,
        ]);

        return redirect()->route('super-admin.repairs.index')->with('success', 'Repair order created successfully.');
    }

    public function edit($id)
    {
        $repair = Repair::findOrFail($id);
        $buyers = Buyer::all();
        $craftsmen = Craftman::all();
        $receivedByOptions = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');
        $receivedThroughOptions = Repair::whereNotNull('item_received_through')->distinct()->pluck('item_received_through');
        $deliveredByOptions = Repair::where('item_delivered_by_type', 'AJPL')->whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        return view('super-admin.repairs.edit', compact('repair', 'buyers', 'craftsmen', 'receivedByOptions', 'receivedThroughOptions', 'deliveredByOptions'));
    }

    public function update(Request $request, $id)
    {
        $repair = Repair::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'buyer_id' => 'required|exists:buyers,id',
            'product_name' => 'required|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'repair_details' => 'nullable|string',
            'sample_details' => 'nullable|string',
            'item_given_to' => 'nullable|string|max:255',
            'image_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'order_no' => 'nullable|string',
            'repair' => 'nullable|string',
            'ref' => 'nullable|string',
            'notes' => 'nullable',
            'item_received_by' => 'nullable|string|max:255',
            'item_received_through' => 'nullable|string|max:255',
            'item_delivered_by_type' => 'nullable|in:Self,AJPL',
            'item_delivered_by' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['buyer_id', 'product_name', 'weight', 'repair_details', 'sample_details', 'item_given_to', 'order_no', 'repair', 'ref', 'notes', 'item_received_by', 'item_received_through', 'item_delivered_by_type', 'item_delivered_by']);

        if ($request->hasFile('image_proof')) {
            $image = $request->file('image_proof');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/repairs'), $imageName);
            $data['image_proof'] = 'images/repairs/' . $imageName;
        }

        $repair->update($data);

        return redirect()->route('super-admin.repairs.index')->with('success', 'Repair updated successfully.');
    }

    public function accept($id)
    {
        $repair = Repair::findOrFail($id);
        $repair->update(['status' => 'Accepted']);
        return redirect()->route('super-admin.repairs.index')->with('success', 'Repair accepted successfully.');
    }

    public function reject(Request $request, $id)
    {
        $repair = Repair::findOrFail($id);
        $repair->update([
            'status' => 'Rejected_by_Admin',
            'reject_reason' => $request->reject_reason,
        ]);
        return redirect()->route('super-admin.repairs.index')->with('success', 'Repair rejected.');
    }

    public function allocate(Request $request, $id)
    {
        $request->validate([
            'craftsman_code' => 'required|string',
            'allocation_notes' => 'nullable|string',
        ]);

        $repair = Repair::findOrFail($id);
        $repair->update([
            'status' => 'Allocated',
            'allocated_craftsman_code' => $request->craftsman_code,
            'craftsman_status' => 'Pending',
            'allocation_notes' => $request->allocation_notes,
            'allocated_by' => auth()->id(),
            'allocated_at' => now(),
        ]);

        // Notify Craftsman
        $craftsman = Craftman::where('craftman_code', $request->craftsman_code)->first();
        if ($craftsman && method_exists($craftsman, 'notify')) {
            $craftsman->notify(new RepairAllocated($repair));
        }

        return redirect()->route('super-admin.repairs.index')->with('success', 'Repair allocated to craftsman successfully.');
    }

    public function complete($id)
    {
        $repair = Repair::findOrFail($id);
        $repair->update([
            'status' => 'Buyer_Accepted',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'buyer_accepted_at' => now(),
        ]);
        
        // Notify Buyer
        if ($repair->buyer && method_exists($repair->buyer, 'notify')) {
            $repair->buyer->notify(new RepairCompleted($repair));
        }

        return redirect()->route('super-admin.repairs.index')->with('success', 'Repair marked as completed.');
    }

    public function bulkComplete(Request $request)
    {
        $repairIds = $request->input('repair_ids', []);
        if (empty($repairIds)) {
            return redirect()->back()->with('error', 'No repair orders selected.');
        }

        $repairs = Repair::whereIn('id', $repairIds)
            ->whereIn('status', ['Pending', 'Accepted', 'In_Process', 'Craftsman_Completed'])
            ->get();
            
        foreach ($repairs as $repair) {
            $repair->update([
                'status' => 'Buyer_Accepted',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'buyer_accepted_at' => now(),
            ]);
            // Notify Buyer
            if ($repair->buyer && method_exists($repair->buyer, 'notify')) {
                $repair->buyer->notify(new RepairCompleted($repair));
            }
        }

        return redirect()->back()->with('success', count($repairs) . ' repair orders marked as completed.');
    }

    public function show($id)
    {
        $repair = Repair::with('buyer', 'craftsman')->findOrFail($id);
        return view('super-admin.repairs.show', compact('repair'));
    }

    public function destroy($id)
    {
        $repair = Repair::findOrFail($id);
        
        // Delete image if exists
        if ($repair->image_proof && file_exists(public_path($repair->image_proof))) {
            unlink(public_path($repair->image_proof));
        }

        $repair->delete();

        return redirect()->route('super-admin.repairs.index')->with('success', 'Repair order deleted successfully.');
    }

    public function getCraftsmen()
    {
        $craftsmen = Craftman::all();
        return response()->json($craftsmen);
    }
}
