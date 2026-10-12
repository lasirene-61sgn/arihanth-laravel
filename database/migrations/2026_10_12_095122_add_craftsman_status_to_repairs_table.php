<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            // craftsman_status: 'pending_acceptance', 'in_process', 'pending_approval'
            $table->string('craftsman_status')->default('pending_acceptance')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn(['craftsman_status']);
        });
    }
};
