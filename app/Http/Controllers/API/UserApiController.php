<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Models
use App\Models\User;
use App\Models\Buyer;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductImage;

// Services
use App\Services\ImageWatermarkService;

class UserApiController extends Controller
{
    // =========================================================================
    // AUTHENTICATION
    // =========================================================================

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->is_frozen) {
             return response()->json(['message' => 'Account is frozen. Please contact admin.'], 403);
        }

        $token = $user->createToken('UserAuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // =========================================================================
    // PROFILE (Read Only)
    // =========================================================================

    public function getProfile(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    // =========================================================================
    // WORK ORDERS
    // =========================================================================

    public function getWorkOrders(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission('work_order')) return response()->json(['message' => 'Forbidden'], 403);

        $perPage = $request->get('per_page', 10);
        
        // Users can usually only see work orders for their Buyer (bp_code) 
        // AND potentially only their own creations?
        // Checking User/WorkOrderController:
        // $query = WorkOrder::with(['product.images'])->where('bp_code', $user->bp_code);
        // It seems they see everything for their BP code, similar to Key Users.
        
        $query = WorkOrder::with(['product.images'])
                    ->where('bp_code', $user->bp_code);

        // Filters
        if ($request->has('status')) {
             if ($request->status == 'in_process') {
                 $query->where('craftsman_status', 'in_process');
             } elseif ($request->status == 'completed') {
                 $query->where('craftsman_status', 'completed');
             } elseif ($request->status == 'rejected') {
                 $query->where('craftsman_status', 'rejected');
             } else {
                 $query->where('status', $request->status);
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

        $workOrders = $query->latest()->paginate($perPage);

        return response()->json($workOrders);
    }
    
    public function getWorkOrder(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('work_order')) return response()->json(['message' => 'Forbidden'], 403);

        $workOrder = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'product.images'])
                        ->where('bp_code', $user->bp_code)
                        ->find($id);

        if (!$workOrder) {
            return response()->json(['message' => 'Work Order not found'], 404);
        }

        return response()->json($workOrder);
    }

    public function storeWorkOrder(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission('work_order')) return response()->json(['message' => 'Forbidden'], 403);
        
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'product_name' => 'required|string|max:255',
            'due_date' => 'required|date',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Logic for Flow 1: If product_code matches an existing product, copy its image
        $finalProductCode = $request->product_code;
        $productImage = null;
        
        if (!empty($finalProductCode)) {
            $existingProduct = Product::with('images')
                ->where('product_code', $finalProductCode)
                ->orWhere('design_code', $finalProductCode)
                ->first();
            
            if ($existingProduct) {
                $finalProductCode = $existingProduct->product_code;
                if ($existingProduct->images->count() > 0) {
                    $existingImage = $existingProduct->images->first();
                    $sourceImagePath = storage_path('app/public/' . $existingImage->path);
                    
                    if (file_exists($sourceImagePath)) {
                        $imageName = time() . '_copied_from_product_' . basename($existingImage->path);
                        $destinationPath = public_path('images/work-orders/' . $imageName);
                        
                        // Ensure directory exists
                        if (!file_exists(dirname($destinationPath))) {
                            mkdir(dirname($destinationPath), 0755, true);
                        }
                        
                        copy($sourceImagePath, $destinationPath);
                        $productImage = 'images/work-orders/' . $imageName;
                        
                        // Apply watermark
                        $watermarkService = new ImageWatermarkService();
                        $watermarkService->addWatermark($productImage, true);
                    }
                }
            }
        } else {
             // Logic for Flow 2: If Product Code is missing, generate OOXXXX style code
             $finalProductCode = $this->generateUniqueProductCode();
        }

        // Handle direct image upload (override)
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/work-orders'), $imageName);
            $productImage = 'images/work-orders/' . $imageName;

            $watermarkService = new ImageWatermarkService();
            $watermarkService->addWatermark($productImage, true);
        }

        // Generate Work Order Number
        $workOrderNumber = WorkOrder::generateWorkOrderNumber();

        // Create Record
        $workOrder = WorkOrder::create([
            'work_order_number' => $workOrderNumber,
            'product_image' => $productImage,
            'bp_code' => $user->bp_code, 
            'customer_name' => $request->customer_name ?? $user->buyer->business_name,
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
            'product_code' => $finalProductCode, 
            'relabel_code' => $request->relabel_code,
            'product_name' => $request->product_name,
            'narration_admin' => $request->narration_admin,
            'status' => 'new', 
            'created_by' => $user->id, 
            'creator_type' => 'user', 
            'creator_user_code' => $user->user_code,
        ]);

        return response()->json(['message' => 'Work Order created successfully', 'work_order' => $workOrder]);
    }

    public function updateWorkOrder(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('work_order')) return response()->json(['message' => 'Forbidden'], 403);

        $workOrder = WorkOrder::where('bp_code', $user->bp_code)->find($id);

        if (!$workOrder) {
            return response()->json(['message' => 'Work Order not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'product_name' => 'required|string|max:255',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $productImage = $workOrder->product_image;

        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/work-orders'), $imageName);
            $productImage = 'images/work-orders/' . $imageName;

            $watermarkService = new ImageWatermarkService();
            $watermarkService->addWatermark($productImage, true);
        }

        $workOrder->update([
            'product_image' => $productImage,
            'customer_name' => $request->customer_name,
            'reference_no' => $request->reference_no,
            'due_date' => $request->due_date,
            'product_category_id' => $request->product_category_id,
            'product_category' => $request->product_category_id ? ProductCategory::find($request->product_category_id)->name : null,
            'subcategory_id' => $request->subcategory_id,
            'subcategory' => $request->subcategory_id ? ProductSubcategory::find($request->subcategory_id)->name : null,
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
            'product_name' => $request->product_name,
            'narration_admin' => $request->narration_admin,
        ]);

         return response()->json(['message' => 'Work Order updated successfully', 'work_order' => $workOrder]);
    }

    public function deleteWorkOrder(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission('work_order')) return response()->json(['message' => 'Forbidden'], 403);

        $workOrder = WorkOrder::where('bp_code', $user->bp_code)->find($id);

        if (!$workOrder) {
            return response()->json(['message' => 'Work Order not found'], 404);
        }
        
        $workOrder->delete();
        return response()->json(['message' => 'Work Order deleted successfully']);
    }

    public function getProductDetails(Request $request) 
    {
        $code = $request->query('product_code');
        if (!$code) return response()->json(['success' => false, 'message' => 'Code required']);

        $product = Product::with('images')
            ->where(function($q) use ($code) {
                $q->where('product_code', $code)->orWhere('design_code', $code);
            })
            ->where('design_status', 'Accepted')
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found or not accepted']);
        }
        
        $imageUrl = null;
        if ($product->images->first()) {
             $imagePath = $product->images->first()->path;
             if (!empty($imagePath)) {
                 $imageUrl = asset('storage/' . $imagePath);
             }
        }

        return response()->json([
            'success' => true,
            'product' => [
                'product_name' => $product->product_name,
                'design_code' => $product->design_code,
                'product_code' => $product->product_code,
                'product_image_url' => $imageUrl,
                'product_category_id' => $product->product_category_id,
                'subcategory_id' => $product->product_subcategory_id,
            ]
        ]);
    }

    private function generateUniqueProductCode()
    {
        $latestOrder = WorkOrder::where('product_code', 'LIKE', 'OO%')
            ->orderBy('product_code', 'desc')
            ->first();

        if (!$latestOrder) {
            return 'OO001';
        }

        $numericPart = preg_replace('/[^0-9]/', '', $latestOrder->product_code);
        $nextNumber = intval($numericPart) + 1;
        return 'OO' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // PRODUCTS
    // =========================================================================

    public function getProducts(Request $request)
    {
        $user = $request->user();
        // Permission check? Web controller does strict check? Assuming yes.
        // User model hasPermission('product')? Actually User permission list in User.php only listed 'work_order', 'profile_management', 'reports', 'settings', 'freeze_account'.
        // But web routes for Product controller exist for User.
        // Let's check permissions. Users might not have explicit 'product' permission in array, 
        // but maybe access is implicitly granted or controlled by something else?
        // In User/ProductController, there is no explicit permission check in code I've seen, but route group might have middleware?
        // Route group in web.php has middleware `auth:web`, `check.account.frozen`.
        // So standard users can access products.
        // I will allow access without explicit 'product' permission check unless specifically required, 
        // OR reuse 'work_order' permission if that implies broader access?
        // Safest is to allow access if authenticated and not frozen, and filter by BP code.
        
        $query = Product::with(['category', 'subcategory', 'images'])
                ->where('bp_code', $user->bp_code);

        if ($request->has('search')) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('product_name', 'like', "%{$search}%")
                   ->orWhere('product_code', 'like', "%{$search}%");
             });
        }
        
        $products = $query->latest()->paginate($request->get('per_page', 10));
        return response()->json($products);
    }
    
    public function getProduct(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::with(['category', 'subcategory', 'images'])
                    ->where('bp_code', $user->bp_code)
                    ->find($id);
                    
        if (!$product) return response()->json(['message' => 'Product not found'], 404);
        
        return response()->json($product);
    }

    public function storeProduct(Request $request)
    {
        $user = $request->user();
        
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
            'bp_code' => $user->bp_code,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            'description' => $request->description,
            'created_by' => $user->id, // Tracking Creator (User ID)
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

        return response()->json(['message' => 'Product created successfully', 'product' => $product]);
    }

    public function updateProduct(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::where('bp_code', $user->bp_code)->find($id);
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

        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    public function deleteProduct(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::where('bp_code', $user->bp_code)->find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);

        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    // =========================================================================
    // DESIGNS (Global - Read Only)
    // =========================================================================

    public function getDesigns(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'images'])
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->notFromFrozenAccounts();

        if ($request->has('search')) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('product_name', 'like', "%{$search}%")
                   ->orWhere('design_code', 'like', "%{$search}%")
                   ->orWhere('product_code', 'like', "%{$search}%");
             });
        }
        
        return response()->json($query->latest()->paginate($request->get('per_page', 15)));
    }

    public function getDesign(Request $request, $id)
    {
        $product = Product::with(['category', 'subcategory', 'images'])->find($id);
        
        if (!$product || !$product->design_code || $product->design_status !== 'Accepted') {
            return response()->json(['message' => 'Design not found'], 404);
        }

        return response()->json($product);
    }

    // =========================================================================
    // CATALOGUE (Personal Accepted Designs - Read Only)
    // =========================================================================
    
    public function getCatalogue(Request $request) 
    {
        $user = $request->user();
        $query = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $user->bp_code)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->notFromFrozenAccounts();
            
        // Unlocked check (usually similar to Buyer/KeyUser)
        $query->where(function($q) {
            $q->whereNull('design_view_unlocked_until')
              ->orWhere('design_view_unlocked_until', '>=', now());
        });
        
        return response()->json($query->latest()->paginate($request->get('per_page', 15)));
    }

    public function getCatalogueItem(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $user->bp_code)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->find($id);
            
        if (!$product) return response()->json(['message' => 'Item not found in catalogue'], 404);
        
        return response()->json($product);
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
