<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_group_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_group_id')->constrained('chat_groups')->onDelete('cascade');
            $table->morphs('user');
            $table->timestamps();
            
            $table->unique(['chat_group_id', 'user_type', 'user_id'], 'group_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_group_participants');
    }
};
