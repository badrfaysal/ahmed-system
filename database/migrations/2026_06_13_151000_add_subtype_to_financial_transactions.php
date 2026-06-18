<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة عمود subtype على financial_transactions لإلغاء استخدام LIKE '%...%' البطيء.
     * تصنيف ذكي حسب notes الموجودة سابقاً.
     */
    public function up(): void
    {
        if (!Schema::hasTable('financial_transactions')) return;

        if (!Schema::hasColumn('financial_transactions', 'subtype')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->string('subtype', 50)->nullable()->after('type');
            });
        }

        // ترجع الـ subtype لكل التعاملات القديمة بناءً على notes
        $rules = [
            'commission_auto'  => '%عمولة تلقائية%',
            'depreciation'     => '%إهلاك أصل ثابت%',
            'return_loss'      => '%خسارة فرق سعر%',
            'bad_debt'         => '%إعدام ديون%',
            'asset_sale_loss'  => '%خسارة بيع أصل%',
            'inventory_loss'   => '%خسارة تسوية جرد%',
            'asset_sale_gain'  => '%بيع أصل%',
            'return_income'    => '%مرتجع%',
            'salary'           => '%راتب%',
            'fuel_deduction'   => '%استقطاع%',
            'fuel_advance'     => '%عهدة نقدية%',
            'shift_diff'       => '%تقفيل الوردية%',
        ];

        foreach ($rules as $sub => $pattern) {
            DB::table('financial_transactions')
              ->whereNull('subtype')
              ->where('notes', 'LIKE', $pattern)
              ->update(['subtype' => $sub]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('financial_transactions', 'subtype')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->dropColumn('subtype');
            });
        }
    }
};
