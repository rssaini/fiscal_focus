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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable()->default(null)->unique();
            $table->dateTime('date')->default(now());
            $table->foreignId('consignee_id')->nullable()->constrained('consignees')->onDelete('set null');
            $table->foreignId('ref_party_id')->nullable()->constrained('parties')->onDelete('set null');
            $table->string('vehicle_no');
            $table->unsignedInteger('tare_wt'); // Weight in KG
            $table->unsignedInteger('gross_wt')->nullable()->default(null); // Weight in KG
            $table->unsignedInteger('net_wt')->nullable(); // Calculated: gross - tare
            $table->decimal('wt_ton', 10, 3)->nullable(); // Calculated: net/1000
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('driver_commission', 15, 2)->default(0);

            $table->string('tp_no')->nullable(); // Transport permit number
            $table->decimal('invoice_rate', 10, 2)->nullable(); // Currency field
            $table->decimal('tp_wt', 10, 2)->nullable(); // TP weight in ton
            $table->decimal('tax_amount', 15, 2)->default(0);

            $table->decimal('total_amount', 15, 2)->nullable(); // amount + total_gst
            $table->string('rec_no')->nullable()->default(null);
            $table->string('royalty_book_no')->nullable();
            $table->string('royalty_receipt_no')->nullable();
            $table->decimal('royalty_wt', 10, 3)->nullable()->default(null);
            $table->enum('status', ['pending','draft', 'confirmed', 'paid', 'partially_paid', 'cancelled'])->default('pending');
            $table->string('consignee_name')->nullable();
            $table->text('consignee_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['invoice_number']);
            $table->index(['date']);
            $table->index(['consignee_id']);
            $table->index(['ref_party_id']);
            $table->index(['status']);
            $table->index(['vehicle_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
