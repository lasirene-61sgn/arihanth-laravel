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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->date('repair_date')->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->text('repair_details')->nullable();
            $table->text('sample_details')->nullable();
            $table->string('item_given_to')->nullable();
            $table->string('image_proof')->nullable();
            $table->string('order_no')->nullable();
            $table->string('repair')->nullable();
            $table->string('ref')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('buyer_id')->references('id')->on('buyers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
