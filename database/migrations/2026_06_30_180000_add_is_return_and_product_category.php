<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 🏷️ فصل "علامة مرتجع العميل" عن الفئة:
    // - sales.is_return: علامة إن الباتش ده مرتجع عميل (بدل ما نحرق الفئة بـ"مرتجعات عملاء")
    // - installments.product_category: فئة المنتج الأصلية تُحفظ على العقد لاستخدامها عند الفسخ
    public function up()
    {
        if (!Schema::hasColumn('sales', 'is_return')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->tinyInteger('is_return')->default(0)->after('inventory_status');
            });
        }
        // الباتشات القديمة المسماة "مرتجعات عملاء" نعلّمها كمرتجع عشان البادج يفضل يظهر
        DB::table('sales')->where('category', 'مرتجعات عملاء')->update(['is_return' => 1]);

        if (!Schema::hasColumn('installments', 'product_category')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->string('product_category')->nullable()->after('supplier_name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('sales', 'is_return')) {
            Schema::table('sales', fn (Blueprint $t) => $t->dropColumn('is_return'));
        }
        if (Schema::hasColumn('installments', 'product_category')) {
            Schema::table('installments', fn (Blueprint $t) => $t->dropColumn('product_category'));
        }
    }
};
