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
        Schema::create('consignees', function (Blueprint $table) {
            $table->id();
            $table->string('consignee_name');
            $table->string('gstin')->nullable();
            $table->text('address');
            $table->text('address2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip', 10);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Indexes
            $table->index(['consignee_name']);
            $table->index(['city']);
            $table->index(['state']);
            $table->index(['status']);
            $table->index(['gstin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignees');
    }
};
