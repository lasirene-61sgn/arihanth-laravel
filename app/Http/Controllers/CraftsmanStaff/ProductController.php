<?php

namespace App\Http\Controllers\CraftsmanStaff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Exports\CraftsmanProductExport;
use App\Imports\ProductImport;
use App\Models\ProductImage;
use App\Services\ImageWatermarkService;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{


    public function index(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('product_view') && !$staff->hasPermission('product_create') && !$staff->hasPermission('product_edit')) {
                abort(403, 'Unauthorized action.');
            }
        }
        $craftsman = $this->currentCraftsman();

        $query = Product::with(['category', 'subcategory', 'images', 'creator'])
            ->where('bp_code', $craftsman->craftman_code)
            ->whereNotNull('type');

        // --- SEARCH & FILTERS ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_product_code')) {
            $query->where('product_code', 'like', '%' . $request->filter_product_code . '%');
        }
        if ($request->filled('filter_product_name')) {
            $query->where('product_name', 'like', '%' . $request->filter_product_name . '%');
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

        // --- SORTING ---
        $sort = $request->get('sort', 'latest');
        if ($sort == 'name_asc') $query->orderBy('product_name', 'asc');
        elseif ($sort == 'name_desc') $query->orderBy('product_name', 'desc');
        else $query->latest();

        $products = $query->paginate(15)->withQueryString();

        // Load categories and subcategories for the dropdowns
        $categories = \App\Models\ProductCategory::orderBy('name')->get();
        $subcategories = \App\Models\ProductSubcategory::orderBy('name')->get();

        return view('craftsman_staff.product.index', compact('products', 'categories', 'subcategories'));
    }



    public function create()
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('product_create')) abort(403, 'Unauthorized action.');
        }
        $categories = ProductCategory::all();
        return view('craftsman_staff.product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('product_create')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();

        if (empty($request->product_code)) {
            $request->merge(['product_code' => Product::generateProductCode()]);
        }

        $request->validate([
            'product_code' => 'nullable|string|max:255|unique:products,product_code',
            'product_name' => 'nullable|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $productCode = $request->product_code ?? Product::generateProductCode();

            $productData = [
                'product_code' => $request->product_code,
                'product_name' => $request->product_name,
                'product_category_id' => $request->product_category_id,
                'product_subcategory_id' => $request->product_subcategory_id,
                'type' => $request->type,
                'size' => $request->size,
                'length' => $request->length,
                'weight_from' => $request->weight_from,
                'weight_to' => $request->weight_to,
                'hallmark' => $request->hallmark,
                'rodium' => $request->rodium,
                'hook' => $request->hook,
                'stone' => $request->stone,
                'enamel' => $request->enamel,
                'open_close' => $request->open_close,
                'created_by' => $craftsman->id,
                'bp_code' => $craftsman->craftman_code,
                'design_status' => 'Pending',
            ];

            if ($staff = $this->currentStaff()) {
                $productData['craftsman_staff_id'] = $staff->id;
            }

            $product = Product::create($productData);

            // Handle image uploads
            if ($request->hasFile('product_images')) {
                $watermarkService = new ImageWatermarkService();
                foreach ($request->file('product_images') as $image) {
                    $path = $image->store('products', 'public');
                    $watermarkedPath = $watermarkService->addWatermark($path);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $watermarkedPath,
                    ]);
                }
            }

            return redirect()->route('craftsman_staff.product.index')->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Product $product)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('product_view') && !$staff->hasPermission('product_create') && !$staff->hasPermission('product_edit')) {
                abort(403, 'Unauthorized action.');
            }
        }
        $craftsman = $this->currentCraftsman();
        if ($product->bp_code != $craftsman->craftman_code) abort(403);
        $product->load(['category', 'subcategory', 'images', 'creator']);
        return view('craftsman_staff.product.show', compact('product'));
    }

    public function edit(Product $product)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('product_edit')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        if ($product->bp_code != $craftsman->craftman_code) abort(403);
        $categories = ProductCategory::all();
        $subcategories = ProductSubcategory::where('product_category_id', $product->product_category_id)->get();
        $product->load('images');
        return view('craftsman_staff.product.edit', compact('product', 'categories', 'subcategories'));
    }

    public function update(Request $request, Product $product)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('product_edit')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        if ($product->bp_code != $craftsman->craftman_code) abort(403);

        $request->validate([
            'product_name' => 'nullable|string|max:255',
            'product_category_id' => 'sometimes|required|exists:product_categories,id',
            'product_subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'sometimes|required|string',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer',
        ]);

        try {
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
            if ($request->hasFile('product_images')) {
                $watermarkService = new ImageWatermarkService();
                foreach ($request->file('product_images') as $image) {
                    $path = $image->store('products', 'public');
                    $watermarkedPath = $watermarkService->addWatermark($path);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $watermarkedPath,
                    ]);
                }
            }

            $product->update($request->except('product_images', 'delete_images'));

            return redirect()->route('craftsman_staff.product.index')->with('success', 'Product updated!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Update failed.')->withInput();
        }
    }

    public function destroy(Product $product)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('product_edit')) abort(403, 'Unauthorized action.'); // Assuming product_edit covers delete too, or we can use product_create
        }
        $craftsman = $this->currentCraftsman();
        if ($product->bp_code != $craftsman->craftman_code) abort(403);

        // Delete image files and records
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }
        $product->images()->delete();
        $product->delete();
        return redirect()->route('craftsman_staff.product.index')->with('success', 'Deleted!');
    }

    public function getSubcategories($categoryId)
    {
        $subcategories = ProductSubcategory::where('product_category_id', $categoryId)->get();

        return response()->json($subcategories);
    }

    public function export(Request $request)
    {
        return Excel::download(new CraftsmanProductExport($request), 'My_Craftsman_Products_' . now()->format('d-m-Y') . '.xlsx');
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_products', []);
        $products = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        return view('admin.product.print-selected', compact('products'));
    }

    public function bulkUpload(Request $request)
    {
        $craftsman = $this->currentCraftsman();
        $request->validate([
            'zip_file' => 'required|mimes:zip|max:10400',
        ]);

        $zip = new \ZipArchive;
        $file = $request->file('zip_file');
        if ($zip->open($file->getRealPath()) === TRUE) {
            $timestamp = time();
            $baseTempPath = storage_path('app/temp_buyer_bulk_' . $timestamp);
            $zip->extractTo($baseTempPath);
            $zip->close();

            $excelFile = null;
            $actualExtarctPath = $baseTempPath;

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseTempPath));

            foreach ($files as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $extension = strtolower($fileInfo->getExtension());
                    if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                        if (strpos($fileInfo->getFileName(), '._') === false) {
                            $excelFile = $fileInfo->getRealPath();
                            $actualExtarctPath = dirname($excelFile);
                            break;
                        }
                    }
                }
            }
            if (!$excelFile) {
                File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('Error', 'Inside the ZIP Excel are nto found');
            }

            try {
                Excel::import(new ProductImport($actualExtarctPath, $craftsman->craftsman_code), $excelFile);
                File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('Success', 'Excel Uploaded and products are also uploaded');
            } catch (\Exception $e) {
                File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('Error', 'Error message: ' . $e->getMessage());
            }
        }
        return redirect()->back()->with('Error', 'Could Not Uploaded Zip');
    }
}
