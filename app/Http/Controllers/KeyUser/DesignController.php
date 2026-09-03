<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignController extends Controller
{
    /**
     * Display all accepted designs globally.
     */
    public function index(Request $request)
{
    $query = Product::with(['category', 'subcategory', 'images'])
        ->whereNotNull('design_code')
        ->where('design_status', 'Accepted')
        ->whereNotNull('type')
        ->where('type', '!=', '')
        ->notFromFrozenAccounts();

    // Quick Search (Search Input)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('design_code', 'like', "%{$search}%")
              ->orWhere('product_name', 'like', "%{$search}%")
              ->orWhere('product_code', 'like', "%{$search}%");
        });
    }

    // Filter by Design Code
    if ($request->filled('filter_design_code')) {
        $query->where('design_code', 'like', '%' . $request->filter_design_code . '%');
    }

    // Filter by Product Code
    if ($request->filled('filter_product_code')) {
        $query->where('product_code', 'like', '%' . $request->filter_product_code . '%');
    }

    // Filter by Product Name
    if ($request->filled('filter_name')) {
        $query->where('product_name', 'like', '%' . $request->filter_name . '%');
    }

    // Filter by Category Dropdown ID
    if ($request->filled('product_category_id')) {
        $query->where('product_category_id', $request->product_category_id);
    }

    // Filter by Subcategory Dropdown ID
    if ($request->filled('subcategory_id')) {
        $query->where(function ($q) use ($request) {
            $q->where('subcategory_id', $request->subcategory_id)
              ->orWhere('product_subcategory_id', $request->subcategory_id);
        });
    }

    // Sorting
    $sort = $request->get('sort', 'created_at');
    if ($sort === 'weight_from') {
        $query->orderBy('weight_from', 'asc');
    } elseif ($sort === 'design_code') {
        $query->orderBy('design_code', 'asc');
    } else {
        $query->latest();
    }

    $designs = $query->paginate(15)->withQueryString();

    // Fetch Categories and Subcategories for the filter dropdowns
    $categories = \App\Models\ProductCategory::orderBy('name')->get();
    $subcategories = \App\Models\ProductSubcategory::orderBy('name')->get();

    return view('key-user.design.index', compact('designs', 'categories', 'subcategories'));
}


    /**
     * Display the details of a specific accepted design.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'subcategory', 'images'])->findOrFail($id);

        if ($product->isDesignLocked(\Illuminate\Support\Facades\Auth::guard('key_user')->user())) {
            abort(403, 'This design is currently locked.');
        }

        // Security check: only show if it has an official design code
        if (!$product->design_code || $product->design_status !== 'Accepted') {
            abort(404, 'Design not found in the approved catalogue.');
        }

        return view('key-user.design.show', compact('product'));
    }

    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\KeyUserDesignExport($request), 'Approved-Design-Catalogue.xlsx');
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_products', []);

        $products = Product::whereIn('id', $ids)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->with(['category', 'subcategory', 'images'])
            ->get();

        return view('admin.product.print-selected', compact('products'));
    }
}
