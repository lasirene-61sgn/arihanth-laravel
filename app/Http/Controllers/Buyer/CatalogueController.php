<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        if (!$buyer) abort(403);

        $query = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $buyer->bp_code)
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
        if ($request->filled('filter_product_name')) {
            $query->where('product_name', 'like', '%' . $request->filter_product_name . '%');
        }
        if ($request->filled('filter_category')) {
            $query->where('product_category_id', $request->filter_category);
        }
        if ($request->filled('filter_subcategory')) {
            $query->where('product_subcategory_id', $request->filter_subcategory);
        }

        // --- SORTING ---
        $sort = $request->get('sort', 'latest');
        if ($sort == 'name_asc') $query->orderBy('product_name', 'asc');
        elseif ($sort == 'name_desc') $query->orderBy('product_name', 'desc');
        else $query->latest();

        $products = $query->paginate(15)->withQueryString();

        // Fetch categories and subcategories for the dropdowns
        $categories = ProductCategory::orderBy('name')->get();
        $subcategories = ProductSubcategory::orderBy('name')->get();

        return view('buyer.catalogue.index', compact('products', 'categories', 'subcategories'));
    }

    public function show($id)
    {
        $buyer = Auth::guard('buyer')->user();

        $product = Product::with(['category', 'subcategory', 'images'])
            ->where('bp_code', $buyer->bp_code)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->findOrFail($id);

        return view('buyer.catalogue.show', compact('product'));
    }

    public function printSelected(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        $ids = $request->input('selected_products', []);

        $products = Product::whereIn('id', $ids)
            ->where('bp_code', $buyer->bp_code)
            ->where('design_status', 'Accepted')
            ->with(['category', 'subcategory', 'images'])
            ->get();

        return view('buyer.catalogue.print-selected', compact('products'));
    }
}
