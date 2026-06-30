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
            $table->date('craftsman_due_date')->nullable()->after('due_date');
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
            $table->string('creator_type')->nullable()->after('created_by');
            $table->unsignedBigInteger('allocated_by')->nullable()->after('creator_type');
            $table->unsignedBigInteger('approved_by')->nullable()->after('allocated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'craftsman_due_date',
                'created_by',
                'creator_type',
                'allocated_by',
                'approved_by'
            ]);
        });
    }
};
