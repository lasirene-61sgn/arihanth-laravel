<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->string('craftsman_voucher_image')->nullable()->after('repair_image');
            $table->string('craftsman_completion_proof_image')->nullable()->after('craftsman_voucher_image');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn(['craftsman_voucher_image', 'craftsman_completion_proof_image']);
        });
    }
};
