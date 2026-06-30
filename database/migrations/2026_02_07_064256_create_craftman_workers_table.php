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
        Schema::create('craftman_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('craftman_id')->constrained('craftmen')->onDelete('cascade');
            $table->string('worker_name');
            $table->string('worker_number')->nullable();
            $table->string('worker_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('craftman_workers');
    }
};
