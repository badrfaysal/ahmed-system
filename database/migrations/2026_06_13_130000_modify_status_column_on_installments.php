<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('installments') && Schema::hasColumn('installments', 'status')) {
            // تحويل العمود من ENUM('active','completed') إلى VARCHAR(32) مرن
            // عشان يقبل قيم زي 'cancelled' و 'written_off' وغيرهم
            DB::statement("ALTER TABLE `installments` MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('installments') && Schema::hasColumn('installments', 'status')) {
            DB::statement("ALTER TABLE `installments` MODIFY `status` ENUM('active','completed') NOT NULL DEFAULT 'active'");
        }
    }
};
