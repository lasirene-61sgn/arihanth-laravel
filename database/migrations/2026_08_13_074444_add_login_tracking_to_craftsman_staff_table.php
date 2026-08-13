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
        Schema::table('craftsman_staff', function (Blueprint $table) {
            $table->string('last_login_ip')->nullable();
            $table->string('last_login_country')->nullable();
            $table->string('last_login_location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('craftsman_staff', function (Blueprint $table) {
            $table->dropColumn(['last_login_ip', 'last_login_country', 'last_login_location']);
        });
    }
};
