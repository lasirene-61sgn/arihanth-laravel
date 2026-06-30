<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exports\CraftsmanCatalogueExport;
use Maatwebsite\Excel\Facades\Excel;

class CatalogueController extends Controller
{
    

public function index(Request $request)
{
    $craftsman = Auth::guard('craftsman')->user();
    
    // Query: Must be mine (bp_code matches) AND Accepted AND have a Design Code
    $query = Product::with(['category', 'subcategory'])
        ->where('bp_code', $craftsman->craftman_code)
        ->where('design_status', 'Accepted')
        ->whereNotNull('design_code')
        ->whereNotNull('type')
        ->where('type', '!=', '');

    // --- SEARCH & FILTERS ---
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('product_name', 'like', "%{$search}%")
              ->orWhere('design_code', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%");
        });
    }

    if ($request->filled('filter_design_code')) {
        $query->where('design_code', 'like', '%' . $request->filter_design_code . '%');
    }
    if ($request->filled('filter_product_code')) {
        $query->where('product_code', 'like', '%' . $request->filter_product_code . '%');
    }
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

    $designs = $query->paginate(15);

    // Attach creator info (Current Craftsman)
    $craftsman = Auth::guard('craftsman')->user();
    foreach($designs as $design) {
        $design->creator_name = $craftsman->full_name ?? $craftsman->name ?? 'Craftsman';
        $design->creator_bp_code = $craftsman->craftman_code ?? 'N/A';
    }

    return view('craftsman.catalogue.index', compact('designs'));
}



    /**
     * Show details only if it belongs to this craftsman.
     */
    public function show($id)
    {
        $craftsman = Auth::guard('craftsman')->user();

        $design = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $craftsman->craftman_code)
            ->where('design_status', 'Accepted')
            ->findOrFail($id);

        return view('craftsman.catalogue.show', compact('design'));
    }

    public function export(Request $request) 
    {
        return Excel::download(new CraftsmanCatalogueExport($request), 'CraftsmanCatalogueExport_' . now()->format('d-m-Y') . '.xlsx');
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_products', []);
        $products = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        return view('admin.product.print-selected', compact('products'));
    }
}