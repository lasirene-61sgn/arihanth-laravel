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
        Schema::table('stock_order_items', function (Blueprint $table) {
            $table->foreignId('craftsman_id')->nullable()->after('product_id')->constrained('craftmen')->onDelete('set null');
            $table->enum('status', ['Pending', 'Accepted', 'Completed', 'Rejected'])->default('Pending')->after('size');
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_order_items', function (Blueprint $table) {
            $table->dropForeign(['craftsman_id']);
            $table->dropColumn(['craftsman_id', 'status', 'rejection_reason']);
        });
    }
};
