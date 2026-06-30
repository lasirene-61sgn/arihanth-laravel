<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('relabel_code')->nullable();
            $table->string('product_name');
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('product_subcategory_id')->nullable()->constrained('product_subcategories')->nullOnDelete();
            $table->enum('type', ['Piece', 'Pair'])->nullable();
            $table->enum('order_type', ['Regular', 'Urgent', 'Super Urgent'])->default('Regular');
            $table->enum('open_close', ['Open', 'Close'])->nullable();
            $table->string('size')->nullable();
            $table->string('length')->nullable();
            $table->decimal('weight_from', 10, 3)->nullable();
            $table->decimal('weight_to', 10, 3)->nullable();
            $table->string('hallmark')->nullable();
            $table->string('rodium')->nullable();
            $table->string('hook')->nullable();
            $table->string('stone')->nullable();
            $table->string('enamel')->nullable();
            $table->string('product_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
