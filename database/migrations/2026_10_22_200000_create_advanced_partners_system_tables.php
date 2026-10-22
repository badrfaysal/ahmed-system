<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ترقية جدول partners الحالي
        if (!Schema::hasTable('partners')) {
            Schema::create('partners', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone', 50)->nullable();
                $table->decimal('capital', 15, 2)->default(0);
                $table->decimal('initial_capital', 15, 2)->default(0);
                $table->decimal('ahmed_ratio', 5, 2)->default(30.00); // نسبة أحمد من أرباح الشريك %
                $table->string('default_payout_action', 30)->default('reinvest'); // 'reinvest' | 'withdraw_all' | 'withdraw_partial'
                $table->date('joined_at')->nullable();
                $table->string('status', 20)->default('active'); // 'active' | 'exited'
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('partners', function (Blueprint $table) {
                if (!Schema::hasColumn('partners', 'phone')) {
                    $table->string('phone', 50)->nullable()->after('name');
                }
                if (!Schema::hasColumn('partners', 'initial_capital')) {
                    $table->decimal('initial_capital', 15, 2)->default(0)->after('capital');
                }
                if (!Schema::hasColumn('partners', 'ahmed_ratio')) {
                    $table->decimal('ahmed_ratio', 5, 2)->default(30.00)->after('initial_capital');
                }
                if (!Schema::hasColumn('partners', 'default_payout_action')) {
                    $table->string('default_payout_action', 30)->default('reinvest')->after('ahmed_ratio');
                }
                if (!Schema::hasColumn('partners', 'joined_at')) {
                    $table->date('joined_at')->nullable()->after('default_payout_action');
                }
                if (!Schema::hasColumn('partners', 'status')) {
                    $table->string('status', 20)->default('active')->after('joined_at');
                }
                if (!Schema::hasColumn('partners', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
            });

            // تفريغ البيانات التجريبية القديمة لتبدأ نظيفة
            DB::table('partners')->truncate();
        }

        // 2. جدول الفترات وتقفيلات الأشهر (partner_periods)
        if (!Schema::hasTable('partner_periods')) {
            Schema::create('partner_periods', function (Blueprint $table) {
                $table->id();
                $table->string('period_name', 255); // مثل: تقفيل شهر أكتوبر 2026
                $table->date('start_date');         // 2026-09-25
                $table->date('end_date');           // 2026-10-25
                $table->unsignedBigInteger('opening_snapshot_id')->nullable();
                $table->decimal('opening_capital', 15, 2)->default(0);
                $table->unsignedBigInteger('closing_snapshot_id')->nullable();
                $table->decimal('closing_capital', 15, 2)->default(0);
                $table->decimal('capital_adjustments_net', 15, 2)->default(0); // صافي التعديلات اليدوية المستبعدة
                $table->decimal('partner_deposits_net', 15, 2)->default(0);    // صافي إيداعات ومسحوبات رأس مال الشركاء خلال الفترة
                $table->decimal('gross_profit', 15, 2)->default(0);            // الربح الصافي التشغيلي القابل للتوزيع (R)
                $table->decimal('total_partners_capital', 15, 2)->default(0);  // إجمالي رأس مال الشركاء عند بداية الفترة
                $table->decimal('total_partners_gross_profit', 15, 2)->default(0);
                $table->decimal('total_ahmed_commission', 15, 2)->default(0);  // إجمالي عمولة أحمد من الشركاء
                $table->decimal('total_partners_net_profit', 15, 2)->default(0);
                $table->decimal('total_withdrawn', 15, 2)->default(0);         // إجمالي ما صُرف نقداً للشركاء
                $table->decimal('total_reinvested', 15, 2)->default(0);        // إجمالي ما عاد لرأس المال ليدور
                $table->string('status', 20)->default('closed');               // 'closed' | 'cancelled'
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamps();

                $table->index('start_date');
                $table->index('end_date');
                $table->index('status');
            });
        }

        // 3. جدول تفاصيل حصص الشركاء في كل تقفيلة (partner_period_lines)
        if (!Schema::hasTable('partner_period_lines')) {
            Schema::create('partner_period_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('period_id');
                $table->unsignedBigInteger('partner_id');
                $table->decimal('opening_capital', 15, 2)->default(0);     // رأس مال الشريك عند الافتتاح
                $table->decimal('capital_share_pct', 8, 4)->default(0);    // نسبته في الشركة (s)
                $table->decimal('ahmed_ratio_pct', 5, 2)->default(30.00);  // نسبة أحمد من ربحه %
                $table->decimal('effective_profit_pct', 8, 4)->default(0); // نسبته الفعلية من أرباح الشركة
                $table->decimal('gross_share', 15, 2)->default(0);         // نصيبه من الربح قبل نسبة أحمد (أو نصيبه من الخسارة)
                $table->decimal('ahmed_commission', 15, 2)->default(0);    // نصيب أحمد من ربح الشريك (0 في حالة الخسارة)
                $table->decimal('net_share', 15, 2)->default(0);           // صافي ربح الشريك (أو خسارته)
                $table->string('payout_action', 30)->default('reinvest');  // 'reinvest' | 'withdraw_all' | 'withdraw_partial'
                $table->decimal('withdrawn_amount', 15, 2)->default(0);    // المبلغ المسحوب نقداً
                $table->unsignedBigInteger('payout_account_id')->nullable(); // الخزنة المصروف منها
                $table->decimal('reinvested_amount', 15, 2)->default(0);   // المبلغ المعاد ليدور في رأس المال
                $table->decimal('closing_capital', 15, 2)->default(0);     // رأس مال الشريك الجديد للدورة القادمة
                $table->timestamps();

                $table->index('period_id');
                $table->index('partner_id');
            });
        }

        // 4. جدول حركات الشركاء المالية المباشرة (partner_movements)
        if (!Schema::hasTable('partner_movements')) {
            Schema::create('partner_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger('period_id')->nullable();
                $table->string('type', 30); // 'initial_deposit', 'capital_increase', 'capital_withdrawal', 'profit_withdrawal', 'profit_reinvest'
                $table->decimal('amount', 15, 2)->default(0);
                $table->unsignedBigInteger('account_id')->nullable();       // الخزنة/المحفظة
                $table->unsignedBigInteger('transaction_id')->nullable();   // ربط بـ financial_transactions
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('partner_id');
                $table->index('period_id');
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_movements');
        Schema::dropIfExists('partner_period_lines');
        Schema::dropIfExists('partner_periods');
        // عدم حذف جدول partners الأساسي للحفاظ على الأمان
    }
};
