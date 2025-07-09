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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('cascade');
            $table->string('uuid'); // For grouping related transactions
            $table->date('transaction_date');
            $table->string('particular'); // Transaction description
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('running_balance', 15, 2);
            $table->enum('running_balance_type', ['credit', 'debit']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index for better performance
            $table->index(['ledger_id', 'transaction_date']);
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
