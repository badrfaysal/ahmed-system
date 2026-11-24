<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const JAMIA_NAME = 'جمعية 300 ألف - يوليو 2026';
    private const COMPANY_NAME = 'شركة الضبع';
    private const MIGRATION_TAG = '[تم الترحيل لبلوك الجمعيات]';

    public function up(): void
    {
        if (
            !Schema::hasTable('jamias')
            || !Schema::hasTable('jamia_shares')
            || !Schema::hasTable('jamia_installments')
        ) {
            return;
        }

        // تنظيف أي قيود قديمة للجمعيات في جدول الديون (تم استبدالها ببلوك موقف الجمعيات)
        if (Schema::hasTable('company_debts') && Schema::hasColumn('company_debts', 'source_type')) {
            DB::table('company_debts')
                ->where('source_type', 'jamia_company_payout')
                ->delete();
        }

        // منع التكرار في حال كانت الجمعية مضافة بالفعل (مثل البيئة المحلية)
        if (DB::table('jamias')->where('name', self::JAMIA_NAME)->exists()) {
            return;
        }

        $companyName = self::COMPANY_NAME;
        $turns = [
            1  => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            2  => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            3  => [
                ['name' => 'احمد محمد',  'is_company' => 0, 'fraction' => 0.5],
                ['name' => $companyName, 'is_company' => 1, 'fraction' => 0.5],
            ],
            4  => [['name' => 'هشام الجزار', 'is_company' => 0, 'fraction' => 1.0]],
            5  => [['name' => 'محمود رزق',   'is_company' => 0, 'fraction' => 1.0]],
            6  => [['name' => 'لمياء بنت خالتي', 'is_company' => 0, 'fraction' => 1.0]],
            7  => [['name' => 'احمد ابوسريع', 'is_company' => 0, 'fraction' => 1.0]],
            8  => [
                ['name' => 'طارق خالد', 'is_company' => 0, 'fraction' => 0.5],
                ['name' => 'كريم',      'is_company' => 0, 'fraction' => 0.5],
            ],
            9  => [['name' => 'محمود مجدي عتمان', 'is_company' => 0, 'fraction' => 1.0]],
            10 => [
                ['name' => 'عبدالحليم', 'is_company' => 0, 'fraction' => 0.5],
                ['name' => 'ابراهيم',   'is_company' => 0, 'fraction' => 0.25],
                ['name' => 'رينا',      'is_company' => 0, 'fraction' => 0.25],
            ],
            11 => [['name' => 'مصطفي عادل ابوزيد', 'is_company' => 0, 'fraction' => 1.0]],
            12 => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            13 => [['name' => 'محمد علي تبع دكتور محمود', 'is_company' => 0, 'fraction' => 1.0]],
            14 => [['name' => 'الشيخ محمد اخو هشام', 'is_company' => 0, 'fraction' => 1.0]],
            15 => [['name' => 'الشيخ محمد اخو هشام', 'is_company' => 0, 'fraction' => 1.0]],
            16 => [['name' => 'ياسر عتمان الجزار', 'is_company' => 0, 'fraction' => 1.0]],
            17 => [['name' => 'ياسر عتمان الجزار', 'is_company' => 0, 'fraction' => 1.0]],
            18 => [['name' => 'استاذ طارق عليش', 'is_company' => 0, 'fraction' => 1.0]],
            19 => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            20 => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            21 => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            22 => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            23 => [
                ['name' => 'طارق خالد',  'is_company' => 0, 'fraction' => 0.5],
                ['name' => $companyName, 'is_company' => 1, 'fraction' => 0.5],
            ],
            24 => [['name' => 'احمد ابوسريع', 'is_company' => 0, 'fraction' => 1.0]],
            25 => [['name' => 'لمياء بنت خالتي', 'is_company' => 0, 'fraction' => 1.0]],
            26 => [['name' => 'محمود رزق', 'is_company' => 0, 'fraction' => 1.0]],
            27 => [['name' => 'هشام الجزار', 'is_company' => 0, 'fraction' => 1.0]],
            28 => [
                ['name' => 'احمد محمد',  'is_company' => 0, 'fraction' => 0.5],
                ['name' => $companyName, 'is_company' => 1, 'fraction' => 0.5],
            ],
            29 => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
            30 => [['name' => $companyName, 'is_company' => 1, 'fraction' => 1.0]],
        ];

        $totalAmount     = 300000.00;
        $duration        = 30;
        $monthlyInst     = 10000.00;
        $startDate       = Carbon::parse('2026-07-01');
        $completedMonths = 5;
        $now             = now();

        $companySharesRowsCount = 0;
        foreach ($turns as $parts) {
            foreach ($parts as $p) {
                if ($p['is_company']) {
                    $companySharesRowsCount++;
                }
            }
        }

        $hasFractionCol = Schema::hasColumn('jamia_shares', 'share_fraction');
        $hasSplitCol    = Schema::hasColumn('jamia_shares', 'split_part');
        $hasPhoneCol    = Schema::hasColumn('jamia_shares', 'member_phone');
        $hasCompanyCol  = Schema::hasColumn('jamia_shares', 'is_company');
        $hasCollStart   = Schema::hasColumn('jamias', 'collection_start_day');
        $hasCollEnd     = Schema::hasColumn('jamias', 'collection_end_day');

        DB::transaction(function () use (
            $companyName, $totalAmount, $duration, $monthlyInst, $companySharesRowsCount,
            $startDate, $completedMonths, $turns, $now,
            $hasFractionCol, $hasSplitCol, $hasPhoneCol, $hasCompanyCol, $hasCollStart, $hasCollEnd
        ) {
            $jamiaPayload = [
                'name'                 => self::JAMIA_NAME,
                'organizer_name'       => $companyName,
                'organizer_phone'      => null,
                'total_amount'         => $totalAmount,
                'duration_months'      => $duration,
                'monthly_installment'  => $monthlyInst,
                'company_shares_count' => $companySharesRowsCount,
                'start_date'           => $startDate->toDateString(),
                'due_day'              => 10,
                'status'               => 'active',
                'notes'                => 'جمعية 300 ألف (30 شهر × 10,000 ج) — أول 5 شهور (من 1-7-2026 حتى 1-11-2026) مسوّاة قبل الإدخال بدون أثر بنكي، وتم ترحيل رصيد دين (300) بقيمة 225,000 ج إلى بلوك موقف الجمعيات.',
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
            if ($hasCollStart) {
                $jamiaPayload['collection_start_day'] = 1;
            }
            if ($hasCollEnd) {
                $jamiaPayload['collection_end_day'] = 10;
            }

            $jamiaId = DB::table('jamias')->insertGetId($jamiaPayload);

            $instRows = [];

            foreach ($turns as $turn => $parts) {
                $payoutDate         = $startDate->copy()->addMonths($turn - 1);
                $isHistoricalPayout = ($turn <= $completedMonths);

                foreach ($parts as $idx => $p) {
                    $fraction   = (float) $p['fraction'];
                    $payoutAmt  = round($totalAmount * $fraction, 2);
                    $monthlyAmt = round($monthlyInst * $fraction, 2);

                    $sharePayload = [
                        'jamia_id'             => $jamiaId,
                        'share_number'         => $turn,
                        'share_label'          => $p['name'],
                        'payout_month'         => $payoutDate->toDateString(),
                        'payout_amount'        => $payoutAmt,
                        'is_collected'         => $isHistoricalPayout ? 1 : 0,
                        'collected_at'         => $isHistoricalPayout ? $payoutDate->copy()->day(10)->setTime(12, 0, 0) : null,
                        'collected_account_id' => null,
                        'collected_tx_id'      => null,
                        'collected_amount'     => $isHistoricalPayout ? $payoutAmt : 0.00,
                        'notes'                => $isHistoricalPayout ? 'تم القبض قبل إدخال الجمعية في السيستم' : null,
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];
                    if ($hasFractionCol) {
                        $sharePayload['share_fraction'] = $fraction;
                    }
                    if ($hasSplitCol) {
                        $sharePayload['split_part'] = $idx + 1;
                    }
                    if ($hasPhoneCol) {
                        $sharePayload['member_phone'] = null;
                    }
                    if ($hasCompanyCol) {
                        $sharePayload['is_company'] = $p['is_company'];
                    }

                    $shareId = DB::table('jamia_shares')->insertGetId($sharePayload);

                    for ($i = 0; $i < $duration; $i++) {
                        $mIndex     = $i + 1;
                        $mDate      = $startDate->copy()->addMonths($i);
                        $isHistPaid = (!$p['is_company'] && $mIndex <= $completedMonths);

                        $instRows[] = [
                            'jamia_id'        => $jamiaId,
                            'share_id'        => $shareId,
                            'month_date'      => $mDate->toDateString(),
                            'month_index'     => $mIndex,
                            'amount'          => $monthlyAmt,
                            'is_paid'         => $isHistPaid ? 1 : 0,
                            'paid_at'         => $isHistPaid ? $mDate->copy()->day(5)->setTime(12, 0, 0) : null,
                            'paid_account_id' => null,
                            'tx_id'           => null,
                            'is_waived'       => 0,
                            'waived_reason'   => null,
                            'notes'           => $isHistPaid ? 'تم التحصيل قبل إدخال الجمعية في السيستم' : null,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];
                    }
                }
            }

            foreach (array_chunk($instRows, 200) as $chunk) {
                DB::table('jamia_installments')->insert($chunk);
            }

            // ترحيل رصيد دين "300" المفتوح (225,000 ج) إلى بلوك موقف الجمعيات بدون مساس بأي خزنة أو حركات مالية
            if (Schema::hasTable('company_debts')) {
                DB::table('company_debts')
                    ->where('creditor_name', '300')
                    ->where('remaining_balance', '>', 0)
                    ->update([
                        'paid_amount'       => DB::raw('total_amount'),
                        'remaining_balance' => 0,
                        'reason'            => DB::raw("CONCAT(COALESCE(reason, ''), ' " . self::MIGRATION_TAG . "')"),
                        'updated_at'        => $now,
                    ]);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('jamias')) {
            return;
        }

        $jamia = DB::table('jamias')->where('name', self::JAMIA_NAME)->first();
        if (!$jamia) {
            return;
        }

        $hasLiveMoves = DB::table('jamia_installments')
            ->where('jamia_id', $jamia->id)
            ->whereNotNull('tx_id')
            ->exists()
            || DB::table('jamia_shares')
                ->where('jamia_id', $jamia->id)
                ->whereNotNull('collected_tx_id')
                ->exists();

        if ($hasLiveMoves) {
            return;
        }

        DB::transaction(function () use ($jamia) {
            DB::table('jamias')->where('id', $jamia->id)->delete();

            if (Schema::hasTable('company_debts')) {
                $rows = DB::table('company_debts')
                    ->where('creditor_name', '300')
                    ->where('reason', 'like', '%' . self::MIGRATION_TAG . '%')
                    ->get();

                foreach ($rows as $row) {
                    $cleanReason = trim(str_replace(self::MIGRATION_TAG, '', (string) $row->reason));
                    DB::table('company_debts')
                        ->where('id', $row->id)
                        ->update([
                            'paid_amount'       => 0,
                            'remaining_balance' => $row->total_amount,
                            'reason'            => $cleanReason,
                            'updated_at'        => now(),
                        ]);
                }
            }
        });
    }
};
