<?php

namespace App\Http\Controllers\SuperAdmin;

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
use App\Imports\WorkOrderImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WorkOrderController extends Controller
{
    // Define the default number of items per page
    private const DEFAULT_PER_PAGE = 10;

    // Define available page size options
    private const PAGE_SIZE_OPTIONS = [25, 50, 75, 100, 150, 200];


    public function create()
    {
        $buyers = Buyer::all();
        $categories = ProductCategory::orderBy('name')->get();
        return view('super-admin.work-order.create', compact('buyers', 'categories'));
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
            'weight_from' => 'required',
            'weight_to' => 'required',
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
            'creator_type' => 'super_admin',
            'created_by' => Auth::id(),
        ]);

        // 4. Handle multiple images upload
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

        return redirect()->route('super-admin.work-order.index', ['tab' => 'new-orders'])
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

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman']);

        // Load product with images for design display
        $product = null;
        if ($workOrder->product_code) {
            $product = \App\Models\Product::with('images')
                ->where('product_code', $workOrder->product_code)
                ->orWhere('design_code', $workOrder->product_code)
                ->first();
        }

        return view('super-admin.work-order.show', compact('workOrder', 'product'));
    }

    public function print(WorkOrder $workOrder)
    {
        return view('super-admin.work-order.print', compact('workOrder'));
    }

    public function allocateForm(WorkOrder $workOrder)
    {
        $craftsmen = Craftman::all();
        return view('super-admin.work-order.allocate', compact('workOrder', 'craftsmen'));
    }

    public function allocate(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status' => 'allocated',
            'craftsman_status' => 'allocated',
            'allocated_by' => Auth::guard('super_admin')->id(),
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

        return redirect()->route('super-admin.work-order.index', ['tab' => 'allocated-orders'])
            ->with('success', 'Work Order allocated successfully!');
    }

    public function bulkAllocate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_ids' => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
            'craftsman_due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $workOrderIds = $request->input('work_order_ids');
        $craftsmanBpCode = $request->input('allocated_craftsman_bp_code');

        $updateData = [
            'allocated_craftsman_bp_code' => $craftsmanBpCode,
            'status' => 'allocated',
            'craftsman_status' => 'allocated',
            'allocated_by' => Auth::guard('super_admin')->id() ?? Auth::id(),
        ];
        
        if ($request->filled('craftsman_due_date')) {
            $updateData['craftsman_due_date'] = $request->input('craftsman_due_date');
        }

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
                // We can send one notification for all, or one summary
                $count = count($workOrderIds);
                $message = "You have been allocated {$count} new Work Orders.";
                // Pass the first work order as reference, but with custom message
                $firstOrder = WorkOrder::find($workOrderIds[0]);
                $craftsman->notify(new \App\Notifications\WorkOrderAllocated($firstOrder, $message));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return redirect()->route('super-admin.work-order.index', ['tab' => 'allocated-orders'])
            ->with('success', count($workOrderIds) . ' Work Orders allocated successfully!');
    }

    public function bulkAllocateForm(Request $request)
    {
        $workOrderIds = $request->input('work_order_ids', []);

        if (empty($workOrderIds)) {
            return redirect()->back()->with('error', 'Please select at least one work order to allocate.');
        }

        // Allow new orders AND rejected orders (craftsman_status = 'rejected')
        $workOrders = WorkOrder::whereIn('id', $workOrderIds)
            ->where(function ($q) {
                $q->where('status', 'new')
                    ->orWhere('craftsman_status', 'rejected');
            })
            ->get();

        if ($workOrders->isEmpty()) {
            return redirect()->back()->with('error', 'No valid work orders selected for allocation.');
        }

        $craftsmen = Craftman::all();
        return view('super-admin.work-order.bulk-allocate', compact('workOrders', 'craftsmen'));
    }

    public function approve(WorkOrder $workOrder)
    {
        if ($workOrder->status !== 'for_approval') {
            return redirect()->back()->with('error', 'Work order is not ready for approval.');
        }

        $workOrder->update([
            'status' => 'completed',
            'approved_by' => Auth::guard('super_admin')->id() ?? Auth::id()
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

        return redirect()->route('super-admin.work-order.index', ['tab' => 'completed-orders'])
            ->with('success', 'Work Order approved and marked as completed!');
    }

    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_ids' => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $workOrderIds = $request->input('work_order_ids');

        WorkOrder::whereIn('id', $workOrderIds)
            ->where('status', 'for_approval')
            ->update([
                'status' => 'completed',
                'approved_by' => Auth::guard('super_admin')->id() ?? Auth::id()
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
                        ? "{$count} of your Work Orders have been completed and approved by SuperAdmin."
                        : "Your Work Order #{$lastOrder->work_order_number} has been completed and approved by SuperAdmin.";

                    $recipient->notify(new \App\Notifications\WorkOrderCompleted($lastOrder, $message));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify requester in bulk: ' . $e->getMessage());
            }
        }

        return redirect()->route('super-admin.work-order.index', ['tab' => 'completed-orders'])
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
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $workOrderIds = $request->input('work_order_ids');

        // Update all selected work orders to completed status
        // We allow completing from any status except already completed
        WorkOrder::whereIn('id', $workOrderIds)
            ->where('status', '!=', 'completed')
            ->update([
                'status' => 'completed',
                'craftsman_status' => 'completed',
                'approved_by' => Auth::guard('super_admin')->id() ?? Auth::id()
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
                        ? "{$count} of your Work Orders have been marked as completed by SuperAdmin."
                        : "Your Work Order #{$lastOrder->work_order_number} has been marked as completed by SuperAdmin.";

                    $recipient->notify(new \App\Notifications\WorkOrderCompleted($lastOrder, $message));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify requester for manual completion: ' . $e->getMessage());
            }
        }

        return redirect()->route('super-admin.work-order.index', ['tab' => 'completed-orders'])
            ->with('success', count($workOrderIds) . ' Work Orders marked as completed!');
    }

    public function reallocateForm(WorkOrder $workOrder)
    {
        $craftsmen = Craftman::all();
        return view('super-admin.work-order.reallocate', compact('workOrder', 'craftsmen'));
    }

    public function reallocate(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status' => 'allocated',
            'craftsman_status' => 'allocated',
            'allocated_by' => Auth::guard('super_admin')->id(),
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

        return redirect()->route('super-admin.work-order.index', ['tab' => 'allocated-orders'])
            ->with('success', 'Work Order reallocated successfully!');
    }

    public function edit(WorkOrder $workOrder)
    {
        $workOrder->load(['productCategory', 'subcategoryRelation']);
        $categories = ProductCategory::whereIn('id', function ($query) {
            $query->select('product_category_id')
                ->from('products')
                ->whereNull('bp_code');
        })->orWhereIn('name', function ($query) {
            $query->select('product_category')
                ->from('work_orders')
                ->whereNull('bp_code');
        })->orderBy('name')->get();
        $buyers = Buyer::all();
        return view('super-admin.work-order.edit', compact('workOrder', 'categories', 'buyers'));
    }

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
    $request->input('return_url') ?? route('super-admin.work-order.index', [
        'tab' => $request->tab ?? 'new-orders'
    ])
)->with('success', 'Work Order #' . $workOrder->work_order_number . ' updated successfully!');
    }

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();
        return redirect()->route('super-admin.work-order.index', ['tab' => 'new-orders'])
            ->with('success', 'Work Order deleted successfully!');
    }

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

    public function showUploadForm()
    {
        return view('super-admin.work-order.upload');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new WorkOrderImport(Auth::user(), 'super_admin');
            Excel::import($import, $request->file('file'));

            return redirect()->route('super-admin.work-order.index')
                ->with('success', $import->importedCount . ' Work Orders imported successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Validation failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

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
        // Eager load product images, work order images, buyer, and craftsman
        $activeTab = $request->get('tab', 'new-orders');

        // Define all queries
        $queries = [
            'new-orders' => WorkOrder::where('status', 'new'),
            'allocated-orders' => WorkOrder::where('status', 'allocated')->where('craftsman_status', '!=', 'in_process'),
            'for-approval-orders' => WorkOrder::where('status', 'for_approval'),
            'completed-orders' => WorkOrder::where('status', 'completed'),
            'in-process-orders' => WorkOrder::where('craftsman_status', 'in_process'),
            'rejected-orders' => WorkOrder::where('craftsman_status', 'rejected'),
            'overdue-orders' => WorkOrder::where('status', '!=', 'completed')
                ->where('craftsman_status', '!=', 'rejected')
                ->where(function ($q) {
                    $q->whereDate('craftsman_due_date', '<', now()->toDateString())
                        ->orWhere(function ($sq) {
                            $sq->whereDate('craftsman_due_date', now()->toDateString())
                                ->whereRaw('HOUR(NOW()) >= 12');
                        });
                }),
            'all-orders' => WorkOrder::query(),
        ];

        $counts = [];
        foreach ($queries as $tabKey => $query) {
            // Apply common filters to all count queries
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
            if ($bpCodeFilter) $query->where('bp_code', $bpCodeFilter);
            if ($categoryFilter) $query->where('product_category_id', $categoryFilter);
            if ($subcategoryFilter) $query->where('subcategory_id', $subcategoryFilter);
            if ($craftsmanFilter) $query->where('allocated_craftsman_bp_code', $craftsmanFilter);

            // Completed filter
            $completedFilter = $request->get('completed_filter');
            if ($tabKey === 'completed-orders' && $completedFilter) {
                $now = now();
                if ($completedFilter === 'day') {
                    $query->whereDate('updated_at', $now->toDateString());
                } elseif ($completedFilter === 'week') {
                    $query->whereBetween('updated_at', [$now->startOfWeek(), $now->endOfWeek()]);
                } elseif ($completedFilter === 'month') {
                    $query->whereMonth('updated_at', $now->month)
                          ->whereYear('updated_at', $now->year);
                }
            }

            // Get count for the badge
            $counts[$tabKey] = (clone $query)->count();

            // Paginate ONLY the active tab
            if ($tabKey === $activeTab) {
                $query->with(['product.images', 'images', 'buyer', 'craftsman'])->orderBy($sortBy, $sortOrder);
                $activeData = $query->paginate($perPage, ['*'], $tabKey . '_page')->withQueryString();
            }
        }

        // Assign paginated data to specific variables for backward compatibility in blade
        $newOrders = ($activeTab === 'new-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['new-orders'], $perPage);
        $allocatedOrders = ($activeTab === 'allocated-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['allocated-orders'], $perPage);
        $forApprovalOrders = ($activeTab === 'for-approval-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['for-approval-orders'], $perPage);
        $completedOrders = ($activeTab === 'completed-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['completed-orders'], $perPage);
        $inProcessOrders = ($activeTab === 'in-process-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['in-process-orders'], $perPage);
        $rejectedOrders = ($activeTab === 'rejected-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['rejected-orders'], $perPage);
        $overdueOrders = ($activeTab === 'overdue-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['overdue-orders'], $perPage);
        $allOrders = ($activeTab === 'all-orders') ? $activeData : new \Illuminate\Pagination\LengthAwarePaginator([], $counts['all-orders'], $perPage);

        // Get unique BP codes (with business names) for filters
        $bpCodes = DB::table('work_orders')
            ->select('work_orders.bp_code', 'buyers.business_name')
            ->leftJoin('buyers', 'work_orders.bp_code', '=', 'buyers.bp_code')
            ->whereNotNull('work_orders.bp_code')
            ->distinct()
            ->orderBy('work_orders.bp_code')
            ->get();
        $categories = ProductCategory::whereIn('id', function ($query) {
            $query->select('product_category_id')
                ->from('products')
                ->whereNull('bp_code');
        })->orWhereIn('name', function ($query) {
            $query->select('product_category')
                ->from('work_orders')
                ->whereNull('bp_code');
        })->orderBy('name')->get();

        $craftsmen = Craftman::orderBy('name')->get();

        $subcategories = [];
        if ($categoryFilter) {
            $subcategories = ProductSubcategory::where('product_category_id', $categoryFilter)->orderBy('name')->get();
        } else {
            $subcategories = ProductSubcategory::orderBy('name')->get();
        }

        return view('super-admin.work-order.index', compact(
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
            'counts'
        ));
    }

    private function exportWorkOrders(Request $request)
    {
        $tab = $request->get('tab', 'new-orders');
        $search = $request->get('search');
        $bpCodeFilter = $request->get('bp_code_filter');
        $categoryFilter = $request->get('category_filter');
        $workOrderIds = $request->get('work_order_ids');

        $query = WorkOrder::query();

        // If specific IDs are provided, only export those
        if (!empty($workOrderIds)) {
            if (is_string($workOrderIds)) {
                $workOrderIds = explode(',', $workOrderIds);
            }
            $query->whereIn('id', $workOrderIds);
        } else {
            switch ($tab) {
                case 'new-orders':
                    $query->where('status', 'new');
                    break;
                case 'allocated-orders':
                    $query->where('status', 'allocated')
                        ->where('craftsman_status', '!=', 'in_process');
                    break;
                case 'for-approval-orders':
                    $query->where('status', 'for_approval');
                    break;
                case 'completed-orders':
                    $query->where('status', 'completed');
                    break;
                case 'in-process-orders':
                    $query->where('craftsman_status', 'in_process');
                    break;
                case 'rejected-orders':
                    $query->where('craftsman_status', 'rejected');
                    break;
                default:
                    $query->where('status', 'new');
            }
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
                    ->orWhere('narration_admin', 'LIKE', $searchTerm);
            });
        }

        // Apply BP Code filter
        if ($bpCodeFilter) {
            $query->where('bp_code', $bpCodeFilter);
        }

        // Apply Category filter
        if ($categoryFilter) {
            $query->where('product_category', $categoryFilter);
        }

        $workOrders = $query->get();

        // Create CSV content
        $headers = [
            'Work Order Number',
            'BP Code',
            'Customer Name',
            'Reference No',
            'Product Code',
            'Product Name',
            'Category',
            'Subcategory',
            'Quantity',
            'Type',
            'Size',
            'Length',
            'Weight From',
            'Weight To',
            'Hallmark',
            'Rodium',
            'Hook',
            'Stone',
            'Enamel',
            'Due Date',
            'Craftsman Due Date',
            'Status',
            'Craftsman Status',
            'Allocated Craftsman BP Code',
            'Narration (Craftsman)',
            'Narration (Admin)',
            'Created At',
            'Created By'
        ];

        $csvData = [];
        $csvData[] = $headers;

        foreach ($workOrders as $order) {
            $csvData[] = [
                $order->work_order_number,
                $order->bp_code,
                $order->customer_name,
                $order->reference_no,
                $order->product_code,
                $order->product_name,
                $order->productCategory ? $order->productCategory->name : 'N/A',
                $order->subcategoryRelation ? $order->subcategoryRelation->name : 'N/A',
                $order->quantity,
                $order->type,
                $order->size,
                $order->length,
                $order->weight_from,
                $order->weight_to,
                $order->hallmark,
                $order->rodium,
                $order->hook,
                $order->stone,
                $order->enamel,
                $order->due_date ? $order->due_date->format('Y-m-d') : 'N/A',
                $order->craftsman_due_date ? $order->craftsman_due_date->format('Y-m-d') : 'N/A',
                $order->status,
                $order->craftsman_status,
                $order->allocated_craftsman_bp_code,
                $order->narration_craftsman,
                $order->narration_admin,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->createdBy ? ($order->createdBy->name ?? $order->createdBy->business_name ?? 'N/A') : 'N/A'
            ];
        }

        // Generate CSV
        $filename = 'work_orders_' . $tab . '_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');

        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV data
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }

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

        // Validate sort parameters
        $allowedSortColumns = ['id', 'work_order_number', 'customer_name', 'product_name', 'quantity', 'due_date', 'status', 'bp_code', 'product_category', 'reference_no', 'type', 'size', 'length', 'weight_from', 'weight_to', 'hallmark', 'rodium', 'hook', 'stone', 'enamel', 'craftsman_due_date', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        if (!in_array($perPage, self::PAGE_SIZE_OPTIONS)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $query = null;

        switch ($tab) {
            case 'new-orders':
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('status', 'new');
                break;
            case 'allocated-orders':
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('status', 'allocated')
                    ->where('craftsman_status', '!=', 'in_process');
                break;
            case 'for-approval-orders':
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('status', 'for_approval');
                break;
            case 'completed-orders':
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('status', 'completed');
                break;
            case 'in-process-orders':
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('craftsman_status', 'in_process');
                break;
            case 'rejected-orders':
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('craftsman_status', 'rejected');
                break;
            case 'overdue-orders':
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('status', '!=', 'completed')
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
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman']);
                break;
            default:
                $query = WorkOrder::with(['product.images', 'buyer', 'craftsman'])->where('status', 'new');
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
                    ->orWhere('narration_admin', 'LIKE', $searchTerm);
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

        $orders = $query->paginate($perPage, ['*'], 'orders_page', $page);

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
     * Show the bulk upload form
     */
    public function showBulkUpload()
    {
        return view('super-admin.work-order.bulk-upload');
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
            $user = auth('process_owner')->user(); // Use the correct guard for super admin
            $importInstance = new \App\Imports\OrderListImport($extractPath, $user, 'super_admin');

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

            return redirect()->route('super-admin.work-order.index')
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
            \Illuminate\Support\Facades\Log::error('Import Error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());


            return redirect()->back()->with('error', 'Import Error: ' . $e->getMessage());
        }
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

        return view('super-admin.work-order.bulk-print', compact('workOrders'));
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
        $newWorkOrder->approved_by = null;
        $newWorkOrder->rejection_reason = null;
        $newWorkOrder->due_date = today()->addDays(7); // Set a new due date (7 days from now)
        $newWorkOrder->craftsman_due_date = today()->addDays(14); // Set a new craftsman due date (14 days from now)

        // Generate a new work order number
        $newWorkOrder->work_order_number = WorkOrder::generateWorkOrderNumber();

        // Save the new work order
        $newWorkOrder->save();

        // For copied work orders, we only copy the original product image, not completion proof images
        // This prevents craftsmen from seeing completion proof images as reference images

        // Copy the main product image if it exists
        if ($workOrder->product_image) {
            $sourcePath = public_path($workOrder->product_image);

            if (file_exists($sourcePath)) {
                // Create a new filename to avoid conflicts
                $pathInfo = pathinfo($workOrder->product_image);
                $newFileName = $pathInfo['filename'] . '_copy_' . time() . '_' . rand(1000, 9999) . '.' . $pathInfo['extension'];
                $newFilePath = $pathInfo['dirname'] . '/' . $newFileName;
                $newFullPath = public_path($newFilePath);

                // Copy the main product image file to the new location
                if (copy($sourcePath, $newFullPath)) {
                    $newWorkOrder->update(['product_image' => $newFilePath]);
                }
            }
        }

        // We intentionally do NOT copy the WorkOrderImage records (craftsman completion proof images)
        // This prevents craftsmen from seeing completion proof images when working on the new order

        return redirect()->route('super-admin.work-order.edit', $newWorkOrder)
            ->with('success', 'Work Order copied successfully! Please review and update as needed.');
    }
}
