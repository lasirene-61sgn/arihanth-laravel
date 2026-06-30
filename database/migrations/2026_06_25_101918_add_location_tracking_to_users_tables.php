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
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'last_login_country')) {
                        $table->string('last_login_country')->nullable()->after('last_login_ip');
                    }
                    if (!Schema::hasColumn($tableName, 'last_login_location')) {
                        $table->string('last_login_location')->nullable()->after('last_login_country');
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
        Schema::table('users_tables', function (Blueprint $table) {
            //
        });
    }
};
