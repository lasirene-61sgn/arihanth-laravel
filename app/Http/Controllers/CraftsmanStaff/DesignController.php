<?php

namespace App\Http\Controllers\CraftsmanStaff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Exports\CraftsmanDesignExport;
use Maatwebsite\Excel\Facades\Excel;

class DesignController extends Controller
{
    /**
     * Display the global approved design catalogue for craftsmen.
     */
    

public function index(Request $request)
{
    $query = Product::with(['category', 'subcategory'])
        ->whereNotNull('design_code')
        ->where('design_status', 'Accepted')
        ->whereNotNull('type'); // Filter out bulk uploaded work orders (which have null type)


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

    return view('craftsman_staff.design.index', compact('designs'));
}



    /**
     * Display specific design details.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'subcategory', 'images'])->findOrFail($id);

        if ($product->isDesignLocked(\Illuminate\Support\Facades\$this->currentCraftsman())) {
             abort(403, 'This design is currently locked.');
        }

        // Ensure it is an approved design
        if (!$product->design_code || $product->design_status !== 'Accepted') {
            abort(404, 'This design is not available in the approved catalogue.');
        }

        return view('craftsman_staff.design.show', compact('product'));
    }

    public function export(Request $request) 
{
    return Excel::download(new CraftsmanDesignExport($request), 'Global_Design_Catalogue_' . now()->format('d-m-Y') . '.xlsx');
}
}
