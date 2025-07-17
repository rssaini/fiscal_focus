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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('payment_reference')->unique(); // Payment reference number
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'upi', 'rtgs', 'neft', 'cheque', 'card', 'discount', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->string('transaction_id')->nullable(); // For UPI/RTGS/NEFT
            $table->string('cheque_number')->nullable(); // For cheque payments
            $table->date('cheque_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'cleared', 'bounced', 'cancelled'])->default('cleared');
            $table->timestamps();

            // Indexes
            $table->index(['sale_id']);
            $table->index(['payment_date']);
            $table->index(['payment_method']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
