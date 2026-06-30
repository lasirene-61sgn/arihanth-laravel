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
        Schema::table('designs', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('designs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            if (Schema::hasColumn('designs', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};