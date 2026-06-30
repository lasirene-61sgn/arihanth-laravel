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
            $table->string('dear')->nullable()->unique();
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->string('dear')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('craftmen', function (Blueprint $table) {
            $table->dropColumn('dear');
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('dear');
        });
    }
};
