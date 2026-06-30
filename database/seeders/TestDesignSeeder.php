<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;

class TestDesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test category
        $category = ProductCategory::firstOrCreate([
            'name' => 'Rings'
        ], [
            'has_hook' => false,
            'has_enamel' => true,
            'has_rodium' => true,
            'has_open_close' => false,
            'has_stone' => true
        ]);

        // Create a test subcategory
        $subcategory = ProductSubcategory::firstOrCreate([
            'product_category_id' => $category->id,
            'name' => 'Gold'
        ]);

        // Create test products
        Product::create([
            'product_code' => 'RING001',
            'product_name' => 'Gold Ring',
            'product_category_id' => $category->id,
            'product_subcategory_id' => $subcategory->id,
            'type' => 'Piece',
            'order_type' => 'Regular',
            'design_status' => 'pending'
        ]);

        Product::create([
            'product_code' => 'RING002',
            'product_name' => 'Silver Ring',
            'product_category_id' => $category->id,
            'product_subcategory_id' => $subcategory->id,
            'type' => 'Piece',
            'order_type' => 'Regular',
            'design_status' => 'accepted',
            'design_code' => 'DSG0001'
        ]);

        Product::create([
            'product_code' => 'RING003',
            'product_name' => 'Platinum Ring',
            'product_category_id' => $category->id,
            'product_subcategory_id' => $subcategory->id,
            'type' => 'Piece',
            'order_type' => 'Regular',
            'design_status' => 'rejected'
        ]);
    }
}