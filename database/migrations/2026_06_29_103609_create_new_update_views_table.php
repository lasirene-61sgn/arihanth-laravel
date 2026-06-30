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
        Schema::create('new_update_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('new_update_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type');
            $table->timestamps();
            
            $table->foreign('new_update_id')->references('id')->on('new_updates')->onDelete('cascade');
            $table->unique(['new_update_id', 'user_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_update_views');
    }
};
