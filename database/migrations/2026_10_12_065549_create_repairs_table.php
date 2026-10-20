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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            
            // Foreign key referencing the buyers table
            $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            
            // Alternatively, if your buyers table uses 'bp_code' as a primary or unique string column:
            // $table->string('bp_code');
            // $table->foreign('bp_code')->references('bp_code')->on('buyers')->onDelete('cascade');

            $table->string('product_name');
            $table->string('order_number');
            $table->enum('repair_or_sample', ['repair', 'sample']);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->text('repair_details')->nullable();
            $table->string('item_delivered_by');
            $table->string('item_received_by');
            $table->unsignedBigInteger('accepted_by_staff_id')->nullable();
            $table->timestamp('staff_accepted_at')->nullable();
            $table->timestamp('staff_completed_at')->nullable();
            $table->unsignedBigInteger('craftsman_staff_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
