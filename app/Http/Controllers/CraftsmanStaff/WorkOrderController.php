<?php

namespace App\Http\Controllers\CraftsmanStaff;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkOrderController extends Controller
{
    // Define the number of items per page
    private const PER_PAGE = 15;
    
    /**
     * Display a listing of work orders allocated to the craftsman.
     */
    public function index(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('wo_view') && !$staff->hasPermission('wo_accept') && !$staff->hasPermission('wo_reject')) {
                abort(403, 'Unauthorized action.');
            }
        }
        $craftsman = $this->currentCraftsman();
        
        $query = WorkOrder::with(['buyer', 'product.images', 'productCategory', 'subcategoryRelation'])
            ->where('allocated_craftsman_bp_code', $craftsman->craftman_code);

        // Apply Category Filter
        if ($request->filled('product_category_filter')) {
            $query->where('product_category_id', $request->product_category_filter);
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerm = '%' . $search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('work_order_number', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('product_name', 'like', $searchTerm)
                  ->orWhere('product_code', 'like', $searchTerm)
                  ->orWhere('bp_code', 'like', $searchTerm)
                  ->orWhere('product_category', 'like', $searchTerm)
                  ->orWhereHas('buyer', function($sq) use ($searchTerm) {
                      $sq->where('dear', 'like', $searchTerm);
                  });
            });
        }

        // Apply sorting
        $query->orderByRaw("CASE 
            WHEN priority = 'Urgent' THEN 1 
            WHEN priority = 'High' THEN 2 
            WHEN priority = 'Normal' THEN 3 
            ELSE 4 END");
            
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', self::PER_PAGE);

        // Get work orders with different statuses using cloned queries
        $allocatedOrders = (clone $query)->where('craftsman_status', 'allocated')
            ->paginate($perPage, ['*'], 'allocated_orders_page');
            
        $inProcessOrders = (clone $query)->where('craftsman_status', 'in_process')
            ->paginate($perPage, ['*'], 'in_process_orders_page');
            
        $completedOrders = (clone $query)->where('craftsman_status', 'completed')
            ->paginate($perPage, ['*'], 'completed_orders_page');
            
        $rejectedOrders = (clone $query)->where('craftsman_status', 'rejected')
            ->paginate($perPage, ['*'], 'rejected_orders_page');

        $overdueOrders = (clone $query)->where('status', '!=', 'completed')
            ->where('craftsman_status', '!=', 'rejected')
            ->where(function($q) {
                $q->whereDate('due_date', '<', now()->toDateString())
                  ->orWhere(function($sq) {
                      $sq->whereDate('due_date', now()->toDateString())
                         ->whereRaw('HOUR(NOW()) >= 12');
                  });
            })
            ->paginate($perPage, ['*'], 'overdue_orders_page');

        $productCategories = ProductCategory::orderBy('name')->get();

        return view('craftsman_staff.work-order.index', compact(
            'allocatedOrders', 
            'inProcessOrders', 
            'completedOrders', 
            'rejectedOrders',
            'overdueOrders',
            'productCategories'
        ));
    }

    /**
     * Export work orders to Excel.
     */
    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CraftsmanWorkOrderExport($request), 
            'work-orders-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Print selected work orders.
     */
    public function printSelected(Request $request)
    {
        $workOrderIds = $request->input('work_order_ids', []);
        
        if (empty($workOrderIds)) {
            return redirect()->back()->with('error', 'No work orders selected for printing.');
        }

        $craftsman = $this->currentCraftsman();
        $workOrders = WorkOrder::with(['buyer', 'product.images', 'productCategory', 'subcategoryRelation', 'craftsman'])
            ->whereIn('id', $workOrderIds)
            ->where('allocated_craftsman_bp_code', $craftsman->craftman_code)
            ->get();

        return view('craftsman_staff.work-order.print-selected', compact('workOrders'));
    }

    /**
     * Display the specified work order.
     */
    public function show(WorkOrder $workOrder)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('wo_view') && !$staff->hasPermission('wo_accept') && !$staff->hasPermission('wo_reject')) {
                abort(403, 'Unauthorized action.');
            }
        }
        $craftsman = $this->currentCraftsman();
        
        // Log for debugging
        Log::info('Craftsman trying to access work order', [
            'craftsman_id' => $craftsman->id,
            'craftsman_code' => $craftsman->craftman_code,
            'work_order_id' => $workOrder->id,
            'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code
        ]);
        
        // Ensure the work order is allocated to this craftsman
        if ($workOrder->allocated_craftsman_bp_code !== $craftsman->craftman_code) {
            Log::warning('Unauthorized access attempt', [
                'craftsman_code' => $craftsman->craftman_code,
                'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code
            ]);
            abort(403, 'Unauthorized access to this work order. Work order is allocated to craftsman code: ' . $workOrder->allocated_craftsman_bp_code . ', but you are logged in as: ' . $craftsman->craftman_code);
        }
        
        $workOrder->load(['images', 'productCategory', 'subcategoryRelation', 'buyer', 'craftsman']);
        return view('craftsman_staff.work-order.show', compact('workOrder'));
    }

    /**
     * Display the print view for the specified work order.
     */
    public function print(WorkOrder $workOrder)
    {
        $craftsman = $this->currentCraftsman();
        
        // Log for debugging
        Log::info('Craftsman trying to print work order', [
            'craftsman_id' => $craftsman->id,
            'craftsman_code' => $craftsman->craftman_code,
            'work_order_id' => $workOrder->id,
            'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code
        ]);
        
        // Ensure the work order is allocated to this craftsman
        if ($workOrder->allocated_craftsman_bp_code !== $craftsman->craftman_code) {
            Log::warning('Unauthorized print attempt', [
                'craftsman_code' => $craftsman->craftman_code,
                'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code
            ]);
            abort(403, 'Unauthorized access to this work order. Work order is allocated to craftsman code: ' . $workOrder->allocated_craftsman_bp_code . ', but you are logged in as: ' . $craftsman->craftman_code);
        }
        
        $workOrder->load(['images', 'product.images', 'productCategory', 'subcategoryRelation', 'buyer', 'craftsman']);
        return view('craftsman_staff.work-order.print', compact('workOrder'));
    }

    /**
     * Accept a work order and move it directly to in process.
     */
    public function accept(WorkOrder $workOrder)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('wo_accept')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        
        // Log for debugging
        Log::info('Craftsman trying to accept work order', [
            'craftsman_id' => $craftsman->id,
            'craftsman_code' => $craftsman->craftman_code,
            'work_order_id' => $workOrder->id,
            'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code,
            'work_order_status' => $workOrder->craftsman_status
        ]);
        
        // Ensure the work order is allocated to this craftsman
        if ($workOrder->allocated_craftsman_bp_code !== $craftsman->craftman_code) {
            Log::warning('Unauthorized accept attempt', [
                'craftsman_code' => $craftsman->craftman_code,
                'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code
            ]);
            abort(403, 'Unauthorized access to this work order. Work order is allocated to craftsman code: ' . $workOrder->allocated_craftsman_bp_code . ', but you are logged in as: ' . $craftsman->craftman_code);
        }
        
        // Check if the work order can be accepted
        if ($workOrder->craftsman_status !== 'allocated') {
            return redirect()->back()->with('error', 'Work order cannot be accepted at this time. Current status: ' . $workOrder->craftsman_status);
        }
        
        $updateData = [
            'craftsman_status' => 'in_process',
            'craftsman_accepted_at' => now(),
        ];
        if ($staff = $this->currentStaff()) {
            $updateData['accepted_by_staff_id'] = $staff->id;
            $updateData['craftsman_staff_id'] = $staff->id;
            $updateData['staff_accepted_at'] = now();
        }
        $workOrder->update($updateData);
        
        // Redirect to the work order index page with a parameter to show the in-process tab
        return redirect()->route('craftsman_staff.work-order.index', ['tab' => 'in-process'])
            ->with('success', 'Work Order accepted and moved to in process!');
    }

    /**
     * Bulk accept work orders.
     */
    public function bulkAccept(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('wo_accept')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        $workOrderIds = $request->input('work_order_ids', []);

        if (empty($workOrderIds)) {
            return redirect()->back()->with('error', 'No work orders selected for bulk acceptance.');
        }

        $count = 0;
        foreach ($workOrderIds as $id) {
            $workOrder = WorkOrder::find($id);

            // Security check: Ensure work order exists and belongs to this craftsman
            if ($workOrder && $workOrder->allocated_craftsman_bp_code === $craftsman->craftman_code && $workOrder->craftsman_status === 'allocated') {
                $updateData = [
                    'craftsman_status' => 'in_process',
                    'craftsman_accepted_at' => now(),
                ];
                if ($staff = $this->currentStaff()) {
                    $updateData['accepted_by_staff_id'] = $staff->id;
                    $updateData['craftsman_staff_id'] = $staff->id;
                    $updateData['staff_accepted_at'] = now();
                }
                $workOrder->update($updateData);
                $count++;
            }
        }

        return redirect()->route('craftsman_staff.work-order.index', ['tab' => 'in-process'])
            ->with('success', $count . ' work orders accepted and moved to in process!');
    }

    /**
     * Bulk reject work orders.
     */
    public function bulkReject(Request $request)
    {
        $craftsman = $this->currentCraftsman();
        $workOrderIds = $request->input('work_order_ids', []);

        if (empty($workOrderIds)) {
            return redirect()->back()->with('error', 'No work orders selected for bulk rejection.');
        }

        $count = 0;
        foreach ($workOrderIds as $id) {
            $workOrder = WorkOrder::find($id);

            // Security check: Ensure work order exists and belongs to this craftsman
            if ($workOrder && $workOrder->allocated_craftsman_bp_code === $craftsman->craftman_code && $workOrder->craftsman_status === 'allocated') {
                $workOrder->update([
                'craftsman_status' => 'rejected',
                'rejection_reason' => $request->input('rejection_reason'),
            ]);
                $count++;
            }
        }

        return redirect()->route('craftsman_staff.work-order.index', ['tab' => 'rejected'])
            ->with('success', $count . ' work orders rejected!');
    }

    /**
     * Bulk complete work orders.
     */
    public function bulkComplete(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            // Bulk complete requires wo_accept implicitly or wo_view? They accepted it, so maybe wo_accept.
            if (!$staff->hasPermission('wo_accept')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        $workOrderIds = $request->input('work_order_ids', []);

        if (empty($workOrderIds)) {
            return redirect()->back()->with('error', 'No work orders selected for bulk completion.');
        }

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/work-orders'), $imageName);
                $uploadedImages[] = 'images/work-orders/' . $imageName;
            }
        }

        $count = 0;
        foreach ($workOrderIds as $id) {
            $workOrder = WorkOrder::find($id);

            // Security check: Ensure work order exists and belongs to this craftsman
            if ($workOrder && $workOrder->allocated_craftsman_bp_code === $craftsman->craftman_code && $workOrder->craftsman_status === 'in_process') {
               $updateData = [
                    'craftsman_status' => 'completed',
                    'craftsman_completed_at' => now(),
                    'status' => 'for_approval',
                ];
                if ($staff = $this->currentStaff()) {
                    $updateData['craftsman_staff_id'] = $staff->id;
                    $updateData['staff_completed_at'] = now();
                }
                $workOrder->update($updateData);

                // Associate uploaded images
                foreach ($uploadedImages as $path) {
                    \App\Models\WorkOrderImage::create([
                        'work_order_id' => $workOrder->id,
                        'image_path' => $path,
                    ]);
                }
                $count++;
            }
        }

        return redirect()->route('craftsman_staff.work-order.index', ['tab' => 'completed'])
            ->with('success', $count . ' work orders marked as completed!');
    }

    /**
     * Reject a work order.
     */
    public function reject(WorkOrder $workOrder)
    {
        $craftsman = $this->currentCraftsman();
        
        // Log for debugging
        Log::info('Craftsman trying to reject work order', [
            'craftsman_id' => $craftsman->id,
            'craftsman_code' => $craftsman->craftman_code,
            'work_order_id' => $workOrder->id,
            'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code,
            'work_order_status' => $workOrder->craftsman_status
        ]);
        
        // Ensure the work order is allocated to this craftsman
        if ($workOrder->allocated_craftsman_bp_code !== $craftsman->craftman_code) {
            Log::warning('Unauthorized reject attempt', [
                'craftsman_code' => $craftsman->craftman_code,
                'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code
            ]);
            abort(403, 'Unauthorized access to this work order. Work order is allocated to craftsman code: ' . $workOrder->allocated_craftsman_bp_code . ', but you are logged in as: ' . $craftsman->craftman_code);
        }
        
        // Check if the work order can be rejected
        if ($workOrder->craftsman_status !== 'allocated') {
            return redirect()->back()->with('error', 'Work order cannot be rejected at this time. Current status: ' . $workOrder->craftsman_status);
        }
        
        $workOrder->update([
        'craftsman_status' => 'rejected',
        'rejection_reason' => request('rejection_reason'),
    ]);
        
        return redirect()->route('craftsman_staff.work-order.index')
            ->with('success', 'Work Order rejected successfully!');
    }

    /**
     * Mark a work order as completed.
     */
    public function complete(Request $request, WorkOrder $workOrder)
    {
        $craftsman = $this->currentCraftsman();
        
        // Log for debugging
        Log::info('Craftsman trying to complete work order', [
            'craftsman_id' => $craftsman->id,
            'craftsman_code' => $craftsman->craftman_code,
            'work_order_id' => $workOrder->id,
            'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code,
            'work_order_status' => $workOrder->craftsman_status
        ]);
        
        // Ensure the work order is allocated to this craftsman
        if ($workOrder->allocated_craftsman_bp_code !== $craftsman->craftman_code) {
            Log::warning('Unauthorized complete attempt', [
                'craftsman_code' => $craftsman->craft_code,
                'work_order_craftsman_code' => $workOrder->allocated_craftsman_bp_code
            ]);
            abort(403, 'Unauthorized access to this work order. Work order is allocated to craftsman code: ' . $workOrder->allocated_craftsman_bp_code . ', but you are logged in as: ' . $craftsman->craftman_code);
        }
        
        // Check if the work order can be marked as completed
        if ($workOrder->craftsman_status !== 'in_process') {
            return redirect()->back()->with('error', 'Work order cannot be marked as completed at this time. Current status: ' . $workOrder->craftsman_status);
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/work-orders'), $imageName);
                $imagePath = 'images/work-orders/' . $imageName;

                \App\Models\WorkOrderImage::create([
                    'work_order_id' => $workOrder->id,
                    'image_path' => $imagePath,
                ]);
            }
        }
        
        $updateData = [
            'craftsman_status' => 'completed',
            'craftsman_completed_at' => now(),
            'status' => 'for_approval',
        ];
        if ($staff = $this->currentStaff()) {
            $updateData['craftsman_staff_id'] = $staff->id;
            $updateData['staff_completed_at'] = now();
        }
        $workOrder->update($updateData);
        
        // Redirect to the work order index page with a parameter to show the completed tab
        return redirect()->route('craftsman_staff.work-order.index', ['tab' => 'completed'])
            ->with('success', 'Work Order marked as completed and sent for approval!');
    }
}
