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
        Schema::create('craftman_aadhar_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftman_id');
            $table->string('aadhar_name');
            $table->string('aadhar_number');
            $table->string('aadhar_image')->nullable();
            $table->timestamps();
            
            $table->foreign('craftman_id')->references('id')->on('craftmen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('craftman_aadhar_details');
    }
};