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
        // Skip this migration if catalogues table doesn't exist
        // Catalogues are managed through products table with design_status and design_code
        if (Schema::hasTable('catalogues')) {
            Schema::table('catalogues', function (Blueprint $table) {
                if (!Schema::hasColumn('catalogues', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('add_video');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('catalogues') && Schema::hasColumn('catalogues', 'created_by')) {
            Schema::table('catalogues', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};