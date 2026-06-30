<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'design_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('design_status')->default('pending')->after('order_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'design_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('design_status');
            });
        }
    }
};
