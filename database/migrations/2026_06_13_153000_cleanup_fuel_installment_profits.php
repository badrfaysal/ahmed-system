<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تنظيف بيانات قديمة:
     * 1. أي installments فيها category='بنزينة' و profit > 0 → نصفرها (المصدر الموثوق fuel_transactions).
     * 2. أي installments فيها customer_phone='بدون' و category='بنزينة' → نحط tag مميز للشركة.
     */
    public function up(): void
    {
        if (!Schema::hasTable('installments')) return;

        // 1. تصفير profit في كل عمليات البنزينة (نمنع double-count مع fuel_transactions.ahmed_profit)
        DB::table('installments')
            ->where('category', 'بنزينة')
            ->where('profit', '>', 0)
            ->update(['profit' => 0]);

        // 2. تغيير "بدون" لـ tag مميز عشان مايتخلطش الـ archive
        // نستخدم product_name لاستخراج اسم الشركة (مش أمثل لكن workable للبيانات القديمة)
        if (Schema::hasColumn('installments', 'customer_phone')) {
            // نعطي كل صف رقم مميز بناءً على id عشان نضمن uniqueness
            DB::statement("
                UPDATE installments
                SET customer_phone = CONCAT('GAS-LEGACY-', id)
                WHERE category = 'بنزينة' AND (customer_phone = 'بدون' OR customer_phone IS NULL)
            ");
        }
    }

    public function down(): void
    {
        // لا نرجع البيانات للحالة القديمة (الـ profit التكراري كان غلط أصلاً)
    }
};
