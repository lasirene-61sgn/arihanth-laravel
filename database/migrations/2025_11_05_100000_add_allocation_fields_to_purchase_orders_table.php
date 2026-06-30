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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('allocated_craftsman_code')->nullable()->after('status');
            $table->string('craftsman_status')->nullable()->after('allocated_craftsman_code');
            $table->json('rejected_items')->nullable()->after('craftsman_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['allocated_craftsman_code', 'craftsman_status', 'rejected_items']);
        });
    }
};