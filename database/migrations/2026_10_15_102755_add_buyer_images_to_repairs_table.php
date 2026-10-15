<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->string('buyer_voucher_image')->nullable()->after('craftsman_completion_proof_image');
            $table->string('buyer_completion_proof_image')->nullable()->after('buyer_voucher_image');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_voucher_image', 
                'buyer_completion_proof_image'
            ]);
        });
    }
};