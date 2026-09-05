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
        Schema::table('repairs', function (Blueprint $table) {
            $table->string('item_received_by')->nullable();
            $table->string('item_received_through')->nullable();
            $table->string('item_delivered_by_type')->nullable(); // 'Self' or 'AJPL'
            $table->string('item_delivered_by')->nullable(); // BP Code or Name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn([
                'item_received_by',
                'item_received_through',
                'item_delivered_by_type',
                'item_delivered_by',
            ]);
        });
    }
};
