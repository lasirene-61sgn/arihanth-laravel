<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Buyer;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;
use App\Services\ImageWatermarkService;
use App\Exports\UserProductExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the products created by the key user.
     */
    

public function index(Request $request)
{
    $user = auth()->guard('web')->user();
    
    $query = Product::with(['creator', 'category', 'subcategory', 'images'])
        ->where('bp_code', $user->bp_code);

    // --- SEARCH & FILTERS ---
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('product_name', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%");
        });
    }

    if ($request->filled('filter_product_code')) {
        $query->where('product_code', 'like', '%' . $request->filter_product_code . '%');
    }
// NEW: Filter by Product Name specifically
if ($request->filled('filter_product_name')) {
    $query->where('product_name', 'like', '%' . $request->filter_product_name . '%');
}

    if ($request->filled('filter_category')) {
        $query->whereHas('category', fn($q) => $q->where('name', 'like', '%' . $request->filter_category . '%'));
    }
    if ($request->filled('filter_subcategory')) {
        $query->whereHas('subcategory', fn($q) => $q->where('name', 'like', '%' . $request->filter_subcategory . '%'));
    }

    // --- SORTING ---
    $sort = $request->get('sort', 'latest');
    if ($sort == 'name_asc') $query->orderBy('product_name', 'asc');
    elseif ($sort == 'name_desc') $query->orderBy('product_name', 'desc');
    else $query->latest();

    $products = $query->get();

    return view('user.product.index', compact('products'));
}



    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        // Get the BP code of the currently logged-in key user
        $User = auth()->guard('web')->user();
        $buyer = Buyer::where('bp_code', $User->bp_code)->first();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('user.product.create', compact('buyer', 'categories'));
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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'created_by' => auth()->guard('web')->id(), // Track which key user created the product
        ]);

        // Handle image uploads
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

        return redirect()->route('user.product.index')
            ->with('success', 'Product created successfully with code: ' . $productCode);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Ensure the user can only view products from their BP code
        $user = auth()->guard('web')->user();
        if ($product->bp_code != $user->bp_code) {
            abort(403, 'Unauthorized access to product');
        }
        
        $product->load(['category', 'subcategory', 'images']);
        return view('user.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Ensure the user can only edit products from their BP code
        $user = auth()->guard('web')->user();
        if ($product->bp_code != $user->bp_code) {
            abort(403, 'Unauthorized access to product');
        }
        
        $categories = ProductCategory::orderBy('name')->get();
        $subcategories = $product->product_category_id ? ProductSubcategory::where('product_category_id', $product->product_category_id)->get() : collect();
        
        return view('user.product.edit', compact('product', 'categories', 'subcategories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Ensure the user can only update products from their BP code
        $user = auth()->guard('web')->user();
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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle image deletion
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $imageId) {
                $image = ProductImage::where('id', $imageId)->where('product_id', $product->id)->first();
                if ($image) {
                    if (Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $image->delete();
                }
            }
        }

        // Handle new image uploads
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

        return redirect()->route('user.product.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Ensure the user can only delete products from their BP code
        $user = auth()->guard('web')->user();
        if ($product->bp_code != $user->bp_code) {
            abort(403, 'Unauthorized access to product');
        }

        // Delete image files and records
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }
        $product->images()->delete();
        
        $product->delete();

        return redirect()->route('user.product.index')
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
    public function export(Request $request) 
{
    return Excel::download(new UserProductExport($request), 'User_Products_' . now()->format('d-m-Y') . '.xlsx');
}
}
