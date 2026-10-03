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
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
            $table->text('cancel_reason')->nullable()->change();
        });

        Schema::table('ac_expenses', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });

        Schema::table('installment_payments', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });

        Schema::table('company_debts', function (Blueprint $table) {
            $table->text('reason')->nullable()->change();
        });
        
        Schema::table('sales', function (Blueprint $table) {
            $table->text('product_name')->nullable()->change();
        });
        
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->text('product_name')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as it's safe to keep them as text
    }
};
