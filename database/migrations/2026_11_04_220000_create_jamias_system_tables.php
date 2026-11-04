<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jamias', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الجمعية
            $table->string('organizer_name')->nullable(); // المنظم / المسؤول
            $table->string('organizer_phone', 50)->nullable();
            $table->decimal('total_amount', 15, 2); // قيمة قبض السهم الواحد
            $table->integer('duration_months'); // عدد الشهور الكلي
            $table->decimal('monthly_installment', 15, 2); // قسط السهم الواحد شهرياً
            $table->integer('company_shares_count')->default(1); // عدد الأسهم التي دخلت بها الشركة
            $table->date('start_date'); // تاريخ أول شهر في الجمعية
            $table->integer('due_day')->default(1); // يوم السداد في الشهر
            $table->string('status', 30)->default('active'); // active, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('start_date');
        });

        Schema::create('jamia_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jamia_id');
            $table->integer('share_number'); // 1, 2, 3..
            $table->string('share_label', 150); // الاسم / الوصف
            $table->date('payout_month'); // شهر استحقاق القبض
            $table->decimal('payout_amount', 15, 2); // مبلغ القبض المتوقع
            
            // حالة القبض
            $table->boolean('is_collected')->default(false);
            $table->timestamp('collected_at')->nullable();
            $table->unsignedBigInteger('collected_account_id')->nullable();
            $table->unsignedBigInteger('collected_tx_id')->nullable();
            $table->decimal('collected_amount', 15, 2)->default(0);

            // حالة التنازل لطرف آخر
            $table->boolean('is_transferred')->default(false);
            $table->timestamp('transferred_at')->nullable();
            $table->date('transferred_from_month')->nullable();
            $table->string('transferred_to_name')->nullable();
            $table->text('transferred_notes')->nullable();
            $table->integer('paid_months_before_transfer')->default(0); // عدد الشهور التي سددتها الشركة قبل التنازل
            $table->decimal('company_claim_amount', 15, 2)->default(0); // حق الشركة المسترد من القبض
            
            // استرداد حق الشركة
            $table->boolean('is_claim_recovered')->default(false);
            $table->timestamp('recovered_at')->nullable();
            $table->unsignedBigInteger('recovered_account_id')->nullable();
            $table->unsignedBigInteger('recovered_tx_id')->nullable();
            $table->decimal('recovered_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('jamia_id')->references('id')->on('jamias')->onDelete('cascade');
            $table->index('payout_month');
            $table->index('is_collected');
            $table->index('is_transferred');
        });

        Schema::create('jamia_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jamia_id');
            $table->unsignedBigInteger('share_id');
            $table->date('month_date'); // تاريخ الشهر (أول يوم في الشهر)
            $table->integer('month_index'); // 1..N
            $table->decimal('amount', 15, 2); // قيمة القسط
            
            // السداد
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_account_id')->nullable();
            $table->unsignedBigInteger('tx_id')->nullable();
            
            // التنازل / الإعفاء
            $table->boolean('is_waived')->default(false); // معفي عن الشركة بسبب التنازل
            $table->string('waived_reason')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('jamia_id')->references('id')->on('jamias')->onDelete('cascade');
            $table->foreign('share_id')->references('id')->on('jamia_shares')->onDelete('cascade');
            $table->index('month_date');
            $table->index('is_paid');
            $table->index('is_waived');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jamia_installments');
        Schema::dropIfExists('jamia_shares');
        Schema::dropIfExists('jamias');
    }
};
