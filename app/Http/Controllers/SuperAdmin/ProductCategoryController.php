<?php

namespace App\Http\Controllers\SuperAdmin;

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

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name,' . $category->id,
        ]);
        $category->update($validated);
        return response()->json(['status' => 'success']);
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->count() > 0) {
            return response()->json(['status' => 'error', 'message' => 'Category has products. Cannot delete.']);
        }
        $category->subcategories()->delete();
        $category->delete();
        return response()->json(['status' => 'success']);
    }

    public function updateSubcategory(Request $request, ProductSubcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $subcategory->update($validated);
        return response()->json(['status' => 'success']);
    }

    public function destroySubcategory(ProductSubcategory $subcategory)
    {
        if ($subcategory->products()->count() > 0) {
            return response()->json(['status' => 'error', 'message' => 'Subcategory has products. Cannot delete.']);
        }
        $subcategory->delete();
        return response()->json(['status' => 'success']);
    }

    public function bulkDeleteCategories(Request $request)
    {
        $ids = $request->input('ids', []);
        $hasProducts = ProductCategory::whereIn('id', $ids)->whereHas('products')->exists();
        if ($hasProducts) {
            return response()->json(['status' => 'error', 'message' => 'Some categories have products. Cannot delete.']);
        }
        ProductSubcategory::whereIn('product_category_id', $ids)->delete();
        ProductCategory::whereIn('id', $ids)->delete();
        return response()->json(['status' => 'success']);
    }

    public function bulkDeleteSubcategories(Request $request)
    {
        $ids = $request->input('ids', []);
        $hasProducts = ProductSubcategory::whereIn('id', $ids)->whereHas('products')->exists();
        if ($hasProducts) {
            return response()->json(['status' => 'error', 'message' => 'Some subcategories have products. Cannot delete.']);
        }
        ProductSubcategory::whereIn('id', $ids)->delete();
        return response()->json(['status' => 'success']);
    }
}
