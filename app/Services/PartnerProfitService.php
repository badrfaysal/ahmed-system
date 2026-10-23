<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\InstallmentFinanceService;

class PartnerProfitService
{
    /**
     * رئيس مجلس الإدارة الحالي (المساهم المُعلَّم is_head) أو null إن لم يُحدد بعد
     */
    public static function headPartner(): ?object
    {
        static $cache = false;
        if ($cache === false) {
            $cache = DB::table('partners')->where('is_head', 1)->where('status', 'active')->first();
        }
        return $cache;
    }

    /**
     * الاسم المعروض لرئيس مجلس الإدارة
     */
    public static function headName(): string
    {
        $head = self::headPartner();
        if ($head) {
            return $head->name;
        }
        $admin = DB::table('users')->where('id', 1)->first();
        return $admin ? $admin->name : 'أحمد يحيى (المؤسس والإدارة)';
    }

    /**
     * المساهمون العاديون (بدون رئيس مجلس الإدارة)
     */
    public static function regularPartnersQuery()
    {
        return DB::table('partners')->where(function ($q) {
            $q->whereNull('is_head')->orWhere('is_head', 0);
        });
    }
    /**
     * حساب المعاينة الحية لتقفيل الفترة
     */
    public static function previewClosing(string $startDate, string $endDate, ?float $customClosingCapital = null): array
    {
        // 1. رأس المال الافتتاحي (أقرب لقطة لتاريخ البداية، أو رأس مال أول لقطة متاحة)
        $openingSnap = DB::table('capital_snapshots')
            ->whereDate('created_at', '<=', $startDate)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$openingSnap) {
            $openingSnap = DB::table('capital_snapshots')
                ->whereDate('created_at', '>=', $startDate)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        $openingCapital = $openingSnap ? (float) $openingSnap->total_capital : 0.0;
        $openingSnapshotId = $openingSnap?->id;

        // 2. رأس المال الختامي
        $closingSnapshotId = null;
        if ($customClosingCapital !== null) {
            $closingCapital = $customClosingCapital;
        } else {
            // البحث عن لقطة مسجلة في تاريخ النهاية
            $closingSnap = DB::table('capital_snapshots')
                ->whereDate('created_at', $endDate)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($closingSnap) {
                $closingCapital = (float) $closingSnap->total_capital;
                $closingSnapshotId = $closingSnap->id;
            } else {
                // استخدام رأس المال اللحظي الحالي
                $currentSummary = InstallmentFinanceService::treasurySummary();
                $closingCapital = (float) ($currentSummary['capital'] ?? 0.0);
            }
        }

        // 3. صافي الربح القابل للتوزيع مباشرة = لقطة الإقفال مطروحاً منها لقطة البداية (حساب مباشر بدون استثناءات)
        $rawDelta = $closingCapital - $openingCapital;
        $grossProfit = $rawDelta;
        $capitalAdjustmentsNet = 0.0;
        $partnerDepositsNet = 0.0;
        $adjIncrease = 0.0;
        $adjDecrease = 0.0;
        $partnerDeposits = 0.0;
        $partnerWithdrawals = 0.0;

        // 4. جلب الشركاء النشطين وحساب حصصهم
        $partners = self::regularPartnersQuery()
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        $totalPartnersCapital = (float) $partners->sum('capital');
        $partnerLines = [];

        $totalPartnersGrossProfit = 0.0;
        $totalAhmedCommission = 0.0;
        $totalPartnersNetProfit = 0.0;
        $totalWithdrawnProposed = 0.0;
        $totalReinvestedProposed = 0.0;

        foreach ($partners as $p) {
            $cap = (float) $p->capital;
            $ahmedRatio = (float) ($p->ahmed_ratio ?? 30.00);

            // نسبة رأس المال من إجمالي الشركة
            $capitalSharePct = $openingCapital > 0 ? ($cap / $openingCapital) * 100 : 0.0;

            if ($grossProfit >= 0) {
                // حالة الربح:
                $grossShare = round(($grossProfit * ($capitalSharePct / 100)), 2);
                $ahmedComm  = round(($grossShare * ($ahmedRatio / 100)), 2);
                $netShare   = round($grossShare - $ahmedComm, 2);
                $effectiveProfitPct = $capitalSharePct * (1 - ($ahmedRatio / 100));
            } else {
                // حالة الخسارة (الشريك يتحمل خسارته بالكامل وأحمد لا يأخذ عمولة من الخسارة):
                $grossShare = round(($grossProfit * ($capitalSharePct / 100)), 2); // رقم سالب
                $ahmedComm  = 0.0;
                $netShare   = $grossShare; // يتحمل كامل الخسارة سالبة
                $effectiveProfitPct = $capitalSharePct;
            }

            // بدون سلوك افتراضي مسبق — التدوير في رأس المال هو الأساس الحسابي للمعاينة لحين تحديد أحمد للقرار عند التقفيل
            $withdrawn = 0.0;
            $reinvested = $netShare;
            $newCapital = max(0.0, $cap + $reinvested);

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
                'default_action'       => 'reinvest',
                'payout_action'        => 'reinvest',
                'withdrawn_amount'     => 0.0,
                'reinvested_amount'    => $reinvested,
                'closing_capital'      => $newCapital,
            ];

            $totalPartnersGrossProfit += $grossShare;
            $totalAhmedCommission     += $ahmedComm;
            $totalPartnersNetProfit   += $netShare;
            $totalWithdrawnProposed   += $withdrawn;
            $totalReinvestedProposed  += $reinvested;
        }

        // 7. حساب بروفايل أحمد للتحليل
        $ahmedOpeningCapital = max(0.0, $openingCapital - $totalPartnersCapital);
        $ahmedCapitalSharePct = $openingCapital > 0 ? ($ahmedOpeningCapital / $openingCapital) * 100 : 100.0;
        $ahmedOwnGrossProfit = round(($grossProfit * ($ahmedCapitalSharePct / 100)), 2);
        $ahmedTotalProfit = round($ahmedOwnGrossProfit + $totalAhmedCommission, 2);
        $ahmedClosingCapital = max(0.0, $closingCapital - ($totalPartnersCapital + $totalReinvestedProposed));

        return [
            'start_date'               => $startDate,
            'end_date'                 => $endDate,
            'opening_snapshot_id'      => $openingSnapshotId,
            'opening_capital'          => $openingCapital,
            'closing_snapshot_id'      => $closingSnapshotId,
            'closing_capital'          => $closingCapital,
            'raw_delta'                => $rawDelta,
            'capital_adjustments_net'  => $capitalAdjustmentsNet,
            'adj_increase'             => $adjIncrease,
            'adj_decrease'             => $adjDecrease,
            'partner_deposits_net'     => $partnerDepositsNet,
            'partner_deposits'         => $partnerDeposits,
            'partner_withdrawals'      => $partnerWithdrawals,
            'gross_profit'             => $grossProfit,
            'total_partners_capital'   => $totalPartnersCapital,
            'total_partners_gross'     => $totalPartnersGrossProfit,
            'total_ahmed_commission'   => $totalAhmedCommission,
            'total_partners_net'       => $totalPartnersNetProfit,
            'total_withdrawn_proposed' => $totalWithdrawnProposed,
            'total_reinvested_proposed'=> $totalReinvestedProposed,
            'partner_lines'            => $partnerLines,
            'ahmed' => [
                'opening_capital'   => $ahmedOpeningCapital,
                'capital_share_pct' => round($ahmedCapitalSharePct, 4),
                'own_profit'        => $ahmedOwnGrossProfit,
                'commissions'       => $totalAhmedCommission,
                'total_profit'      => $ahmedTotalProfit,
                'closing_capital'   => $ahmedClosingCapital,
            ],
        ];
    }

    /**
     * اعتماد تقفيل الفترة وحفظ الحركات في الداتابيز والخزائن
     */
    public static function executeClosing(array $preview, array $partnerDecisions, string $periodName, ?string $notes = null, ?int $closedBy = 1): int
    {
        return DB::transaction(function () use ($preview, $partnerDecisions, $periodName, $notes, $closedBy) {
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
                'total_withdrawn'             => 0, // سيُحسب بعد قليل
                'total_reinvested'            => 0,
                'status'                      => 'closed',
                'notes'                       => $notes,
                'closed_by'                   => $closedBy,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            $totalWithdrawnActual  = 0.0;
            $totalReinvestedActual = 0.0;

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

                $reinvested = $netShare - $withdrawn;
                $newCapital = max(0.0, (float) $line['opening_capital'] + $reinvested);

                // حفظ سطر الشريك في partner_period_lines
                DB::table('partner_period_lines')->insert([
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
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

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

            // 4. معالجة حصة وقرار أحمد (المؤسس والإدارة)
            $ahmedDecision = $partnerDecisions['ahmed'] ?? [];
            $ahmedOpeningCap = (float) ($preview['ahmed']['opening_capital'] ?? 0.0);
            $ahmedCapitalSharePct = (float) ($preview['ahmed']['capital_share_pct'] ?? 0.0);
            $ahmedOwnProfit = (float) ($preview['ahmed']['own_profit'] ?? 0.0);
            $ahmedCommission = (float) ($preview['ahmed']['commissions'] ?? 0.0);
            $ahmedTotalProfit = (float) ($preview['ahmed']['total_profit'] ?? 0.0);

            $ahmedAction = $ahmedDecision['payout_action'] ?? 'reinvest';
            $ahmedWithdrawn = 0.0;
            $ahmedAccountId = null;

            if ($ahmedTotalProfit > 0) {
                if ($ahmedAction === 'withdraw_all') {
                    $ahmedWithdrawn = $ahmedTotalProfit;
                } elseif ($ahmedAction === 'withdraw_partial') {
                    $ahmedWithdrawn = min($ahmedTotalProfit, max(0.0, (float) ($ahmedDecision['custom_withdrawn'] ?? 0.0)));
                } else {
                    $ahmedAction = 'reinvest';
                    $ahmedWithdrawn = 0.0;
                }

                $ahmedAccountId = !empty($ahmedDecision['account_id']) ? (int) $ahmedDecision['account_id'] : null;

                if ($ahmedWithdrawn > 0 && !$ahmedAccountId) {
                    throw new \Exception("يرجى تحديد الخزنة المصروف منها أرباح رئيس مجلس الإدارة (" . self::headName() . ").");
                }
            } else {
                $ahmedAction = 'reinvest';
                $ahmedWithdrawn = 0.0;
                $ahmedAccountId = null;
            }

            $ahmedReinvested = $ahmedTotalProfit - $ahmedWithdrawn;
            $ahmedClosingCap = max(0.0, $ahmedOpeningCap + $ahmedReinvested);

            // صرف المبلغ المسحوب نقداً لأحمد من الخزنة المحددة إن وُجد
            if ($ahmedWithdrawn > 0 && $ahmedAccountId) {
                $acc = DB::table('accounts')->where('id', $ahmedAccountId)->lockForUpdate()->first();
                if ($acc) {
                    $balBefore = (float) $acc->balance;
                    $balAfter  = $balBefore - $ahmedWithdrawn;

                    DB::table('accounts')->where('id', $ahmedAccountId)->update([
                        'balance'    => $balAfter,
                        'updated_at' => now(),
                    ]);

                    DB::table('financial_transactions')->insertGetId([
                        'type'            => 'partner_withdrawal',
                        'subtype'         => 'ahmed_profit_payout',
                        'amount'          => $ahmedWithdrawn,
                        'balance_before'  => $balBefore,
                        'balance_after'   => $balAfter,
                        'from_account_id' => $ahmedAccountId,
                        'person_name'     => self::headName() . ' (رئيس مجلس الإدارة)',
                        'notes'           => "صرف أرباح رئيس مجلس الإدارة: " . self::headName() . " عن فترة {$periodName}",
                        'status'          => 'active',
                        'ref_type'        => 'partner',
                        'ref_id'          => self::headPartner()?->id ?? 0,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }

            $totalWithdrawnActual  += $ahmedWithdrawn;
            $totalReinvestedActual += $ahmedReinvested;

            // تحديث الإجماليات الفعلية وحسابات أحمد في جدول الفترة
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
                'updated_at'              => now(),
            ]);

            return $periodId;
        });
    }

    /**
     * إيداع رأس مال شريك (تأسيسي أو زيادة) مع التأثير على الخزنة
     */
    public static function depositCapital(int $partnerId, float $amount, int $accountId, ?string $notes = null, ?int $userId = 1, bool $isInitial = false): void
    {
        DB::transaction(function () use ($partnerId, $amount, $accountId, $notes, $userId, $isInitial) {
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
            $newCap = (float) $partner->capital + $amount;
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
            DB::table('partner_movements')->insert([
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
        });
    }

    /**
     * سحب من رأس مال الشريك (أو تخارج) مع التأثير على الخزنة
     */
    public static function withdrawCapital(int $partnerId, float $amount, int $accountId, ?string $notes = null, ?int $userId = 1, bool $isExit = false): void
    {
        DB::transaction(function () use ($partnerId, $amount, $accountId, $notes, $userId, $isExit) {
            $partner = DB::table('partners')->where('id', $partnerId)->lockForUpdate()->first();
            if (!$partner) {
                throw new \Exception('الشريك غير موجود');
            }

            if ($amount > (float) $partner->capital) {
                throw new \Exception('المبلغ المطلوب سحبه يتجاوز رصيد رأس مال الشريك الحالي.');
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

            // خصم من رأس مال الشريك
            $newCap = max(0.0, (float) $partner->capital - $amount);
            $updates = [
                'capital'    => $newCap,
                'updated_at' => now(),
            ];
            if ($isExit || $newCap == 0) {
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
                'notes'           => $notes ?: ($isExit ? "تصفية وتخارج الشريك: {$partner->name}" : "سحب من رأس مال الشريك: {$partner->name}"),
                'status'          => 'active',
                'ref_type'        => 'partner',
                'ref_id'          => $partnerId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('partner_movements')->insert([
                'partner_id'     => $partnerId,
                'period_id'      => null,
                'type'           => $isExit ? 'capital_withdrawal' : 'capital_withdrawal',
                'amount'         => $amount,
                'account_id'     => $accountId,
                'transaction_id' => $ftId,
                'notes'          => $notes ?: ($isExit ? 'تصفية وتخارج نهائي' : 'سحب من رأس المال'),
                'created_by'     => $userId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });
    }
}
