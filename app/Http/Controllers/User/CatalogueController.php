<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Exports\UserCatalogueExport;
use Maatwebsite\Excel\Facades\Excel;

class CatalogueController extends Controller
{
    /**
     * Display only key user's own accepted products (catalogue)
     */
    

public function index(Request $request)
{
    $user = Auth::user();
    
    $query = Product::with(['category', 'subcategory', 'images'])
        ->where('bp_code', $user->bp_code)
        ->where('design_status', 'Accepted')
        ->whereNotNull('design_code')
        ->whereNotNull('type')
        ->where('type', '!=', '')
        ->notFromFrozenAccounts();

    // Only show designs that are currently unlocked
    $query->where(function($q) {
        $q->whereNull('design_view_unlocked_until')
          ->orWhere('design_view_unlocked_until', '>=', now());
    });

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

    $products = $query->get();

    return view('user.catalogue.index', compact('products'));
}

public function export(Request $request) 
{
    return Excel::download(new UserCatalogueExport($request), 'My_Catalogue_' . now()->format('d-m-Y') . '.xlsx');
}
}
