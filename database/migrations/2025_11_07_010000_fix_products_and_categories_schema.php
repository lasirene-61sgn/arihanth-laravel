<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // product_categories: add flags if missing
        if (Schema::hasTable('product_categories')) {
            Schema::table('product_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('product_categories', 'has_hook')) {
                    $table->boolean('has_hook')->default(false);
                }
                if (!Schema::hasColumn('product_categories', 'has_enamel')) {
                    $table->boolean('has_enamel')->default(false);
                }
                if (!Schema::hasColumn('product_categories', 'has_rodium')) {
                    $table->boolean('has_rodium')->default(false);
                }
                if (!Schema::hasColumn('product_categories', 'has_open_close')) {
                    $table->boolean('has_open_close')->default(false);
                }
                if (!Schema::hasColumn('product_categories', 'has_stone')) {
                    $table->boolean('has_stone')->default(false);
                }
            });
        }

        // products: add missing columns
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'relabel_code')) {
                    $table->string('relabel_code')->nullable()->after('product_code');
                }
                if (!Schema::hasColumn('products', 'weight_from')) {
                    $table->decimal('weight_from', 10, 3)->nullable()->after('length');
                }
                if (!Schema::hasColumn('products', 'weight_to')) {
                    $table->decimal('weight_to', 10, 3)->nullable()->after('weight_from');
                }
            });
        }

        // product_subcategories: create if missing
        if (!Schema::hasTable('product_subcategories')) {
            Schema::create('product_subcategories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_category_id');
                $table->string('name');
                $table->timestamps();
                $table->unique(['product_category_id', 'name']);
            });
        }

        // product_images: create if missing
        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('path');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // This migration is additive; we won't drop columns in down to avoid data loss.
        // Optionally, you could drop created tables if needed.
        // Schema::dropIfExists('product_subcategories');
        // Schema::dropIfExists('product_images');
    }
};
