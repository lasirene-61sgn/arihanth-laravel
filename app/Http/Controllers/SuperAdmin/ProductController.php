<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductSubcategory;
use App\Services\ImageWatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\ProductExport;
use App\Exports\ProductTemplateExport;
use App\Imports\ProductImport;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // 1. Handle Export
        if ($request->get('export') === 'excel') {
            return Excel::download(new ProductExport($request), 'products-list.xlsx');
        }

        $query = Product::with(['category', 'subcategory', 'creator', 'images'])
            ->notFromFrozenAccounts()
            ->whereNotNull('type');

        // 2. Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%$search%")
                    ->orWhere('product_code', 'like', "%$search%");
            });
        }

        // 3. Filter Logic (Category)
        if ($request->filled('category_filter')) {
            $query->where('product_category_id', $request->category_filter);
        }

        // 4. Sort Logic
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(15)->withQueryString();

        // Get categories for the filter dropdown
        $categories = ProductCategory::orderBy('name')->get();
        $all_categories = ProductCategory::withCount('products')
            ->with(['subcategories' => function($query) {
                $query->withCount('products');
            }])
            ->orderBy('name')
            ->get();

        return view('super-admin.product.index', compact('products', 'categories', 'all_categories'));
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        return view('super-admin.product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (empty($request->product_code)) {
            $request->merge(['product_code' => Product::generateProductCode()]);
        }

        $validated = $request->validate([
            'product_code' => 'nullable|string|max:255|unique:products,product_code',
            // 'relabel_code' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'bp_code' => 'nullable|string|exists:buyers,bp_code',
            'craftsman_code' => 'nullable|exists:craftmen,craftman_code',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|in:Piece,Pair',
            // 'order_type' => 'nullable|in:Regular,Urgent,Super Urgent',
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
            // 'relabel_code' => $validated['relabel_code'] ?? null,
            'product_name' => $validated['product_name'],
            'bp_code' => $validated['bp_code'] ?? $validated['craftsman_code'] ?? null,
            'product_category_id' => $validated['product_category_id'],
            'product_subcategory_id' => $validated['subcategory_id'] ?? null,
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
            'created_by' => auth()->guard('super_admin')->id(),
        ]);

        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $image) {
                // Logic: Store in public disk so it's accessible via /storage/
                $path = $image->store('products', 'public');

                // Applies public\images\ajlogo.png via the service
                $watermarkedPath = $watermarkService->addWatermark($path);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $watermarkedPath,
                ]);
            }
        }

        return redirect()->route('super-admin.product.index')->with('success', 'Product created successfully');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'subcategory', 'images']);
        return view('super-admin.product.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();
        $subcategories = $product->product_category_id ? ProductSubcategory::where('product_category_id', $product->product_category_id)->get() : collect();
        $product->load('images');
        return view('super-admin.product.edit', compact('product', 'categories', 'subcategories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_code' => 'nullable|string|max:255|unique:products,product_code,' . $product->id,
            // 'relabel_code' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'bp_code' => 'nullable|string|exists:buyers,bp_code',
            'craftsman_code' => 'nullable|exists:craftmen,craftman_code',
            
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|in:Piece,Pair',
            // 'order_type' => 'nullable|in:Regular,Urgent,Super Urgent',
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
                // Store in public disk
                $path = $image->store('products', 'public');

                // Applies public\images\ajlogo.png via the service
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

        return redirect()->route('super-admin.product.index')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        $product->delete();
        return back()->with('success', 'Product deleted successfully');
    }

    public function generateCode()
    {
        return response()->json(['code' => Product::generateProductCode()]);
    }

    public function getSubcategories(Request $request)
    {
        $request->validate(['category_id' => 'required|exists:product_categories,id']);
        $subcategories = ProductSubcategory::where('product_category_id', $request->category_id)
            ->withCount(['products' => function($query) {
                $query->whereNotNull('design_code');
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

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_products', []);
        $products = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        return view('super-admin.product.print-selected', compact('products'));
    }

    // public function import(Request $request){
    //     $request->validate([
    //         'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
    //     ]);
    //     try{
    //         Excel::import(new ProductImport, $request->file('import_file'));
    //         return redirect()->back()->with('success', 'Products Imported Successfully');
    //     }catch(\Exception $e){
    //         return redirect()->back()->with('Error', 'Error During import: ' . $e->getMessage());
    //     }
    // }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|mimes:zip|max:102400',
        ]);

        $zip = new \ZipArchive;
        $file = $request->file('zip_file');

        if ($zip->open($file->getRealPath()) === TRUE) {
            $timestamp = time();
            $baseTempPath = storage_path('app/temp_bulk_' . $timestamp);
            $zip->extractTo($baseTempPath);
            $zip->close();

            $excelFile = null;
            $actualExtractPath = $baseTempPath;

            // CORRECTED ITERATOR: Use RecursiveIteratorIterator
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseTempPath));

            foreach ($files as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $extension = strtolower($fileInfo->getExtension());
                    if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                        // Ignore hidden system files
                        if (strpos($fileInfo->getFilename(), '._') === false) {
                            $excelFile = $fileInfo->getRealPath();
                            // This is key: point the path to where the images actually are
                            $actualExtractPath = dirname($excelFile);
                            break;
                        }
                    }
                }
            }

            if (!$excelFile) {
                \Illuminate\Support\Facades\File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('error', 'Excel file not found inside the zip');
            }

            try {
                // Use the actual path where the CSV and Images live
                \Maatwebsite\Excel\Facades\Excel::import(new ProductImport($actualExtractPath), $excelFile);

                \Illuminate\Support\Facades\File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('success', 'Everything uploaded successfully');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('error', 'Error During Import: ' . $e->getMessage());
            }
        }

        // This MUST be outside the IF block
        return redirect()->back()->with('error', 'Could not open the zip file. The file may be corrupt.');
    }
}
