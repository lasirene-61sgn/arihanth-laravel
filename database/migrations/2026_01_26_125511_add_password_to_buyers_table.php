<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('password')->nullable();
        });
        
        // Set a default password for existing buyers
        $buyers = \App\Models\Buyer::all();
        foreach ($buyers as $buyer) {
            $buyer->update([
                'password' => Hash::make('password') // Set a default password for existing buyers
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
