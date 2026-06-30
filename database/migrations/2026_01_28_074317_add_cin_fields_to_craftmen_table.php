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
            $table->string('cin_no', 21)->nullable()->after('tan_attachment');
            $table->string('cin_attachment')->nullable()->after('cin_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('craftmen', function (Blueprint $table) {
            $table->dropColumn(['cin_no', 'cin_attachment']);
        });
    }
};
