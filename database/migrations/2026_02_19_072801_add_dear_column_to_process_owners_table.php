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
        Schema::table('process_owners', function (Blueprint $table) {
            $table->string('dear')->nullable()->unique()->after('user_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_owners', function (Blueprint $table) {
            $table->dropColumn('dear');
        });
    }
};
