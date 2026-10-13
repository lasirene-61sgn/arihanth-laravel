<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->string('repair_image')->nullable()->after('repair_details');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn(['repair_image']);
        });
    }
};
