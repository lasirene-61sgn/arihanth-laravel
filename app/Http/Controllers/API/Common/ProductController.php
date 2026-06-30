<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\Design;
use App\Services\ImageWatermarkService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ProductController extends Controller
{
    /**
     * Resolve the bp_code / craftman_code for the current user's "ownership" scope.
     * Returns null for super_admin/admin (sees everything).
     */
    private function ownerCode($user): ?string
    {
        if ($user instanceof \App\Models\Buyer || $user instanceof \App\Models\KeyUser || $user instanceof \App\Models\User) {
            return $user->bp_code ?? null;
        }
        if ($user instanceof \App\Models\Craftman) {
            return $user->craftman_code ?? null;
        }
        if (isset($user->role) && ($user->role === 'buyer')) {
            return $user->bp_code ?? null;
        }
        if (isset($user->role) && ($user->role === 'craftsman')) {
            return $user->craftman_code ?? null;
        }
        return null; // SuperAdmin / Admin
    }

    /**
     * List products for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

       $sortBy = $request->get('sort_by', 'id');

    // 2. Check for 'sort' (?sort=asc), then 'sort_order', then default to 'asc'
    $sortOrder = strtolower($request->get('sort') ?: $request->get('sort_order', 'asc'));

    // 3. Validate Order
    if (!in_array($sortOrder, ['asc', 'desc'])) {
        $sortOrder = 'asc'; 
    }

    $allowedSortColumns = [
        'id', 'design_code', 'product_code', 'product_name', 
        'type', 'size', 'weight_from', 'weight_to', 'hallmark', 
        'rodium', 'hook', 'stone', 'enamel', 'bp_code', 'created_at',
    ];

    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'id'; // Defaulting to ID for "first-to-last" logic
    }

    $query = Product::with(['category', 'subcategory', 'images']);


        $code = $this->ownerCode($user);
        if ($code !== null) {
            // For buyers and craftsmen, show strictly their own items
            $query->where('bp_code', $code);
        } else {
            // For admins/superadmins, show everything EXCEPT items that haven't been assigned
            // a bp_code (which are likely Work Order imports that should be hidden from main list)
            $query->whereNotNull('bp_code');
        }
        $query->orderBy($sortBy, $sortOrder);

        // ── Search ──
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%$search%")
                    ->orWhere('bp_code', 'LIKE', "%$search%")
                    ->orWhere('product_code', 'LIKE', "%$search%")
                    ->orWhere('design_code', 'LIKE', "%$search%");
            });
        }

        // ── Filters ──
        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->filled('category_name')) {
            $categoryName = $request->category_name;
            $query->whereHas('category', function ($q) use ($categoryName) {
                $q->where('name', $categoryName);
            });
        }

        if ($request->filled('subcategory_id')) {
            $query->where('product_subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('subcategory_name') || $request->filled('subcategory')) {
            $subcategoryName = $request->subcategory_name ?: $request->subcategory;
            $query->whereHas('subcategory', function ($q) use ($subcategoryName) {
                $q->where('name', $subcategoryName);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        if ($request->filled('weight_from')) {
            $query->where('weight_from', '>=', $request->weight_from);
        }

        if ($request->filled('weight_to')) {
            $query->where('weight_to', '<=', $request->weight_to);
        }

        if ($request->filled('hallmark')) {
            $query->where('hallmark', $request->hallmark);
        }

        if ($request->filled('rodium')) {
            $query->where('rodium', $request->rodium);
        }

        if ($request->filled('hook')) {
            $query->where('hook', $request->hook);
        }

        if ($request->filled('stone')) {
            $query->where('stone', $request->stone);
        }

        if ($request->filled('enamel')) {
            $query->where('enamel', $request->enamel);
        }

        if ($request->filled('bp_code')) {
            $query->where('bp_code', $request->bp_code);
        }

        if ($request->filled('craftsman_code')) {
            $query->where('bp_code', $request->craftsman_code);
        }

        if ($request->filled('craftman_code')) {
            $query->where('bp_code', $request->craftman_code);
        }

        // ── Selected IDs (for print/export selected) ──
        if ($request->filled('ids')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        $query->orderBy($sortBy, $sortOrder);

        // ── Export (CSV download) ──
        if ($request->has('export')) {
            $products = $query->get();

            $exportData = $products->map(function ($product) {
                return [
                    'Product Code'  => $product->product_code,
                    'Product Name'  => $product->product_name,
                    'Category'      => $product->category->name ?? '',
                    'Subcategory'   => $product->subcategory->name ?? '',
                    'Type'          => $product->type,
                    'Size'          => $product->size,
                    'Weight From'   => $product->weight_from,
                    'Weight To'     => $product->weight_to,
                    'Hallmark'      => $product->hallmark,
                    'Rodium'        => $product->rodium,
                    'Hook'          => $product->hook,
                    'Stone'         => $product->stone,
                    'Enamel'        => $product->enamel,
                    'BP Code'       => $product->bp_code,
                    'Created At'    => $product->created_at ? $product->created_at->format('Y-m-d') : '',
                ];
            });

            $filename = 'products_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return response()->stream(function () use ($exportData) {
                $file = fopen('php://output', 'w');
                if ($exportData->isNotEmpty()) {
                    fputcsv($file, array_keys($exportData->first()));
                    foreach ($exportData as $row) {
                        fputcsv($file, $row);
                    }
                }
                fclose($file);
            }, 200, $headers);
        }

        // ── Print (full data, no pagination) ──
        if ($request->has('print')) {
            $products = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $products,
            ]);
        }

        // ── Paginated list ──
        return response()->json([
            'success' => true,
            'data'    => $query->paginate($request->get('per_page', 15))
        ]);
    }

    /**
     * Show a single product.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $query = Product::with(['category', 'subcategory', 'images']);
        // If we strictly want to hide them from show as well, uncomment the whereNotNull below:
        // ->whereNotNull('bp_code');

        $code = $this->ownerCode($user);
        if ($code !== null) {
            // Buyers and craftsmen must only see their own
            $query->where('bp_code', $code);
        } else {
            // Admins should not see unassigned (imported) products in standard show views
            $query->whereNotNull('bp_code');
        }

        $product = $query->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'product_code'        => 'nullable|string|max:255',
            'design_code'         => 'nullable|string|max:255',
            'relabel_code'        => 'nullable|string|max:255',
            'product_name'        => 'nullable|string|max:255',
            'product_category_id' => 'required_without:category_id|exists:product_categories,id',
            'category_id'         => 'nullable|exists:product_categories,id',
            'subcategory_id'      => 'nullable|exists:product_subcategories,id',
            'product_subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type'                => 'required|string|in:Piece,Pair',
            'open_close'          => 'nullable|string|in:Open,Close',
            'size'                => 'nullable|string|max:255',
            'length'              => 'nullable|string|max:255',
            'weight_from'         => 'nullable|numeric|min:0',
            'weight_to'           => 'nullable|numeric',
            'hallmark'            => 'nullable|string|max:255',
            'rodium'              => 'nullable|string|max:255',
            'hook'                => 'nullable|string|max:255',
            'stone'               => 'nullable|string|max:255',
            'enamel'              => 'nullable|string|max:255',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $ownerCode = $this->ownerCode($user);

        // SuperAdmin can supply bp_code explicitly
        if ($ownerCode === null && $request->filled('bp_code')) {
            $ownerCode = $request->bp_code;
        }

        // Check if product_code or design_code already exists
        $productCode = $request->product_code;
        $designCode = $request->design_code;

        // Auto-generate design code if not provided
        if (empty($designCode) && ($request->filled('product_category_id') || $request->filled('category_id')) && $request->filled('weight_from')) {
            $catId = $request->product_category_id ?? $request->category_id;
            $designCode = Product::generateDesignCode($catId, $request->weight_from);
        }

        $existingProduct = null;
        if (!empty($productCode) || !empty($designCode)) {
            $existingProduct = Product::where(function ($q) use ($productCode, $designCode) {
                if (!empty($productCode)) {
                    $q->orWhere('product_code', $productCode);
                }
                if (!empty($designCode)) {
                    $q->orWhere('design_code', $designCode);
                }
            })->first();
        }

        if ($existingProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Product with this code or design code already exists',
                'data' => $existingProduct->load(['category', 'subcategory', 'images'])
            ], 422);
        }

        $product = Product::create([
            'product_code'           => $productCode,
            'design_code'            => $designCode,
            'relabel_code'           => $request->relabel_code,
            'bp_code'                => $ownerCode,
            'product_name'           => $request->product_name,
            'product_category_id'    => $request->product_category_id ?? $request->category_id,
            'product_subcategory_id' => $request->product_subcategory_id ?? $request->subcategory_id,
            'type'                   => $request->type,
            'open_close'             => $request->open_close,
            'size'                   => $request->size,
            'length'                 => $request->length,
            'weight_from'            => $request->weight_from,
            'weight_to'              => $request->weight_to,
            'hallmark'               => $request->hallmark,
            'rodium'                 => $request->rodium,
            'hook'                   => $request->hook,
            'stone'                  => $request->stone,
            'enamel'                 => $request->enamel,
            'created_by'             => $user->id,
        ]);

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            $uploadedImages = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $uploadedImages = $request->file('image');
        }

        if (!empty($uploadedImages)) {
            $watermarkService = new ImageWatermarkService();
            if (!is_array($uploadedImages)) {
                $uploadedImages = [$uploadedImages];
            }
            foreach ($uploadedImages as $index => $image) {
                $path = $image->store('products', 'public');
                $watermarkedPath = $watermarkService->addWatermark($path);
                ProductImage::create(['product_id' => $product->id, 'path' => $watermarkedPath]);

                // Set first image as main product image
                if ($index === 0) {
                    $product->update(['product_image' => $watermarkedPath]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data'    => $product->load(['category', 'subcategory', 'images'])
        ], 201);
    }

    /**
     * Update a product.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $query = Product::query();

        $code = $this->ownerCode($user);
        if ($code !== null) {
            $query->where('bp_code', $code);
        }

        $product = $query->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_code'        => 'nullable|string|max:255|unique:products,product_code,' . $product->id,
            'design_code'         => 'nullable|string|max:255|unique:products,design_code,' . $product->id,
            'relabel_code'        => 'nullable|string|max:255',
            'product_name'        => 'sometimes|required|string|max:255',
            'product_category_id' => 'sometimes|required_without:category_id|exists:product_categories,id',
            'category_id'         => 'nullable|exists:product_categories,id',
            'subcategory_id'      => 'nullable|exists:product_subcategories,id',
            'product_subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type'                => 'sometimes|required|string|in:Piece,Pair',
            'open_close'          => 'nullable|string|in:Open,Close',
            'size'                => 'nullable|string|max:255',
            'length'              => 'nullable|string|max:255',
            'weight_from'         => 'nullable|numeric|min:0',
            'weight_to'           => 'nullable|numeric|gte:weight_from',
            'hallmark'            => 'nullable|string|max:255',
            'rodium'              => 'nullable|string|max:255',
            'hook'                => 'nullable|string|max:255',
            'stone'               => 'nullable|string|max:255',
            'enamel'              => 'nullable|string|max:255',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product->update([
            'product_code'           => $request->product_code ?? $product->product_code,
            'design_code'            => $request->design_code ?? $product->design_code,
            'relabel_code'           => $request->relabel_code ?? $product->relabel_code,
            'product_name'           => $request->product_name,
            'product_category_id'    => $request->product_category_id ?? $request->category_id ?? $product->product_category_id,
            'product_subcategory_id' => $request->product_subcategory_id ?? $request->subcategory_id ?? $product->product_subcategory_id,
            'type'                   => $request->type,
            'open_close'             => $request->open_close,
            'size'                   => $request->size,
            'length'                 => $request->length,
            'weight_from'            => $request->weight_from,
            'weight_to'              => $request->weight_to,
            'hallmark'               => $request->hallmark,
            'rodium'                 => $request->rodium,
            'hook'                   => $request->hook,
            'stone'                  => $request->stone,
            'enamel'                 => $request->enamel,
        ]);

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            $uploadedImages = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $uploadedImages = $request->file('image');
        }

        if (!empty($uploadedImages)) {
            $watermarkService = new ImageWatermarkService();
            if (!is_array($uploadedImages)) {
                $uploadedImages = [$uploadedImages];
            }
            foreach ($uploadedImages as $index => $image) {
                $path = $image->store('products', 'public');
                $watermarkedPath = $watermarkService->addWatermark($path);
                ProductImage::create(['product_id' => $product->id, 'path' => $watermarkedPath]);

                // If main image is empty, set the first new image as main image
                if ($index === 0 && empty($product->product_image)) {
                    $product->update(['product_image' => $watermarkedPath]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data'    => $product->load(['category', 'subcategory', 'images'])
        ]);
    }

    /**
     * Delete a product.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $query = Product::query();

        $code = $this->ownerCode($user);
        if ($code !== null) {
            $query->where('bp_code', $code);
        }

        $product = $query->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        foreach ($product->images as $image) {
            if (file_exists(public_path($image->path))) {
                @unlink(public_path($image->path));
            }
            $image->delete();
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    /**
     * Generate PDF for selected products.
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        $query = Product::with(['category', 'subcategory', 'images']);

        $code = $this->ownerCode($user);
        if ($code !== null) {
            $query->where('bp_code', $code);
        } else {
            $query->whereNotNull('bp_code');
        }

        // ── Selected IDs ──
        if ($request->filled('ids')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No product IDs provided'], 400);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No products found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.products.generate-pdf', compact('products'))->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = count($products) === 1
                ? "Product_" . $products->first()->product_code . ".pdf"
                : "Product_Catalog_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Product PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function categories()
    {
        $categories = ProductCategory::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Store new product category
     */
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255|unique:product_categories,name',
            'has_hook'       => 'nullable|boolean',
            'has_enamel'     => 'nullable|boolean',
            'has_rodium'     => 'nullable|boolean',
            'has_open_close' => 'nullable|boolean',
            'has_stone'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $category = ProductCategory::create([
            'name'           => $request->name,
            'has_hook'       => $request->boolean('has_hook'),
            'has_enamel'     => $request->boolean('has_enamel'),
            'has_rodium'     => $request->boolean('has_rodium'),
            'has_open_close' => $request->boolean('has_open_close'),
            'has_stone'      => $request->boolean('has_stone'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data'    => $category
        ], 201);
    }

    public function subcategories(Request $request)
    {
        $categoryId = $request->get('category_id');

        $query = ProductSubcategory::with('category')->orderBy('name');

        if ($categoryId) {
            $query->where('product_category_id', $categoryId);
        }

        $subcategories = $query->get()->map(function ($sub) {
            return [
                'id' => $sub->id,
                'name' => $sub->name,
                'product_category_id' => $sub->product_category_id,
                'category_name' => $sub->category->name ?? null,
            ];
        });

        return response()->json(['success' => true, 'data' => $subcategories]);
    }

    public function showSubcategory(Request $request, $id)
    {
        $subcategory = ProductSubcategory::with('category')->find($id);

        if (!$subcategory) {
            return response()->json(['success' => false, 'message' => 'Subcategory not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'product_category_id' => $subcategory->product_category_id,
                'category_name' => $subcategory->category->name ?? null,
            ]
        ]);
    }

    /**
     * Store new product subcategory
     */
    public function storeSubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_category_id' => 'required|exists:product_categories,id',
            'name'                => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $subcategory = ProductSubcategory::create([
            'product_category_id' => $request->product_category_id,
            'name'                => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory created successfully',
            'data'    => $subcategory->load('category')
        ], 201);
    }

    public function getCategoryOptions(Request $request)
    {
        $categoryId = $request->query('category_id');
        if (!$categoryId) {
            return response()->json([
                'success' => false,
                'message' => 'Category ID is required'
            ], 422);
        }

        $category = ProductCategory::find($categoryId);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'has_hook' => (bool) $category->has_hook,
            'has_enamel' => (bool) $category->has_enamel,
            'has_rodium' => (bool) $category->has_rodium,
            'has_open_close' => (bool) $category->has_open_close,
            'has_stone' => (bool) $category->has_stone,
        ]);
    }

    public function getProductDetails(Request $request)
    {
        $code = $request->query('product_code');
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'product_code is required'], 422);
        }

        // Load both images and designs relationships
        $product = Product::with(['images', 'designs'])
            ->where(fn($q) => $q->where('product_code', $code)->orWhere('design_code', $code))
            ->where('design_status', 'Accepted')
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found or not accepted'], 404);
        }

        $imageUrl = null;

        // Priority 1: Check product images
        if ($product->images->isNotEmpty()) {
            $imagePath = $product->images->first()->path;
            if (!empty($imagePath)) {
                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $imageUrl = $imagePath;
                } elseif (strpos($imagePath, 'images/work-orders/') === 0 || strpos($imagePath, 'images/') === 0 || strpos($imagePath, 'storage/') === 0) {
                    $imageUrl = asset($imagePath);
                } else {
                    $imageUrl = asset('storage/' . $imagePath);
                }
            }
        }

        // Priority 2: If no product image, check design images (first design)
        if (empty($imageUrl) && $product->designs->isNotEmpty()) {
            $design = $product->designs->first();
            if ($design && !empty($design->image)) {
                $imagePath = $design->image;
                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $imageUrl = $imagePath;
                } elseif (strpos($imagePath, 'images/') === 0 || strpos($imagePath, 'storage/') === 0) {
                    $imageUrl = asset($imagePath);
                } else {
                    $imageUrl = asset('storage/' . $imagePath);
                }
            }
        }

        // Priority 3: Fallback to product's own design_code image field
        if (empty($imageUrl) && !empty($product->design_code)) {
            // Try to find a design with matching design_code
            $design = Design::where('design_code', $product->design_code)->first();
            if ($design && !empty($design->image)) {
                $imagePath = $design->image;
                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $imageUrl = $imagePath;
                } elseif (strpos($imagePath, 'images/') === 0 || strpos($imagePath, 'storage/') === 0) {
                    $imageUrl = asset($imagePath);
                } else {
                    $imageUrl = asset('storage/' . $imagePath);
                }
            }
        }

        $productData = [
            'product_name'        => $product->product_name,
            'design_code'         => $product->design_code,
            'product_code'        => $product->product_code,
            'product_image_url'   => $imageUrl,
            'product_category_id' => $product->product_category_id,
            'subcategory_id'      => $product->product_subcategory_id,
            'type'                => $product->type,
            'open_close'          => $product->open_close,
            'hallmark'            => $product->hallmark,
            'rodium'              => $product->rodium,
            'hook'                => $product->hook,
            'size'                => $product->size,
            'stone'               => $product->stone,
            'enamel'              => $product->enamel,
            'length'              => $product->length,
            'weight_from'         => $product->weight_from,
            'weight_to'           => $product->weight_to,
            'relabel_code'        => $product->relabel_code,
        ];

        return response()->json([
            'success' => true,
            'data'    => $productData,
            'product' => $productData // Added for backwards compatibility with frontend JS
        ]);
    }

    /**
     * Bulk upload products via ZIP file.
     */
    public function bulkUpload(Request $request)
    {
        $user = $request->user();
        
        // Check if user is admin/super_admin, craftsman, or buyer
        $allowedRoles = ['super_admin', 'admin', 'craftsman', 'buyer'];
        $userRole = $user->role ?? '';

        if (!in_array($userRole, $allowedRoles) && !($user instanceof \App\Models\Buyer) && !($user instanceof \App\Models\Craftman)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'zip_file' => 'required|mimes:zip|max:102400',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $zip = new ZipArchive;
        $file = $request->file('zip_file');

        if ($zip->open($file->getRealPath()) === TRUE) {
            $timestamp = time();
            $baseTempPath = storage_path('app/temp_bulk_api_' . $timestamp);
            $zip->extractTo($baseTempPath);
            $zip->close();

            $excelFile = null;
            $actualExtractPath = $baseTempPath;

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseTempPath));

            foreach ($files as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $extension = strtolower($fileInfo->getExtension());
                    if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                        if (strpos($fileInfo->getFilename(), '._') === false) {
                            $excelFile = $fileInfo->getRealPath();
                            $actualExtractPath = dirname($excelFile);
                            break;
                        }
                    }
                }
            }

            if (!$excelFile) {
                File::deleteDirectory($baseTempPath);
                return response()->json(['success' => false, 'message' => 'Excel file not found inside the zip'], 400);
            }

            try {
                // Determine if we should force a BP code (e.g. if a craftsman or buyer is uploading)
                $forcedBPCode = null;
                if (($user->role ?? '') === 'craftsman' || $user instanceof \App\Models\Craftman) {
                    $forcedBPCode = $user->craftman_code ?? $user->user_code;
                } elseif (($user->role ?? '') === 'buyer' || $user instanceof \App\Models\Buyer) {
                    $forcedBPCode = $user->bp_code ?? null;
                }

                Excel::import(new ProductImport($actualExtractPath, $forcedBPCode), $excelFile);

                File::deleteDirectory($baseTempPath);
                return response()->json(['success' => true, 'message' => 'Products uploaded successfully']);
            } catch (\Exception $e) {
                File::deleteDirectory($baseTempPath);
                return response()->json(['success' => false, 'message' => 'Error during import: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Could not open the zip file'], 400);
    }
}
