<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 🔒 لما الأدمن يفعّلها لموظف معيّن، الأرقام المالية الحساسة (رأس المال/السيولة/أرباح/
    // إجمالي منظومة الأقساط) بتتخفي عنه افتراضياً في الداشبورد والخزانة وشاشة الأقساط،
    // مع أيقونة إظهار/إخفاء يدوية على كل رقم.
    public function up()
    {
        if (!Schema::hasColumn('users', 'hide_financials')) {
            Schema::table('users', function (Blueprint $table) {
                $table->tinyInteger('hide_financials')->default(0)->after('role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'hide_financials')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('hide_financials'));
        }
    }
};
