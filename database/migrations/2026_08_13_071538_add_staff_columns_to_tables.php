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
            $table->unsignedBigInteger('accepted_by_staff_id')->nullable();
            $table->timestamp('staff_accepted_at')->nullable();
            $table->timestamp('staff_completed_at')->nullable();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('accepted_by_staff_id')->nullable();
            $table->timestamp('staff_accepted_at')->nullable();
            $table->timestamp('staff_completed_at')->nullable();
        });

        Schema::table('repairs', function (Blueprint $table) {
            $table->unsignedBigInteger('accepted_by_staff_id')->nullable();
            $table->timestamp('staff_accepted_at')->nullable();
            $table->timestamp('staff_completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['accepted_by_staff_id', 'staff_accepted_at', 'staff_completed_at']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['accepted_by_staff_id', 'staff_accepted_at', 'staff_completed_at']);
        });

        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn(['accepted_by_staff_id', 'staff_accepted_at', 'staff_completed_at']);
        });
    }
};
