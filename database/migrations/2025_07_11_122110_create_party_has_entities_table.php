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
        Schema::create('party_has_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained('parties')->onDelete('cascade');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();

            // Create composite unique index on all three fields
            $table->unique(['party_id', 'model_type', 'model_id'], 'party_has_entities_unique');

            // Additional indexes for better query performance
            $table->index(['model_type', 'model_id'], 'party_has_entities_model_index');
            $table->index('party_id', 'party_has_entities_party_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_has_entities');
    }
};
