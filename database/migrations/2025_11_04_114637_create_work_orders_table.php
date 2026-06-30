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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique();
            $table->string('product_image')->nullable();
            $table->string('bp_code')->nullable();
            $table->string('customer_name');
            $table->string('reference_no')->nullable();
            $table->date('due_date')->nullable();
            $table->string('product_category')->nullable();
            $table->string('quantity');
            $table->string('type')->nullable();
            $table->string('order_type')->nullable();
            $table->string('weight_from')->nullable();
            $table->string('weight_to')->nullable();
            $table->text('narration_craftsman')->nullable();
            $table->text('narration_admin')->nullable();
            $table->string('open_close')->nullable();
            $table->string('hallmark')->nullable();
            $table->string('rodium')->nullable();
            $table->string('hook')->nullable();
            $table->string('size')->nullable();
            $table->string('stone')->nullable();
            $table->string('enamel')->nullable();
            $table->string('length')->nullable();
            $table->string('product_code')->nullable();
            $table->string('relabel_code')->nullable();
            $table->string('product_name')->nullable();
            $table->date('craftsman_due_date')->nullable();
            $table->string('allocated_craftsman_bp_code')->nullable();
            $table->string('status')->default('new'); // new, allocated, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};