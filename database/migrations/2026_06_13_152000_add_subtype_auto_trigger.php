<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * يضيف TRIGGER على financial_transactions:
     * كل INSERT جديد بـ subtype=NULL يتم تصنيفه أوتوماتيك حسب notes/type.
     * المميز: مفيش حاجة نعدل في الـ controllers.
     */
    public function up(): void
    {
        if (!Schema::hasTable('financial_transactions')) return;
        if (!Schema::hasColumn('financial_transactions', 'subtype')) return;

        // امسح أي trigger قديم بنفس الاسم لو موجود
        DB::unprepared("DROP TRIGGER IF EXISTS ft_set_subtype_before_insert");

        DB::unprepared("
            CREATE TRIGGER ft_set_subtype_before_insert
            BEFORE INSERT ON financial_transactions
            FOR EACH ROW
            BEGIN
                IF NEW.subtype IS NULL OR NEW.subtype = '' THEN
                    IF NEW.notes LIKE '%عمولة تلقائية%' THEN
                        SET NEW.subtype = 'commission_auto';
                    ELSEIF NEW.notes LIKE '%إهلاك أصل ثابت%' THEN
                        SET NEW.subtype = 'depreciation';
                    ELSEIF NEW.notes LIKE '%إعدام ديون%' THEN
                        SET NEW.subtype = 'bad_debt';
                    ELSEIF NEW.notes LIKE '%خسارة فرق سعر%' THEN
                        SET NEW.subtype = 'return_loss';
                    ELSEIF NEW.notes LIKE '%خسارة بيع أصل%' THEN
                        SET NEW.subtype = 'asset_sale_loss';
                    ELSEIF NEW.notes LIKE '%خسارة تسوية جرد%' THEN
                        SET NEW.subtype = 'inventory_loss';
                    ELSEIF NEW.notes LIKE '%بيع أصل%' THEN
                        SET NEW.subtype = 'asset_sale_gain';
                    ELSEIF NEW.notes LIKE '%مرتجع%' THEN
                        SET NEW.subtype = 'return_income';
                    ELSEIF NEW.notes LIKE '%استقطاع%' THEN
                        SET NEW.subtype = 'fuel_deduction';
                    ELSEIF NEW.notes LIKE '%عهدة نقدية%' THEN
                        SET NEW.subtype = 'fuel_advance';
                    ELSEIF NEW.notes LIKE '%تقفيل الوردية%' THEN
                        SET NEW.subtype = 'shift_diff';
                    ELSEIF NEW.notes LIKE '%راتب%' OR NEW.notes LIKE '%salary%' THEN
                        SET NEW.subtype = 'salary';
                    ELSEIF NEW.notes LIKE '%بنزينة%' THEN
                        SET NEW.subtype = 'fuel_other';
                    END IF;
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS ft_set_subtype_before_insert");
    }
};
