<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Insert initial data
        DB::table('admin_categories')->insert([
            ['name' => 'sales', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'orders', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'accounts', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'finance', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_categories');
    }
};
