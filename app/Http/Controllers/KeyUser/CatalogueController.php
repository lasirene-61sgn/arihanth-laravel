<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KeyUserCatalogueExport;

class CatalogueController extends Controller
{
    /**
     * Display only the LOGGED-IN key user's own accepted products
     */
   public function index(Request $request)
{
    $user = Auth::guard('key_user')->user();
    if (!$user) return redirect()->route('key-user.login');

    $query = Product::with(['category', 'subcategory', 'images'])
        ->where('bp_code', $user->bp_code)
        ->where('design_status', 'Accepted')
        ->whereNotNull('design_code')
        ->whereNotNull('type')
        ->where('type', '!=', '')
        ->notFromFrozenAccounts();

    // Only show designs that are currently unlocked
    $query->where(function ($q) {
        $q->whereNull('design_view_unlocked_until')
            ->orWhere('design_view_unlocked_until', '>=', now());
    });

    // --- SEARCH & FILTERS ---
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
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

    // Map Creator Info
    $products->transform(function ($product) {
        $creator = \App\Models\KeyUser::find($product->created_by) ?? \App\Models\Buyer::find($product->created_by);
        $product->creator_name = $creator->full_name ?? $creator->company_name ?? 'Unknown';
        $product->creator_code = $creator->user_code ?? $creator->bp_code ?? 'N/A';
        return $product;
    });

    // Load Categories and Subcategories for Dropdown Filters
    $categories = \App\Models\ProductCategory::orderBy('name')->get();
    $subcategories = \App\Models\ProductSubcategory::orderBy('name')->get();

    return view('key-user.catalogue.index', compact('products', 'categories', 'subcategories'));
}
    public function export(Request $request)
    {
        // Generate a clean filename with the date
        $fileName = 'My-Catalogue-' . now()->format('d-M-Y') . '.xlsx';

        return Excel::download(new KeyUserCatalogueExport($request), $fileName);
    }

    public function printSelected(Request $request)
    {
        $user = Auth::guard('key_user')->user();
        $ids = $request->input('selected_products', []);

        $products = Product::whereIn('id', $ids)
            ->where('bp_code', $user->bp_code)
            ->where('design_status', 'Accepted')
            ->with(['category', 'subcategory', 'images'])
            ->get();

        return view('admin.product.print-selected', compact('products'));
    }
}
