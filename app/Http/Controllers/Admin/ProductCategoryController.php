<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function store(Request $request)
    {
        // Validate request
        if ($request->has('parent_category_id')) {
            // Creating a subcategory
            $request->validate([
                'parent_category_id' => 'required|exists:product_categories,id',
                'name' => 'required|string|max:255',
            ]);

            $subcategory = ProductSubcategory::create([
                'product_category_id' => $request->parent_category_id,
                'name' => $request->name,
            ]);

            return response()->json(['subcategory' => $subcategory]);
        } else {
            // Creating a category
            $request->validate([
                'name' => 'required|string|max:255',
                'has_hook' => 'nullable|boolean',
                'has_enamel' => 'nullable|boolean',
                'has_rodium' => 'nullable|boolean',
                'has_open_close' => 'nullable|boolean',
                'has_stone' => 'nullable|boolean',
            ]);

            $category = ProductCategory::create([
                'name' => $request->name,
                'has_hook' => $request->has_hook ?? false,
                'has_enamel' => $request->has_enamel ?? false,
                'has_rodium' => $request->has_rodium ?? false,
                'has_open_close' => $request->has_open_close ?? false,
                'has_stone' => $request->has_stone ?? false,
            ]);

            return response()->json(['category' => $category]);
        }
    }
}