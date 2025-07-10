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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->enum('voucher_type', ['journal', 'payment', 'receipt', 'contra']);
            $table->date('voucher_date');
            $table->string('reference_number')->nullable();
            $table->text('narration');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->json('attachments')->nullable(); // Store file paths
            $table->timestamps();

            $table->index(['voucher_type', 'voucher_date']);
            $table->index(['voucher_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
