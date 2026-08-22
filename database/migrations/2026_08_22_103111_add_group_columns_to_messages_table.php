<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('conversation_id')->nullable()->change();
            $table->foreignId('chat_group_id')->nullable()->after('conversation_id')->constrained('chat_groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['chat_group_id']);
            $table->dropColumn('chat_group_id');
            // Assuming conversation_id was not nullable before
            $table->unsignedBigInteger('conversation_id')->nullable(false)->change();
        });
    }
};
