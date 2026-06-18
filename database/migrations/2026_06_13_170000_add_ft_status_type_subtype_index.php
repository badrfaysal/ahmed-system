<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index مركّب جديد لـ financial_transactions يخدم الاستعلام الموحّد:
     *   WHERE status = 'active' GROUP BY type, subtype, person_name
     * المُستخدم في InstallmentFinanceService::financialBreakdown()
     * والذي حلّ محل 10 استعلامات LIKE '%...%' منفصلة.
     *
     * بدون هذا الـ index، الاستعلام الجديد سيعمل full table scan على status.
     */
    public function up(): void
    {
        if (!Schema::hasTable('financial_transactions')) return;
        foreach (['status', 'type', 'subtype'] as $col) {
            if (!Schema::hasColumn('financial_transactions', $col)) return;
        }

        $exists = DB::select(
            "SELECT 1 FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name   = 'financial_transactions'
               AND index_name   = 'idx_ft_status_type_subtype'
             LIMIT 1"
        );
        if (!empty($exists)) return;

        try {
            DB::statement(
                "CREATE INDEX `idx_ft_status_type_subtype`
                 ON `financial_transactions` (`status`, `type`, `subtype`)"
            );
        } catch (\Throwable $e) {
            // فشل إنشاء الـ index لا يجب أن يكسر الـ migrations
        }
    }

    public function down(): void
    {
        try {
            DB::statement("DROP INDEX `idx_ft_status_type_subtype` ON `financial_transactions`");
        } catch (\Throwable $e) {
            //
        }
    }
};
