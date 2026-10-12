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
        $tables = ['users', 'buyers', 'craftmen', 'craftsman_staff', 'key_users', 'process_owners'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'voip_token')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'fcm_token')) {
                        $table->string('voip_token')->nullable()->after('fcm_token');
                    } else {
                        $table->string('voip_token')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'buyers', 'craftmen', 'craftsman_staff', 'key_users', 'process_owners'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'voip_token')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('voip_token');
                });
            }
        }
    }
};
