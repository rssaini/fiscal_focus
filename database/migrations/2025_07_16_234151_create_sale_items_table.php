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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->unsignedInteger('tare_wt');
            $table->unsignedInteger('gross_wt')->nullable()->default(null); // Weight in KG
            $table->decimal('net_wt', 10, 3)->nullable()->default(null); // Quantity in tons or pieces
            $table->decimal('rate', 10, 2); // Rate per unit
            $table->decimal('amount', 15, 2); // Calculated: quantity * rate
            $table->integer('sort_order')->default(0); // For ordering items
            $table->timestamps();

            // Indexes
            $table->index(['sale_id']);
            $table->index(['product_id']);
            $table->index(['sale_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
