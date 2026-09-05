<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\BuyerDesignExport as ExportsBuyerDesignExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\BuyerDesignExport;
use Maatwebsite\Excel\Facades\Excel;

class DesignController extends Controller
{
    public function index(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();

        // 1. Start Query with standard filters
        $query = Product::with(['category', 'subcategory'])
            ->where('bp_code', $buyer->bp_code)
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->notFromFrozenAccounts();

        // 2. QUICK SEARCH (Search Name or Design Code)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('design_code', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        // 3. ADVANCED FILTERS
        if ($request->filled('filter_name')) {
            $query->where('product_name', 'like', '%' . $request->filter_name . '%');
        }
        if ($request->filled('filter_code')) {
            $query->where('design_code', 'like', '%' . $request->filter_code . '%');
        }
        if ($request->filled('category')) {
            $query->where('product_category_id', $request->category);
        }
        if ($request->filled('subcategory')) {
            $query->where('product_subcategory_id', $request->subcategory);
        }

        // 4. SORTING (Rearrange Table)
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        // 5. Get Data (using paginate for better ERP performance)
        $designs = $query->paginate(15);
        $categories = ProductCategory::all();
        $subcategories = ProductSubcategory::all();

        return view('buyer.design.index', compact('designs', 'categories', 'subcategories'));
    }

    /**
     * Display the details of a specific accepted design.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'subcategory', 'images'])->findOrFail($id);

        if ($product->isDesignLocked(Auth::guard('buyer')->user())) {
            abort(403, 'This design is currently locked.');
        }

        // Security check: only show if it has an official design code
        if (!$product->design_code || $product->design_status !== 'Accepted') {
            abort(404, 'Design not found in the approved catalogue.');
        }

        return view('buyer.design.show', compact('product'));
    }
    public function export(Request $request)
    {
        // We name the file with today's date so it's easy to find
        $fileName = 'Approved_Designs_' . now()->format('Y-m-d_H-i') . '.xlsx';

        // We pass the entire $request to the Export class to keep the filters
        return Excel::download(new ExportsBuyerDesignExport($request), $fileName);
    }
}
