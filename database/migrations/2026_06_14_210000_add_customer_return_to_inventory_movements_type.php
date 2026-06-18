<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('inventory_movements')) return;

        // إضافة customer_return لقائمة أنواع حركات المخزن (مرتجع من العميل)
        DB::statement("ALTER TABLE `inventory_movements`
            MODIFY `type` ENUM(
                'adjustment_up','adjustment_down','transfer',
                'supplier_return','customer_return','damage'
            ) NOT NULL");
    }

    public function down()
    {
        if (!Schema::hasTable('inventory_movements')) return;

        DB::statement("ALTER TABLE `inventory_movements`
            MODIFY `type` ENUM(
                'adjustment_up','adjustment_down','transfer','supplier_return'
            ) NOT NULL");
    }
};
