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
            $table->string('status')->default('Pending');
            $table->text('reject_reason')->nullable();
            $table->string('allocated_craftsman_code')->nullable();
            $table->string('craftsman_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'reject_reason',
                'allocated_craftsman_code',
                'craftsman_status'
            ]);
        });
    }
};
