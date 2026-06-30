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
        Schema::table('craftmen', function (Blueprint $table) {
            if (!Schema::hasColumn('craftmen', 'password')) {
                $table->string('password')->after('note');
            }
            if (!Schema::hasColumn('craftmen', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('craftmen', function (Blueprint $table) {
            if (Schema::hasColumn('craftmen', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('craftmen', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
};