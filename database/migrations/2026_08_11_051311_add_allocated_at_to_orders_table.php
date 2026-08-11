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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->timestamp('allocated_at')->nullable();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('allocated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('allocated_at');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('allocated_at');
        });
    }
};
