<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->string('item_returned_by')->nullable()->after('status');
            $table->string('item_collected_by')->nullable()->after('item_returned_by');
            $table->timestamp('completed_at')->nullable()->after('item_collected_by');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn(['item_returned_by', 'item_collected_by', 'completed_at']);
        });
    }
};
