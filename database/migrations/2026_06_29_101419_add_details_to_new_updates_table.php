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
        Schema::table('new_updates', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration')->nullable()->comment('Duration in seconds');
            $table->string('media_path')->nullable();
            $table->string('media_type')->nullable(); // 'image' or 'video'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_updates', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'duration', 'media_path', 'media_type']);
        });
    }
};
