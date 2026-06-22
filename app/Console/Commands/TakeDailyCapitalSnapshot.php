<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\InstallmentFinanceService;

class TakeDailyCapitalSnapshot extends Command
{
    protected $signature   = 'snapshot:capital {--notes= : ملاحظة اختيارية للقطة}';
    protected $description  = 'يأخذ لقطة تلقائية للمركز المالي وأرصدة الخزائن (نفس زر «حفظ لقطة للتقارير»)';

    public function handle(): int
    {
        try {
            // 1. بيانات المركز المالي الحالي
            $summary = InstallmentFinanceService::treasurySummary();

            // 2. كل الخزائن والمحافظ النشطة
            $wallets = DB::table('accounts')
                ->whereIn('category', ['bank_wallet', 'safe_cash'])
                ->get();

            $walletsData = [];
            foreach ($wallets as $w) {
                $walletsData[] = [
                    'id'      => $w->id,
                    'name'    => $w->account_name,
                    'balance' => $w->balance,
                ];
            }

            // 3. حفظ اللقطة
            DB::table('capital_snapshots')->insert([
                'total_capital'   => $summary['capital'],
                'liquidity_total' => $summary['liquidity'],
                'wallets_data'    => json_encode($walletsData, JSON_UNESCAPED_UNICODE),
                'notes'           => $this->option('notes') ?: '📸 لقطة تلقائية يومية (12 منتصف الليل)',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $this->info('✅ تم حفظ لقطة المركز المالي: ' . now()->format('Y-m-d H:i'));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ فشل أخذ اللقطة: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
