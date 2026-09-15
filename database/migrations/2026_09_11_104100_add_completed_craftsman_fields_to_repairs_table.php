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
            $table->string('completed_craftsman_name')->nullable()->after('completion_proof');
            $table->string('completed_craftsman_code')->nullable()->after('completed_craftsman_name');
            $table->string('completed_craftsman_mobile')->nullable()->after('completed_craftsman_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn([
                'completed_craftsman_name',
                'completed_craftsman_code',
                'completed_craftsman_mobile'
            ]);
        });
    }
};
