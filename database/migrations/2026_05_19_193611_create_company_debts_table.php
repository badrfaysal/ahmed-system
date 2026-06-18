<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // جدول الديون اللي على الشركة
        Schema::create('company_debts', function (Blueprint $table) {
            $table->id();
            $table->string('creditor_name'); // اسم الدائن (المورد أو الجهة)
            $table->string('reason'); // سبب الدين (شراء بضاعة آجل، سلفة، إلخ)
            $table->decimal('total_amount', 15, 2); // إجمالي الدين
            $table->decimal('paid_amount', 15, 2)->default(0); // ما تم سداده
            $table->decimal('remaining_balance', 15, 2); // المتبقي علينا
            $table->timestamps();
        });

        // جدول دفعات سداد الديون اللي علينا
        Schema::create('company_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_debt_id');
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('account_id'); // اتدفع من أي خزنة
            $table->timestamp('payment_date')->useCurrent();
        });
    }
    public function down() {
        Schema::dropIfExists('company_debt_payments');
        Schema::dropIfExists('company_debts');
    }
};