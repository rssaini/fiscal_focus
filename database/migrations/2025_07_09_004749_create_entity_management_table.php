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
        Schema::create('entity_management', function (Blueprint $table) {
            $table->id();
            $table->string('head_name')->unique(); // customers, employees, partners, vendors, suppliers
            $table->foreignId('chart_of_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->string('voucher_type'); // sale, expense, purchase, receipt, payment, journal
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index('head_name');
            $table->index('voucher_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_management');
    }
};
