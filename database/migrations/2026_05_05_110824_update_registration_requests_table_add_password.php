<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('registration_requests', 'password')) {
                $table->string('password')->after('gst_no');
            }
            if (Schema::hasColumn('registration_requests', 'pan_no')) {
                $table->dropColumn('pan_no');
            }
            if (Schema::hasColumn('registration_requests', 'aadhar_no')) {
                $table->dropColumn('aadhar_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->string('pan_no')->nullable();
            $table->string('aadhar_no')->nullable();
        });
    }
};
