<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('installment_payments') && ! Schema::hasColumn('installment_payments', 'discount_applied')) {
            Schema::table('installment_payments', function (Blueprint $table) {
                $table->decimal('discount_applied', 14, 2)->default(0)->after('amount_paid');
            });
        }

        if (Schema::hasTable('installments') && ! Schema::hasColumn('installments', 'close_reason')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->string('close_reason', 32)->nullable()->after('remaining_balance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('installment_payments', 'discount_applied')) {
            Schema::table('installment_payments', function (Blueprint $table) {
                $table->dropColumn('discount_applied');
            });
        }
        if (Schema::hasColumn('installments', 'close_reason')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->dropColumn('close_reason');
            });
        }
    }
};
