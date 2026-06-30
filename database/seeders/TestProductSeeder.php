<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;

class TestProductSeeder extends Seeder
{
    public function run()
    {
        $category = ProductCategory::first();
        $subcategory = ProductSubcategory::first();
        
        if ($category && $subcategory) {
            Product::create([
                'product_code' => 'PRD9999',
                'product_name' => 'Test Product 2',
                'product_category_id' => $category->id,
                'product_subcategory_id' => $subcategory->id,
                'design_code' => 'TEST002'
            ]);
            
            echo "Test product created successfully!\n";
        } else {
            echo "Need category and subcategory to create product.\n";
        }
    }
}