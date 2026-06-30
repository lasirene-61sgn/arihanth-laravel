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
            $table->string('user_code')->unique()->after('id');
            $table->string('bp_code')->nullable()->after('user_code');
            $table->string('full_name')->after('name');
            $table->string('email_id')->after('full_name');
            $table->string('mobile_no')->after('email_id');
            $table->string('status')->nullable()->after('password');
            $table->date('dob')->nullable()->after('status');
            $table->string('city')->nullable()->after('dob');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('pincode')->nullable()->after('country');
            $table->string('profile_picture')->nullable()->after('pincode');
            $table->string('aadhar_photo')->nullable()->after('profile_picture');
            $table->string('aadhar_number')->nullable()->after('aadhar_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_code',
                'bp_code',
                'full_name',
                'email_id',
                'mobile_no',
                'status',
                'dob',
                'city',
                'state',
                'country',
                'pincode',
                'profile_picture',
                'aadhar_photo',
                'aadhar_number'
            ]);
        });
    }
};