<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Buyer;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductImage;
use App\Services\ImageWatermarkService;
use App\Exports\BuyerProductExport;
use App\Imports\ProductImport;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
   // Make sure this is at the top
public function index(Request $request)
{
    $buyer = Auth::guard('buyer')->user();
    
    // 1. Security Check
    if (!$buyer->hasPermission('product')) {
        return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
    }
    
    // 2. Start Query
    $query = Product::with(['category', 'subcategory', 'images'])
                ->where('bp_code', $buyer->bp_code);

    // 3. Handle SEARCH
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('product_name', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%");
        });
    }

    // 4. Handle ADVANCED FILTERS (Using your actual column names)
    if ($request->filled('filter_name')) {
        $query->where('product_name', 'like', '%' . $request->filter_name . '%');
    }
    if ($request->filled('filter_code')) {
        $query->where('product_code', 'like', '%' . $request->filter_code . '%');
    }
    
    // UPDATED: Using product_category_id instead of category_id
    if ($request->filled('category')) {
        $query->where('product_category_id', $request->category);
    }
    
    // UPDATED: Using product_subcategory_id instead of subcategory_id
    if ($request->filled('subcategory')) {
        $query->where('product_subcategory_id', $request->subcategory);
    }

    // 5. Handle SORTING
    $sort = $request->get('sort', 'created_at');
    
    // Safety check for sorting: if user selects 'category_id', change to 'product_category_id'
    if($sort == 'category_id') $sort = 'product_category_id';

    $direction = $request->get('direction', 'desc');
    $query->orderBy($sort, $direction);

    // 6. Get Data
    $products = $query->paginate(10);
    $categories = ProductCategory::all(); 
    $subcategories = ProductSubcategory::all();
            
    return view('buyer.product.index', compact('products', 'categories', 'subcategories'));
}
    
    public function create()
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has product permission
        if (!$buyer->hasPermission('product')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        
        $categories = ProductCategory::orderBy('name')->get();
        
        return view('buyer.product.create', compact('buyer', 'categories'));
    }
    
    public function store(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has product permission
        if (!$buyer->hasPermission('product')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }

        $validator = Validator::make($request->all(), [
            'product_code' => 'nullable|string|max:255|unique:products,product_code',
            'product_name' => 'nullable|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string|in:Piece,Pair',
            // 'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = Product::create([
            'product_code' => $request->product_code,
            'bp_code' => $buyer->bp_code,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            // 'description' => $request->description,
            'created_by' => $buyer->id,
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

        return redirect()->route('buyer.product.index')
            ->with('success', 'Product created successfully.');
    }
    
    public function show($id)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has product permission
        if (!$buyer->hasPermission('product')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        
        $product = Product::with(['category', 'subcategory', 'images', 'creator'])
            ->where('bp_code', $buyer->bp_code)
            ->findOrFail($id);
            
        return view('buyer.product.show', compact('product'));
    }
    
    public function edit($id)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has product permission
        if (!$buyer->hasPermission('product')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        
        $product = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $buyer->bp_code)
            ->findOrFail($id);
            
        $categories = ProductCategory::orderBy('name')->get();
        $subcategories = $product->product_category_id ? 
            ProductSubcategory::where('product_category_id', $product->product_category_id)->get() : 
            collect();
            
        return view('buyer.product.edit', compact('product', 'categories', 'subcategories'));
    }
    
    public function update(Request $request, $id)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has product permission
        if (!$buyer->hasPermission('product')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }

        $product = Product::where('bp_code', $buyer->bp_code)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_code' => 'nullable|string|max:255|unique:products,product_code,' . $product->id,
            'product_name' => 'nullable|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|string|in:Piece,Pair',
            // 'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product->update([
            'product_code' => $request->product_code,
            'product_name' => $request->product_name,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            // 'description' => $request->description,
        ]);

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

        return redirect()->route('buyer.product.index')
            ->with('success', 'Product updated successfully.');
    }
    
    public function destroy($id)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has product permission
        if (!$buyer->hasPermission('product')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }

        $product = Product::where('bp_code', $buyer->bp_code)->findOrFail($id);
        
        // Delete associated images
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }
        
        $product->delete();

        return redirect()->route('buyer.product.index')
            ->with('success', 'Product deleted successfully.');
    }
    
    public function getSubcategories(Request $request)
    {
        $subcategories = ProductSubcategory::where('product_category_id', $request->category_id)
            ->orderBy('name')
            ->get();
            
        return response()->json($subcategories);
    }
    
    public function getCategoryOptions(Request $request)
    {
        $categories = ProductCategory::orderBy('name')->get();
        return response()->json($categories);
    }

    public function export(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        return Excel::download(
            new BuyerProductExport($request, $buyer->bp_code), 
            'products_report_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function printSelected(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        $ids = $request->input('selected_products', []);
        
        $products = Product::whereIn('id', $ids)
            ->where('bp_code', $buyer->bp_code)
            ->with(['category', 'subcategory', 'images'])
            ->get();
            
        return view('admin.product.print-selected', compact('products'));
    }

    public function bulkUpload(Request $request){
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has product permission
        if (!$buyer->hasPermission('product')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        $request->validate([
            'zip_file' => 'required|mimes:zip|max:10400',
        ]);
        $zip = new \ZipArchive;
        $file = $request->file('zip_file');
        if($zip->open($file->getRealPath()) === TRUE){
            $timestamp = time();
            $baseTempPath = storage_path('app/temp_buyer_bulk_' . $timestamp);
            $zip->extractTo($baseTempPath);
            $zip->close();

            $excelFile = null;
            $actualExtarctPath = $baseTempPath;

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseTempPath));

            foreach($files as $fileInfo){
                if($fileInfo->isFile()){
                    $extension = strtolower($fileInfo->getExtension());
                    if(in_array($extension, ['xlsx', 'xls', 'csv'])){
                        if(strpos($fileInfo->getFileName(), '._') === false){
                            $excelFile = $fileInfo->getRealPath();
                            $actualExtarctPath = dirname($excelFile);
                            break;
                        }
                    }
                }
            }
            if(!$excelFile){
                File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('Error', 'Inside the ZIP Excel are nto found');
            }

            try{
                Excel::import(new ProductImport($actualExtarctPath, $buyer->bp_code), $excelFile);
                File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('Success', 'Excel Uploaded and products are also uploaded');
            }catch(\Exception $e){
                File::deleteDirectory($baseTempPath);
                return redirect()->back()->with('Error', 'Error message: ' . $e->getMessage());
            }
        }
        return redirect()->back()->with('Error', 'Could Not Uploaded Zip');
    }
}