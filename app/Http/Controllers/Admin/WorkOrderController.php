<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\WorkOrderImage;
use App\Services\ImageWatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\ProcessOwner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Imports\WorkOrderImport;
use App\Imports\OrderListImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Exports\AdminWorkOrderExport;

class WorkOrderController extends Controller
{
    // Define the default number of items per page
    private const DEFAULT_PER_PAGE = 10;

    // Define available page size options
    private const PAGE_SIZE_OPTIONS = [25, 50, 75, 100, 150, 200];

    /**
     * Display a listing of work orders.
     */
    public function index(Request $request)
    {
        // Handle search and filtering
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $bpCodeFilter = $request->get('bp_code_filter');
        $categoryFilter = $request->get('category_filter');
        $subcategoryFilter = $request->get('subcategory_filter');
        $craftsmanFilter = $request->get('craftsman_filter');
        $designCodeFilter = $request->get('design_code_filter');
        $productCodeFilter = $request->get('product_code_filter');
        $returnFilter = $request->get('return_filter');

        // Validate sort parameters
        $allowedSortColumns = ['id', 'work_order_number', 'customer_name', 'product_name', 'quantity', 'due_date', 'status', 'bp_code', 'product_category', 'reference_no', 'type', 'size', 'length', 'weight_from', 'weight_to', 'hallmark', 'rodium', 'hook', 'stone', 'enamel', 'craftsman_due_date', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        if ($request->ajax() && $request->has('tab')) {
            return $this->loadWorkOrdersAjax($request);
        }

        // Export functionality
        if ($request->has('export')) {
            return $this->exportWorkOrders($request);
        }

        $perPage = $request->get('per_page', self::DEFAULT_PER_PAGE);
        if (!in_array($perPage, self::PAGE_SIZE_OPTIONS)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        // Apply search, sort and pagination for each tab
        // Eager load product images, buyer, and craftsman
        $newOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'new');
        $allocatedOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'allocated')
            ->where('craftsman_status', '!=', 'in_process');
        $forApprovalOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'for_approval');
        $completedOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'completed');
        $inProcessOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('craftsman_status', 'in_process');
        $rejectedOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('craftsman_status', 'rejected');
        $allOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman']);

        // Query for Overdue Orders: active orders (not completed/rejected) that are past due
        $overdueOrdersQuery = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])
            ->where('status', '!=', 'completed')
            ->where('craftsman_status', '!=', 'rejected')
            ->where(function ($q) {
                $q->whereDate('craftsman_due_date', '<', now()->toDateString())
                    ->orWhere(function ($sq) {
                        $sq->whereDate('craftsman_due_date', now()->toDateString())
                            ->whereRaw('HOUR(NOW()) >= 12');
                    });
            });

        // Completed filter
        $completedFilter = $request->get('completed_filter');
        if ($completedFilter) {
            $now = now();
            if ($completedFilter === 'day') {
                $completedOrdersQuery->whereDate('updated_at', $now->toDateString());
            } elseif ($completedFilter === 'week') {
                $completedOrdersQuery->whereBetween('updated_at', [$now->startOfWeek(), $now->endOfWeek()]);
            } elseif ($completedFilter === 'month') {
                $completedOrdersQuery->whereMonth('updated_at', $now->month)
                                     ->whereYear('updated_at', $now->year);
            }
        }

        // Apply filters to all queries
        $queries = [
            $newOrdersQuery,
            $allocatedOrdersQuery,
            $forApprovalOrdersQuery,
            $completedOrdersQuery,
            $inProcessOrdersQuery,
            $rejectedOrdersQuery,
            $overdueOrdersQuery,
            $allOrdersQuery
        ];

        foreach ($queries as $query) {
            // Apply search if present
            if ($search) {
                $searchTerm = '%' . $search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('work_order_number', 'LIKE', $searchTerm)
                        ->orWhere('customer_name', 'LIKE', $searchTerm)
                        ->orWhere('product_name', 'LIKE', $searchTerm)
                        ->orWhere('product_code', 'LIKE', $searchTerm)
                        ->orWhere('bp_code', 'LIKE', $searchTerm)
                        ->orWhere('reference_no', 'LIKE', $searchTerm)
                        ->orWhere('type', 'LIKE', $searchTerm)
                        ->orWhere('size', 'LIKE', $searchTerm)
                        ->orWhere('length', 'LIKE', $searchTerm)
                        ->orWhere('hallmark', 'LIKE', $searchTerm)
                        ->orWhere('rodium', 'LIKE', $searchTerm)
                        ->orWhere('hook', 'LIKE', $searchTerm)
                        ->orWhere('stone', 'LIKE', $searchTerm)
                        ->orWhere('enamel', 'LIKE', $searchTerm)
                        ->orWhere('narration_craftsman', 'LIKE', $searchTerm)
                        ->orWhere('narration_admin', 'LIKE', $searchTerm)
                        ->orWhereHas('craftsman', function ($sq) use ($searchTerm) {
                            $sq->where('craftman_code', 'LIKE', $searchTerm)
                                ->orWhere('dear', 'LIKE', $searchTerm);
                        })
                        ->orWhereHas('buyer', function ($sq) use ($searchTerm) {
                            $sq->where('dear', 'LIKE', $searchTerm);
                        });
                });
            }

            // Apply BP Code filter
            if ($bpCodeFilter) {
                $query->where('bp_code', $bpCodeFilter);
            }

            // Apply Category filter
            if ($categoryFilter) {
                $query->where('product_category_id', $categoryFilter);
            }

            // Apply Subcategory filter
            if ($subcategoryFilter) {
                $query->where('subcategory_id', $subcategoryFilter);
            }

            // Apply Craftsman filter
            if ($craftsmanFilter) {
                $query->where('allocated_craftsman_bp_code', $craftsmanFilter);
            }

            if ($designCodeFilter) {
                $query->where(function($q) use ($designCodeFilter) {
                    $q->where('design_code', $designCodeFilter)
                      ->orWhere('product_code', $designCodeFilter);
                });
            }

            if ($productCodeFilter) {
                $query->where(function($q) use ($productCodeFilter) {
                    $q->where('product_code', $productCodeFilter)
                      ->orWhere('design_code', $productCodeFilter);
                });
            }

            if ($returnFilter === 'returned') {
                $query->where(function($q) {
                    $q->where('admin_return_count', '>', 0)
                      ->orWhere('superadmin_return_count', '>', 0)
                      ->orWhereNotNull('return_note');
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);
        }

        $newOrders = $newOrdersQuery->paginate($perPage, ['*'], 'new_orders_page');
        $allocatedOrders = $allocatedOrdersQuery->paginate($perPage, ['*'], 'allocated_orders_page');
        $forApprovalOrders = $forApprovalOrdersQuery->paginate($perPage, ['*'], 'for_approval_orders_page');
        $completedOrders = $completedOrdersQuery->paginate($perPage, ['*'], 'completed_orders_page');
        $inProcessOrders = $inProcessOrdersQuery->paginate($perPage, ['*'], 'in_process_orders_page');
        $rejectedOrders = $rejectedOrdersQuery->paginate($perPage, ['*'], 'rejected_orders_page');
        $overdueOrders = $overdueOrdersQuery->paginate($perPage, ['*'], 'overdue_orders_page');
        $allOrders = $allOrdersQuery->paginate($perPage, ['*'], 'all_orders_page');

        // Get unique BP codes and categories for filters
        $bpCodes = WorkOrder::select('bp_code', DB::raw('MAX(customer_name) as customer_name'))
            ->whereNotNull('bp_code')
            ->groupBy('bp_code')
            ->get();
        $categories = ProductCategory::orderBy('name')->get();

        $craftsmen = Craftman::orderBy('name')->get();

        $subcategories = [];
        if ($categoryFilter) {
            $subcategories = ProductSubcategory::where('product_category_id', $categoryFilter)->orderBy('name')->get();
        } else {
            $subcategories = ProductSubcategory::orderBy('name')->get();
        }

        $designCodes = DB::table('products')->whereNotNull('design_code')->where('design_code', '!=', '')->distinct()->orderBy('design_code')->pluck('design_code');
        $productCodes = DB::table('products')->whereNotNull('product_code')->where('product_code', '!=', '')->distinct()->orderBy('product_code')->pluck('product_code');

        $superAdmins = \App\Models\ProcessOwner::where('role', 'super_admin')->get();

        return view('admin.work-order.index', compact(
            'newOrders',
            'allocatedOrders',
            'forApprovalOrders',
            'completedOrders',
            'inProcessOrders',
            'rejectedOrders',
            'overdueOrders',
            'allOrders',
            'search',
            'sortBy',
            'sortOrder',
            'bpCodes',
            'categories',
            'subcategories',
            'craftsmen',
            'bpCodeFilter',
            'categoryFilter',
            'subcategoryFilter',
            'craftsmanFilter',
            'designCodeFilter',
            'productCodeFilter',
            'designCodes',
            'productCodes',
            'superAdmins'
        ));
    }

    /**
     * Show the form for creating a new work order.
     */
    public function create()
    {
        $buyers = Buyer::all();
        $categories = ProductCategory::orderBy('name')->get();
        return view('admin.work-order.create', compact('buyers', 'categories'));
    }

    /**
     * Store a newly created work order.
     * Generates a Product Code if one is not provided.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'product_name' => 'nullable|string|max:255',
            'weight_from' => 'required',
            'weight_to' => 'required',
            'due_date' => 'required|date',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Logic for Flow 1: If product_code matches an existing product, copy its image
        $finalProductCode = $request->product_code;
        $productImage = null;

        if (!empty($finalProductCode)) {
            // Try to find existing product by product_code or design_code
            $existingProduct = Product::with('images')
                ->where('product_code', $finalProductCode)
                ->orWhere('design_code', $finalProductCode)
                ->first();

            if ($existingProduct && $existingProduct->images->count() > 0) {
                // Copy the first image from the existing product
                $existingImage = $existingProduct->images->first();
                $sourceImagePath = storage_path('app/public/' . $existingImage->path);

                if (file_exists($sourceImagePath)) {
                    // Copy image to work-orders directory with new name
                    $imageName = time() . '_copied_from_product_' . basename($existingImage->path);
                    $destinationPath = public_path('images/work-orders/' . $imageName);

                    // Make sure the directory exists
                    if (!file_exists(dirname($destinationPath))) {
                        mkdir(dirname($destinationPath), 0755, true);
                    }

                    copy($sourceImagePath, $destinationPath);

                    $productImage = 'images/work-orders/' . $imageName;

                    // Apply watermark to the copied image
                    $watermarkService = new ImageWatermarkService();
                    $watermarkService->addWatermark($productImage, true);
                }
            }
        } else {
            // Logic for Flow 2: If Product Code is missing, generate OOXXXX style code
            $finalProductCode = $this->generateUniqueProductCode();
        }

        // 1. Handle direct image upload (this overrides any copied image)
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/work-orders'), $imageName);
            $productImage = 'images/work-orders/' . $imageName;

            $watermarkService = new ImageWatermarkService();
            $watermarkService->addWatermark($productImage, true);
        }

        // 2. Generate work order number (System ID)
        $workOrderNumber = WorkOrder::generateWorkOrderNumber();

        // 3. Create record preserving all requested fields
        $workOrder = WorkOrder::create([
            'work_order_number' => $workOrderNumber,
            'product_image' => $productImage,
            'bp_code' => $request->bp_code,
            'customer_name' => $request->customer_name,
            'reference_no' => $request->reference_no,
            'due_date' => $request->due_date,
            'product_category' => $request->product_category_id ? ProductCategory::find($request->product_category_id)->name : null,
            'product_category_id' => $request->product_category_id,
            'subcategory' => $request->subcategory_id ? ProductSubcategory::find($request->subcategory_id)->name : null,
            'subcategory_id' => $request->subcategory_id,
            'quantity' => $request->quantity,
            'type' => $request->type,
            'open_close' => $request->open_close,
            'weight_from' => $request->weight_from,
            'weight_to' => $request->weight_to,
            'hallmark' => $request->hallmark,
            'rodium' => $request->rodium,
            'hook' => $request->hook,
            'size' => $request->size,
            'stone' => $request->stone,
            'enamel' => $request->enamel,
            'length' => $request->length,
            'screw_name' => $request->screw_name,
            'product_code' => $finalProductCode, // Used generated or existing code
            'relabel_code' => $request->relabel_code,
            'product_name' => $request->product_name,
            'craftsman_due_date' => $request->craftsman_due_date,
            'narration_craftsman' => $request->narration_craftsman,
            'narration_admin' => $request->narration_admin,
            'status' => 'new',
            'created_by' => Auth::id(), // Track who created the work order
            'creator_type' => 'admin',
        ]);

        // Handle multiple images upload
        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $index => $file) {
                $imageName = time() . '_multi_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/work-orders'), $imageName);
                $imagePath = 'images/work-orders/' . $imageName;

                $watermarkService->addWatermark($imagePath, true);

                WorkOrderImage::create([
                    'work_order_id' => $workOrder->id,
                    'image_path' => $imagePath,
                ]);

                // If no single image was set yet, set the first of multi-images as the primary
                if (!$workOrder->product_image) {
                    $workOrder->update(['product_image' => $imagePath]);
                }
            }
        }

        return redirect()->route('admin.work-order.index', ['tab' => $request->input('tab', 'new-orders')])
            ->with('success', 'Work Order created successfully with Product Code: ' . $finalProductCode);
    }

    /**
     * Generates a unique product code starting with 'OO' followed by numbers.
     * Example: OO001, OO002...
     */
    private function generateUniqueProductCode()
    {
        // Find the latest work order that has a code starting with 'OO'
        $latestOrder = WorkOrder::where('product_code', 'LIKE', 'OO%')
            ->orderBy('product_code', 'desc')
            ->first();

        if (!$latestOrder) {
            return 'OO001';
        }

        // Extract the numeric part and increment it
        $numericPart = preg_replace('/[^0-9]/', '', $latestOrder->product_code);
        $nextNumber = intval($numericPart) + 1;

        // Pad with zeros to keep the format consistent (OO001)
        return 'OO' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified work order.
     */
    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'images']);
        
        // Load product with images for design display to avoid eager loading bugs with orWhere
        $product = null;
        if ($workOrder->product_code) {
            $product = \App\Models\Product::with('images')
                ->where('product_code', $workOrder->product_code)
                ->orWhere('design_code', $workOrder->product_code)
                ->first();
        }

        $superAdmins = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
        return view('admin.work-order.show', compact('workOrder', 'superAdmins', 'product'));
    }

    /**
     * Display the print view for the specified work order.
     */
    public function print(WorkOrder $workOrder)
    {
        return view('admin.work-order.print', compact('workOrder'));
    }

    /**
     * Show the form for allocating a work order to a craftsman.
     */
    public function allocateForm(WorkOrder $workOrder)
    {
        $craftsmen = Craftman::all();
        return view('admin.work-order.allocate', compact('workOrder', 'craftsmen'));
    }

    /**
     * Allocate a work order to a craftsman.
     */
    public function allocate(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
            'priority' => 'nullable|string|in:Urgent,High,Normal',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status' => 'allocated',
            'craftsman_status' => 'allocated', // Set initial craftsman status
            'allocated_by' => Auth::guard('admin')->id(),
            'allocated_at' => now(),
            'priority' => $request->priority,
        ]);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $request->allocated_craftsman_bp_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new \App\Notifications\WorkOrderAllocated($workOrder));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return redirect()->route('admin.work-order.index', ['tab' => $request->input('tab', 'allocated-orders')])
            ->with('success', 'Work Order allocated successfully!');
    }

    /**
     * Approve a work order that has been completed by a craftsman.
     */
    public function approve(Request $request, WorkOrder $workOrder)
    {
        // Check if the work order is in for approval status
        if ($workOrder->status !== 'for_approval') {
            return redirect()->back()->with('error', 'Work order is not ready for approval.');
        }

        $workOrder->update([
            'status' => 'completed',
            'approved_by' => Auth::id()
        ]);

        // Notify Original Requester
        try {
            $recipient = null;
            if ($workOrder->creator_type === 'buyer') {
                $recipient = \App\Models\Buyer::where('bp_code', $workOrder->creator_user_code)->first();
            } elseif ($workOrder->creator_type === 'key_user') {
                $recipient = \App\Models\KeyUser::where('user_code', $workOrder->creator_user_code)->first();
            } elseif ($workOrder->creator_type === 'user') {
                $recipient = \App\Models\User::where('user_code', $workOrder->creator_user_code)->first();
            }

            if ($recipient && $recipient->fcm_token) {
                $recipient->notify(new \App\Notifications\WorkOrderCompleted($workOrder));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify requester: ' . $e->getMessage());
        }

        return redirect()->route('admin.work-order.index', ['tab' => $request->input('tab', 'allocated-orders')])
            ->with('success', 'Work Order approved and marked as completed!');
    }

    /**
     * Bulk approve work orders.
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_ids' => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the selected work orders
        $workOrderIds = $request->input('work_order_ids');

        // Update all selected work orders
        WorkOrder::whereIn('id', $workOrderIds)
            ->where('status', 'for_approval') // Only approve orders waiting for approval
            ->update([
                'status' => 'completed',
                'approved_by' => Auth::id()
            ]);

        // Notify Original Requesters (Grouped)
        $recipients = [];
        foreach ($workOrderIds as $id) {
            try {
                $workOrder = WorkOrder::find($id);
                if ($workOrder && $workOrder->status === 'completed') {
                    $recipientKey = "{$workOrder->creator_type}_{$workOrder->creator_user_code}";
                    if (!isset($recipients[$recipientKey])) {
                        $recipient = null;
                        if ($workOrder->creator_type === 'buyer') {
                            $recipient = \App\Models\Buyer::where('bp_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'key_user') {
                            $recipient = \App\Models\KeyUser::where('user_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'user') {
                            $recipient = \App\Models\User::where('user_code', $workOrder->creator_user_code)->first();
                        }

                        if ($recipient) {
                            $recipients[$recipientKey] = [
                                'model' => $recipient,
                                'count' => 0,
                                'lastOrder' => $workOrder
                            ];
                        }
                    }

                    if (isset($recipients[$recipientKey])) {
                        $recipients[$recipientKey]['count']++;
                        $recipients[$recipientKey]['lastOrder'] = $workOrder;
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to resolve recipient for work order: ' . $e->getMessage());
            }
        }

        foreach ($recipients as $data) {
            try {
                $recipient = $data['model'];
                if ($recipient && $recipient->fcm_token) {
                    $count = $data['count'];
                    $lastOrder = $data['lastOrder'];
                    $message = $count > 1
                        ? "{$count} of your Work Orders have been completed and approved by Admin."
                        : "Your Work Order #{$lastOrder->work_order_number} has been completed and approved by Admin.";

                    $recipient->notify(new \App\Notifications\WorkOrderCompleted($lastOrder, $message));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify requester in bulk: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.work-order.index', ['tab' => $request->input('tab', 'allocated-orders')])
            ->with('success', count($workOrderIds) . ' Work Orders approved successfully!');
    }

    /**
     * Bulk complete work orders.
     */
    public function bulkComplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_ids' => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $workOrderIds = $request->input('work_order_ids');

        // Update all selected work orders to completed status
        // We allow completing from any status except already completed
        WorkOrder::whereIn('id', $workOrderIds)
            ->where('status', '!=', 'completed')
            ->update([
                'status' => 'completed',
                'craftsman_status' => 'completed',
                'approved_by' => Auth::id()
            ]);

        // Notify Requesters
        $recipients = [];
        foreach ($workOrderIds as $id) {
            try {
                $workOrder = WorkOrder::find($id);
                if ($workOrder && $workOrder->status === 'completed') {
                    $recipientKey = "{$workOrder->creator_type}_{$workOrder->creator_user_code}";
                    if (!isset($recipients[$recipientKey])) {
                        $recipient = null;
                        if ($workOrder->creator_type === 'buyer') {
                            $recipient = \App\Models\Buyer::where('bp_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'key_user') {
                            $recipient = \App\Models\KeyUser::where('user_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'user') {
                            $recipient = \App\Models\User::where('user_code', $workOrder->creator_user_code)->first();
                        }

                        if ($recipient) {
                            $recipients[$recipientKey] = [
                                'model' => $recipient,
                                'count' => 0,
                                'lastOrder' => $workOrder
                            ];
                        }
                    }

                    if (isset($recipients[$recipientKey])) {
                        $recipients[$recipientKey]['count']++;
                        $recipients[$recipientKey]['lastOrder'] = $workOrder;
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to resolve recipient for manual completion: ' . $e->getMessage());
            }
        }

        foreach ($recipients as $data) {
            try {
                $recipient = $data['model'];
                if ($recipient && $recipient->fcm_token) {
                    $count = $data['count'];
                    $lastOrder = $data['lastOrder'];
                    $message = $count > 1
                        ? "{$count} of your Work Orders have been marked as completed by Admin."
                        : "Your Work Order #{$lastOrder->work_order_number} has been marked as completed by Admin.";

                    $recipient->notify(new \App\Notifications\WorkOrderCompleted($lastOrder, $message));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify requester for manual completion: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.work-order.index', ['tab' => 'completed-orders'])
            ->with('success', count($workOrderIds) . ' Work Orders marked as completed!');
    }

    /**
     * Reallocate a work order to a different craftsman.
     */
    public function reallocateForm(WorkOrder $workOrder)
    {
        $craftsmen = Craftman::all();
        return view('admin.work-order.reallocate', compact('workOrder', 'craftsmen'));
    }

    /**
     * Process reallocation of a work order to a different craftsman.
     */
    public function reallocate(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status' => 'allocated',
            'craftsman_status' => 'allocated', // Reset craftsman status
            'allocated_by' => Auth::guard('admin')->id(),
            'allocated_at' => now(),
        ]);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $request->allocated_craftsman_bp_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new \App\Notifications\WorkOrderAllocated($workOrder, "Work Order #{$workOrder->work_order_number} has been reallocated to you."));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return redirect()->route('admin.work-order.index', ['tab' => $request->input('tab', 'allocated-orders')])
            ->with('success', 'Work Order reallocated successfully!');
    }

    /**
     * Show the form for editing the specified work order.
     */
    public function edit(WorkOrder $workOrder)
    {
        $workOrder->load(['productCategory', 'subcategoryRelation']);
        $categories = ProductCategory::orderBy('name')->get();
        $buyers = Buyer::all();
        return view('admin.work-order.edit', compact('workOrder', 'categories', 'buyers'));
    }

    /**
     * Update the specified work order in storage.
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'product_name' => 'nullable|string|max:255',
            'due_date' => 'required|date',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle image based on product code lookup or direct upload
        $productImage = $workOrder->product_image; // Preserve existing image

        // Handle explicit removal of primary image
        if ($request->has('remove_product_image') && $request->remove_product_image == 1) {
            if ($productImage && file_exists(public_path($productImage))) {
                @unlink(public_path($productImage));
            }
            $productImage = null;
        }

        // Handle removal of specific images from gallery
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageId) {
                $image = WorkOrderImage::find($imageId);
                if ($image && $image->work_order_id == $workOrder->id) {
                    if (file_exists(public_path($image->image_path))) {
                        @unlink(public_path($image->image_path));
                    }
                    $image->delete();
                }
            }
        }

        if (!empty($request->product_code) && !$request->hasFile('product_image') && !$request->has('remove_product_image')) {
            // Only copy if code changed OR if currently has no image
            if ($request->product_code !== $workOrder->product_code || empty($workOrder->product_image)) {
                // If product code is provided and no new image uploaded, try to copy from existing product
                $existingProduct = Product::with('images')
                    ->where('product_code', $request->product_code)
                    ->orWhere('design_code', $request->product_code)
                    ->first();

                if ($existingProduct && $existingProduct->images->count() > 0) {
                    // Copy the first image from the existing product
                    $existingImage = $existingProduct->images->first();
                    $sourceImagePath = storage_path('app/public/' . $existingImage->path);

                    if (file_exists($sourceImagePath)) {
                        // Copy image to work-orders directory with new name
                        $imageName = time() . '_copied_from_product_' . basename($existingImage->path);
                        $destinationPath = public_path('images/work-orders/' . $imageName);

                        // Make sure the directory exists
                        if (!file_exists(dirname($destinationPath))) {
                            mkdir(dirname($destinationPath), 0755, true);
                        }

                        copy($sourceImagePath, $destinationPath);

                        $productImage = 'images/work-orders/' . $imageName;

                        // Apply watermark to the copied image
                        $watermarkService = new ImageWatermarkService();
                        $watermarkService->addWatermark($productImage, true);
                    }
                }
            }
        } elseif ($request->hasFile('product_image')) {
            // Handle direct image upload (this overrides any copied image)
            $image = $request->file('product_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/work-orders'), $imageName);
            $productImage = 'images/work-orders/' . $imageName;

            $watermarkService = new ImageWatermarkService();
            $watermarkService->addWatermark($productImage, true);
        }

        $workOrder->update([
            'product_image' => $productImage,
            'bp_code' => $request->bp_code,
            'customer_name' => $request->customer_name,
            'reference_no' => $request->reference_no,
            'due_date' => $request->due_date,
            'product_category' => $request->product_category_id ? ProductCategory::find($request->product_category_id)->name : null,
            'product_category_id' => $request->product_category_id,
            'subcategory' => $request->subcategory_id ? ProductSubcategory::find($request->subcategory_id)->name : null,
            'subcategory_id' => $request->subcategory_id,
            'quantity' => $request->quantity,
            'type' => $request->type,
            'open_close' => $request->open_close,
            'weight_from' => $request->weight_from,
            'weight_to' => $request->weight_to,
            'hallmark' => $request->hallmark,
            'rodium' => $request->rodium,
            'hook' => $request->hook,
            'size' => $request->size,
            'stone' => $request->stone,
            'enamel' => $request->enamel,
            'length' => $request->length,
            'screw_name' => $request->screw_name,
            'product_code' => $request->product_code,
            'relabel_code' => $request->relabel_code,
            'product_name' => $request->product_name,
            'craftsman_due_date' => $request->craftsman_due_date,
            'narration_craftsman' => $request->narration_craftsman,
            'narration_admin' => $request->narration_admin,
        ]);

        // Handle new multiple images upload
        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $index => $file) {
                $imageName = time() . '_multi_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/work-orders'), $imageName);
                $imagePath = 'images/work-orders/' . $imageName;

                $watermarkService->addWatermark($imagePath, true);

                WorkOrderImage::create([
                    'work_order_id' => $workOrder->id,
                    'image_path' => $imagePath,
                ]);

                // If no primary image exists, use this one
                if (!$workOrder->product_image) {
                    $workOrder->update(['product_image' => $imagePath]);
                }
            }
        }

        return redirect()->to(
            $request->input('return_url') ?? route('admin.work-order.index', [
                'tab' => $request->tab ?? 'new-orders'
            ])
        )->with('success', 'Work Order #' . $workOrder->work_order_number . ' updated successfully!');
    }

    /**
     * Remove the specified work order from storage.
     */
    public function destroy(Request $request, WorkOrder $workOrder)
    {
        // Use tab from form input, fallback to referer, then default
        $tab = $request->input('tab');
        if (!$tab) {
            $tab = 'new-orders';
            $referer = request()->headers->get('referer');
            if ($referer) {
                parse_str(parse_url($referer, PHP_URL_QUERY) ?? '', $queryParams);
                if (!empty($queryParams['tab'])) {
                    $tab = $queryParams['tab'];
                }
            }
        }

        $workOrder->delete();

        return redirect()->route('admin.work-order.index', ['tab' => $tab])
            ->with('success', 'Work Order deleted successfully!');
    }

    /**
     * Copy a completed work order to create a new one.
     */
    public function copy(WorkOrder $workOrder)
    {
        // Create a new work order based on the existing one
        $newWorkOrder = $workOrder->replicate();

        // Reset status and other fields that shouldn't be copied
        $newWorkOrder->status = 'new';
        $newWorkOrder->craftsman_status = null;
        $newWorkOrder->allocated_craftsman_bp_code = null;
        $newWorkOrder->allocated_by = null; // Reset allocator
        $newWorkOrder->allocated_at = null;
        $newWorkOrder->approved_by = null;
        $newWorkOrder->rejection_reason = null;

        // Reset return logs & notes for a fresh cycle
        $newWorkOrder->return_note = null;
        $newWorkOrder->return_due_date = null;
        $newWorkOrder->damaged_image = null;
        $newWorkOrder->admin_return_count = 0;
        $newWorkOrder->superadmin_return_count = 0;
        $newWorkOrder->admin_undo_count = 0;
        $newWorkOrder->superadmin_undo_count = 0;

        // Reset staff tracking
        $newWorkOrder->craftsman_staff_id = null;
        $newWorkOrder->accepted_by_staff_id = null;
        $newWorkOrder->staff_accepted_at = null;
        $newWorkOrder->staff_completed_at = null;

        $newWorkOrder->due_date = today()->addDays(7); // Set a new due date (7 days from now)
        $newWorkOrder->craftsman_due_date = today()->addDays(14); // Set a new craftsman due date (14 days from now)

        // Set the creator to the person copying it
        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user() ?? \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $newWorkOrder->created_by = $user->id;
            $newWorkOrder->creator_type = 'admin';
            $newWorkOrder->creator_user_code = $user->user_code ?? null;
        }

        // Generate a new work order number
        $newWorkOrder->work_order_number = WorkOrder::generateWorkOrderNumber();

        // Save the new work order
        $newWorkOrder->save();

        // We intentionally do NOT copy the WorkOrderImage records (craftsman completion proof images)
        // This prevents craftsmen from seeing completion proof images when working on the new order

        return redirect()->route('admin.work-order.edit', $newWorkOrder)
            ->with('success', 'Work Order copied successfully! Please review and update as needed.');
    }

    /**
     * Load work orders via AJAX for infinite scroll
     */
    public function loadWorkOrdersAjax(Request $request)
    {
        $tab = $request->get('tab', 'new-orders');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', self::DEFAULT_PER_PAGE);
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $bpCodeFilter = $request->get('bp_code_filter');
        $categoryFilter = $request->get('category_filter');
        $subcategoryFilter = $request->get('subcategory_filter');
        $craftsmanFilter = $request->get('craftsman_filter');
        $designCodeFilter = $request->get('design_code_filter');
        $productCodeFilter = $request->get('product_code_filter');
        $returnFilter = $request->get('return_filter');

        // Validate page size
        if (!in_array($perPage, self::PAGE_SIZE_OPTIONS)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $query = null;

        switch ($tab) {
            case 'new-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'new');
                break;
            case 'allocated-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'allocated')
                    ->where('craftsman_status', '!=', 'in_process');
                break;
            case 'for-approval-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'for_approval');
                break;
            case 'completed-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'completed');
                break;
            case 'in-process-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('craftsman_status', 'in_process');
                break;
            case 'rejected-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('craftsman_status', 'rejected');
                break;
            case 'overdue-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', '!=', 'completed')
                    ->where('craftsman_status', '!=', 'rejected')
                    ->where(function ($q) {
                        $q->whereDate('due_date', '<', now()->toDateString())
                            ->orWhere(function ($sq) {
                                $sq->whereDate('due_date', now()->toDateString())
                                    ->whereRaw('HOUR(NOW()) >= 12');
                            });
                    });
                break;
            case 'all-orders':
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman']);
                break;
            default:
                $query = WorkOrder::with(['product.images', 'images', 'buyer', 'craftsman'])->where('status', 'new');
        }

        // Apply search if present
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('work_order_number', 'LIKE', $searchTerm)
                    ->orWhere('customer_name', 'LIKE', $searchTerm)
                    ->orWhere('product_name', 'LIKE', $searchTerm)
                    ->orWhere('product_code', 'LIKE', $searchTerm)
                    ->orWhere('bp_code', 'LIKE', $searchTerm)
                    ->orWhere('reference_no', 'LIKE', $searchTerm)
                    ->orWhere('type', 'LIKE', $searchTerm)
                    ->orWhere('size', 'LIKE', $searchTerm)
                    ->orWhere('length', 'LIKE', $searchTerm)
                    ->orWhere('hallmark', 'LIKE', $searchTerm)
                    ->orWhere('rodium', 'LIKE', $searchTerm)
                    ->orWhere('hook', 'LIKE', $searchTerm)
                    ->orWhere('stone', 'LIKE', $searchTerm)
                    ->orWhere('enamel', 'LIKE', $searchTerm)
                    ->orWhere('narration_craftsman', 'LIKE', $searchTerm)
                    ->orWhere('narration_admin', 'LIKE', $searchTerm)
                    ->orWhereHas('craftsman', function ($sq) use ($searchTerm) {
                        $sq->where('craftman_code', 'LIKE', $searchTerm)
                            ->orWhere('dear', 'LIKE', $searchTerm);
                    })
                    ->orWhereHas('buyer', function ($sq) use ($searchTerm) {
                        $sq->where('dear', 'LIKE', $searchTerm);
                    });
            });
        }

        // Apply BP Code filter
        if ($bpCodeFilter) {
            $query->where('bp_code', $bpCodeFilter);
        }

        // Apply Category filter
        if ($categoryFilter) {
            $query->where('product_category_id', $categoryFilter);
        }

        // Apply Subcategory filter
        if ($subcategoryFilter) {
            $query->where('subcategory_id', $subcategoryFilter);
        }

        // Apply Craftsman filter
        if ($craftsmanFilter) {
            $query->where('allocated_craftsman_bp_code', $craftsmanFilter);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        $orders = $query->paginate($perPage, ['*'], $tab . '_page', $page);

        return response()->json([
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'next_page_url' => $orders->nextPageUrl(),
            'prev_page_url' => $orders->previousPageUrl(),
            'tab' => $tab
        ]);
    }
    /**
     * Bulk allocate work orders to a craftsman.
     */
    public function bulkAllocate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_ids' => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
            'craftsman_due_date' => 'nullable|date',
            'priority' => 'nullable|string|in:Urgent,High,Normal',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the selected work orders
        $workOrderIds = $request->input('work_order_ids');
        $craftsmanBpCode = $request->input('allocated_craftsman_bp_code');

        $updateData = [
            'allocated_craftsman_bp_code' => $craftsmanBpCode,
            'status' => 'allocated',
            'craftsman_status' => 'allocated',
            'allocated_by' => Auth::id(),
            'allocated_at' => now(),
            'priority' => $request->priority,
        ];
        
        if ($request->filled('craftsman_due_date')) {
            $updateData['craftsman_due_date'] = $request->input('craftsman_due_date');
        }

        // Update all selected work orders
        WorkOrder::whereIn('id', $workOrderIds)
            ->where(function ($q) {
                $q->where('status', 'new')
                    ->orWhere('craftsman_status', 'rejected');
            })
            ->update($updateData);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $craftsmanBpCode)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $count = count($workOrderIds);
                $message = "You have been allocated {$count} new Work Orders.";
                $firstOrder = WorkOrder::find($workOrderIds[0]);
                $craftsman->notify(new \App\Notifications\WorkOrderAllocated($firstOrder, $message));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return redirect()->route('admin.work-order.index', ['tab' => 'allocated-orders'])
            ->with('success', count($workOrderIds) . ' Work Orders allocated successfully!');
    }

    /**
     * Show the form for bulk allocation.
     */
    public function bulkAllocateForm(Request $request)
    {
        // Get the selected work order IDs from the request
        $workOrderIds = $request->input('work_order_ids', []);

        // If no work orders are selected, redirect back with error
        if (empty($workOrderIds)) {
            return redirect()->back()->with('error', 'Please select at least one work order to allocate.');
        }

        // Get the work orders - allow new orders AND rejected orders (craftsman_status = 'rejected')
        $workOrders = WorkOrder::whereIn('id', $workOrderIds)
            ->where(function ($q) {
                $q->where('status', 'new')
                    ->orWhere('craftsman_status', 'rejected');
            })
            ->get();

        // If no valid work orders found, redirect back with error
        if ($workOrders->isEmpty()) {
            return redirect()->back()->with('error', 'No valid work orders selected for allocation.');
        }

        $craftsmen = Craftman::all();

        // ------------------ CRAFTSMAN PERFORMANCE SUGGESTIONS ------------------
        $categories = $workOrders->pluck('product_category_id')->filter()->unique()->toArray();
        $designCodes = $workOrders->pluck('design_code')->filter()->unique()->toArray();
        $productCodes = $workOrders->pluck('product_code')->filter()->unique()->toArray();

        $suggestedCraftsmen = [];
        
        if (!empty($categories) || !empty($designCodes) || !empty($productCodes)) {
            $craftsmanStats = [];
            foreach ($craftsmen as $craftsman) {
                // Determine completed matching orders count
                $count = WorkOrder::where('allocated_craftsman_bp_code', $craftsman->craftman_code)
                    ->where(function($query) {
                        $query->where('craftsman_status', 'completed')
                              ->orWhere('status', 'completed');
                    })
                    ->where(function($query) use ($categories, $designCodes, $productCodes) {
                        if (!empty($categories)) {
                            $query->orWhereIn('product_category_id', $categories);
                        }
                        if (!empty($designCodes)) {
                            $query->orWhereIn('design_code', $designCodes);
                        }
                        if (!empty($productCodes)) {
                            $query->orWhereIn('product_code', $productCodes);
                        }
                    })
                    ->count();

                if ($count > 0) {
                    $craftsmanStats[] = [
                        'craftsman' => $craftsman,
                        'completed_count' => $count
                    ];
                }
            }

            // Sort descending by highest completed matching count
            usort($craftsmanStats, function($a, $b) {
                return $b['completed_count'] <=> $a['completed_count'];
            });

            // Get Top 3
            $suggestedCraftsmen = array_slice($craftsmanStats, 0, 3);
        }
        // -----------------------------------------------------------------------

        return view('admin.work-order.bulk-allocate', compact('workOrders', 'craftsmen', 'suggestedCraftsmen'));
    }

    /**
     * Bulk Print Logic for Work Orders
     */
    public function bulkPrint(Request $request)
    {
        $workOrderIds = $request->input('work_order_ids', []);

        if (empty($workOrderIds)) {
            return redirect()->back()->with('error', 'No work orders selected for printing.');
        }

        $workOrders = WorkOrder::with(['product.images', 'productCategory', 'subcategoryRelation', 'buyer', 'craftsman'])
            ->whereIn('id', $workOrderIds)
            ->get();

        return view('admin.work-order.bulk-print', compact('workOrders'));
    }

    /**
     * Show the bulk upload form.
     */
    public function showUploadForm()
    {
        return view('admin.work-order.upload');
    }

    /**
     * Import work orders from Excel/CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new WorkOrderImport(Auth::user(), 'admin'), $request->file('file'));

            return redirect()->route('admin.work-order.index')
                ->with('success', 'Work Orders imported successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Validation failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template for work order import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=work_order_template.csv',
        ];

        $content = "customer_name,product_name,quantity,due_date,product_category,subcategory,bp_code,reference_no,type,open_close,weight_from,weight_to,hallmark,rodium,hook,size,stone,enamel,length,product_code,relabel_code,craftsman_due_date,narration_craftsman,narration_admin,allocated_craftsman_bp_code\n";
        $content .= "John Doe,Gold Ring,10,2025-12-31,Jewelry,Rings,BP001,REF001,Regular,Open,5.0,10.0,Yes,No,Yes,10mm,No,Yes,20mm,PROD001,REL001,2025-12-30,Good quality,Approved,CRAFT001\n";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'work_order_template.csv', $headers);
    }

    /**
     * Get product details by product code/design code (AJAX).
     */
    public function getProductDetails(Request $request)
    {
        $code = $request->query('product_code');

        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Code is required']);
        }

        $product = Product::with('images')
            ->where(function ($query) use ($code) {
                $query->where('product_code', $code)
                    ->orWhere('design_code', $code);
            })
            ->where('design_status', 'Accepted')  // Only accepted designs
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found or not accepted']);
        }

        // Handle image URL construction - try different possible locations
        $firstImage = null;
        if ($product->images->first()) {
            $imagePath = $product->images->first()->path;

            // Different possible image locations and formats
            if (!empty($imagePath)) {
                // Check if it's already a full URL
                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $firstImage = $imagePath;
                }
                // If the path starts with 'images/work-orders/', it's a public image
                elseif (strpos($imagePath, 'images/work-orders/') === 0) {
                    $firstImage = asset($imagePath);
                }
                // If the path starts with 'images/', it's a public image
                elseif (strpos($imagePath, 'images/') === 0) {
                    $firstImage = asset($imagePath);
                }
                // If the path starts with 'storage/' already
                elseif (strpos($imagePath, 'storage/') === 0) {
                    $firstImage = asset($imagePath);
                }
                // Otherwise, it's likely a storage image
                else {
                    $firstImage = asset('storage/' . $imagePath);
                }

                // Check if the image file actually exists
                if ($firstImage) {
                    // Extract the path from the asset URL to check if file exists
                    $pathToCheck = str_replace(asset(''), '', $firstImage);
                    if (substr($pathToCheck, 0, 7) === 'storage') {
                        $fullPath = storage_path('app/public/' . substr($pathToCheck, 8));
                    } else {
                        $fullPath = public_path($pathToCheck);
                    }

                    if (!file_exists($fullPath)) {
                        $firstImage = null; // File doesn't exist, set to null
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'product' => [
                'product_name' => $product->product_name,
                'design_code' => $product->design_code,
                'product_code' => $product->product_code,
                'product_image_url' => $firstImage,
                'product_category_id' => $product->product_category_id,
                'subcategory_id' => $product->product_subcategory_id,
                'type' => $product->type,
                'open_close' => $product->open_close,
                'hallmark' => $product->hallmark,
                'rodium' => $product->rodium,
                'hook' => $product->hook,
                'size' => $product->size,
                'stone' => $product->stone,
                'enamel' => $product->enamel,
                'length' => $product->length,
                'weight_from' => $product->weight_from,
                'weight_to' => $product->weight_to,
                'relabel_code' => $product->relabel_code,
            ]
        ]);
    }

    /**
     * Show the bulk upload form
     */
    public function showBulkUpload()
    {
        return view('admin.work-order.bulk-upload');
    }

    /**
     * Import OrderList Excel file to create work orders
     */
    public function importOrderList(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        try {
            // Relaxed ZipArchive check to allow attempting XLSX if user insists or environment changes
            // $extension = strtolower($request->file('file')->getClientOriginalExtension());
            // if ($extension !== 'csv' && !class_exists('ZipArchive')) { ... }

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'import_' . time() . '.' . $extension;

            // Define absolute path destination
            $destinationPath = storage_path('app/imports');
            if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);

            // Move file explicitly
            $file->move($destinationPath, $filename);
            $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;

            $excelFileToImport = $fullPath;
            $extractPath = null;
            $tempExtractDir = null;

            // Handle ZIP file
            if ($extension === 'zip') {
                $tempExtractDir = storage_path('app/imports/temp_' . time());
                if (!file_exists($tempExtractDir)) mkdir($tempExtractDir, 0755, true);

                $extracted = false;

                // Method A: PHP ZipArchive
                if (class_exists('ZipArchive')) {
                    $zip = new \ZipArchive;
                    if ($zip->open($fullPath) === TRUE) {
                        $zip->extractTo($tempExtractDir);
                        $zip->close();
                        $extracted = true;
                    }
                }

                // Method B: PowerShell (Fallback for Windows)
                if (!$extracted && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Use standard PowerShell Expand-Archive
                    $cmd = 'powershell -command "Expand-Archive -Path \'' . $fullPath . '\' -DestinationPath \'' . $tempExtractDir . '\' -Force"';
                    // Execute command
                    @shell_exec($cmd);

                    // Verify if files exist
                    if (count(scandir($tempExtractDir)) > 2) {
                        $extracted = true;
                    }
                }

                if (!$extracted) {
                    // Method C: 7-zip or Try error message
                    throw new \Exception("Cannot extract ZIP. PHP ZipArchive extension is missing and system commands failed.");
                }

                $extractPath = $tempExtractDir;

                // Find Excel file in extracted contents
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempExtractDir));
                foreach ($iterator as $info) {
                    if ($info->isFile() && in_array(strtolower($info->getExtension()), ['xlsx', 'xls', 'csv'])) {
                        // Don't pick hidden files
                        if (strpos($info->getFilename(), '.') === 0) continue;

                        $excelFileToImport = $info->getPathname();
                        break;
                    }
                }
            }

            // Perform Import
            $user = Auth::user();
            // Since this is Admin controller, user is a ProcessOwner with role 'admin' (or similar)
            // But checking ProcessOwner model, it has 'role'. 
            // We can just pass 'admin' as type.
            $importInstance = new \App\Imports\OrderListImport($extractPath, $user, 'admin');

            // MAGIC BYTE CHECK: Correct the extension if mismatched
            // This fixes "Invalid Spreadsheet" errors where .xls is actually .xlsx (Zip)
            $handle = fopen($excelFileToImport, 'rb');
            $header = fread($handle, 4);
            fclose($handle);
            $hexHeader = bin2hex($header);

            $finalPath = $excelFileToImport;

            // Check for ZIP signature (PK..) -> XLSX
            if ($hexHeader === '504b0304') {
                $pInfo = pathinfo($excelFileToImport);

                // If it is an XLSX (renamed or original)
                // If extension is missing or zip, treat as xlsx if signature matches
                $isXlsx = true;

                if (!class_exists('ZipArchive')) {
                    // FALLBACK: Use Python to convert XLSX to CSV

                    // Fix: openpyxl refuses to read files with .xls extension even if content is xlsx.
                    // We must ensure the file sent to python has .xlsx extension.
                    if (strtolower($pInfo['extension']) !== 'xlsx') {
                        $newPath = $pInfo['dirname'] . '/' . $pInfo['filename'] . '.xlsx';
                        rename($excelFileToImport, $newPath);
                        $excelFileToImport = $newPath;
                    }

                    $pythonScript = storage_path('app/convert_xlsx.py');
                    $csvPath = $pInfo['dirname'] . '/' . $pInfo['filename'] . '_converted.csv';

                    // Ensure script exists
                    if (!file_exists($pythonScript)) {
                        throw new \Exception("Server missing Zip extension AND helper script ($pythonScript) not found.");
                    }

                    // Execute Python conversion
                    // Using shell_exec to run python. Assumption: 'python' is in PATH.
                    $cmd = 'python "' . $pythonScript . '" "' . $excelFileToImport . '" "' . $csvPath . '"';
                    $output = shell_exec($cmd . " 2>&1");

                    if (file_exists($csvPath) && filesize($csvPath) > 0) {
                        // Success! Use the CSV instead
                        $finalPath = $csvPath;
                        $excelFileToImport = $csvPath; // Update for cleanup
                    } else {
                        // Conversion failed
                        @unlink($excelFileToImport);
                        if ($tempExtractDir) {
                            $cmd = 'rmdir /s /q "' . $tempExtractDir . '"';
                            @exec($cmd);
                        }
                        throw new \Exception("Your server cannot read .xlsx files. Python conversion failed: " . $output);
                    }
                } else {
                    // Normal ZipArchive processing (if it was enabled)
                    if (strtolower($pInfo['extension']) !== 'xlsx') {
                        $newPath = $pInfo['dirname'] . '/' . $pInfo['filename'] . '.xlsx';
                        rename($excelFileToImport, $newPath);
                        $finalPath = $newPath;
                    }
                }
            }
            // Check for OLE signature (D0CF..) -> XLS
            elseif ($hexHeader === 'd0cf11e0') {
                $pInfo = pathinfo($excelFileToImport);
                if (strtolower($pInfo['extension']) !== 'xls') {
                    $newPath = $pInfo['dirname'] . '/' . $pInfo['filename'] . '.xls';
                    rename($excelFileToImport, $newPath);
                    $finalPath = $newPath;
                }
            }

            // Import
            Excel::import($importInstance, $finalPath);

            // Cleanup matches
            @unlink($finalPath);
            // If we renamed, original might warrant cleanup too if not overwritten? (rename moves it)
            if ($finalPath !== $excelFileToImport && file_exists($excelFileToImport)) {
                @unlink($excelFileToImport);
            }

            if ($tempExtractDir) {
                // Recursive delete function
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempExtractDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    $todo($fileinfo->getRealPath());
                }
                rmdir($tempExtractDir);
            }

            return redirect()->route('admin.work-order.index')
                ->with('success', $importInstance->importedCount . ' Order List Work Orders imported successfully!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $messages = [];
            foreach ($failures as $failure) {
                $messages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return redirect()->back()
                ->with('error', 'Validation failed: ' . implode('<br>', $messages));
        } catch (\Throwable $e) {
            // Log error
            Log::error('Import Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with('error', 'Import Error: ' . $e->getMessage());
        }
    }

    /**
     * Export work orders to CSV based on tab and filters
     */
    public function export(Request $request)
    {
        $tab = $request->get('tab', 'new-orders');
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort parameters
        $allowedSortColumns = ['id', 'work_order_number', 'customer_name', 'product_name', 'quantity', 'due_date', 'status'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Get the appropriate query based on tab
        switch ($tab) {
            case 'new-orders':
                $query = WorkOrder::where('status', 'new');
                $filename = 'new_work_orders';
                break;
            case 'allocated-orders':
                $query = WorkOrder::where('status', 'allocated')
                    ->where('craftsman_status', '!=', 'in_process');
                $filename = 'allocated_work_orders';
                break;
            case 'for-approval-orders':
                $query = WorkOrder::where('status', 'for_approval');
                $filename = 'work_orders_for_approval';
                break;
            case 'completed-orders':
                $query = WorkOrder::where('status', 'completed');
                $filename = 'completed_work_orders';
                break;
            case 'in-process-orders':
                $query = WorkOrder::where('craftsman_status', 'in_process');
                $filename = 'in_process_work_orders';
                break;
            case 'rejected-orders':
                $query = WorkOrder::where('craftsman_status', 'rejected');
                $filename = 'rejected_work_orders';
                break;
            case 'overdue-orders':
                $query = WorkOrder::where('status', '!=', 'completed')
                    ->where('craftsman_status', '!=', 'rejected')
                    ->where(function ($q) {
                        $q->whereDate('craftsman_due_date', '<', now()->toDateString())
                            ->orWhere(function ($sq) {
                                $sq->whereDate('craftsman_due_date', now()->toDateString())
                                    ->whereRaw('HOUR(NOW()) >= 12');
                            });
                    });
                $filename = 'overdue_work_orders';
                break;
            case 'all-orders':
                $query = WorkOrder::query();
                $filename = 'all_work_orders';
                break;
            default:
                $query = WorkOrder::where('status', 'new');
                $filename = 'work_orders';
        }

        // Apply specific IDs if provided (Bulk Export)
        if ($request->filled('work_order_ids')) {
            $ids = $request->work_order_ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $query->whereIn('id', $ids);
        }

        // Apply search filter
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('work_order_number', 'LIKE', $searchTerm)
                    ->orWhere('customer_name', 'LIKE', $searchTerm)
                    ->orWhere('product_name', 'LIKE', $searchTerm)
                    ->orWhere('product_code', 'LIKE', $searchTerm)
                    ->orWhere('bp_code', 'LIKE', $searchTerm)
                    ->orWhere('reference_no', 'LIKE', $searchTerm);
            });
        }

        // Apply extra filters
        if ($request->filled('bp_code_filter')) {
            $query->where('bp_code', $request->bp_code_filter);
        }

        if ($request->filled('category_filter')) {
            $query->where('product_category', $request->category_filter);
        }

        if ($request->filled('subcategory_filter')) {
            $query->where('subcategory', $request->subcategory_filter);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Get all results
        $workOrders = $query->get();

        // Format data for export
        $exportData = $workOrders->map(function ($order) {
            return [
                'ID' => $order->id,
                'Work Order Number' => $order->work_order_number,
                'Customer Name' => $order->customer_name,
                'Product Name' => $order->product_name,
                'Product Code' => $order->product_code,
                'Quantity' => $order->quantity,
                'Weight From' => $order->weight_from,
                'Weight To' => $order->weight_to,
                'Reference No' => $order->reference_no,
                'Due Date' => $order->due_date ? $order->due_date->format('Y-m-d') : 'N/A',
                'Craftsman Due Date' => $order->craftsman_due_date ? $order->craftsman_due_date->format('Y-m-d') : 'N/A',
                'Status' => $order->status,
                'Craftsman Status' => $order->craftsman_status,
                'Allocated Craftsman' => $order->craftsman ? $order->craftsman->business_name : 'N/A',
                'Created By' => $order->createdBy ? $order->createdBy->name : 'N/A',
                'Created At' => $order->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $order->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Create CSV content
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($exportData) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            if ($exportData->isNotEmpty()) {
                fputcsv($file, array_keys($exportData->first()));
            }

            // Add data rows
            foreach ($exportData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download the work orders as an Excel file.
     */
    public function exportWorkOrders(Request $request)
    {
        $filename = 'WorkOrders_' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(new AdminWorkOrderExport($request), $filename);
    }

    public function sendUndoOtp(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'superadmin_id' => 'required|exists:process_owners,id',
            'delivery_method' => 'nullable|string|in:sms,whatsapp'
        ]);

        $superAdmin = ProcessOwner::where('role', 'super_admin')->findOrFail($request->superadmin_id);
        
        $otp = rand(100000, 999999);
        $cacheKey = "undo_otp_admin_{$workOrder->id}";
        Cache::put($cacheKey, $otp, 600); // 10 minutes

        $method = $request->delivery_method ?? 'sms';

        if ($superAdmin->mobile_no) {
            if ($method === 'whatsapp') {
                $this->sendWhatsAppMsg($superAdmin->mobile_no, $otp);
                return response()->json(['success' => true, 'message' => 'OTP sent to SuperAdmin via WhatsApp successfully.']);
            } else {
                $this->sendSMSMsg($superAdmin->mobile_no, $otp);
                return response()->json(['success' => true, 'message' => 'OTP sent to SuperAdmin via SMS successfully.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'SuperAdmin does not have a valid mobile number.']);
    }

    public function undo(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->admin_undo_count >= 1) {
            $request->validate([
                'otp' => 'required|numeric'
            ]);
            
            $cacheKey = "undo_otp_admin_{$workOrder->id}";
            $cachedOtp = Cache::get($cacheKey);
            
            if (!$cachedOtp || $cachedOtp != $request->otp) {
                return back()->with('error', 'Invalid or expired OTP. Please request a new one.');
            }
            Cache::forget($cacheKey);
        }

        $this->performUndo($workOrder);
        
        $workOrder->admin_undo_count += 1;
        $workOrder->save();

        return back()->with('success', 'Work Order status undone successfully.');
    }

    public function sendReturnOtp(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'superadmin_id' => 'required|exists:process_owners,id',
            'delivery_method' => 'nullable|string|in:sms,whatsapp'
        ]);

        $superAdmin = ProcessOwner::where('role', 'super_admin')->findOrFail($request->superadmin_id);
        
        $otp = rand(100000, 999999);
        $cacheKey = "return_otp_admin_{$workOrder->id}";
        Cache::put($cacheKey, $otp, 600); // 10 minutes

        $method = $request->delivery_method ?? 'sms';

        if ($superAdmin->mobile_no) {
            if ($method === 'whatsapp') {
                $this->sendWhatsAppMsg($superAdmin->mobile_no, $otp);
                return response()->json(['success' => true, 'message' => 'Return OTP sent via WhatsApp successfully.']);
            } else {
                $this->sendSMSMsg($superAdmin->mobile_no, $otp);
                return response()->json(['success' => true, 'message' => 'Return OTP sent via SMS successfully.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'SuperAdmin does not have a valid mobile number.']);
    }

    public function processReturn(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'return_due_date' => 'required|date',
            'return_note' => 'nullable|string',
            'damaged_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);

        if ($workOrder->admin_return_count >= 1) {
            $request->validate([
                'otp' => 'required|numeric'
            ]);
            
            $cacheKey = "return_otp_admin_{$workOrder->id}";
            $cachedOtp = Cache::get($cacheKey);
            
            if (!$cachedOtp || $cachedOtp != $request->otp) {
                return back()->with('error', 'Invalid or expired OTP. Please request a new one.');
            }
            Cache::forget($cacheKey);
        }

        if ($workOrder->status == 'for_approval') {
            $workOrder->status = 'in_process';
            $workOrder->craftsman_status = 'in_process';
        }

        if ($request->hasFile('damaged_image')) {
            $file = $request->file('damaged_image');
            $filePath = $file->store('damaged_images', 'public');
            $workOrder->damaged_image = $filePath;
        }

        $workOrder->return_due_date = $request->return_due_date;
        $workOrder->return_note = $request->return_note;
        $workOrder->admin_return_count += 1;
        $workOrder->save();

        return back()->with('success', 'Work Order returned successfully.');
    }

    private function performUndo(WorkOrder $workOrder)
    {
        if (strtolower($workOrder->status) === 'completed') {
            $workOrder->status = 'for_approval';
            $workOrder->craftsman_status = 'completed'; // Keeps craftsman at completed while admin re-evaluates
        } elseif (strtolower($workOrder->status) === 'for_approval') {
            $workOrder->status = 'in_process';
            $workOrder->craftsman_status = 'in_process';
        } elseif (strtolower($workOrder->status) === 'in_process' || strtolower($workOrder->craftsman_status) === 'in_process') {
            $workOrder->status = 'allocated';
            $workOrder->craftsman_status = 'allocated';
        }
    }

    private function sendSMSMsg($phone, $otp)
    {
        $phone = trim($phone);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        $authKey = '501083AjcyWEDYv69ba6085P1';
        $templateId = '69ba5f87a27ca7c5ac011655';

        $payload = [
            'template_id' => $templateId,
            'short_url'   => '0',
            'recipients'  => [
                [
                    'mobiles' => $phone,
                    'var'     => (string)$otp
                ]
            ]
        ];

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'authkey' => $authKey,
            'accept'  => 'application/json',
        ])->post('https://api.msg91.com/api/v5/flow/', $payload);

        Log::info('MSG91 Undo OTP Sent:', [
            'phone' => $phone,
            'response' => $response->json()
        ]);

        return $response->successful();
    }

    private function sendWhatsAppMsg($phone, $otp)
    {
        $phone = trim($phone);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        // Note: Using SMS flow as fallback for WhatsApp since WhatsApp API is not yet implemented in the codebase.
        // Update this to MSG91 WhatsApp API endpoint and template once configured.
        Log::warning('WhatsApp OTP requested but API not configured. Falling back to SMS flow.', ['phone' => $phone]);
        return $this->sendSMSMsg($phone, $otp);
    }
}
