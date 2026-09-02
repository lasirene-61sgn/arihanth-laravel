<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductSubcategory;
use App\Services\ImageWatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Exports\ProductAdminExport;
use App\Imports\ProductImport;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */


    public function index(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'creator', 'images'])
            ->notFromFrozenAccounts()
            ->whereNotNull('bp_code');

        // --- SEARCH & FILTERS ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('bp_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_name')) {
            $query->where('product_name', 'like', '%' . $request->filter_name . '%');
        }
        if ($request->filled('filter_code')) {
            $query->where('product_code', 'like', '%' . $request->filter_code . '%');
        }
        if ($request->filled('filter_category')) {
            $query->where('product_category_id', $request->filter_category);
        }
        if ($request->filled('filter_subcategory')) {
            $query->where('product_subcategory_id', $request->filter_subcategory);
        }
        if ($request->filled('filter_bp_code')) {
            $query->where('bp_code', $request->filter_bp_code);
        }

        if ($request->filled('filter_design_code')) {
            $query->where('design_code', 'like', '%' . $request->filter_design_code . '%');
        }
        
        if (!$request->filled('filter_bp_code') && $request->filled('filter_craftsman')) {
            $query->where('bp_code', $request->filter_craftsman);
        }
        if ($request->filled('filter_product_code')) {
            $query->where('product_code', 'like', '%' . $request->filter_product_code . '%');
        }

        // --- SORTING ---
        $sort = $request->get('sort', 'latest');
        if ($sort == 'name_asc') $query->orderBy('product_name', 'asc');
        elseif ($sort == 'name_desc') $query->orderBy('product_name', 'desc');
        else $query->latest();

        $products = $query->paginate(15)->withQueryString();
        
        $all_categories = ProductCategory::withCount('products')
            ->with(['subcategories' => function($query) {
                $query->withCount('products');
            }])
            ->orderBy('name')
            ->get();

        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.product.index', compact('products', 'all_categories', 'buyers', 'craftsmen', 'subCategories', 'categories'));
    }



    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.product.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        // Auto-generate product code if not provided
        if (empty($request->product_code)) {
            $request->merge(['product_code' => Product::generateProductCode()]);
        }

        $validated = $request->validate([
            'product_code' => 'nullable|string|max:255|unique:products,product_code',
            'relabel_code' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'bp_code' => 'nullable|string|exists:buyers,bp_code',
            'craftsman_code' => 'nullable|string|exists:craftmen,craftman_code',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|in:Piece,Pair',
            // 'order_type' => 'required|in:Regular,Urgent,Super Urgent',
            'open_close' => 'nullable|in:Open,Close',
            'size' => 'nullable|string|max:255',
            'length' => 'nullable|string|max:255',
            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|gte:weight_from',
            'hallmark' => 'nullable|string|max:255',
            'rodium' => 'nullable|string|max:255',
            'hook' => 'nullable|string|max:255',
            'stone' => 'nullable|string|max:255',
            'enamel' => 'nullable|string|max:255',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $product = Product::create([
            'product_code' => $validated['product_code'],
            'relabel_code' => $validated['relabel_code'] ?? null,
            'product_name' => $validated['product_name'],
            'bp_code' => $validated['bp_code'] ?? $validated['craftsman_code'] ?? null,
            'product_category_id' => $validated['product_category_id'],
            'product_subcategory_id' => $validated['product_subcategory_id'] ?? null,
            'type' => $validated['type'],
            // 'order_type' => $validated['order_type'],
            'open_close' => $validated['open_close'] ?? null,
            'size' => $validated['size'] ?? null,
            'length' => $validated['length'] ?? null,
            'weight_from' => $validated['weight_from'] ?? null,
            'weight_to' => $validated['weight_to'] ?? null,
            'hallmark' => $validated['hallmark'] ?? null,
            'rodium' => $validated['rodium'] ?? null,
            'hook' => $validated['hook'] ?? null,
            'stone' => $validated['stone'] ?? null,
            'enamel' => $validated['enamel'] ?? null,
            'created_by' => auth()->guard('admin')->id(), // Track which admin created the product
        ]);

        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $image) {
                $path = $image->store('products', 'public');

                // Apply watermark to the image
                $watermarkedPath = $watermarkService->addWatermark($path);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $watermarkedPath,
                ]);
            }
        }

        return redirect()->route('admin.product.index')->with('success', 'Product created successfully');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'subcategory', 'images']);
        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.product.show', compact('product'));
    }

    

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = ProductCategory::whereIn('id', function ($query) {
            $query->select('product_category_id')
                ->from('products')
                ->whereNotNull('bp_code');
        })->orWhereIn('name', function ($query) {
            $query->select('product_category')
                ->from('work_orders')
                ->whereNotNull('bp_code');
        })->orderBy('name')->get();
        $subcategories = $product->product_category_id ? ProductSubcategory::where('product_category_id', $product->product_category_id)->get() : collect();
        $product->load('images');
        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.product.edit', compact('product', 'categories', 'subcategories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_code' => 'nullable|string|max:255|unique:products,product_code,' . $product->id,
            'relabel_code' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'bp_code' => 'nullable|string|exists:buyers,bp_code',
            'craftsman_code' => 'nullable|string|exists:craftmen,craftman_code',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|in:Piece,Pair',
            // 'order_type' => 'required|in:Regular,Urgent,Super Urgent',
            'open_close' => 'nullable|in:Open,Close',
            'size' => 'nullable|string|max:255',
            'length' => 'nullable|string|max:255',
            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|gte:weight_from',
            'hallmark' => 'nullable|string|max:255',
            'rodium' => 'nullable|string|max:255',
            'hook' => 'nullable|string|max:255',
            'stone' => 'nullable|string|max:255',
            'enamel' => 'nullable|string|max:255',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $data = $validated;
        $data['bp_code'] = $validated['bp_code'] ?? $validated['craftsman_code'] ?? $product->bp_code;
        unset($data['craftsman_code']);

        $product->update($data);

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

        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $image) {
                $path = $image->store('products', 'public');

                // Apply watermark to the image
                $watermarkedPath = $watermarkService->addWatermark($path);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $watermarkedPath,
                ]);
            }
        }

        $returnUrl = $request->input('return_url');
        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Product updated successfully');
        }

        return redirect()->route('admin.product.index')->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        $product->delete();
        return back()->with('success', 'Product deleted successfully');
    }

    // AJAX: get subcategories by category
    public function getSubcategories(Request $request)
    {
        $request->validate(['category_id' => 'required|exists:product_categories,id']);
        $subcategories = ProductSubcategory::where('product_category_id', $request->category_id)
            ->withCount(['products' => function($query) {
                $query->whereNotNull('design_code'); // Usually we only count designs in this context
            }])
            ->orderBy('name')
            ->get();
            
        $category = ProductCategory::withCount(['products' => function($query) {
                $query->whereNotNull('design_code');
            }])->find($request->category_id);
            
        return response()->json([
            'subcategories' => $subcategories,
            'total_products' => $category ? $category->products_count : 0
        ]);
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

    public function getAllJson()
    {
        // Load all necessary relationships including images and design_code
        $products = Product::with(['category', 'subcategory', 'images'])->notFromFrozenAccounts()->get();
        return response()->json($products);
    }

    public function getProductJson(Product $product)
    {
        $product->load(['category', 'subcategory', 'images', 'creator']);

        // Add creator name for display
        $product->creator_name = $product->creator ?
            ($product->creator->full_name ?? $product->creator->name ?? 'N/A') :
            'N/A';

        return response()->json($product);
    }
    

    public function export(Request $request)
    {
        return Excel::download(new ProductAdminExport($request), 'ProductAdminExport_' . now()->format('d-m-Y') . '.xlsx');
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_products', []);
        $products = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        
        $buyers = Buyer::orderBy('business_name')->get();
        $craftsmen = Craftman::orderBy('business_name')->get();
        $subCategories = ProductSubcategory::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('admin.product.print-selected', compact('products'));
    }

    public function bulkUpload(Request $request)
{
    $request->validate([
        'zip_file' => 'required|mimes:zip|max:102400',
    ]);

    $zip = new \ZipArchive;
    $file = $request->file('zip_file');

    if ($zip->open($file->getRealPath()) === TRUE) {
        $timestamp = time();
        $baseTempPath = storage_path('app/temp_admin_bulk_' . $timestamp);
        $zip->extractTo($baseTempPath);
        $zip->close();

        $excelFile = null;
        $actualExtractPath = $baseTempPath;
        
        // Correctly using RecursiveIteratorIterator to look inside folders
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseTempPath));

        foreach ($files as $fileInfo) {
            // Check if it's a file, not a directory
            if ($fileInfo->isFile()) {
                $extension = strtolower($fileInfo->getExtension());
                if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                    // Ignore hidden system files starting with ._
                    if (strpos($fileInfo->getFilename(), '._') === false) {
                        $excelFile = $fileInfo->getRealPath();
                        // Point to the folder where the Excel actually lives
                        $actualExtractPath = dirname($excelFile);
                        break;
                    }
                }
            }
        }

        if (!$excelFile || !file_exists($excelFile)) {
            \Illuminate\Support\Facades\File::deleteDirectory($baseTempPath);
            return redirect()->back()->with('Error', 'Excel Not found. Check your ZIP structure.');
        }

        try {
            // Use full namespaces to avoid "Class not found" errors
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProductImport($actualExtractPath), $excelFile);
            
            \Illuminate\Support\Facades\File::deleteDirectory($baseTempPath);
            return redirect()->back()->with('Success', 'Products and Images added successfully');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\File::deleteDirectory($baseTempPath);
            // Catching specific errors from the import class
            return redirect()->back()->with('Error', 'Import Error: ' . $e->getMessage());
        }
    }

    return redirect()->back()->with('Error', 'Could Not Open Zip File');
}
}
