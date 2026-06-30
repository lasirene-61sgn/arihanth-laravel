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
        Schema::create('stock_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            $table->foreignId('craftsman_id')->nullable()->constrained('craftmen')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->enum('status', ['Pending', 'Allocated', 'Completed', 'Cancelled'])->default('Pending');
            $table->integer('total_items')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_orders');
    }
};
