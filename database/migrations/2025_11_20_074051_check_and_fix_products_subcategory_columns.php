<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the products table exists
        if (Schema::hasTable('products')) {
            // Check if subcategory_id column exists
            if (Schema::hasColumn('products', 'subcategory_id')) {
                // Check if product_subcategory_id column exists
                if (Schema::hasColumn('products', 'product_subcategory_id')) {
                    // Move data from subcategory_id to product_subcategory_id if needed
                    DB::statement('UPDATE products SET product_subcategory_id = subcategory_id WHERE product_subcategory_id IS NULL AND subcategory_id IS NOT NULL');
                    
                    // Drop the old subcategory_id column
                    Schema::table('products', function (Blueprint $table) {
                        $table->dropColumn('subcategory_id');
                    });
                } else {
                    // Rename subcategory_id to product_subcategory_id
                    Schema::table('products', function (Blueprint $table) {
                        $table->renameColumn('subcategory_id', 'product_subcategory_id');
                    });
                }
            }
            
            // Ensure product_subcategory_id column exists and has proper constraints
            if (!Schema::hasColumn('products', 'product_subcategory_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreignId('product_subcategory_id')->nullable()->constrained('product_subcategories')->nullOnDelete()->after('product_category_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way fix migration, no rollback needed
    }
};