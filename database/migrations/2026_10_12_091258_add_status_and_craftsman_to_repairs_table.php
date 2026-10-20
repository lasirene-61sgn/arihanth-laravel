<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            if (!Schema::hasColumn('repairs', 'status')) {
                $table->string('status')->default('pending')->after('reference_number');
            }
            if (!Schema::hasColumn('repairs', 'craftman_code')) {
                // Storing the craftman_code directly as a string (or foreign key if craftman_code is a unique key in the craftman table)
                $table->string('craftman_code')->nullable()->after('status');
            }
            
            // If craftman_code is the primary/unique key in your craftman table, you can add this foreign constraint:
            // $table->foreign('craftman_code')->references('craftman_code')->on('craftsmen')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            // Drop foreign key first if you used it, then drop the column
            // $table->dropForeign(['craftman_code']);
            $table->dropColumn(['status', 'craftman_code']);
        });
    }
};
