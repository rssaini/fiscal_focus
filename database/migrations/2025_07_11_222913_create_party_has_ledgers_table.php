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
        Schema::create('party_has_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained('parties')->onDelete('cascade');
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('cascade');
            $table->timestamps();

            // Create unique index on both columns
            $table->unique(['party_id', 'ledger_id'], 'party_has_ledgers_unique');

            // Additional indexes for better query performance
            $table->index('party_id', 'party_has_ledgers_party_index');
            $table->index('ledger_id', 'party_has_ledgers_ledger_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_has_ledgers');
    }
};
