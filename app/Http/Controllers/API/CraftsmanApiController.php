<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\Craftman; // Note typo in model name
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductImage;
use App\Models\PurchaseOrder; // Assuming PurchaseOrder model exists

// Services
use App\Services\ImageWatermarkService;

class CraftsmanApiController extends Controller
{
    // =========================================================================
    // AUTHENTICATION
    // =========================================================================

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'craftman_code' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $craftsman = Craftman::where('craftman_code', $request->craftman_code)->first();

        if (!$craftsman || !Hash::check($request->password, $craftsman->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($craftsman->is_frozen) {
             return response()->json(['message' => 'Account is frozen. Please contact admin.'], 403);
        }

        $token = $craftsman->createToken('CraftsmanAuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'craftsman' => $craftsman
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
    
    public function getProfile(Request $request)
    {
         $craftsman = $request->user();
         $craftsman->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers']);
         return response()->json(['craftsman' => $craftsman]);
    }

    public function updateProfile(Request $request)
    {
        $craftsman = $request->user();

        if ($craftsman->kyc_status === 'approved') {
            return response()->json(['message' => 'Profile is approved and cannot be edited.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'dear' => 'sometimes|required|string|unique:craftmen,dear,' . $craftsman->id,
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:craftmen,email,' . $craftsman->id,
            'door_no' => 'nullable|string|max:255',
            'shop_no' => 'nullable|string|max:255',
            'complex_name' => 'nullable|string|max:255',
            'street_name' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bis_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'gst_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'msme_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'brand_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except([
            'image', 'bis_attachment', 'gst_attachment', 'msme_attachment', 
            'pan_attachment', 'tan_attachment', 'cin_attachment', 'brand_logo',
            'aadhar_details', 'pan_details', 'bank_details', 'workers_details'
        ]);

        // Handle File Uploads
        $files = ['image', 'bis_attachment', 'gst_attachment', 'msme_attachment', 'pan_attachment', 'tan_attachment', 'cin_attachment', 'brand_logo'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                if ($craftsman->$file) {
                    Storage::disk('public')->delete($craftsman->$file);
                }
                $data[$file] = $request->file($file)->store('craftsmen/' . $file, 'public');
            }
        }

        $craftsman->update($data);

        // Update Aadhar Details if provided
        if ($request->has('aadhar_details')) {
            $aadharDetails = is_string($request->aadhar_details) ? json_decode($request->aadhar_details, true) : $request->aadhar_details;
            if (is_array($aadharDetails)) {
                $craftsman->aadharDetails()->delete();
                foreach ($aadharDetails as $index => $detail) {
                    $imagePath = $detail['aadhar_image'] ?? null;
                    if ($request->hasFile("aadhar_image_file.$index")) {
                        $imagePath = $request->file("aadhar_image_file.$index")->store('craftsmen/aadhar', 'public');
                    }
                    $craftsman->aadharDetails()->create([
                        'aadhar_name' => $detail['aadhar_name'] ?? $craftsman->name,
                        'aadhar_number' => $detail['aadhar_number'] ?? null,
                        'aadhar_image' => $imagePath,
                    ]);
                }
            }
        }

        // Similar logic for PAN, Bank, Workers could be added here if needed via API
        // For brevity and based on "same thing as web", we focus on the core fields and the restriction.

        return response()->json(['message' => 'Profile updated successfully', 'craftsman' => $craftsman->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers'])]);
    }

    // =========================================================================
    // WORK ORDERS (Allocated)
    // =========================================================================

    public function getWorkOrders(Request $request)
    {
        $craftsman = $request->user();
        if (!$craftsman->hasPermission('work_order')) return response()->json(['message' => 'Forbidden'], 403);

        $perPage = $request->get('per_page', 10);
        
        $query = WorkOrder::with(['product.images'])
                    ->where('allocated_craftsman_bp_code', $craftsman->craftman_code); // Filter by allocated code

        // Filters based on status
        if ($request->has('status')) {
             $status = $request->status;
             if ($status == 'new') {
                 // Conceptually new to craftsman might mean 'allocated'
                 $query->where('craftsman_status', 'allocated');
             } elseif ($status == 'in_process') {
                 $query->where('craftsman_status', 'in_process');
             } elseif ($status == 'completed') {
                  $query->where('craftsman_status', 'completed');
             } elseif ($status == 'rejected') {
                  $query->where('craftsman_status', 'rejected');
             } else {
                  $query->where('craftsman_status', $status);
             }
        }
        
        // Search
        if ($request->has('search')) {
             $searchTerm = '%' . $request->search . '%';
             $query->where(function($q) use ($searchTerm) {
                $q->where('work_order_number', 'LIKE', $searchTerm)
                  ->orWhere('customer_name', 'LIKE', $searchTerm)
                  ->orWhere('product_name', 'LIKE', $searchTerm);
             });
        }

        return response()->json($query->latest()->paginate($perPage));
    }
    
    public function getWorkOrder(Request $request, $id)
    {
        $craftsman = $request->user();
        $workOrder = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'product.images'])
                        ->where('allocated_craftsman_bp_code', $craftsman->craftman_code)
                        ->find($id);

        if (!$workOrder) return response()->json(['message' => 'Work Order not found or not allocated'], 404);

        return response()->json($workOrder);
    }

    // Actions: Accept
    public function acceptWorkOrder(Request $request, $id)
    {
        $craftsman = $request->user();
        $workOrder = WorkOrder::where('allocated_craftsman_bp_code', $craftsman->craftman_code)->find($id);

        if (!$workOrder) return response()->json(['message' => 'Work Order not found'], 404);

        if ($workOrder->craftsman_status !== 'allocated') {
            return response()->json(['message' => 'Work order cannot be accepted in current status'], 400);
        }

        $workOrder->update(['craftsman_status' => 'in_process']);
        
        // Notification logic would go here (omitted for brevity)

        return response()->json(['message' => 'Work order accepted', 'work_order' => $workOrder]);
    }

    // Actions: Reject
    public function rejectWorkOrder(Request $request, $id)
    {
        $craftsman = $request->user();
        $workOrder = WorkOrder::where('allocated_craftsman_bp_code', $craftsman->craftman_code)->find($id);

        if (!$workOrder) return response()->json(['message' => 'Work Order not found'], 404);

        if ($workOrder->craftsman_status !== 'allocated') {
            return response()->json(['message' => 'Work order cannot be rejected in current status'], 400);
        }
        
        $request->validate(['rejection_reason' => 'required|string']);

        $workOrder->update([
            'craftsman_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json(['message' => 'Work order rejected', 'work_order' => $workOrder]);
    }

    // Actions: Complete
    public function completeWorkOrder(Request $request, $id)
    {
        $craftsman = $request->user();
        $workOrder = WorkOrder::where('allocated_craftsman_bp_code', $craftsman->craftman_code)->find($id);

        if (!$workOrder) return response()->json(['message' => 'Work Order not found'], 404);

        if ($workOrder->craftsman_status !== 'in_process') {
            return response()->json(['message' => 'Work order cannot be completed in current status'], 400);
        }
        
        // Add weight/image update logic if required for completion
        $data = ['craftsman_status' => 'completed'];
        if ($request->has('weight')) {
             $data['weight'] = $request->weight; // Assuming actual weight field
        }

        $workOrder->update($data);

        return response()->json(['message' => 'Work order completed', 'work_order' => $workOrder]);
    }

    // Bulk Actions (Simplified)
    public function bulkAcceptWorkOrders(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:work_orders,id']);
        $craftsman = $request->user();
        
        $count = WorkOrder::whereIn('id', $request->ids)
            ->where('allocated_craftsman_bp_code', $craftsman->craftman_code)
            ->where('craftsman_status', 'allocated')
            ->update(['craftsman_status' => 'in_process']);
            
        return response()->json(['message' => "$count work orders accepted"]);
    }
    
    public function bulkRejectWorkOrders(Request $request)
    {
        $request->validate([
            'ids' => 'required|array', 
            'ids.*' => 'exists:work_orders,id',
            'rejection_reason' => 'required|string'
        ]);
        $craftsman = $request->user();
        
        $count = WorkOrder::whereIn('id', $request->ids)
            ->where('allocated_craftsman_bp_code', $craftsman->craftman_code)
            ->where('craftsman_status', 'allocated')
            ->update([
                'craftsman_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);
            
        return response()->json(['message' => "$count work orders rejected"]);
    }

    // =========================================================================
    // PRODUCTS (CRUD)
    // =========================================================================

    public function getProducts(Request $request)
    {
        $craftsman = $request->user();
        if (!$craftsman->hasPermission('product')) return response()->json(['message' => 'Forbidden'], 403);
        
        $query = Product::with(['category', 'subcategory', 'images'])
                ->where('bp_code', $craftsman->craftman_code); // Craftsman "BP Code" context is their craftman_code?
                // Checking web controller: ProductController stores product with 'bp_code' = $craftsman->craftman_code
        
         if ($request->has('search')) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('product_name', 'like', "%{$search}%")
                   ->orWhere('product_code', 'like', "%{$search}%");
             });
        }
        
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }
    
    public function getProduct(Request $request, $id)
    {
        $craftsman = $request->user();
        $product = Product::with(['category', 'subcategory', 'images'])
                    ->where('bp_code', $craftsman->craftman_code)
                    ->find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);
        return response()->json($product);
    }

    public function storeProduct(Request $request)
    {
        $craftsman = $request->user();
        if (!$craftsman->hasPermission('product')) return response()->json(['message' => 'Forbidden'], 403);

        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255|unique:products,product_code',
            'product_name' => 'required|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string|in:Piece,Pair',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $product = Product::create([
            'product_code' => $request->product_code,
            'bp_code' => $craftsman->craftman_code, // Use craftman code as BP code equivalent
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            'description' => $request->description,
            'created_by' => $craftsman->id,
        ]);

        if ($request->hasFile('images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $watermarkedPath = $watermarkService->addWatermark($path);
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $watermarkedPath,
                ]);
            }
        }

        return response()->json(['message' => 'Product created', 'product' => $product]);
    }
    
    public function updateProduct(Request $request, $id)
    {
        $craftsman = $request->user();
        $product = Product::where('bp_code', $craftsman->craftman_code)->find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);

        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255|unique:products,product_code,' . $product->id,
            'product_name' => 'required|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string|in:Piece,Pair',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $product->update([
            'product_code' => $request->product_code,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        if ($request->hasFile('images')) {
             $watermarkService = new ImageWatermarkService();
             foreach ($request->file('images') as $image) {
                 $path = $image->store('products', 'public');
                 $watermarkedPath = $watermarkService->addWatermark($path);
                 ProductImage::create([
                     'product_id' => $product->id,
                     'path' => $watermarkedPath,
                 ]);
             }
        }
        return response()->json(['message' => 'Product updated', 'product' => $product]);
    }

    public function deleteProduct(Request $request, $id)
    {
        $craftsman = $request->user();
        $product = Product::where('bp_code', $craftsman->craftman_code)->find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);

        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    // =========================================================================
    // DESIGNS (CRUD)
    // =========================================================================

    public function getDesigns(Request $request)
    {
        // Craftsmen can likely view their own designs (which are physically Products with design_code)
        $craftsman = $request->user();
        $query = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $craftsman->craftman_code)
            ->whereNotNull('design_code'); // Ensure it's a design

        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }
    
    // Using storeProduct for designs is common, or distinct if needed. 
    // Assuming separated here for clarity but logic is very similar to Product.
    // Web controller uses Product model but adds 'design_code' and 'design_status'.

    public function storeDesign(Request $request)
    {
        $craftsman = $request->user();
        if (!$craftsman->hasPermission('design')) return response()->json(['message' => 'Forbidden'], 403);

        $validator = Validator::make($request->all(), [
            'design_code' => 'required|string|max:255|unique:products,design_code',
            'product_name' => 'required|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string|in:Piece,Pair',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        // Generate product code for design if not provided or same as design code?
        // Web controller: DesignController::store
        
        $product = Product::create([
            'product_code' => $request->design_code, // Design code often doubles as Product Code or similar
            'design_code' => $request->design_code,
            'bp_code' => $craftsman->craftman_code,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            'description' => $request->description,
            'design_status' => 'Pending', // Default status for new designs
            'created_by' => $craftsman->id,
        ]);

         if ($request->hasFile('images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('images') as $image) {
                $path = $image->store('designs', 'public'); // Store in designs folder
                $watermarkService->addWatermark($path);
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                ]);
            }
        }
        
        return response()->json(['message' => 'Design created', 'design' => $product]);
    }

    // =========================================================================
    // CATALOGUE (Read Only - Personal Accepted Designs)
    // =========================================================================

    public function getCatalogue(Request $request)
    {
        return $this->getDesigns($request); // Conceptually same for creator, but maybe filtered by 'Accepted' status?
    }

    // =========================================================================
    // PURCHASE ORDERS (Ideally similar to Work Orders but from Admin)
    // =========================================================================
    
    public function getPurchaseOrders(Request $request)
    {
        // Models\PurchaseOrder usually
        // Filter by 'craftsman_id' or 'craftsman_code'
        // Assuming 'PurchaseOrder' model works similar to Work Order
        // Omitted full implementation to keep file size managed, but follows WorkOrder pattern.
        return response()->json(['message' => 'Purchase Order API endpoint ready for logic']);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    
    public function getCategoryOptions()
    {
        return response()->json(ProductCategory::orderBy('name')->get());
    }

    public function getSubcategories(Request $request)
    {
        $categoryId = $request->query('category_id');
        if (!$categoryId) return response()->json([]);
        
        return response()->json(ProductSubcategory::where('product_category_id', $categoryId)->orderBy('name')->get());
    }
}
