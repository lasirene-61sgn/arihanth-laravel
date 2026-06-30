<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Models
use App\Models\Buyer;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductImage;
use App\Models\KeyUser;
use App\Models\User;

// Services
use App\Services\ImageWatermarkService;

class BuyerApiController extends Controller
{
    // =========================================================================
    // AUTHENTICATION
    // =========================================================================

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $buyer = Buyer::where('email', $request->email)->first();

        if (!$buyer || !Hash::check($request->password, $buyer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($buyer->is_frozen) {
            return response()->json(['message' => 'Account is frozen. Please contact admin.'], 403);
        }

        $token = $buyer->createToken('BuyerAuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'buyer' => $buyer
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // =========================================================================
    // PROFILE
    // =========================================================================

    public function getProfile(Request $request)
    {
        $buyer = $request->user();
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return response()->json(['buyer' => $buyer]);
    }

    public function updateProfile(Request $request)
    {
        $buyer = $request->user();

        if ($buyer->kyc_status === 'approved') {
            return response()->json(['message' => 'Profile is approved and cannot be edited.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'dear' => 'sometimes|required|string|unique:buyers,dear,' . $buyer->id,
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:buyers,email,' . $buyer->id,
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
            'aadhar_details', 'pan_details', 'bank_details'
        ]);

        // Handle File Uploads
        $files = ['image', 'bis_attachment', 'gst_attachment', 'msme_attachment', 'pan_attachment', 'tan_attachment', 'cin_attachment', 'brand_logo'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                if ($buyer->$file) {
                    Storage::disk('public')->delete($buyer->$file);
                }
                $data[$file] = $request->file($file)->store('buyers/' . $file, 'public');
            }
        }

        $buyer->update($data);

        // Update Aadhar Details if provided (JSON input expected or multipart)
        if ($request->has('aadhar_details')) {
            $aadharDetails = is_string($request->aadhar_details) ? json_decode($request->aadhar_details, true) : $request->aadhar_details;
            if (is_array($aadharDetails)) {
                $buyer->aadharDetails()->delete();
                foreach ($aadharDetails as $index => $detail) {
                    $imagePath = $detail['aadhar_image'] ?? null;
                    if ($request->hasFile("aadhar_image_file.$index")) {
                        $imagePath = $request->file("aadhar_image_file.$index")->store('buyers/aadhar', 'public');
                    }
                    $buyer->aadharDetails()->create([
                        'aadhar_name' => $detail['aadhar_name'] ?? $buyer->name,
                        'aadhar_number' => $detail['aadhar_number'] ?? null,
                        'aadhar_image' => $imagePath,
                    ]);
                }
            }
        }

        // Similar logic for PAN and Bank details could be added here

        return response()->json(['message' => 'Profile updated successfully', 'buyer' => $buyer->load(['aadharDetails', 'panDetails', 'bankDetails'])]);
    }

    // =========================================================================
    // WORK ORDERS
    // =========================================================================

    public function getWorkOrders(Request $request)
    {
        $buyer = $request->user();
        $perPage = $request->get('per_page', 10);

        $query = WorkOrder::with(['product.images'])
            ->where('bp_code', $buyer->bp_code);

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
            $query->where(function ($q) use ($searchTerm) {
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
        $buyer = $request->user();
        $workOrder = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'product.images'])
            ->where('bp_code', $buyer->bp_code)
            ->find($id);

        if (!$workOrder) {
            return response()->json(['message' => 'Work Order not found'], 404);
        }

        return response()->json($workOrder);
    }

    public function storeWorkOrder(Request $request)
    {
        $buyer = $request->user();

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
            'bp_code' => $buyer->bp_code,
            'customer_name' => $request->customer_name ?? $buyer->business_name,
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
            'created_by' => $buyer->id,
            'creator_type' => 'buyer',
            'creator_user_code' => null,
        ]);

        return response()->json(['message' => 'Work Order created successfully', 'work_order' => $workOrder]);
    }

    public function updateWorkOrder(Request $request, $id)
    {
        $buyer = $request->user();
        $workOrder = WorkOrder::where('bp_code', $buyer->bp_code)->find($id);

        if (!$workOrder) {
            return response()->json(['message' => 'Work Order not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'product_name' => 'required|string|max:255',
            'due_date' => 'required|date',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
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
            // Allow updating product code if needed, or lock it? Web allows code update + image copy logic
        ]);

        return response()->json(['message' => 'Work Order updated successfully', 'work_order' => $workOrder]);
    }

    public function deleteWorkOrder(Request $request, $id)
    {
        $buyer = $request->user();
        $workOrder = WorkOrder::where('bp_code', $buyer->bp_code)->find($id);

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
            ->where(function ($q) use ($code) {
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
                $imageUrl = asset('storage/' . $imagePath); // Simplified for API
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
                // ... other fields
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
        $buyer = $request->user();
        if (!$buyer->hasPermission('product')) return response()->json(['message' => 'Forbidden'], 403);

        $query = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $buyer->bp_code);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate($request->get('per_page', 10));
        return response()->json($products);
    }

    public function getProduct(Request $request, $id)
    {
        $buyer = $request->user();
        if (!$buyer->hasPermission('product')) return response()->json(['message' => 'Forbidden'], 403);

        $product = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $buyer->bp_code)
            ->find($id);

        if (!$product) return response()->json(['message' => 'Product not found'], 404);

        return response()->json($product);
    }

    public function storeProduct(Request $request)
    {
        $buyer = $request->user();
        if (!$buyer->hasPermission('product')) return response()->json(['message' => 'Forbidden'], 403);

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
            'bp_code' => $buyer->bp_code,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            'description' => $request->description,
            'created_by' => $buyer->id,
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
        $buyer = $request->user();
        if (!$buyer->hasPermission('product')) return response()->json(['message' => 'Forbidden'], 403);

        $product = Product::where('bp_code', $buyer->bp_code)->find($id);
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
        $buyer = $request->user();
        if (!$buyer->hasPermission('product')) return response()->json(['message' => 'Forbidden'], 403);

        $product = Product::where('bp_code', $buyer->bp_code)->find($id);
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
    // DESIGNS (Global)
    // =========================================================================

    public function getDesigns(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'images'])
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->notFromFrozenAccounts();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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

        if ($product->isDesignLocked($request->user())) {
            return response()->json(['message' => 'Design locked'], 403);
        }

        return response()->json($product);
    }

    // =========================================================================
    // CATALOGUE (Personal Accepted Designs)
    // =========================================================================

    public function getCatalogue(Request $request)
    {
        $buyer = $request->user();
        $query = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $buyer->bp_code)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->notFromFrozenAccounts();

        // Unlocked check
        $query->where(function ($q) {
            $q->whereNull('design_view_unlocked_until')
                ->orWhere('design_view_unlocked_until', '>=', now());
        });

        return response()->json($query->latest()->paginate($request->get('per_page', 15)));
    }

    public function getCatalogueItem(Request $request, $id)
    {
        $buyer = $request->user();
        $product = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $buyer->bp_code)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->find($id);

        if (!$product) return response()->json(['message' => 'Item not found in catalogue'], 404);

        return response()->json($product);
    }

    // =========================================================================
    // KEY USERS
    // =========================================================================

    public function getKeyUsers(Request $request)
    {
        $buyer = $request->user();
        if (!$buyer->hasPermission('key_user')) return response()->json(['message' => 'Forbidden'], 403);

        $query = KeyUser::where('bp_code', $buyer->bp_code);
        if ($request->has('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate($request->get('per_page', 10)));
    }

    public function storeKeyUser(Request $request)
    {
        $buyer = $request->user();
        if (!$buyer->hasPermission('key_user')) return response()->json(['message' => 'Forbidden'], 403);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:key_users,email_id',
            'mobile_no' => 'required|string|unique:key_users,mobile_no',
            'password' => 'required|string|min:8',
            'permissions' => 'array',
            'permissions.*' => Rule::in(KeyUser::getAllPermissions()),
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $keyUser = KeyUser::create([
            'user_code' => KeyUser::generateUserCode(),
            'bp_code' => $buyer->bp_code,
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => bcrypt($request->password),
            'password_plain' => $request->password,
            'permissions' => $request->permissions ?? [],
            'status' => 1,
        ]);

        return response()->json(['message' => 'Key User created successfully', 'key_user' => $keyUser]);
    }

    public function updateKeyUser(Request $request, $id)
    {
        $buyer = $request->user();
        if (!$buyer->hasPermission('key_user')) return response()->json(['message' => 'Forbidden'], 403);

        $keyUser = KeyUser::where('bp_code', $buyer->bp_code)->find($id);
        if (!$keyUser) return response()->json(['message' => 'Key User not found'], 404);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:key_users,email_id,' . $keyUser->id,
            'mobile_no' => 'required|string|unique:key_users,mobile_no,' . $keyUser->id,
            'password' => 'nullable|string|min:8',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $keyUser->update([
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => $request->password ? bcrypt($request->password) : $keyUser->password,
            'password_plain' => $request->password ?? $keyUser->password_plain,
            'permissions' => $request->permissions ?? $keyUser->permissions,
            'status' => $request->status ?? $keyUser->status,
        ]);

        return response()->json(['message' => 'Key User updated', 'key_user' => $keyUser]);
    }

    public function deleteKeyUser(Request $request, $id)
    {
        $buyer = $request->user();
        if (!$buyer->hasPermission('key_user')) return response()->json(['message' => 'Forbidden'], 403);

        $keyUser = KeyUser::where('bp_code', $buyer->bp_code)->find($id);
        if (!$keyUser) return response()->json(['message' => 'Key User not found'], 404);

        $keyUser->delete();
        return response()->json(['message' => 'Key User deleted']);
    }

    // =========================================================================
    // USERS (End Users)
    // =========================================================================

    public function getUsers(Request $request)
    {
        $buyer = $request->user();
        $query = User::where('bp_code', $buyer->bp_code);

        // Add User permission check if implied by web controller logic, web controller doesn't explicitly check permission but checks frozen.

        return response()->json($query->paginate($request->get('per_page', 10)));
    }

    public function storeUser(Request $request)
    {
        $buyer = $request->user();
        if ($buyer->is_frozen) return response()->json(['message' => 'Account frozen'], 403);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_no' => 'required|string|unique:users,mobile_no',
            'password' => 'required|string|min:8',
            'permissions' => 'array',
            'permissions.*' => Rule::in(User::getAllPermissions()),
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $user = User::create([
            'user_code' => User::generateUserCode(),
            'bp_code' => $buyer->bp_code,
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'email_id' => $request->email,
            'mobile_no' => $request->mobile_no,
            'password' => bcrypt($request->password),
            'password_plain' => $request->password,
            'permissions' => $request->permissions ?? [],
            'status' => 1,
            'is_frozen' => false,
            'created_by' => $buyer->id,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function updateUser(Request $request, $id)
    {
        $buyer = $request->user();
        $user = User::where('bp_code', $buyer->bp_code)->find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile_no' => 'required|string|unique:users,mobile_no,' . $user->id,
            'password' => 'nullable|string|min:8',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $user->update([
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'email_id' => $request->email,
            'mobile_no' => $request->mobile_no,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'password_plain' => $request->password ?? $user->password_plain,
            'permissions' => $request->permissions ?? $user->permissions,
        ]);

        return response()->json(['message' => 'User updated', 'user' => $user]);
    }

    public function deleteUser(Request $request, $id)
    {
        $buyer = $request->user();
        $user = User::where('bp_code', $buyer->bp_code)->find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->delete();
        return response()->json(['message' => 'User deleted']);
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
