<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\InstallmentFinanceService;

class PartnerProfitService
{
    /**
     * حساب الإدارة الحالي (المساهم المُعلَّم is_head) أو null إن لم يُحدد بعد
     */
    public static function headPartner(bool $fresh = false): ?object
    {
        static $cache = false;
        if ($fresh || $cache === false) {
            $cache = DB::table('partners')->where('is_head', 1)->where('status', 'active')->first();
        }
        return $cache;
    }

    /**
     * الاسم المعروض لحساب الإدارة
     */
    public static function headName(): string
    {
        $head = self::headPartner();
        if ($head) {
            return $head->name;
        }
        $admin = DB::table('users')->where('id', 1)->first();
        return $admin ? $admin->name : 'حساب الإدارة (ربح أحمد)';
    }

    /**
     * المساهمون العاديون (بدون حساب الإدارة)
     */
    public static function regularPartnersQuery()
    {
        return DB::table('partners')->where(function ($q) {
            $q->whereNull('is_head')->orWhere('is_head', 0);
        });
    }

    /**
     * ملخص الحصص المعتمدة للشركاء وحساب الإدارة (ثابتة ولا تتأثر بتقلبات رأس مال الشركة اللحظي إلا عند الإقفال أو الإيداع/السحب المباشر)
     */
    public static function approvedSharesSummary(): array
    {
        $activePartners = self::regularPartnersQuery()
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        $headPartner = self::headPartner(true);

        $totalPartnersCapital = round((float) $activePartners->sum('capital'), 2);
        $ahmedCapital = $headPartner ? round((float) $headPartner->capital, 2) : 0.0;
        $totalApprovedCapital = round($totalPartnersCapital + $ahmedCapital, 2);

        $ahmedSharePct = $totalApprovedCapital > 0
            ? round(($ahmedCapital / $totalApprovedCapital) * 100, 4)
            : 0.0;

        $partnersSharePct = $totalApprovedCapital > 0
            ? round(($totalPartnersCapital / $totalApprovedCapital) * 100, 4)
            : 0.0;

        return [
            'active_partners'        => $activePartners,
            'head_partner'           => $headPartner,
            'total_partners_capital' => $totalPartnersCapital,
            'ahmed_capital'          => $ahmedCapital,
            'total_approved_capital' => $totalApprovedCapital,
            'ahmed_share_pct'        => $ahmedSharePct,
            'partners_share_pct'     => $partnersSharePct,
        ];
    }

    /**
     * التقاط خريطة الأرصدة الحالية لجميع الحسابات النشطة (حساب الإدارة + الشركاء) لتسجيل الهيستوري
     */
    public static function captureActiveSharesMap(): array
    {
        $all = DB::table('partners')
            ->where('status', 'active')
            ->orderByDesc('is_head')
            ->orderBy('id', 'asc')
            ->get();

        $map = [];
        foreach ($all as $p) {
            $map[$p->id] = [
                'id'      => $p->id,
                'name'    => $p->name,
                'is_head' => (bool) $p->is_head,
                'capital' => round((float) $p->capital, 2),
            ];
        }
        return $map;
    }

    /**
     * تسجيل حركة تغير النسب (النسب كانت كام وبقت كام) في جدول الهيستوري
     */
    public static function recordShareHistory(
        string $eventType,
        string $title,
        array $beforeMap,
        array $afterMap,
        ?int $periodId = null,
        ?int $movementId = null,
        ?int $actorPartnerId = null,
        ?string $notes = null,
        ?int $userId = 1,
        $timestamp = null
    ): int {
        $now = $timestamp ?: now();

        $allIds = array_unique(array_merge(array_keys($beforeMap), array_keys($afterMap)));

        $totalBefore = 0.0;
        $totalAfter  = 0.0;

        foreach ($allIds as $pid) {
            $totalBefore += (float) ($beforeMap[$pid]['capital'] ?? 0.0);
            $totalAfter  += (float) ($afterMap[$pid]['capital'] ?? 0.0);
        }

        $totalBefore = round($totalBefore, 2);
        $totalAfter  = round($totalAfter, 2);

        $historyId = DB::table('partner_share_histories')->insertGetId([
            'event_type'           => $eventType,
            'title'                => $title,
            'period_id'            => $periodId,
            'movement_id'          => $movementId,
            'actor_partner_id'     => $actorPartnerId,
            'total_capital_before' => $totalBefore,
            'total_capital_after'  => $totalAfter,
            'notes'                => $notes,
            'created_by'           => $userId,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        foreach ($allIds as $pid) {
            $b = $beforeMap[$pid] ?? null;
            $a = $afterMap[$pid] ?? null;

            $name   = $a['name'] ?? ($b['name'] ?? "شريك #{$pid}");
            $isHead = (bool) ($a['is_head'] ?? ($b['is_head'] ?? false));

            $capBefore = round((float) ($b['capital'] ?? 0.0), 2);
            $capAfter  = round((float) ($a['capital'] ?? 0.0), 2);
            $capChange = round($capAfter - $capBefore, 2);

            $pctBefore = $totalBefore > 0 ? round(($capBefore / $totalBefore) * 100, 4) : 0.0;
            $pctAfter  = $totalAfter > 0 ? round(($capAfter / $totalAfter) * 100, 4) : 0.0;
            $pctDiff   = round($pctAfter - $pctBefore, 4);

            DB::table('partner_share_history_lines')->insert([
                'history_id'       => $historyId,
                'partner_id'       => $pid,
                'partner_name'     => $name,
                'is_head'          => $isHead ? 1 : 0,
                'capital_before'   => $capBefore,
                'share_pct_before' => $pctBefore,
                'capital_change'   => $capChange,
                'capital_after'    => $capAfter,
                'share_pct_after'  => $pctAfter,
                'share_pct_diff'   => $pctDiff,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        return $historyId;
    }

    /**
     * حساب المعاينة الحية لتقفيل الفترة
     */
    public static function previewClosing(string $startDate, string $endDate, ?float $customClosingCapital = null): array
    {
        // 1. جلب الشركاء النشطين وحساب الإدارة لمعرفة إجمالي الحصص المعتمدة الحالية
        $partners = self::regularPartnersQuery()
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        $headPartner = self::headPartner(true);

        $totalPartnersCapital = round((float) $partners->sum('capital'), 2);
        $ahmedOpeningCapital  = $headPartner ? round((float) $headPartner->capital, 2) : 0.0;
        $totalOpeningShares   = round($totalPartnersCapital + $ahmedOpeningCapital, 2);

        // 2. تحديد رأس مال بداية الدورة المرجعي من آخر إقفال معتمد حصراً (لا يمكن تجاوزه أو اختيار تاريخ عشوائي)
        $lastClosedPeriod = DB::table('partner_periods')
            ->where('status', 'closed')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastClosedPeriod) {
            $startDate = $lastClosedPeriod->end_date;
        } else {
            $baseCreatedAt = DB::table('partners')->min('created_at');
            $startDate = $baseCreatedAt
                ? \Carbon\Carbon::parse($baseCreatedAt)->format('Y-m-d')
                : now()->format('Y-m-d');
        }

        $partnerDeposits = 0.0;
        $partnerWithdrawals = 0.0;
        $partnerDepositsNet = 0.0;
        $lastPeriodWithdrawn = 0.0;
        $rawLastClosingCapital = 0.0;

        if ($lastClosedPeriod) {
            $rawLastClosingCapital = (float) $lastClosedPeriod->closing_capital;
            $lastPeriodWithdrawn   = (float) $lastClosedPeriod->total_withdrawn;
            $openingSnapshotId     = $lastClosedPeriod->closing_snapshot_id;

            // حركات الإيداع والسحب المباشر من رأس مال الشركاء بعد آخر تقفيلة
            $directMovements = DB::table('partner_movements')
                ->whereNull('period_id')
                ->where('created_at', '>', $lastClosedPeriod->created_at)
                ->get();

            foreach ($directMovements as $m) {
                if (in_array($m->type, ['initial_deposit', 'capital_increase'])) {
                    $partnerDeposits += (float) $m->amount;
                } elseif ($m->type === 'capital_withdrawal') {
                    $partnerWithdrawals += (float) $m->amount;
                }
            }

            $partnerDeposits    = round($partnerDeposits, 2);
            $partnerWithdrawals = round($partnerWithdrawals, 2);
            $partnerDepositsNet = round($partnerDeposits - $partnerWithdrawals, 2);

            // رأس مال البداية الفعلي للدورة بعد خصم ما سُحب نقداً في التقفيلة السابقة وصافي مسحوبات/إيداعات رأس المال
            $openingCapital = round(($rawLastClosingCapital - $lastPeriodWithdrawn) + $partnerDepositsNet, 2);
        } else {
            // في حال أول إقفال ولم يكن هناك إقفال سابق، نعتمد إجمالي الحصص التأسيسية المعتمدة كمرجع لبداية الدورة
            $openingCapital        = $totalOpeningShares;
            $openingSnapshotId     = null;
            $rawLastClosingCapital = $totalOpeningShares;
        }

        // 3. رأس المال الختامي
        $closingSnapshotId = null;
        if ($customClosingCapital !== null) {
            $closingCapital = round($customClosingCapital, 2);
        } else {
            $closingSnap = DB::table('capital_snapshots')
                ->whereDate('created_at', $endDate)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($closingSnap) {
                $closingCapital    = round((float) $closingSnap->total_capital, 2);
                $closingSnapshotId = $closingSnap->id;
            } else {
                $currentSummary = InstallmentFinanceService::treasurySummary();
                $closingCapital = round((float) ($currentSummary['capital'] ?? 0.0), 2);
            }
        }

        // 4. صافي الربح القابل للتوزيع = رأس مال الإقفال الحالي مطروحاً منه رأس مال البداية الصافي
        $rawDelta              = round($closingCapital - $openingCapital, 2);
        $grossProfit           = $rawDelta;
        $capitalAdjustmentsNet = 0.0;
        $adjIncrease           = 0.0;
        $adjDecrease           = 0.0;

        // 5. حساب حصص الشركاء بناءً على نسبتهم من إجمالي الحصص المعتمدة (شركاء + حساب الإدارة)
        $partnerLines = [];
        $totalPartnersGrossProfit = 0.0;
        $totalAhmedCommission     = 0.0;
        $totalPartnersNetProfit   = 0.0;
        $totalWithdrawnProposed   = 0.0;
        $totalReinvestedProposed  = 0.0;

        foreach ($partners as $p) {
            $cap        = round((float) $p->capital, 2);
            $ahmedRatio = (float) ($p->ahmed_ratio ?? 30.00);

            // النسبة قبل التقفيل من إجمالي الحصص المعتمدة (ثابتة لا تتأثر بتغير رأس مال الشركة قبل التقفيل)
            $capitalSharePct = $totalOpeningShares > 0 ? ($cap / $totalOpeningShares) * 100 : 0.0;

            if ($grossProfit >= 0) {
                $grossShare         = round($grossProfit * ($capitalSharePct / 100), 2);
                $ahmedComm          = round($grossShare * ($ahmedRatio / 100), 2);
                $netShare           = round($grossShare - $ahmedComm, 2);
                $effectiveProfitPct = $capitalSharePct * (1 - ($ahmedRatio / 100));
            } else {
                $grossShare         = round($grossProfit * ($capitalSharePct / 100), 2);
                $ahmedComm          = 0.0;
                $netShare           = $grossShare;
                $effectiveProfitPct = $capitalSharePct;
            }

            $withdrawn  = 0.0;
            $reinvested = $netShare;
            $newCapital = max(0.0, round($cap + $reinvested, 2));

            $partnerLines[] = [
                'partner_id'           => $p->id,
                'name'                 => $p->name,
                'phone'                => $p->phone,
                'opening_capital'      => $cap,
                'capital_share_pct'    => round($capitalSharePct, 4),
                'ahmed_ratio_pct'      => $ahmedRatio,
                'effective_profit_pct' => round($effectiveProfitPct, 4),
                'gross_share'          => $grossShare,
                'ahmed_commission'     => $ahmedComm,
                'net_share'            => $netShare,
                'monthly_fixed_withdrawal' => !empty($p->monthly_fixed_withdrawal) ? (float) $p->monthly_fixed_withdrawal : 0.0,
                'default_action'       => 'reinvest',
                'payout_action'        => 'reinvest',
                'withdrawn_amount'     => 0.0,
                'reinvested_amount'    => $reinvested,
                'closing_capital'      => $newCapital,
                'closing_share_pct'    => 0.0, // يُحسب بعد اكتمال المجموع الختامي
                'share_pct_diff'       => 0.0,
            ];

            $totalPartnersGrossProfit += $grossShare;
            $totalAhmedCommission     += $ahmedComm;
            $totalPartnersNetProfit   += $netShare;
            $totalWithdrawnProposed   += $withdrawn;
            $totalReinvestedProposed  += $reinvested;
        }

        $totalPartnersGrossProfit = round($totalPartnersGrossProfit, 2);
        $totalAhmedCommission     = round($totalAhmedCommission, 2);
        $totalPartnersNetProfit   = round($totalPartnersNetProfit, 2);
        $totalReinvestedProposed  = round($totalReinvestedProposed, 2);

        // 6. حساب حصة حساب الإدارة (الرصيد المدور في حساب الإدارة + أرباح نسب الشغل من الشركاء)
        $ahmedCapitalSharePct = $totalOpeningShares > 0 ? ($ahmedOpeningCapital / $totalOpeningShares) * 100 : 0.0;
        if ($ahmedOpeningCapital > 0 && $totalOpeningShares > 0) {
            // لضمان التطابق التام للقروش مع إجمالي الربح الموزع
            $ahmedOwnGrossProfit = round($grossProfit - $totalPartnersGrossProfit, 2);
        } else {
            $ahmedOwnGrossProfit = 0.0;
        }

        $ahmedTotalProfit    = round($ahmedOwnGrossProfit + $totalAhmedCommission, 2);
        $ahmedClosingCapital = max(0.0, round($ahmedOpeningCapital + $ahmedTotalProfit, 2));

        // 7. حساب النسب الجديدة بعد التقفيل (بقت كام %) في وضع المعاينة الافتراضي (تدوير)
        $totalClosingShares = round(($totalPartnersCapital + $totalReinvestedProposed) + $ahmedClosingCapital, 2);

        foreach ($partnerLines as &$pl) {
            $pl['closing_share_pct'] = $totalClosingShares > 0
                ? round(($pl['closing_capital'] / $totalClosingShares) * 100, 4)
                : 0.0;
            $pl['share_pct_diff'] = round($pl['closing_share_pct'] - $pl['capital_share_pct'], 4);
        }
        unset($pl);

        $ahmedClosingSharePct = $totalClosingShares > 0
            ? round(($ahmedClosingCapital / $totalClosingShares) * 100, 4)
            : 0.0;
        $ahmedSharePctDiff = round($ahmedClosingSharePct - round($ahmedCapitalSharePct, 4), 4);

        return [
            'start_date'                => $startDate,
            'end_date'                  => $endDate,
            'opening_snapshot_id'       => $openingSnapshotId,
            'raw_last_closing_capital'  => $rawLastClosingCapital,
            'last_period_withdrawn'     => $lastPeriodWithdrawn,
            'opening_capital'           => $openingCapital,
            'closing_snapshot_id'       => $closingSnapshotId,
            'closing_capital'           => $closingCapital,
            'raw_delta'                 => $rawDelta,
            'capital_adjustments_net'   => $capitalAdjustmentsNet,
            'adj_increase'              => $adjIncrease,
            'adj_decrease'              => $adjDecrease,
            'partner_deposits_net'      => $partnerDepositsNet,
            'partner_deposits'          => $partnerDeposits,
            'partner_withdrawals'       => $partnerWithdrawals,
            'gross_profit'              => $grossProfit,
            'total_partners_capital'    => $totalPartnersCapital,
            'total_opening_shares'      => $totalOpeningShares,
            'total_closing_shares'      => $totalClosingShares,
            'total_partners_gross'      => $totalPartnersGrossProfit,
            'total_ahmed_commission'    => $totalAhmedCommission,
            'total_partners_net'        => $totalPartnersNetProfit,
            'total_withdrawn_proposed'  => $totalWithdrawnProposed,
            'total_reinvested_proposed' => $totalReinvestedProposed,
            'partner_lines'             => $partnerLines,
            'ahmed' => [
                'partner_id'               => $headPartner?->id,
                'monthly_fixed_withdrawal' => !empty($headPartner?->monthly_fixed_withdrawal) ? (float) $headPartner->monthly_fixed_withdrawal : 0.0,
                'opening_capital'          => $ahmedOpeningCapital,
                'capital_share_pct' => round($ahmedCapitalSharePct, 4),
                'own_profit'        => $ahmedOwnGrossProfit,
                'commissions'       => $totalAhmedCommission,
                'total_profit'      => $ahmedTotalProfit,
                'closing_capital'   => $ahmedClosingCapital,
                'closing_share_pct' => $ahmedClosingSharePct,
                'share_pct_diff'    => $ahmedSharePctDiff,
            ],
        ];
    }

    /**
     * اعتماد تقفيل الفترة وحفظ الحركات وتحديث النسب والهيستوري في الداتابيز والخزائن
     */
    public static function executeClosing(array $preview, array $partnerDecisions, string $periodName, ?string $notes = null, ?int $closedBy = 1): int
    {
        return DB::transaction(function () use ($preview, $partnerDecisions, $periodName, $notes, $closedBy) {
            // التقاط النسب والأرصدة قبل التقفيل لتسجيلها في الهيستوري
            $beforeMap = self::captureActiveSharesMap();

            // 1. التأكد من حفظ لقطة ختامية إذا لم تكن موجودة
            $closingSnapId = $preview['closing_snapshot_id'];
            if (!$closingSnapId) {
                $summary = InstallmentFinanceService::treasurySummary();
                $wallets = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
                $walletsData = [];
                foreach ($wallets as $w) {
                    $walletsData[] = ['id' => $w->id, 'name' => $w->account_name, 'balance' => $w->balance];
                }

                $closingSnapId = DB::table('capital_snapshots')->insertGetId([
                    'total_capital'   => $preview['closing_capital'],
                    'liquidity_total' => $summary['liquidity'] ?? 0,
                    'wallets_data'    => json_encode($walletsData, JSON_UNESCAPED_UNICODE),
                    'notes'           => '📸 لقطة تقفيل فترة الشركاء: ' . $periodName,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // 2. إنشاء سجل التقفيلة partner_periods
            $periodId = DB::table('partner_periods')->insertGetId([
                'period_name'                 => $periodName,
                'start_date'                  => $preview['start_date'],
                'end_date'                    => $preview['end_date'],
                'opening_snapshot_id'         => $preview['opening_snapshot_id'],
                'opening_capital'             => $preview['opening_capital'],
                'closing_snapshot_id'         => $closingSnapId,
                'closing_capital'             => $preview['closing_capital'],
                'capital_adjustments_net'     => $preview['capital_adjustments_net'],
                'partner_deposits_net'        => $preview['partner_deposits_net'],
                'gross_profit'                => $preview['gross_profit'],
                'total_partners_capital'      => $preview['total_partners_capital'],
                'total_partners_gross_profit' => $preview['total_partners_gross'],
                'total_ahmed_commission'      => $preview['total_ahmed_commission'],
                'total_partners_net_profit'   => $preview['total_partners_net'],
                'total_withdrawn'             => 0,
                'total_reinvested'            => 0,
                'status'                      => 'closed',
                'notes'                       => $notes,
                'closed_by'                   => $closedBy,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            $totalWithdrawnActual  = 0.0;
            $totalReinvestedActual = 0.0;
            $insertedLineIds = [];

            // 3. معالجة كل شريك
            foreach ($preview['partner_lines'] as $line) {
                $pid = $line['partner_id'];
                $decision = $partnerDecisions[$pid] ?? [];

                $action = $decision['payout_action'] ?? 'reinvest';
                $netShare = (float) $line['net_share'];

                $withdrawn = 0.0;
                $payoutAccountId = null;

                if ($netShare > 0) {
                    if ($action === 'withdraw_all') {
                        $withdrawn = $netShare;
                    } elseif ($action === 'withdraw_partial') {
                        $withdrawn = min($netShare, max(0.0, (float) ($decision['custom_withdrawn'] ?? 0.0)));
                    } else {
                        $action = 'reinvest';
                        $withdrawn = 0.0;
                    }

                    $payoutAccountId = !empty($decision['account_id']) ? (int) $decision['account_id'] : null;

                    if ($withdrawn > 0 && !$payoutAccountId) {
                        throw new \Exception("يرجى تحديد الخزنة المصروف منها أرباح الشريك «{$line['name']}».");
                    }
                } else {
                    $action = 'reinvest';
                    $withdrawn = 0.0;
                    $payoutAccountId = null;
                }

                $reinvested = round($netShare - $withdrawn, 2);
                $newCapital = max(0.0, round((float) $line['opening_capital'] + $reinvested, 2));

                // حفظ سطر الشريك في partner_period_lines
                $lineId = DB::table('partner_period_lines')->insertGetId([
                    'period_id'            => $periodId,
                    'partner_id'           => $pid,
                    'opening_capital'      => $line['opening_capital'],
                    'capital_share_pct'    => $line['capital_share_pct'],
                    'ahmed_ratio_pct'      => $line['ahmed_ratio_pct'],
                    'effective_profit_pct' => $line['effective_profit_pct'],
                    'gross_share'          => $line['gross_share'],
                    'ahmed_commission'     => $line['ahmed_commission'],
                    'net_share'            => $line['net_share'],
                    'payout_action'        => $action,
                    'withdrawn_amount'     => $withdrawn,
                    'payout_account_id'    => $payoutAccountId,
                    'reinvested_amount'    => $reinvested,
                    'closing_capital'      => $newCapital,
                    'closing_share_pct'    => 0, // يُحدث بعد حساب الإجمالي الختامي
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $insertedLineIds[$lineId] = $newCapital;

                // تحديث رأس مال الشريك الجديد
                DB::table('partners')->where('id', $pid)->update([
                    'capital'    => $newCapital,
                    'updated_at' => now(),
                ]);

                // صرف المبلغ المسحوب من الخزنة إن وُجد
                if ($withdrawn > 0 && $payoutAccountId) {
                    $acc = DB::table('accounts')->where('id', $payoutAccountId)->lockForUpdate()->first();
                    if ($acc) {
                        $balBefore = (float) $acc->balance;
                        $balAfter  = $balBefore - $withdrawn;

                        DB::table('accounts')->where('id', $payoutAccountId)->update([
                            'balance'    => $balAfter,
                            'updated_at' => now(),
                        ]);

                        $ftId = DB::table('financial_transactions')->insertGetId([
                            'type'            => 'partner_withdrawal',
                            'subtype'         => 'profit_payout',
                            'amount'          => $withdrawn,
                            'balance_before'  => $balBefore,
                            'balance_after'   => $balAfter,
                            'from_account_id' => $payoutAccountId,
                            'person_name'     => $line['name'],
                            'notes'           => "صرف أرباح شريك: {$line['name']} عن فترة {$periodName}",
                            'status'          => 'active',
                            'ref_type'        => 'partner',
                            'ref_id'          => $pid,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);

                        DB::table('partner_movements')->insert([
                            'partner_id'     => $pid,
                            'period_id'      => $periodId,
                            'type'           => 'profit_withdrawal',
                            'amount'         => $withdrawn,
                            'account_id'     => $payoutAccountId,
                            'transaction_id' => $ftId,
                            'notes'          => "صرف أرباح نقداً للشريك: {$line['name']}",
                            'created_by'     => $closedBy,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }

                // تسجيل حركة إعادة استثمار إن وُجدت
                if ($reinvested != 0) {
                    DB::table('partner_movements')->insert([
                        'partner_id'     => $pid,
                        'period_id'      => $periodId,
                        'type'           => 'profit_reinvest',
                        'amount'         => $reinvested,
                        'account_id'     => null,
                        'transaction_id' => null,
                        'notes'          => ($reinvested > 0 ? "تدوير أرباح وإضافتها لرأس المال: " : "تحمل خسائر الفترة: ") . $line['name'],
                        'created_by'     => $closedBy,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                $totalWithdrawnActual  += $withdrawn;
                $totalReinvestedActual += $reinvested;
            }

            // 4. معالجة حصة وقرار حساب الإدارة (الأرباح من نسب الشغل + حصة الرصيد المدور)
            $ahmedDecision        = $partnerDecisions['ahmed'] ?? [];
            $ahmedOpeningCap      = (float) ($preview['ahmed']['opening_capital'] ?? 0.0);
            $ahmedCapitalSharePct = (float) ($preview['ahmed']['capital_share_pct'] ?? 0.0);
            $ahmedOwnProfit       = (float) ($preview['ahmed']['own_profit'] ?? 0.0);
            $ahmedCommission      = (float) ($preview['ahmed']['commissions'] ?? 0.0);
            $ahmedTotalProfit     = (float) ($preview['ahmed']['total_profit'] ?? 0.0);

            $ahmedAction    = $ahmedDecision['payout_action'] ?? 'reinvest';
            $ahmedWithdrawn = 0.0;
            $ahmedAccountId = null;

            if ($ahmedTotalProfit > 0) {
                if ($ahmedAction === 'withdraw_all') {
                    $ahmedWithdrawn = $ahmedTotalProfit;
                } elseif ($ahmedAction === 'withdraw_partial') {
                    $ahmedWithdrawn = min($ahmedTotalProfit, max(0.0, (float) ($ahmedDecision['custom_withdrawn'] ?? 0.0)));
                } else {
                    $ahmedAction    = 'reinvest';
                    $ahmedWithdrawn = 0.0;
                }

                $ahmedAccountId = !empty($ahmedDecision['account_id']) ? (int) $ahmedDecision['account_id'] : null;

                if ($ahmedWithdrawn > 0 && !$ahmedAccountId) {
                    throw new \Exception("يرجى تحديد الخزنة المصروف منها أرباح حساب الإدارة (" . self::headName() . ").");
                }
            } else {
                $ahmedAction    = 'reinvest';
                $ahmedWithdrawn = 0.0;
                $ahmedAccountId = null;
            }

            $ahmedReinvested = round($ahmedTotalProfit - $ahmedWithdrawn, 2);
            $ahmedClosingCap = max(0.0, round($ahmedOpeningCap + $ahmedReinvested, 2));

            // تحديث رصيد حساب الإدارة الفعلي في جدول partners ليشارك بالرصيد الجديد في الدورة القادمة
            $headPartner = self::headPartner(true);
            if ($headPartner) {
                DB::table('partners')->where('id', $headPartner->id)->update([
                    'capital'    => $ahmedClosingCap,
                    'updated_at' => now(),
                ]);
                $headPartnerId = $headPartner->id;
            } else {
                $headPartnerId = DB::table('partners')->insertGetId([
                    'name'                  => 'ربح احمد',
                    'capital'               => $ahmedClosingCap,
                    'initial_capital'       => 0,
                    'ahmed_ratio'           => 0,
                    'default_payout_action' => 'reinvest',
                    'joined_at'             => now()->format('Y-m-d'),
                    'status'                => 'active',
                    'is_head'               => 1,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            // صرف المبلغ المسحوب نقداً لحساب الإدارة من الخزنة المحددة إن وُجد
            if ($ahmedWithdrawn > 0 && $ahmedAccountId) {
                $acc = DB::table('accounts')->where('id', $ahmedAccountId)->lockForUpdate()->first();
                if ($acc) {
                    $balBefore = (float) $acc->balance;
                    $balAfter  = $balBefore - $ahmedWithdrawn;

                    DB::table('accounts')->where('id', $ahmedAccountId)->update([
                        'balance'    => $balAfter,
                        'updated_at' => now(),
                    ]);

                    $ftId = DB::table('financial_transactions')->insertGetId([
                        'type'            => 'partner_withdrawal',
                        'subtype'         => 'ahmed_profit_payout',
                        'amount'          => $ahmedWithdrawn,
                        'balance_before'  => $balBefore,
                        'balance_after'   => $balAfter,
                        'from_account_id' => $ahmedAccountId,
                        'person_name'     => self::headName() . ' (حساب الإدارة)',
                        'notes'           => "صرف أرباح حساب الإدارة: " . self::headName() . " عن فترة {$periodName}",
                        'status'          => 'active',
                        'ref_type'        => 'partner',
                        'ref_id'          => $headPartnerId,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    DB::table('partner_movements')->insert([
                        'partner_id'     => $headPartnerId,
                        'period_id'      => $periodId,
                        'type'           => 'profit_withdrawal',
                        'amount'         => $ahmedWithdrawn,
                        'account_id'     => $ahmedAccountId,
                        'transaction_id' => $ftId,
                        'notes'          => "صرف أرباح نقداً من حساب الإدارة: " . self::headName(),
                        'created_by'     => $closedBy,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }

            if ($ahmedReinvested != 0) {
                DB::table('partner_movements')->insert([
                    'partner_id'     => $headPartnerId,
                    'period_id'      => $periodId,
                    'type'           => 'profit_reinvest',
                    'amount'         => $ahmedReinvested,
                    'account_id'     => null,
                    'transaction_id' => null,
                    'notes'          => ($ahmedReinvested > 0 ? "تدوير أرباح نسب الشغل وإضافتها لحساب الإدارة: " : "تحمل خسائر الفترة بحساب الإدارة: ") . self::headName(),
                    'created_by'     => $closedBy,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            $totalWithdrawnActual  = round($totalWithdrawnActual + $ahmedWithdrawn, 2);
            $totalReinvestedActual = round($totalReinvestedActual + $ahmedReinvested, 2);

            // 5. حساب وحفظ النسب الجديدة بعد التقفيل (closing_share_pct) لكل شريك ولحساب الإدارة
            $totalClosingSharesActual = round(array_sum($insertedLineIds) + $ahmedClosingCap, 2);

            foreach ($insertedLineIds as $lineId => $capAfter) {
                $closingPct = $totalClosingSharesActual > 0
                    ? round(($capAfter / $totalClosingSharesActual) * 100, 4)
                    : 0.0;
                DB::table('partner_period_lines')->where('id', $lineId)->update([
                    'closing_share_pct' => $closingPct,
                ]);
            }

            $ahmedClosingSharePct = $totalClosingSharesActual > 0
                ? round(($ahmedClosingCap / $totalClosingSharesActual) * 100, 4)
                : 0.0;

            // تحديث الإجماليات الفعلية وحسابات الإدارة في جدول الفترة
            DB::table('partner_periods')->where('id', $periodId)->update([
                'total_withdrawn'         => $totalWithdrawnActual,
                'total_reinvested'        => $totalReinvestedActual,
                'ahmed_opening_capital'   => $ahmedOpeningCap,
                'ahmed_capital_share_pct' => $ahmedCapitalSharePct,
                'ahmed_own_profit'        => $ahmedOwnProfit,
                'ahmed_commission'        => $ahmedCommission,
                'ahmed_total_profit'      => $ahmedTotalProfit,
                'ahmed_payout_action'     => $ahmedAction,
                'ahmed_withdrawn_amount'  => $ahmedWithdrawn,
                'ahmed_payout_account_id' => $ahmedAccountId,
                'ahmed_reinvested_amount' => $ahmedReinvested,
                'ahmed_closing_capital'   => $ahmedClosingCap,
                'ahmed_closing_share_pct' => $ahmedClosingSharePct,
                'updated_at'              => now(),
            ]);

            // 6. حفظ سجل الهيستوري لتغير النسب (كانت كام وبقت كام)
            $afterMap = self::captureActiveSharesMap();
            self::recordShareHistory(
                'period_closing',
                "إقفال دورة: {$periodName}",
                $beforeMap,
                $afterMap,
                $periodId,
                null,
                null,
                $notes ?: "تحديث الحصص والنسب بعد إقفال الدورة وتوزيع الأرباح",
                $closedBy
            );

            return $periodId;
        });
    }

    /**
     * إيداع رأس مال شريك (تأسيسي أو زيادة) مع التأثير على الخزنة وتسجيل هيستوري النسب
     */
    public static function depositCapital(int $partnerId, float $amount, int $accountId, ?string $notes = null, ?int $userId = 1, bool $isInitial = false): void
    {
        DB::transaction(function () use ($partnerId, $amount, $accountId, $notes, $userId, $isInitial) {
            $beforeMap = self::captureActiveSharesMap();

            $partner = DB::table('partners')->where('id', $partnerId)->lockForUpdate()->first();
            if (!$partner) {
                throw new \Exception('الشريك غير موجود');
            }

            $acc = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->first();
            if (!$acc) {
                throw new \Exception('الخزنة المختارة غير موجودة');
            }

            $balBefore = (float) $acc->balance;
            $balAfter  = $balBefore + $amount;

            // تحديث رصيد الخزنة
            DB::table('accounts')->where('id', $accountId)->update([
                'balance'    => $balAfter,
                'updated_at' => now(),
            ]);

            // تحديث رأس مال الشريك
            $newCap = round((float) $partner->capital + $amount, 2);
            $updates = [
                'capital'    => $newCap,
                'updated_at' => now(),
            ];
            if ($isInitial) {
                $updates['initial_capital'] = (float) ($partner->initial_capital ?: $amount);
            }
            DB::table('partners')->where('id', $partnerId)->update($updates);

            // تسجيل العملية المالية في financial_transactions (معلمة بـ ref_type = partner)
            $ftId = DB::table('financial_transactions')->insertGetId([
                'type'           => 'partner_deposit',
                'subtype'        => $isInitial ? 'initial_capital' : 'capital_addition',
                'amount'         => $amount,
                'balance_before' => $balBefore,
                'balance_after'  => $balAfter,
                'to_account_id'  => $accountId,
                'person_name'    => $partner->name,
                'notes'          => $notes ?: ($isInitial ? "إيداع رأس مال تأسيسي للشريك: {$partner->name}" : "زيادة رأس مال للشريك: {$partner->name}"),
                'status'         => 'active',
                'ref_type'       => 'partner',
                'ref_id'         => $partnerId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // تسجيل الحركة في partner_movements
            $movId = DB::table('partner_movements')->insertGetId([
                'partner_id'     => $partnerId,
                'period_id'      => null,
                'type'           => $isInitial ? 'initial_deposit' : 'capital_increase',
                'amount'         => $amount,
                'account_id'     => $accountId,
                'transaction_id' => $ftId,
                'notes'          => $notes ?: ($isInitial ? 'إيداع رأس مال تأسيسي' : 'زيادة رأس مال'),
                'created_by'     => $userId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // تسجيل هيستوري تغير النسب
            $afterMap = self::captureActiveSharesMap();
            $title = ($isInitial ? "إيداع تأسيسي للشريك: " : "زيادة رأس مال الشريك: ") . $partner->name . " (+" . number_format($amount, 2) . " ج)";
            self::recordShareHistory(
                $isInitial ? 'initial_deposit' : 'capital_increase',
                $title,
                $beforeMap,
                $afterMap,
                null,
                $movId,
                $partnerId,
                $notes,
                $userId
            );
        });
    }

    /**
     * سحب من رأس مال الشريك (أو تخارج) مع التأثير على الخزنة وتسجيل هيستوري النسب
     */
    public static function withdrawCapital(int $partnerId, float $amount, int $accountId, ?string $notes = null, ?int $userId = 1, bool $isExit = false): void
    {
        DB::transaction(function () use ($partnerId, $amount, $accountId, $notes, $userId, $isExit) {
            $beforeMap = self::captureActiveSharesMap();

            $partner = DB::table('partners')->where('id', $partnerId)->lockForUpdate()->first();
            if (!$partner) {
                throw new \Exception('الشريك غير موجود');
            }

            if ($amount > (float) $partner->capital) {
                throw new \Exception('المبلغ المطلوب سحبه يتجاوز الرصيد الحالي المتاح.');
            }

            $acc = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->first();
            if (!$acc) {
                throw new \Exception('الخزنة المختارة غير موجودة');
            }

            if ((float) $acc->balance < $amount) {
                throw new \Exception('رصيد الخزنة لا يكفي لسحب هذا المبلغ.');
            }

            $balBefore = (float) $acc->balance;
            $balAfter  = $balBefore - $amount;

            // خصم من الخزنة
            DB::table('accounts')->where('id', $accountId)->update([
                'balance'    => $balAfter,
                'updated_at' => now(),
            ]);

            // خصم من الرصيد
            $newCap = max(0.0, round((float) $partner->capital - $amount, 2));
            $updates = [
                'capital'    => $newCap,
                'updated_at' => now(),
            ];
            if (($isExit || $newCap == 0) && empty($partner->is_head)) {
                $updates['status'] = 'exited';
            }
            DB::table('partners')->where('id', $partnerId)->update($updates);

            // تسجيل العملية المالية
            $ftId = DB::table('financial_transactions')->insertGetId([
                'type'            => 'partner_withdrawal',
                'subtype'         => $isExit ? 'partner_exit' : 'capital_withdrawal',
                'amount'          => $amount,
                'balance_before'  => $balBefore,
                'balance_after'   => $balAfter,
                'from_account_id' => $accountId,
                'person_name'     => $partner->name,
                'notes'           => $notes ?: ($isExit ? "تصفية وتخارج الشريك: {$partner->name}" : "سحب من رصيد: {$partner->name}"),
                'status'          => 'active',
                'ref_type'        => 'partner',
                'ref_id'          => $partnerId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $movId = DB::table('partner_movements')->insertGetId([
                'partner_id'     => $partnerId,
                'period_id'      => null,
                'type'           => 'capital_withdrawal',
                'amount'         => $amount,
                'account_id'     => $accountId,
                'transaction_id' => $ftId,
                'notes'          => $notes ?: ($isExit ? 'تصفية وتخارج نهائي' : 'سحب من الرصيد / رأس المال'),
                'created_by'     => $userId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // تسجيل هيستوري تغير النسب
            $afterMap = self::captureActiveSharesMap();
            if ($isExit || ($newCap == 0 && empty($partner->is_head))) {
                // إذا تخارج، نضع رصيده 0 في afterMap ليظهر انتقال نسبته إلى 0% في سجل هذا الحدث
                $afterMap[$partnerId] = [
                    'id'      => $partnerId,
                    'name'    => $partner->name,
                    'is_head' => (bool) $partner->is_head,
                    'capital' => 0.0,
                ];
            }
            $title = ($isExit ? "تصفية وتخارج الشريك: " : "سحب من رصيد: ") . $partner->name . " (-" . number_format($amount, 2) . " ج)";
            self::recordShareHistory(
                $isExit ? 'partner_exit' : 'capital_withdrawal',
                $title,
                $beforeMap,
                $afterMap,
                null,
                $movId,
                $partnerId,
                $notes,
                $userId
            );
        });
    }
}
