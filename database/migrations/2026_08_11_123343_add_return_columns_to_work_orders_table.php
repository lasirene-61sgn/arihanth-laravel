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
            $table->integer('admin_return_count')->default(0)->after('admin_undo_count');
            $table->integer('superadmin_return_count')->default(0)->after('superadmin_undo_count');
            $table->date('return_due_date')->nullable()->after('status');
            $table->text('return_note')->nullable()->after('return_due_date');
            $table->string('damaged_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['admin_return_count', 'superadmin_return_count', 'return_due_date', 'return_note']);
        });
    }
};
