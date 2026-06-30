<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->string('design_code')->unique();
            $table->string('design_type')->nullable();
            $table->string('image')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('design_name')->nullable();
            $table->json('select_product')->nullable();
            $table->json('select_order')->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('details')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
