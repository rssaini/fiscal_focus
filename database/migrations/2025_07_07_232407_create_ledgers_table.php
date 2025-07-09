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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('folio');
            $table->boolean('is_active')->default(true);
            $table->date('opening_date');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('balance_type', ['credit', 'debit'])->default('debit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
