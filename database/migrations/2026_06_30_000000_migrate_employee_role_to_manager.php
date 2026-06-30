<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // 💡 الدور 'employee' كان يعني "الموظف الحالي" (صلاحيات شبه كاملة). بقى عندنا دور موظف جديد أكثر تقييدًا،
    // فبننقل كل من كان 'employee' إلى 'manager' (بنفس الصلاحيات القديمة تمامًا) ونحجز 'employee' للدور المقيد الجديد.
    public function up()
    {
        DB::table('users')->where('role', 'employee')->update(['role' => 'manager']);
    }

    public function down()
    {
        DB::table('users')->where('role', 'manager')->update(['role' => 'employee']);
    }
};
