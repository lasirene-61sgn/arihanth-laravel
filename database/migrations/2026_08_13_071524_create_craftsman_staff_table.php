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
        Schema::create('craftsman_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftsman_id');
            $table->string('staff_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('password');
            $table->string('password_plain')->nullable();
            $table->string('aadhar_number')->nullable();
            $table->string('image')->nullable();
            $table->string('aadhar_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('permissions')->nullable();
            $table->timestamps();

            // Depending on how craftmen table is named
            // $table->foreign('craftsman_id')->references('id')->on('craftmen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('craftsman_staff');
    }
};
