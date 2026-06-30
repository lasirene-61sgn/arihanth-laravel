<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'design_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('design_code')->nullable()->unique()->after('design_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'design_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['design_code']);
                $table->dropColumn('design_code');
            });
        }
    }
};
