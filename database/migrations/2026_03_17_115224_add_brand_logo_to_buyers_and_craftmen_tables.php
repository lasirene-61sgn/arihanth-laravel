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
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('brand_logo')->nullable()->after('image');
        });
        Schema::table('craftmen', function (Blueprint $table) {
            $table->string('brand_logo')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('brand_logo');
        });
        Schema::table('craftmen', function (Blueprint $table) {
            $table->dropColumn('brand_logo');
        });
    }
};
