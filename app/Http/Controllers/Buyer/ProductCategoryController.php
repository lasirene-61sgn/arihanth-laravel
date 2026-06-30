<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function store(Request $request)
    {
        // If 'parent_category_id' present, create subcategory; else create category
        if ($request->filled('parent_category_id')) {
            $validated = $request->validate([
                'parent_category_id' => 'required|exists:product_categories,id',
                'name' => 'required|string|max:255',
            ]);

            $subcategory = ProductSubcategory::create([
                'product_category_id' => $validated['parent_category_id'],
                'name' => $validated['name'],
            ]);

            return response()->json(['status' => 'success', 'subcategory' => $subcategory]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'has_hook' => 'nullable|boolean',
            'has_enamel' => 'nullable|boolean',
            'has_rodium' => 'nullable|boolean',
            'has_open_close' => 'nullable|boolean',
            'has_stone' => 'nullable|boolean',
        ]);

        $category = ProductCategory::create([
            'name' => $validated['name'],
            'has_hook' => $request->boolean('has_hook'),
            'has_enamel' => $request->boolean('has_enamel'),
            'has_rodium' => $request->boolean('has_rodium'),
            'has_open_close' => $request->boolean('has_open_close'),
            'has_stone' => $request->boolean('has_stone'),
        ]);

        return response()->json(['status' => 'success', 'category' => $category]);
    }
    
    public function getCategoryOptions(Request $request)
    {
        $categoryId = $request->get('category_id');
        
        if (!$categoryId) {
            return response()->json([
                'has_hook' => false,
                'has_enamel' => false,
                'has_rodium' => false,
                'has_open_close' => false,
                'has_stone' => false,
            ]);
        }
        
        $category = ProductCategory::find($categoryId);
        
        if (!$category) {
            return response()->json([
                'has_hook' => false,
                'has_enamel' => false,
                'has_rodium' => false,
                'has_open_close' => false,
                'has_stone' => false,
            ]);
        }
        
        return response()->json([
            'has_hook' => (bool) $category->has_hook,
            'has_enamel' => (bool) $category->has_enamel,
            'has_rodium' => (bool) $category->has_rodium,
            'has_open_close' => (bool) $category->has_open_close,
            'has_stone' => (bool) $category->has_stone,
        ]);
    }
}