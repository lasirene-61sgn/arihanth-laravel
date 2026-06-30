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
        Schema::create('craftmen', function (Blueprint $table) {
            $table->id();
            $table->string('craftman_code')->unique();
            $table->string('business_name');
            $table->string('name');
            $table->string('mobile');
            $table->string('landline')->nullable();
            $table->string('email')->unique();
            $table->string('business_email')->nullable();
            $table->string('refered_by')->nullable();
            $table->text('more')->nullable();
            $table->string('door_no')->nullable();
            $table->string('shop_no')->nullable();
            $table->string('complex_name')->nullable();
            $table->string('building_name')->nullable();
            $table->string('street_name')->nullable();
            $table->string('area')->nullable();
            $table->string('pincode')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('map_location')->nullable();
            $table->text('location_guide')->nullable();
            // KYC Fields
            $table->string('bis_no')->nullable();
            $table->string('bis_attachment')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('gst_attachment')->nullable();
            $table->string('msme_no')->nullable();
            $table->string('msme_attachment')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('pan_attachment')->nullable();
            $table->string('tan_no')->nullable();
            $table->string('tan_attachment')->nullable();
            $table->string('image')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('aadhar_attach')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('passbook')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('branch')->nullable();
            $table->string('bank_city')->nullable();
            $table->string('bank_state')->nullable();
            $table->text('note')->nullable();
            $table->string('password'); // Add password field for authentication
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('craftmen');
    }
};