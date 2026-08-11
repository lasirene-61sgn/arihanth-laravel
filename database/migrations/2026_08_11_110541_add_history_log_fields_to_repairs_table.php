<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('creator_type')->nullable();
            $table->string('creator_user_code')->nullable();
            $table->unsignedBigInteger('allocated_by')->nullable();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('craftsman_accepted_at')->nullable();
            $table->timestamp('craftsman_completed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->date('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn([
                'created_by',
                'creator_type',
                'creator_user_code',
                'allocated_by',
                'allocated_at',
                'craftsman_accepted_at',
                'craftsman_completed_at',
                'approved_by',
                'approved_at',
                'due_date'
            ]);
        });
    }
};
