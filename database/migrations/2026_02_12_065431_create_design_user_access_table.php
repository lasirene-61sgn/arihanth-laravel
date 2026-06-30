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
        Schema::create('design_user_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->enum('user_type', ['buyer', 'key_user', 'user', 'craftsman']);
            $table->string('user_code');
            $table->timestamp('unlocked_until')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Indexes for performance
            $table->index(['product_id', 'user_type', 'user_code'], 'idx_product_user');
            $table->index('unlocked_until', 'idx_unlocked_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_user_access');
    }

};
