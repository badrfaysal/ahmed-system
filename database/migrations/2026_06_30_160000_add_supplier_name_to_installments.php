<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 💡 تخزين اسم المورد الأصلي على العقد — عشان وقت فسخ العقد ترجع البضاعة للمخزن
    // باسم المورد الصح بدل "فسخ عقد: اسم العميل".
    public function up()
    {
        if (!Schema::hasColumn('installments', 'supplier_name')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->string('supplier_name')->nullable()->after('product_name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('installments', 'supplier_name')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->dropColumn('supplier_name');
            });
        }
    }
};
