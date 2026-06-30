<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'bp_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('bp_code')->nullable()->after('design_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'bp_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('bp_code');
            });
        }
    }
};