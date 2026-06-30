<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Exports\AdminCatalogueExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;


class CatalogueController extends Controller
{
    /**
     * Display all accepted products (catalogue)
     */
    public function index(Request $request)
{
    $query = Product::with(['category', 'subcategory', 'images', 'creator'])
        ->where('design_status', 'Accepted')
        ->whereNotNull('design_code')
        ->whereNotNull('bp_code')
        ->notFromFrozenAccounts();

    // Advanced Filters from Dropdown
    if ($request->filled('product_name')) {
        $query->where('product_name', 'like', '%' . $request->product_name . '%');
    }
    if ($request->filled('product_code')) {
        $query->where('product_code', 'like', '%' . $request->product_code . '%');
    }
    if ($request->filled('category')) {
        $query->whereHas('category', function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->category . '%');
        });
    }
    if ($request->filled('subcategory')) {
        $query->whereHas('subcategory', function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->subcategory . '%');
        });
    }
    if ($request->filled('bp_code')) {
        $query->where('bp_code', 'like', '%' . $request->bp_code . '%');
    }

    // Global Search
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('product_name', 'like', '%' . $request->search . '%')
              ->orWhere('product_code', 'like', '%' . $request->search . '%')
              ->orWhere('design_code', 'like', '%' . $request->search . '%');
        });
    }

    // Sorting
    $sort = $request->get('sort', 'latest');
    switch ($sort) {
        case 'name_asc':
            $query->orderBy('product_name', 'asc');
            break;
        case 'design_asc':
            $query->orderBy('design_code', 'asc');
            break;
        default:
            $query->latest();
            break;
    }

    $products = $query->paginate(15)->withQueryString();
    return view('admin.catalogue.index', compact('products'));
}

public function export(Request $request) 
{
    // Passes the request filters to the Excel Export class
    return Excel::download(new AdminCatalogueExport($request), 'catalogue_export.xlsx');
}

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_products', []);
        $products = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        return view('admin.catalogue.print-selected', compact('products'));
    }
}
