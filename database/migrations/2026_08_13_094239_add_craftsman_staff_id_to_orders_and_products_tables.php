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
        if (!Schema::hasColumn('work_orders', 'craftsman_staff_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('craftsman_staff_id')->nullable();
            });
        }

        if (!Schema::hasColumn('purchase_orders', 'craftsman_staff_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('craftsman_staff_id')->nullable();
            });
        }

        if (!Schema::hasColumn('repairs', 'craftsman_staff_id')) {
            Schema::table('repairs', function (Blueprint $table) {
                $table->unsignedBigInteger('craftsman_staff_id')->nullable();
            });
        }

        if (!Schema::hasColumn('products', 'craftsman_staff_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('craftsman_staff_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('craftsman_staff_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('craftsman_staff_id');
        });

        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn('craftsman_staff_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('craftsman_staff_id');
        });
    }
};
