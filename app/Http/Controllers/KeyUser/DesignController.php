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
    $query = Product::with(['category', 'subcategory'])
        ->whereNotNull('design_code')
        ->where('design_status', 'Accepted')
        ->whereNotNull('type')
        ->where('type', '!=', '')
        ->notFromFrozenAccounts();


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

    // Filter by Category Relationship
    if ($request->filled('filter_category')) {
        $query->whereHas('category', function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->filter_category . '%');
        });
    }

    // Filter by Subcategory Relationship
    if ($request->filled('filter_subcategory')) {
        $query->whereHas('subcategory', function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->filter_subcategory . '%');
        });
    }

    $designs = $query->latest()->paginate(15);

    return view('key-user.design.index', compact('designs'));
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