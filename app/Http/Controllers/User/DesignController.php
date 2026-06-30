<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Exports\UserDesignExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class DesignController extends Controller
{
    /**
     * Display the global approved design catalogue.
     */
    

public function index(Request $request)
{
    $query = Product::with(['category', 'subcategory'])
        ->whereNotNull('design_code')
        ->where('design_status', 'Accepted')
        ->whereNotNull('type')
        ->where('type', '!=', '')
        ->notFromFrozenAccounts();


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

    $designs = $query->get();

    return view('user.design.index', compact('designs'));
}



    /**
     * Display specific design details.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'subcategory', 'images'])->findOrFail($id);

        if ($product->isDesignLocked(\Illuminate\Support\Facades\Auth::user())) {
             abort(403, 'This design is currently locked.');
        }

        // Security check: only show if it has an official design code
        if (!$product->design_code || $product->design_status !== 'Accepted') {
            abort(404, 'Design not found in the approved catalogue.');
        }

        return view('user.design.show', compact('product'));
    }
    public function export(Request $request) 
{
    return Excel::download(new UserDesignExport($request), 'Approved_Designs_' . now()->format('d-m-Y') . '.xlsx');
}
}