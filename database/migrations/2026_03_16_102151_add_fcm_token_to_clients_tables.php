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
        Schema::table('users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('password_plain');
        });
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('password_plain');
        });
        Schema::table('key_users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('password_plain');
        });
        Schema::table('process_owners', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('password_plain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
        Schema::table('key_users', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
        Schema::table('process_owners', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
