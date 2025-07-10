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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->enum('customer_type', ['individual', 'business'])->default('individual');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('website')->nullable();
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();
            $table->text('billing_address');
            $table->text('shipping_address')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('India');
            $table->string('pincode');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->integer('credit_days')->default(30);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('opening_balance_type', ['debit', 'credit'])->default('debit');
            $table->date('opening_date')->default(now());
            $table->foreignId('ledger_id')->nullable()->constrained('ledgers')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->json('additional_fields')->nullable();
            $table->timestamps();

            $table->index(['status', 'customer_type']);
            $table->index(['city', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
