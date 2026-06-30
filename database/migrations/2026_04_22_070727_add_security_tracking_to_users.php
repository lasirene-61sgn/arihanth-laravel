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
        $tables = ['process_owners', 'buyers', 'craftmen', 'key_users', 'users'];
        
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'password_update_count')) {
                        $table->integer('password_update_count')->default(0)->after('password');
                    }
                    if (!Schema::hasColumn($table->getTable(), 'last_login_ip')) {
                        $table->string('last_login_ip')->nullable()->after('password_update_count');
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
        $tables = ['process_owners', 'buyers', 'craftmen', 'key_users', 'users'];
        
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['password_update_count', 'last_login_ip']);
                });
            }
        }
    }
};
