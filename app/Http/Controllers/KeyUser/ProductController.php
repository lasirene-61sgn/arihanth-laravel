<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Buyer;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\ProductImage;
use App\Services\ImageWatermarkService;
use App\Exports\KeyUserProductExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the products created by the key user.
     */
    public function index(Request $request)
{
    $keyUser = auth()->guard('key_user')->user() ?? auth()->guard('buyer')->user();

    if (!$keyUser) {
        return redirect()->route('key-user.login')->with('error', 'Please log in to continue.');
    }

    $query = Product::with(['creator', 'images'])
        ->where('bp_code', $keyUser->bp_code);

    // 1. Quick Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('product_name', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%");
        });
    }

    // 2. Advanced Filters (Status completely removed)
    if ($request->filled('filter_name')) {
        $query->where('product_name', 'like', '%' . $request->filter_name . '%');
    }
    if ($request->filled('filter_code')) {
        $query->where('product_code', 'like', '%' . $request->filter_code . '%');
    }
    if ($request->filled('product_category_id')) {
        $query->where('product_category_id', $request->product_category_id);
    }
    if ($request->filled('subcategory_id')) {
        $query->where(function ($q) use ($request) {
            $q->where('subcategory_id', $request->subcategory_id)
              ->orWhere('product_subcategory_id', $request->subcategory_id);
        });
    }
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }
    if ($request->filled('order_type')) {
        $query->where('order_type', $request->order_type);
    }

    // 3. Sorting
    $sort = $request->get('sort', 'created_at');
    $direction = $request->get('direction', 'desc');
    $query->orderBy($sort, $direction);

    $products = $query->paginate(15)->withQueryString();

    // Fetch categories and subcategories for the dropdowns
    $categories = \App\Models\ProductCategory::orderBy('name')->get();
    $subcategories = $request->filled('product_category_id')
        ? \App\Models\ProductSubcategory::where('product_category_id', $request->product_category_id)->orderBy('name')->get()
        : \App\Models\ProductSubcategory::orderBy('name')->get();

    return view('key-user.product.index', compact('products', 'categories', 'subcategories'));
}



    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        // Get the BP code of the currently logged-in user (key user or buyer)
        $bpCode = null;

        if (auth()->guard('key_user')->check()) {
            $keyUser = auth()->guard('key_user')->user();
            $bpCode = $keyUser->bp_code;
        } elseif (auth()->guard('buyer')->check()) {
            $buyer = auth()->guard('buyer')->user();
            $bpCode = $buyer->bp_code;
        }

        $buyer = $bpCode ? Buyer::where('bp_code', $bpCode)->first() : null;
        $categories = ProductCategory::orderBy('name')->get();

        return view('key-user.product.create', compact('buyer', 'categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'nullable|string|max:255|unique:products,product_code',
            'bp_code' => 'required|string|exists:buyers,bp_code',
            'product_name' => 'nullable|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string|in:Piece,Pair',
            // 'order_type' => 'required|string|in:Regular,Urgent,Super Urgent',
            'open_close' => 'nullable|string|in:Open,Close',
            'hallmark' => 'nullable|string|in:Yes,No',
            'rodium' => 'nullable|string|in:Yes,No',
            'hook' => 'nullable|string|in:Yes,No',
            'size' => 'nullable|string|max:50',
            'stone' => 'nullable|string|max:100',
            'enamel' => 'nullable|string|in:Yes,No',
            'length' => 'nullable|string|max:50',
            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|gte:weight_from',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Use provided product code instead of auto-generating
        $productCode = $request->product_code;

        $product = Product::create([
            'product_code' => $productCode,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            // 'order_type' => $request->order_type,
            'open_close' => $request->open_close,
            'hallmark' => $request->hallmark,
            'rodium' => $request->rodium,
            'hook' => $request->hook,
            'size' => $request->size,
            'stone' => $request->stone,
            'enamel' => $request->enamel,
            'length' => $request->length,
            'weight_from' => $request->weight_from,
            'weight_to' => $request->weight_to,
            'bp_code' => $request->bp_code,
            'created_by' => auth()->guard('key_user')->check() ? auth()->guard('key_user')->id() : auth()->guard('buyer')->id(), // Track which user created the product
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                // Apply watermark
                $watermarkService->addWatermark($path);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('key-user.product.index')
            ->with('success', 'Product created successfully with code: ' . $productCode);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Ensure the user can only view products for their bp_code
        $user = auth()->guard('key_user')->user() ?? auth()->guard('buyer')->user();
        if ($product->bp_code != $user->bp_code) {
            abort(403, 'Unauthorized access to product');
        }

        $product->load(['category', 'subcategory', 'images']);
        return view('key-user.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Ensure the user can only edit products for their bp_code
        $user = auth()->guard('key_user')->user() ?? auth()->guard('buyer')->user();
        if ($product->bp_code != $user->bp_code) {
            abort(403, 'Unauthorized access to product');
        }

        $categories = ProductCategory::orderBy('name')->get();
        $subcategories = $product->product_category_id ? ProductSubcategory::where('product_category_id', $product->product_category_id)->get() : collect();

        return view('key-user.product.edit', compact('product', 'categories', 'subcategories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Ensure the user can only update products for their bp_code
        $user = auth()->guard('key_user')->user() ?? auth()->guard('buyer')->user();
        if ($product->bp_code != $user->bp_code) {
            abort(403, 'Unauthorized access to product');
        }

        $validator = Validator::make($request->all(), [
            'product_code' => 'nullable|string|max:255|unique:products,product_code,' . $product->id,
            'bp_code' => 'required|string|exists:buyers,bp_code',
            'product_name' => 'nullable|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string|in:Piece,Pair',
            // 'order_type' => 'required|string|in:Regular,Urgent,Super Urgent',
            'open_close' => 'nullable|string|in:Open,Close',
            'hallmark' => 'nullable|string|in:Yes,No',
            'rodium' => 'nullable|string|in:Yes,No',
            'hook' => 'nullable|string|in:Yes,No',
            'size' => 'nullable|string|max:50',
            'stone' => 'nullable|string|max:100',
            'enamel' => 'nullable|string|in:Yes,No',
            'length' => 'nullable|string|max:50',
            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|gte:weight_from',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle image upload
        if ($request->hasFile('images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                // Apply watermark
                $watermarkService->addWatermark($path);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                ]);
            }
        }

        $product->update([
            'product_code' => $request->product_code,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            // 'order_type' => $request->order_type,
            'open_close' => $request->open_close,
            'hallmark' => $request->hallmark,
            'rodium' => $request->rodium,
            'hook' => $request->hook,
            'size' => $request->size,
            'stone' => $request->stone,
            'enamel' => $request->enamel,
            'length' => $request->length,
            'weight_from' => $request->weight_from,
            'weight_to' => $request->weight_to,
            'bp_code' => $request->bp_code,
        ]);

        return redirect()->route('key-user.product.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Ensure the user can only delete products for their bp_code
        $user = auth()->guard('key_user')->user() ?? auth()->guard('buyer')->user();
        if ($product->bp_code != $user->bp_code) {
            abort(403, 'Unauthorized access to product');
        }

        // Note: Actual file deletion is typically handled by a cleanup job or manually 
        // if we want to be safe, but here we just delete the database records.
        $product->images()->delete();

        $product->delete();

        return redirect()->route('key-user.product.index')
            ->with('success', 'Product deleted successfully!');
    }

    // AJAX: get subcategories by category
    public function getSubcategories(Request $request)
    {
        $request->validate(['category_id' => 'required|exists:product_categories,id']);
        $subcategories = ProductSubcategory::where('product_category_id', $request->category_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($subcategories);
    }

    // AJAX: get category options flags to show dynamic fields
    public function getCategoryOptions(Request $request)
    {
        $request->validate(['category_id' => 'required|exists:product_categories,id']);
        $category = ProductCategory::findOrFail($request->category_id);
        return response()->json([
            'has_hook' => (bool) $category->has_hook,
            'has_enamel' => (bool) $category->has_enamel,
            'has_rodium' => (bool) $category->has_rodium,
            'has_open_close' => (bool) $category->has_open_close,
            'has_stone' => (bool) $category->has_stone,
        ]);
    }

    // Add the Export Method too
    public function export(Request $request)
    {

        $keyUser = auth()->guard('key_user')->user() ?? auth()->guard('buyer')->user();
        return Excel::download(
            new KeyUserProductExport($request, $keyUser->bp_code),
            'key-user-products.xlsx'
        );
    }

    public function printSelected(Request $request)
    {
        $keyUser = auth()->guard('key_user')->user() ?? auth()->guard('buyer')->user();
        $ids = $request->input('selected_products', []);

        $products = Product::whereIn('id', $ids)
            ->where('bp_code', $keyUser->bp_code)
            ->with(['category', 'subcategory', 'images'])
            ->get();

        return view('admin.product.print-selected', compact('products'));
    }
}
