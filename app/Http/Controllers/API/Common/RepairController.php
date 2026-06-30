<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Repair;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProcessOwner;
use App\Services\ImageWatermarkService;
use App\Notifications\RepairAllocated;
use App\Notifications\RepairCompleted;
use Dompdf\Dompdf;
use Dompdf\Options;

class RepairController extends Controller
{
    // =========================================================================
    // HELPERS — role detection
    // =========================================================================

    private function isAdmin($user): bool
    {
        return in_array($user->role ?? '', ['super_admin', 'admin']);
    }

    private function isCraftsman($user): bool
    {
        return ($user->role ?? '') === 'craftsman' || $user instanceof \App\Models\Craftman;
    }

    private function isBuyerSide($user): bool
    {
        return $user instanceof \App\Models\Buyer
            || $user instanceof \App\Models\KeyUser
            || $user instanceof \App\Models\User
            || ($user->role ?? '') === 'buyer';
    }

    /**
     * Resolve buyer_id for buyer-side users
     */
    private function getBuyerId($user)
    {
        if ($user instanceof \App\Models\Buyer) {
            return $user->id;
        }
        // KeyUser and User belong to a Buyer via bp_code
        if (isset($user->bp_code)) {
            $buyer = Buyer::where('bp_code', $user->bp_code)->first();
            return $buyer ? $buyer->id : null;
        }
        return null;
    }

    // =========================================================================
    // INDEX — with tabs + counts (Full logic)
    // =========================================================================

    public function index(Request $request)
    {
        $user  = $request->user();
        $admin = $this->isAdmin($user);

        // ── Scope helper: applies buyer/craftman filter ──
        $scopeFilter = function ($query) use ($user, $admin) {
            if ($admin) return $query;
            if ($this->isCraftsman($user)) {
                return $query->where('allocated_craftsman_code', $user->craftman_code);
            }
            if ($this->isBuyerSide($user)) {
                $buyerId = $this->getBuyerId($user);
                return $query->where('buyer_id', $buyerId);
            }
            return $query->whereRaw('1=0'); // Default deny
        };


        // ── Base query ──
        $perPage   = $request->get('per_page', 10);
        $search    = $request->get('search');
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (!in_array($sortBy, ['id', 'repair_date', 'product_name', 'weight', 'status', 'created_at'])) {
            $sortBy = 'created_at';
        }

        $query = Repair::with(['buyer', 'craftsman']);
        $scopeFilter($query);


        // ── Search ──
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%$search%")
                    ->orWhere('repair_details', 'LIKE', "%$search%")
                    ->orWhere('item_given_to', 'LIKE', "%$search%")
                    ->orWhere('order_no', 'LIKE', "%$search%")
                    ->orWhere('ref', 'LIKE', "%$search%")
                    ->orWhere('allocated_craftsman_code', 'LIKE', "%$search%")
                    ->orWhereHas('buyer', function ($bq) use ($search) {
                        $bq->where('name', 'LIKE', "%$search%")
                           ->orWhere('business_name', 'LIKE', "%$search%")
                           ->orWhere('bp_code', 'LIKE', "%$search%");
                    });
            });
        }

        // ── Global Filter Alias (matching PO controller logic) ──
        if ($request->filled('filter')) {
            $f = $request->filter;
            $query->where(function ($q) use ($f) {
                $q->where('allocated_craftsman_code', $f)
                    ->orWhere('product_name', 'LIKE', "%$f%")
                    ->orWhere('order_no', 'LIKE', "%$f%")
                    ->orWhere('ref', 'LIKE', "%$f%")
                    ->orWhereHas('buyer', function ($bq) use ($f) {
                        $bq->where('bp_code', $f)
                           ->orWhere('name', 'LIKE', "%$f%");
                    });
            });
        }

        // ── Additional Filters ──
        if ($request->filled('date_from')) {
            $query->whereDate('repair_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('repair_date', '<=', $request->date_to);
        }
        if ($request->filled('product_name')) {
            $query->where('product_name', 'LIKE', "%{$request->product_name}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('weight')) {
            $query->where('weight', $request->weight);
        }
        if ($request->filled('bp_code')) {
            $buyer = \App\Models\Buyer::where('bp_code', $request->bp_code)->first();
            if ($buyer) {
                $query->where('buyer_id', $buyer->id);
            } else {
                $query->where('id', 0); // No results if code invalid
            }
        }
        if ($admin && $request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }
        if ($admin && $request->filled('craftsman_code')) {
            $query->where('allocated_craftsman_code', $request->craftsman_code);
        }

        // ── Selection by IDs ──
        if ($request->filled('repair_ids')) {
            $ids = $request->repair_ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        $query->orderBy($sortBy, $sortOrder);

        // ── Print (Full data JSON) ──
        if ($request->has('print')) {
            $repairs = $query->get();
            // Resolve full image URLs
            $repairs->transform(function ($repair) {
                if ($repair->image_proof) {
                    $repair->image_proof_url = asset($repair->image_proof);
                }
                return $repair;
            });
            return response()->json([
                'success' => true,
                'data'    => $repairs
            ]);
        }

        // ── Paginated Response ──
        $repairs = $query->paginate($perPage);
        $repairs->getCollection()->transform(function ($repair) {
            if ($repair->image_proof) {
                $repair->image_proof_url = asset($repair->image_proof);
            }
            return $repair;
        });

        return response()->json([
            'success' => true,
            'data'    => $repairs
        ]);
    }

    public function show(Request $request, $id)
    {
        $user   = $request->user();
        $admin  = $this->isAdmin($user);
        $repair = Repair::with(['buyer', 'craftsman'])->find($id);

        if (!$repair) {
            return response()->json(['success' => false, 'message' => 'Repair not found'], 404);
        }

        // Role-based check
        if (!$admin) {
            if ($this->isCraftsman($user) && $repair->allocated_craftsman_code !== $user->craftman_code) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            if ($this->isBuyerSide($user) && $repair->buyer_id !== $this->getBuyerId($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        if ($repair->image_proof) {
            $repair->image_proof_url = asset($repair->image_proof);
        }

        return response()->json(['success' => true, 'data' => $repair]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'buyer_id'       => 'nullable|exists:buyers,id',
            'bp_code'        => 'nullable|exists:buyers,bp_code',
            'product_name'   => 'required|string|max:255',
            'weight'         => 'nullable|numeric|min:0',
            'repair_details' => 'nullable|string',
            'sample_details' => 'nullable|string',
            'item_given_to'  => 'nullable|string|max:255',
            'image_proof'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'order_no' => 'nullable|string',
            'repair' => 'nullable|string',
            'ref' => 'nullable|string',
            'notes' => 'nullable',
            'repair_date'    => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image_proof')) {
            $image     = $request->file('image_proof');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/repairs'), $imageName);
            $imagePath = 'images/repairs/' . $imageName;

            // Optional: Watermark
            try {
                $watermarkService = new ImageWatermarkService();
                $watermarkService->addWatermark($imagePath, true);
            } catch (\Exception $e) {
                Log::error('Failed to add watermark to repair image: ' . $e->getMessage());
            }
        }

        $buyerId = $request->buyer_id;

        // If bp_code is provided, resolve buyer_id from it
        if (!$buyerId && $request->filled('bp_code')) {
            $buyer = Buyer::where('bp_code', $request->bp_code)->first();
            $buyerId = $buyer ? $buyer->id : null;
        }

        if (!$buyerId && $this->isBuyerSide($user)) {
            $buyerId = $this->getBuyerId($user);
        }

        $repair = Repair::create([
            'buyer_id'       => $buyerId,
            'repair_date'    => $request->repair_date ?? now()->toDateString(),
            'product_name'   => $request->product_name,
            'weight'         => $request->weight,
            'repair_details' => $request->repair_details,
            'sample_details' => $request->sample_details,
            'item_given_to'  => $request->item_given_to,
            'image_proof'    => $imagePath,
            'order_no' =>$request->order_no,
            'repair' => $request->repair,
            'ref' => $request->ref,
            'notes' => $request->notes,
            'status'         => 'Pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Repair created successfully', 'data' => $repair], 201);
    }

    public function update(Request $request, $id)
    {
        $user   = $request->user();
        $admin  = $this->isAdmin($user);
        $repair = Repair::find($id);

        if (!$repair) {
            return response()->json(['success' => false, 'message' => 'Repair not found'], 404);
        }

        // Only Admin or the Buyer who created it can update
        if (!$admin && (!$this->isBuyerSide($user) || $repair->buyer_id !== $this->getBuyerId($user))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'buyer_id'       => 'nullable|exists:buyers,id',
            'bp_code'        => 'nullable|exists:buyers,bp_code',
            'product_name'   => 'required|string|max:255',
            'weight'         => 'nullable|numeric|min:0',
            'repair_details' => 'nullable|string',
            'sample_details' => 'nullable|string',
            'item_given_to'  => 'nullable|string|max:255',
            'image_proof'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'order_no' => 'nullable|string',
            'repair' => 'nullable|string',
            'ref' => 'nullable|string',
            'notes' => 'nullable',
            'repair_date'    => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['product_name', 'weight', 'repair_details', 'sample_details', 'item_given_to', 'repair_date', 'order_no', 'repair', 'ref', 'notes']);

        $buyerId = $request->buyer_id;
        if (!$buyerId && $request->filled('bp_code')) {
            $buyer = Buyer::where('bp_code', $request->bp_code)->first();
            $buyerId = $buyer ? $buyer->id : null;
        }

        if ($buyerId) {
            $data['buyer_id'] = $buyerId;
        }

        if ($request->hasFile('image_proof')) {
            // Delete old image if exists
            if ($repair->image_proof && file_exists(public_path($repair->image_proof))) {
                @unlink(public_path($repair->image_proof));
            }

            $image     = $request->file('image_proof');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/repairs'), $imageName);
            $data['image_proof'] = 'images/repairs/' . $imageName;

            // Watermark
            try {
                $watermarkService = new ImageWatermarkService();
                $watermarkService->addWatermark($data['image_proof'], true);
            } catch (\Exception $e) {
                Log::error('Failed to add watermark to updated repair image: ' . $e->getMessage());
            }
        }

        $repair->update($data);

        return response()->json(['success' => true, 'message' => 'Repair updated successfully', 'data' => $repair]);
    }

    public function destroy(Request $request, $id)
    {
        $user   = $request->user();
        $admin  = $this->isAdmin($user);
        $repair = Repair::find($id);

        if (!$repair) {
            return response()->json(['success' => false, 'message' => 'Repair not found'], 404);
        }

        if (!$admin && (!$this->isBuyerSide($user) || $repair->buyer_id !== $this->getBuyerId($user))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($repair->image_proof && file_exists(public_path($repair->image_proof))) {
            @unlink(public_path($repair->image_proof));
        }

        $repair->delete();

        return response()->json(['success' => true, 'message' => 'Repair deleted successfully']);
    }

    // =========================================================================
    // ACTIONS
    // =========================================================================

    public function accept($id)
    {
        $repair = Repair::findOrFail($id);
        $repair->update(['status' => 'Accepted']);
        return response()->json(['success' => true, 'message' => 'Repair accepted']);
    }

    public function reject(Request $request, $id)
    {
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);
        $repair = Repair::findOrFail($id);

        // If Admin is rejecting a Craftsman's completion
        if ($isAdmin && $repair->status === 'Craftsman_Completed') {
            $repair->update([
                'status'           => 'Allocated', // Send back to allocated
                'craftsman_status' => 'Pending',   // Reset craftsman status
                'reject_reason'    => $request->reject_reason,
            ]);
            return response()->json(['success' => true, 'message' => 'Repair completion rejected. Sent back to craftsman.']);
        }

        $repair->update([
            'status'        => 'Rejected_by_Admin',
            'reject_reason' => $request->reject_reason,
        ]);
        return response()->json(['success' => true, 'message' => 'Repair rejected']);
    }

    public function allocate(Request $request, $id)
    {
        $request->validate([
            'craftsman_code'   => 'required|exists:craftmen,craftman_code',
            'allocation_notes' => 'nullable|string',
        ]);

        $repair = Repair::findOrFail($id);
        $repair->update([
            'status'                   => 'Allocated',
            'allocated_craftsman_code' => $request->craftsman_code,
            'craftsman_status'         => 'Pending',
            'allocation_notes'         => $request->allocation_notes,
        ]);

        // Notify Craftsman
        $craftsman = Craftman::where('craftman_code', $request->craftsman_code)->first();
        if ($craftsman && method_exists($craftsman, 'notify')) {
            $craftsman->notify(new RepairAllocated($repair));
        }

        return response()->json(['success' => true, 'message' => 'Repair allocated']);
    }

    public function complete(Request $request, $id)
    {
        $user = $request->user();
        $repair = Repair::findOrFail($id);

        // If Craftsman marks as done
        if ($this->isCraftsman($user)) {
            $repair->update([
                'status'           => 'Craftsman_Completed',
                'craftsman_status' => 'Completed'
            ]);
            return response()->json(['success' => true, 'message' => 'Repair marked as completed by craftsman. Waiting for admin approval.']);
        }

        // If Admin marks as fully done
        if ($this->isAdmin($user)) {
            $repair->update(['status' => 'Completed']);
            
            // Notify Buyer
            if ($repair->buyer && method_exists($repair->buyer, 'notify')) {
                $repair->buyer->notify(new RepairCompleted($repair));
            }
            
            return response()->json(['success' => true, 'message' => 'Repair fully completed by admin.']);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    public function buyerAccept(Request $request, $id)
    {
        $user = $request->user();
        $repair = Repair::findOrFail($id);

        if (!$this->isBuyerSide($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $repair->update(['status' => 'Buyer_Accepted']);
        return response()->json(['success' => true, 'message' => 'Repair accepted by buyer']);
    }

    public function buyerReject(Request $request, $id)
    {
        $user = $request->user();
        $repair = Repair::findOrFail($id);

        if (!$this->isBuyerSide($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $repair->update([
            'status' => 'Buyer_Rejected',
            'reject_reason' => $request->reject_reason
        ]);
        return response()->json(['success' => true, 'message' => 'Repair rejected by buyer']);
    }

    /**
     * Generate PDF for repairs
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        $admin = $this->isAdmin($user);

        $query = Repair::with(['buyer', 'craftsman']);

        // ── Apply same scope filter ──
        if (!$admin) {
            if ($this->isCraftsman($user)) {
                $query->where('allocated_craftsman_code', $user->craftman_code);
            } elseif ($this->isBuyerSide($user)) {
                $buyerId = $this->getBuyerId($user);
                $query->where('buyer_id', $buyerId);
            } else {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
        }

        // ── Filter by IDs ──
        if ($request->filled('repair_ids')) {
            $ids = is_string($request->repair_ids) ? explode(',', $request->repair_ids) : $request->repair_ids;
            $query->whereIn('id', $ids);
        } elseif ($request->filled('ids')) {
            $ids = is_string($request->ids) ? explode(',', $request->ids) : $request->ids;
            $query->whereIn('id', $ids);
        }

        // ── Additional Filters (mirror index) ──
        if ($request->filled('date_from')) {
            $query->whereDate('repair_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('repair_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($admin && $request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }
        if ($admin && $request->filled('craftsman_code')) {
            $query->where('allocated_craftsman_code', $request->craftsman_code);
        }

        $repairs = $query->latest()->get();

        if ($repairs->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No repairs found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.repairs.generate-pdf', compact('repairs'))->render());
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = count($repairs) === 1
                ? "Repair_Report_" . $repairs->first()->id . ".pdf"
                : "Repairs_Report_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Repair PDF Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF'], 500);
        }
    }
}
