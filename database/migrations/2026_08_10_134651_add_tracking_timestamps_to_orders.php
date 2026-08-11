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
            $table->timestamp('craftsman_accepted_at')->nullable();
            $table->timestamp('craftsman_completed_at')->nullable();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('craftsman_accepted_at')->nullable();
            $table->timestamp('craftsman_completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['craftsman_accepted_at', 'craftsman_completed_at']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['craftsman_accepted_at', 'craftsman_completed_at']);
        });
    }
};
