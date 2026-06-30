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
        Schema::table('craftmen', function (Blueprint $table) {
            $table->string('kyc_status')->default('pending')->after('is_frozen'); // pending, approved, rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('craftmen', function (Blueprint $table) {
            $table->dropColumn('kyc_status');
        });
    }
};
