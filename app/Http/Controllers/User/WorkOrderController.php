<?php

namespace App\Http\Controllers\User;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkOrderController extends Controller
{
    // Define the default number of items per page
    private const DEFAULT_PER_PAGE = 10;
    
    // Define available page size options
    private const PAGE_SIZE_OPTIONS = [5, 10, 15, 20, 25, 30, 40, 50];
    
    /**
     * Display a listing of the work orders.
     */
    public function index(Request $request)
    {
        // Handle search and filtering
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $categoryFilter = $request->get('category_filter');
        $subcategoryFilter = $request->get('subcategory_filter');
        
        // Validate sort parameters
        $allowedSortColumns = ['id', 'work_order_number', 'customer_name', 'product_name', 'quantity', 'due_date', 'status'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }
        
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        if ($request->ajax() && $request->has('tab')) {
            return $this->loadWorkOrdersAjax($request);
        }
        
        $perPage = $request->get('per_page', self::DEFAULT_PER_PAGE);
        if (!in_array($perPage, self::PAGE_SIZE_OPTIONS)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }
        
        // Get the current user's BP code
        $user = auth()->guard('web')->user();
        $userBpCode = $user->bp_code ?? null;
        $currentUserId = $user->id;
        
        // For users, show only work orders they created
        $allOrdersQuery = WorkOrder::with(['product.images'])
            ->where('bp_code', $userBpCode)
            ->where('creator_type', 'user')
            ->where('created_by', $currentUserId);
        
        // Apply search if present
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $allOrdersQuery->where(function($q) use ($searchTerm) {
                $q->where('work_order_number', 'LIKE', $searchTerm)
                  ->orWhere('customer_name', 'LIKE', $searchTerm)
                  ->orWhere('product_name', 'LIKE', $searchTerm);
            });
        }
        
        // Apply Category filter
        if ($categoryFilter) {
            $allOrdersQuery->where('product_category_id', $categoryFilter);
        }

        // Apply Subcategory filter
        if ($subcategoryFilter) {
            $allOrdersQuery->where('subcategory_id', $subcategoryFilter);
        }

        // Apply sorting
        $allOrdersQuery->orderBy($sortBy, $sortOrder);
        
        // Split orders by status for different tabs
        $newOrders = $allOrdersQuery->clone()->where('status', 'new')->paginate($perPage, ['*'], 'new_orders_page');
        $allocatedOrders = $allOrdersQuery->clone()->where('status', 'allocated')->paginate($perPage, ['*'], 'allocated_orders_page');
        $inProcessOrders = $allOrdersQuery->clone()->where('craftsman_status', 'in_process')->paginate($perPage, ['*'], 'in_process_orders_page');
        $completedOrders = $allOrdersQuery->clone()->where('craftsman_status', 'completed')->paginate($perPage, ['*'], 'completed_orders_page');
        $rejectedOrders = $allOrdersQuery->clone()->where('craftsman_status', 'rejected')->paginate($perPage, ['*'], 'rejected_orders_page');
        
        $categories = ProductCategory::orderBy('name')->get();
        $subcategories = [];
        if ($categoryFilter) {
            $subcategories = ProductSubcategory::where('product_category_id', $categoryFilter)->orderBy('name')->get();
        } else {
            $subcategories = ProductSubcategory::orderBy('name')->get();
        }
        
        return view('user.work-order.index', compact(
            'newOrders', 'allocatedOrders', 'completedOrders', 'inProcessOrders', 'rejectedOrders', 'search', 'sortBy', 'sortOrder',
            'categories', 'subcategories', 'categoryFilter', 'subcategoryFilter'
        ));
    }

    /**
     * Load work orders via AJAX for tabs.
     */
    private function loadWorkOrdersAjax(Request $request)
    {
        $tab = $request->get('tab');
        $search = $request->get('search', '');
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', self::DEFAULT_PER_PAGE);
        $categoryFilter = $request->get('category_filter');
        $subcategoryFilter = $request->get('subcategory_filter');
        
        // Validate sort parameters
        $allowedSortColumns = ['id', 'work_order_number', 'customer_name', 'product_name', 'quantity', 'due_date', 'status'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }
        
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        if (!in_array($perPage, self::PAGE_SIZE_OPTIONS)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }
        
        // Get the current user's BP code
        $user = auth()->guard('web')->user();
        $userBpCode = $user->bp_code ?? null;
        $currentUserId = $user->id;
        
        $query = WorkOrder::with(['product.images'])
            ->where('bp_code', $userBpCode)
            ->where('creator_type', 'user')
            ->where('created_by', $currentUserId);
        
        // Apply search if present
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('work_order_number', 'LIKE', $searchTerm)
                  ->orWhere('customer_name', 'LIKE', $searchTerm)
                  ->orWhere('product_name', 'LIKE', $searchTerm);
            });
        }
        
        // Apply Category filter
        if ($categoryFilter) {
            $query->where('product_category_id', $categoryFilter);
        }

        // Apply Subcategory filter
        if ($subcategoryFilter) {
            $query->where('subcategory_id', $subcategoryFilter);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        // Filter by status based on tab
        switch($tab) {
            case 'new-orders':
                $workOrders = $query->where('status', 'new')->paginate($perPage);
                break;
            case 'allocated-orders':
                $workOrders = $query->where('status', 'allocated')->paginate($perPage);
                break;
            case 'in-process-orders':
                $workOrders = $query->where('craftsman_status', 'in_process')->paginate($perPage);
                break;
            case 'completed-orders':
                $workOrders = $query->where('craftsman_status', 'completed')->paginate($perPage);
                break;
            case 'rejected-orders':
                $workOrders = $query->where('craftsman_status', 'rejected')->paginate($perPage);
                break;
            default:
                $workOrders = $query->paginate($perPage);
                break;
        }
        
        return response()->json([
            'html' => view('user.work-order.partials.orders-table', compact('workOrders'))->render(),
            'pagination' => (string) $workOrders->links()
        ]);
    }

    /**
     * Show the form for creating a new work order.
     */
    public function create()
    {
        $user = auth()->guard('web')->user();
        $buyer = $user->buyer; // Get the associated buyer
        $userBpCode = $user->bp_code ?? '';
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('user.work-order.create', compact('buyer', 'categories'));
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
            
            if ($existingProduct) {
                // Normalize: Use the actual product_code if a design_code was provided
                $finalProductCode = $existingProduct->product_code;
                
                if ($existingProduct->images->count() > 0) {
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

        // 2. Get the current user's BP code and set customer name
        $user = auth()->guard('web')->user();
        $bpCode = $user->bp_code ?? '';
        
        // Get customer name from the user's associated buyer
        $customerName = $user->buyer->business_name ?? $request->customer_name;

        // 3. Generate work order number (System ID)
        $workOrderNumber = WorkOrder::generateWorkOrderNumber();

        // 4. Create record preserving all requested fields (without craftsman fields)
        $workOrder = WorkOrder::create([
            'work_order_number' => $workOrderNumber,
            'product_image' => $productImage,
            'bp_code' => $bpCode, // Auto-set from user's BP
            'customer_name' => $customerName, // Auto-set from user's buyer
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
            'product_code' => $finalProductCode, // Used generated or existing code
            'relabel_code' => $request->relabel_code,
            'product_name' => $request->product_name,
            // Note: No craftsman_due_date and narration_craftsman for user
            'narration_admin' => $request->narration_admin,
            'status' => 'new', // Goes directly to new orders for admin/superadmin
            'created_by' => $user->id, // Track who created the work order
            'creator_type' => 'user',
            'creator_user_code' => $user->user_code,
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

        return redirect()->route('user.work-order.index', ['tab' => 'new-orders'])
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
        
        // Load product with images for design display
        $product = null;
        if ($workOrder->product_code) {
            $product = \App\Models\Product::with('images')
                ->where('product_code', $workOrder->product_code)
                ->orWhere('design_code', $workOrder->product_code)
                ->first();
        }
        
        return view('user.work-order.show', compact('workOrder', 'product'));
    }

    /**
     * Display the print view for the specified work order.
     */
    public function print(WorkOrder $workOrder)
    {
        return view('user.work-order.print', compact('workOrder'));
    }

    /**
     * Show the form for editing the specified work order.
     */
    public function edit(WorkOrder $workOrder)
    {
        $workOrder->load(['productCategory', 'subcategoryRelation']);
        $user = auth()->guard('web')->user();
        $userBpCode = $user->bp_code ?? '';
        $categories = ProductCategory::orderBy('name')->get();
        return view('user.work-order.edit', compact('workOrder', 'categories'));
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
        
        if (!empty($request->product_code) && !$request->hasFile('product_image')) {
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
            'bp_code' => $workOrder->bp_code, // Keep the original BP code
            'customer_name' => $workOrder->customer_name, // Keep the original customer name
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
            'product_code' => $request->product_code,
            'relabel_code' => $request->relabel_code,
            'product_name' => $request->product_name,
            // Note: No craftsman_due_date and narration_craftsman for user
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


        return redirect()->route('user.work-order.index')->with('success', 'Work Order updated successfully!');
    }

    /**
     * Remove the specified work order from storage.
     */
    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();
        return redirect()->route('user.work-order.index', ['tab' => 'new-orders'])
            ->with('success', 'Work Order deleted successfully!');
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
            ->where(function($query) use ($code) {
                $query->where('product_code', $code)
                      ->orWhere('design_code', $code);
            })
            ->where('design_status', 'Accepted')  // Only accepted designs
            ->first();
        
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found or not accepted']);
        }

        // Handle image URL construction - try different possible locations
        $fileUrl = null;
        $fileType = 'image';
        
        if ($product->images->first()) {
            $imagePath = $product->images->first()->path;
            
            if (!empty($imagePath)) {
                // Determine file type
                $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $fileType = 'pdf';
                }
                
                // Safe URL generation
                if (strpos($imagePath, 'storage/') === 0 || strpos($imagePath, 'images/') === 0 || strpos($imagePath, 'uploads/') === 0 || filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $fileUrl = asset($imagePath);
                } else {
                    $fileUrl = asset('storage/' . $imagePath);
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'product' => [
                'product_name' => $product->product_name,
                'design_code' => $product->design_code,
                'product_code' => $product->product_code,
                'product_image_url' => $fileUrl,
                'file_type' => $fileType,
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
}