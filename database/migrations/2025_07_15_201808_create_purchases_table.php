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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->dateTime('datetime');
            $table->foreignId('mines_id')->constrained('mines')->onDelete('cascade');
            $table->string('rec_no');
            $table->string('token_no');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->integer('gross_wt'); // in kg
            $table->integer('tare_wt'); // in kg
            $table->integer('net_wt')->nullable(); // auto calculated (gross - tare)
            $table->decimal('wt_ton', 8, 2)->nullable(); // auto calculated (net/1000)
            $table->string('driver'); // auto populated from vehicle
            $table->decimal('commission', 10, 2)->nullable(); // INR currency
            $table->enum('use_at', ['stock', 'manufacturing'])->default('manufacturing');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('datetime');
            $table->index('rec_no');
            $table->index('token_no');
            $table->index('mines_id');
            $table->index('vehicle_id');
            $table->index('use_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
