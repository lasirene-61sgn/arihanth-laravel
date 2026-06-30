<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CatalogueController extends Controller
{
    public function index(Request $request)
{
    $query = Product::with(['category', 'subcategory', 'images', 'creator'])
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->notFromFrozenAccounts();

    // Search Logic (Global)
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('product_name', 'like', '%' . $request->search . '%')
              ->orWhere('product_code', 'like', '%' . $request->search . '%')
              ->orWhere('bp_code', 'like', '%' . $request->search . '%')
              ->orWhere('design_code', 'like', '%' . $request->search . '%');
        });
    }

    // BP Code Filter
    if ($request->filled('bp_code')) {
        $query->where('bp_code', $request->bp_code);
    }

    // Product Name Filter
    if ($request->filled('product_name')) {
        $query->where('product_name', 'like', '%' . $request->product_name . '%');
    }

    // Product Code Filter
    if ($request->filled('product_code')) {
        $query->where('product_code', 'like', '%' . $request->product_code . '%');
    }

    // Category Filter
    if ($request->filled('category_id')) {
        $query->where('product_category_id', $request->category_id);
    }

    // Export Logic
    if ($request->get('export') === 'excel') {
        return Excel::download(new ProductExport($request), 'catalogue_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    $products = $query->latest()->paginate(15)->withQueryString();
    $categories = ProductCategory::orderBy('name')->get();
    $bpCodes = Product::whereNotNull('bp_code')->where('bp_code', '!=', '')->distinct()->orderBy('bp_code')->pluck('bp_code');

    return view('super-admin.catalogue.index', compact('products', 'categories', 'bpCodes'));
}

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_products', []);
        $products = Product::whereIn('id', $ids)->with(['category', 'subcategory', 'images'])->get();
        return view('super-admin.catalogue.print-selected', compact('products'));
    }
}
