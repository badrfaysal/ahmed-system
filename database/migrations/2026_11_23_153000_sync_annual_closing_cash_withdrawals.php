<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * مزامنة إجمالي المسحوبات النقدية في التقفيلات السنوية لتشمل المسحوبات المباشرة من الرصيد (capital_withdrawal)
     * بجانب مسحوبات أرباح الدورات الشهرية (withdrawn_amount / ahmed_withdrawn_amount) بدون أي تكرار.
     */
    public function up(): void
    {
        if (
            !Schema::hasTable('partner_annual_closings')
            || !Schema::hasTable('partner_annual_closing_lines')
            || !Schema::hasTable('partner_movements')
        ) {
            return;
        }

        $closings = DB::table('partner_annual_closings')->get();
        foreach ($closings as $closing) {
            $year      = (int) $closing->closing_year;
            $yearStart = "{$year}-01-01";
            $yearEnd   = "{$year}-12-31";

            $yearPeriodIds = Schema::hasTable('partner_periods')
                ? DB::table('partner_periods')
                    ->where('status', 'closed')
                    ->whereBetween('end_date', [$yearStart, $yearEnd])
                    ->pluck('id')
                    ->all()
                : [];

            $lines = DB::table('partner_annual_closing_lines')
                ->where('annual_closing_id', $closing->id)
                ->get();

            $sumWithdrawn = 0.0;
            foreach ($lines as $line) {
                $periodWithdrawn = 0.0;
                if (!empty($yearPeriodIds)) {
                    if (!empty($line->is_head)) {
                        $periodWithdrawn = round((float) DB::table('partner_periods')
                            ->whereIn('id', $yearPeriodIds)
                            ->sum('ahmed_withdrawn_amount'), 2);
                    } elseif (Schema::hasTable('partner_period_lines')) {
                        $periodWithdrawn = round((float) DB::table('partner_period_lines')
                            ->where('partner_id', $line->partner_id)
                            ->whereIn('period_id', $yearPeriodIds)
                            ->sum('withdrawn_amount'), 2);
                    }
                }

                $directWithdrawn = round((float) DB::table('partner_movements')
                    ->where('partner_id', $line->partner_id)
                    ->where('type', 'capital_withdrawal')
                    ->whereBetween('created_at', ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"])
                    ->sum('amount'), 2);

                $totalLineWithdrawn = round($periodWithdrawn + $directWithdrawn, 2);

                DB::table('partner_annual_closing_lines')
                    ->where('id', $line->id)
                    ->update([
                        'year_withdrawn' => $totalLineWithdrawn,
                        'updated_at'     => now(),
                    ]);

                $sumWithdrawn += $totalLineWithdrawn;
            }

            DB::table('partner_annual_closings')
                ->where('id', $closing->id)
                ->update([
                    'total_year_withdrawn' => round($sumWithdrawn, 2),
                    'updated_at'           => now(),
                ]);
        }
    }

    public function down(): void
    {
        // لا حاجة للتراجع عن مزامنة أرقام المسحوبات الصحيحة
    }
};
