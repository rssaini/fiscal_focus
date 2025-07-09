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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 20)->unique(); // e.g., 1000, 1100, 1110
            $table->string('account_name');
            $table->enum('account_type', [
                'asset',
                'liability',
                'equity',
                'revenue',
                'expense'
            ]);
            $table->enum('account_subtype', [
                // Assets
                'current_asset',
                'fixed_asset',
                'intangible_asset',
                'other_asset',
                // Liabilities
                'current_liability',
                'long_term_liability',
                // Equity
                'owner_equity',
                'retained_earnings',
                // Revenue
                'operating_revenue',
                'other_revenue',
                // Expenses
                'cost_of_goods_sold',
                'operating_expense',
                'other_expense'
            ]);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('level')->default(1); // 1 = Main category, 2 = Sub category, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_posting')->default(true); // false for header accounts
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->index(['account_type', 'account_subtype']);
            $table->index(['parent_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
