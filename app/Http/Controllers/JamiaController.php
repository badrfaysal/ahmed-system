<?php

namespace App\Http\Controllers;

use App\Models\Jamia;
use App\Models\JamiaShare;
use App\Models\JamiaInstallment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * منظومة الجمعيات من منظور "المنظم":
 *  - الشركة بتلم الجمعية من الأعضاء كل شهر (فترة التحصيل من يوم 1 لغاية يوم 10 بحد أقصى).
 *  - تحصيل قسط العضو  = داخل للخزنة  (income  / ref_type = jamia_collection).
 *  - صرف القبض لصاحب الدور = خارج من الخزنة (expense / ref_type = jamia_payout_out).
 *  - أسهم الشركة (شركة الضبع) لا تحتاج تحصيل ولا صرف نقدي، لأن الفلوس أصلاً عند الشركة.
 *
 * ملاحظة على استخدام الأعمدة:
 *  - jamia_installments.is_paid / paid_account_id / tx_id  => حالة تحصيل القسط من العضو.
 *  - jamia_shares.is_collected / collected_*                => حالة صرف (تسليم) القبض لصاحب الدور.
 */
class JamiaController extends SystemController
{
    public const COMPANY_NAME = 'شركة الضبع';
    public const MAX_COLLECTION_DAY = 10;

    /**
     * كاش خفيف لفحص وجود الجداول والأعمدة بدل ضرب information_schema في كل تحميل لصفحة الجمعيات.
     * في بيئة الاختبارات نرجع الفحص المباشر لأن كل test يعيد إنشاء الجداول بتركيبات مختلفة.
     */
    private static function schemaFlags(): array
    {
        $resolver = static function (): array {
            $hasPartners     = Schema::hasTable('partners');
            $hasInstallments = Schema::hasTable('installments');

            return [
                'company_debts'                     => Schema::hasTable('company_debts'),
                'partners'                          => $hasPartners,
                'partners_monthly_fixed_withdrawal' => $hasPartners && Schema::hasColumn('partners', 'monthly_fixed_withdrawal'),
                'partners_status'                   => $hasPartners && Schema::hasColumn('partners', 'status'),
                'partners_is_head'                  => $hasPartners && Schema::hasColumn('partners', 'is_head'),
                'installments'                      => $hasInstallments,
                'installments_status'               => $hasInstallments && Schema::hasColumn('installments', 'status'),
                'installment_payments'              => Schema::hasTable('installment_payments'),
            ];
        };

        if (app()->runningUnitTests()) {
            return $resolver();
        }

        return Cache::remember('jamia_schema_flags_v1', 600, $resolver);
    }

    /**
     * عرض شاشة التدفقات النقدية للجمعيات
     */
    public function index(Request $request)
    {
        // مزامنة أي حركات تم إلغاؤها من سجل العمليات لضمان مطابقة الحالة 100%
        $this->syncWithCancelledTransactions();
        self::syncAllJamiaCompanyDebts();

        // الشهر المختار (الافتراضي: الشهر الحالي)
        $selectedMonth  = $request->input('month', now()->format('Y-m'));
        $monthDate      = Carbon::parse($selectedMonth . '-01');
        $monthStart     = $monthDate->copy()->startOfMonth()->toDateString();
        $nextMonthStart = $monthDate->copy()->startOfMonth()->addMonth()->toDateString();

        // 1. حسابات المحافظ / الخزن المتاحة
        $accounts = DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->where('status', 'active')
            ->orderBy('account_name')
            ->get();

        // 2. أقساط المشتركين المطلوب تحصيلها هذا الشهر (نطاق تاريخ يستخدم الفهرس مباشرة في MySQL و SQLite)
        $allMonthInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->leftJoin('accounts as a', 'ji.paid_account_id', '=', 'a.id')
            ->where('ji.month_date', '>=', $monthStart)
            ->where('ji.month_date', '<', $nextMonthStart)
            ->where('ji.is_waived', false)
            ->where('j.status', 'active')
            ->select(
                'ji.*',
                'j.name as jamia_name',
                'j.collection_start_day',
                'j.collection_end_day',
                'js.share_label',
                'js.share_number',
                'js.share_fraction',
                'js.split_part',
                'js.member_phone',
                'js.is_company',
                'a.account_name as paid_account_name'
            )
            ->orderBy('js.share_number')
            ->orderBy('js.split_part')
            ->get()
            ->map(function ($inst) {
                $inst->status = self::installmentStatus($inst);
                return $inst;
            });

        // أقساط الأعضاء فقط (بدون أسهم الشركة)
        $monthInstallments = $allMonthInstallments->where('is_company', false)->values();

        // 3. أدوار القبض هذا الشهر (اللي هيستلم الجمعية)
        $monthShares = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->leftJoin('accounts as ca', 'js.collected_account_id', '=', 'ca.id')
            ->leftJoin('accounts as ra', 'js.recovered_account_id', '=', 'ra.id')
            ->where('js.payout_month', '>=', $monthStart)
            ->where('js.payout_month', '<', $nextMonthStart)
            ->where('j.status', 'active')
            ->select(
                'js.*',
                'j.name as jamia_name',
                'j.collection_end_day',
                'ca.account_name as collected_account_name',
                'ra.account_name as recovered_account_name'
            )
            ->orderBy('js.share_number')
            ->orderBy('js.split_part')
            ->get();

        // 4. مؤشرات الشهر:
        //  - تحصيل أقساط الجمعية من الأعضاء فقط (بدون أسهم الشركة)
        $expectedCollection  = (float) $monthInstallments->sum('amount');
        $collectedThisMonth  = (float) $monthInstallments->where('is_paid', true)->sum('amount');
        $remainingCollection = max(0.0, $expectedCollection - $collectedThisMonth);
        $paidMembersCount    = $monthInstallments->where('is_paid', true)->count();
        $unpaidMembersCount  = $monthInstallments->where('is_paid', false)->count();
        $lateThisMonth       = $monthInstallments->filter(fn ($i) => $i->status['key'] === 'late');
        $lateCountThisMonth  = $lateThisMonth->count();
        $lateAmountThisMonth = (float) $lateThisMonth->sum('amount');

        $membersOnlyExpected  = $expectedCollection;
        $membersOnlyCollected = $collectedThisMonth;

        // التزاماتي للجمعيات هذا الشهر (أقساط أسهم الشركة في الجمعيات النشطة)
        $companyMonthInstallments = $allMonthInstallments->where('is_company', true)->values();
        $companyInstallmentsDue   = (float) $companyMonthInstallments->sum('amount');
        $companyInstallmentsPaid  = (float) $companyMonthInstallments->where('is_paid', true)->sum('amount');
        $companySharesMonthCount  = $companyMonthInstallments->count();

        // إجمالي القبض المستحق (الخارج للأعضاء فقط بدون دور الشركة — يحسب الدفعات الجزئية بدقة)
        $memberPayouts       = $monthShares->where('is_company', false)->values();
        $payoutDue           = (float) $memberPayouts->sum('payout_amount');
        $payoutDone          = (float) $memberPayouts->sum('collected_amount');
        $payoutRemaining     = max(0.0, round($payoutDue - $payoutDone, 2));
        $payoutMembersCount  = $memberPayouts->count();
        $payoutDoneCount     = $memberPayouts->where('is_collected', true)->count();
        $payoutPendingCount  = $memberPayouts->where('is_collected', false)->count();

        $memberPayoutDue     = $payoutDue;
        $memberPayoutDone    = $payoutDone;

        // قبض الشركة هذا الشهر (لو للشركة دور قبض في جمعية هذا الشهر)
        $companyPayouts          = $monthShares->where('is_company', true)->values();
        $companyPayoutDue        = (float) $companyPayouts->sum('payout_amount');
        $companyPayoutDone       = (float) $companyPayouts->sum('collected_amount');
        $companyPayoutRemaining  = max(0.0, round($companyPayoutDue - $companyPayoutDone, 2));
        $companyPayoutCount      = $companyPayouts->count();

        // دفعات الصرف التفصيلية المسجلة لأدوار الشهر (لعرض كل دفعة جزئية وإمكانية إلغائها منفردة)
        $monthShareIds = $monthShares->pluck('id')->all();
        $payoutTxsByShare = !empty($monthShareIds)
            ? DB::table('financial_transactions as ft')
                ->leftJoin('accounts as a', function ($join) {
                    $join->on('a.id', '=', DB::raw('COALESCE(ft.from_account_id, ft.to_account_id)'));
                })
                ->whereIn('ft.ref_type', ['jamia_payout_out', 'jamia_share'])
                ->whereIn('ft.ref_id', $monthShareIds)
                ->where('ft.status', 'active')
                ->select('ft.id', 'ft.ref_id', 'ft.amount', 'ft.type', 'ft.notes', 'ft.created_at', 'a.account_name')
                ->orderBy('ft.id')
                ->get()
                ->groupBy('ref_id')
            : collect();

        // صافي موقف الشركة (قبض دور الشركة - التزامات أسهم الشركة)
        $companyNetExpected      = $companyPayoutDue - $companyInstallmentsDue;
        $companyNetActual        = $companyPayoutDone - $companyInstallmentsPaid;

        // حسبة الجمعيات الشهر ده (الداخل من أقساط الأعضاء فقط مقابل الخارج لقبض الأعضاء فقط)
        $totalInExpected         = $expectedCollection;
        $totalInActual           = $collectedThisMonth;
        $totalOutExpected        = $payoutDue;
        $totalOutActual          = $payoutDone;
        $netGeneralExpected      = $totalInExpected - $totalOutExpected;
        $netGeneralActual        = $totalInActual - $totalOutActual;

        $netExpectedThisMonth = $netGeneralExpected;
        $netActualThisMonth   = $netGeneralActual;

        // 5. شارت التدفقات السنوي (التحصيل المتوقع من الأعضاء vs القبض المصروف لهم)
        $year          = $monthDate->year;
        $yearStart     = sprintf('%04d-01-01', $year);
        $nextYearStart = sprintf('%04d-01-01', $year + 1);
        $arabicMonths  = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        $collectionByMonth = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->where('ji.month_date', '>=', $yearStart)
            ->where('ji.month_date', '<', $nextYearStart)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->groupBy('ji.month_date')
            ->selectRaw('ji.month_date as m, SUM(ji.amount) as total')
            ->get()
            ->reduce(function ($carry, $row) {
                $key = substr((string) $row->m, 0, 10);
                $carry[$key] = ($carry[$key] ?? 0) + (float) $row->total;
                return $carry;
            }, []);

        $payoutByMonth = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->where('js.payout_month', '>=', $yearStart)
            ->where('js.payout_month', '<', $nextYearStart)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->groupBy('js.payout_month')
            ->selectRaw('js.payout_month as m, SUM(js.payout_amount) as total')
            ->get()
            ->reduce(function ($carry, $row) {
                $key = substr((string) $row->m, 0, 10);
                $carry[$key] = ($carry[$key] ?? 0) + (float) $row->total;
                return $carry;
            }, []);

        $chartLabels = $chartCollection = $chartPayout = $chartNet = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d-01', $year, $m);
            $in  = round((float) ($collectionByMonth[$key] ?? 0), 2);
            $out = round((float) ($payoutByMonth[$key] ?? 0), 2);
            $chartLabels[]     = $arabicMonths[$m];
            $chartCollection[] = $in;
            $chartPayout[]     = $out;
            $chartNet[]        = round($in - $out, 2);
        }

        // 6. دليل الجمعيات بالأعضاء والإحصائيات الكلية (تجميع SQL خفيف بدل تحميل آلاف موديلات الأقساط في الذاكرة)
        $today              = now();
        $todayMonthStart    = $today->copy()->startOfMonth()->toDateString();
        $todayNextMonth     = $today->copy()->startOfMonth()->addMonth()->toDateString();
        $todayDay           = (int) $today->day;

        $instStatsByJamia = DB::table('jamia_installments')
            ->selectRaw(
                'jamia_id, share_id, ' .
                'SUM(CASE WHEN is_waived = 0 THEN amount ELSE 0 END) as total_due, ' .
                'SUM(CASE WHEN is_waived = 0 AND is_paid = 1 THEN amount ELSE 0 END) as total_collected, ' .
                'SUM(CASE WHEN is_waived = 0 AND is_paid = 1 THEN 1 ELSE 0 END) as paid_count, ' .
                'SUM(CASE WHEN is_waived = 1 THEN 1 ELSE 0 END) as waived_count, ' .
                'SUM(CASE WHEN tx_id IS NOT NULL THEN 1 ELSE 0 END) as tx_count, ' .
                'SUM(CASE WHEN is_waived = 0 AND is_paid = 0 AND month_date < ? THEN 1 ELSE 0 END) as late_prev_months, ' .
                'SUM(CASE WHEN is_waived = 0 AND is_paid = 0 AND month_date >= ? AND month_date < ? THEN 1 ELSE 0 END) as late_curr_month',
                [$todayMonthStart, $todayMonthStart, $todayNextMonth]
            )
            ->groupBy('jamia_id', 'share_id')
            ->get()
            ->groupBy('jamia_id');

        $jamias = Jamia::with([
                'shares' => fn ($q) => $q->with(['collectedAccount', 'recoveredAccount'])->orderBy('share_number')->orderBy('split_part'),
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($jamia) use ($instStatsByJamia, $todayDay) {
                $memberShareSet = [];
                foreach ($jamia->shares as $sh) {
                    if (!$sh->is_company) {
                        $memberShareSet[$sh->id] = true;
                    }
                }

                $startDay = max(1, (int) ($jamia->collection_start_day ?? 1));
                $endDay   = min(self::MAX_COLLECTION_DAY, max($startDay, (int) ($jamia->collection_end_day ?? self::MAX_COLLECTION_DAY)));
                $isCurrMonthLate = $todayDay > $endDay;

                $totalDue       = 0.0;
                $totalCollected = 0.0;
                $hasInstTx      = false;
                $lateByShare    = [];
                $paidByShare    = [];
                $waivedByShare  = [];

                foreach ($instStatsByJamia->get($jamia->id, collect()) as $st) {
                    if ((int) $st->tx_count > 0) {
                        $hasInstTx = true;
                    }
                    $waivedCnt = (int) $st->waived_count;
                    if ($waivedCnt > 0) {
                        $waivedByShare[$st->share_id] = $waivedCnt;
                    }
                    if (!isset($memberShareSet[$st->share_id])) {
                        continue;
                    }
                    $totalDue       += (float) $st->total_due;
                    $totalCollected += (float) $st->total_collected;

                    $paidCnt = (int) $st->paid_count;
                    if ($paidCnt > 0) {
                        $paidByShare[$st->share_id] = $paidCnt;
                    }
                    $lateCnt = (int) $st->late_prev_months + ($isCurrMonthLate ? (int) $st->late_curr_month : 0);
                    if ($lateCnt > 0) {
                        $lateByShare[$st->share_id] = $lateCnt;
                    }
                }

                $jamia->calc_total_due       = $totalDue;
                $jamia->calc_total_collected = $totalCollected;
                $jamia->calc_remaining       = $totalDue - $totalCollected;
                $jamia->calc_progress        = $totalDue > 0 ? round(($totalCollected / $totalDue) * 100) : 0;
                $jamia->calc_has_cash_moves  = $hasInstTx
                    || $jamia->shares->contains(fn ($s) => $s->is_collected || (float) ($s->collected_amount ?? 0) > 0)
                    || $jamia->shares->where('is_transferred', true)->isNotEmpty()
                    || $jamia->shares->where('is_claim_recovered', true)->isNotEmpty();
                $jamia->members_count        = $jamia->shares->count();
                $jamia->company_shares_in    = $jamia->shares->where('is_company', true)->count();
                $jamia->transferred_count    = $jamia->shares->where('is_transferred', true)->count();
                $jamia->collected_count      = $jamia->shares->where('is_collected', true)->count();
                $jamia->late_by_share        = $lateByShare;
                $jamia->paid_by_share        = $paidByShare;
                $jamia->waived_by_share      = $waivedByShare;
                $jamia->late_total_count     = array_sum($lateByShare);

                return $jamia;
            });

        // 7. الجمعيات النشطة في الشهر المختار مجمعة (إعادة استخدام $jamias المحمّلة بدل استعلام إضافي)
        $monthJamias = $jamias
            ->where('status', 'active')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(function ($jamia) use ($monthDate, $monthInstallments, $monthShares) {
                $start = Carbon::parse($jamia->start_date)->startOfMonth();
                $jamia->month_index        = (($monthDate->year - $start->year) * 12) + ($monthDate->month - $start->month) + 1;
                $jamia->month_installments = $monthInstallments->where('jamia_id', $jamia->id)->values();
                $jamia->month_payouts      = $monthShares->where('jamia_id', $jamia->id)->values();
                return $jamia;
            })
            ->filter(fn ($j) => $j->month_index >= 1 && $j->month_index <= $j->duration_months)
            ->values();

        // 8. متابعة المتأخرين في السداد (الأعضاء فقط في كل الشهور اللي فترة تحصيلها خلصت)
        $overdueInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->where('ji.is_paid', false)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->where('ji.month_date', '<', $todayNextMonth)
            ->select(
                'ji.*',
                'j.name as jamia_name',
                'j.collection_start_day',
                'j.collection_end_day',
                'js.share_label',
                'js.share_number',
                'js.share_fraction',
                'js.split_part',
                'js.member_phone',
                'js.is_company'
            )
            ->orderBy('ji.month_date')
            ->orderBy('j.name')
            ->orderBy('js.share_number')
            ->orderBy('js.split_part')
            ->get()
            ->map(function ($inst) {
                $inst->status = self::installmentStatus($inst);
                return $inst;
            })
            ->filter(fn ($i) => $i->status['key'] === 'late')
            ->values();

        // 9. إحصائيات عامة + قائمة الأعضاء الفريدة (من البيانات المحمّلة بالفعل بدون 3 استعلامات مكررة)
        $totalMembersShares = 0;
        $totalCompanyShares = 0;
        $membersIndex       = [];

        foreach ($jamias as $jamia) {
            foreach ($jamia->shares as $sh) {
                if ($sh->is_company) {
                    $totalCompanyShares++;
                    continue;
                }
                $totalMembersShares++;
                $normName = trim($sh->share_label);
                if ($normName === '') continue;
                $phone = trim((string) ($sh->member_phone ?? ''));
                $key = $phone !== '' ? 'p:' . $phone : 'n:' . mb_strtolower($normName);
                if (!isset($membersIndex[$key])) {
                    $membersIndex[$key] = [
                        'name'         => $normName,
                        'phone'        => $phone,
                        'shares_count' => 0,
                        'jamias'       => [],
                        'share_id'     => $sh->id,
                    ];
                }
                $membersIndex[$key]['shares_count']++;
                $membersIndex[$key]['jamias'][$jamia->id] = $jamia->name;
                if (empty($membersIndex[$key]['phone']) && !empty($phone)) {
                    $membersIndex[$key]['phone'] = $phone;
                }
            }
        }

        $globalStats = [
            'active_jamias_count' => $jamias->where('status', 'active')->count(),
            'members_count'       => $totalMembersShares,
            'company_shares'      => $totalCompanyShares,
            'overdue_count'       => $overdueInstallments->count(),
            'overdue_amount'      => (float) $overdueInstallments->sum('amount'),
        ];

        $membersList = collect($membersIndex)->map(function ($m) {
            return [
                'name'         => $m['name'],
                'phone'        => $m['phone'],
                'shares_count' => $m['shares_count'],
                'jamias_count' => count($m['jamias']),
                'jamias_names' => implode('، ', array_filter(array_values($m['jamias']))),
                'share_id'     => $m['share_id'],
            ];
        })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $companyName = self::COMPANY_NAME;

        // 11. إجمالي الأقساط المستحقة من منظومة الأقساط من يوم 1 إلى يوم 10
        $instSystem1to10 = $this->computeInstallmentsSystem1To10($monthDate);

        // 12. مجموع المسحوبات الشهرية الثابتة للشركاء النشطين (خارج شهري من شاشة الشركاء)
        $partnersMonthlyWithdrawals = $this->computePartnersMonthlyWithdrawals();

        return view('cashflows', compact(
            'selectedMonth',
            'monthDate',
            'accounts',
            'monthInstallments',
            'monthShares',
            'payoutTxsByShare',
            'monthJamias',
            'expectedCollection',
            'collectedThisMonth',
            'remainingCollection',
            'paidMembersCount',
            'unpaidMembersCount',
            'lateCountThisMonth',
            'lateAmountThisMonth',
            'membersOnlyExpected',
            'membersOnlyCollected',
            'payoutDue',
            'payoutDone',
            'payoutRemaining',
            'payoutMembersCount',
            'payoutDoneCount',
            'payoutPendingCount',
            'memberPayoutDue',
            'memberPayoutDone',
            'companyMonthInstallments',
            'companyInstallmentsDue',
            'companyInstallmentsPaid',
            'companySharesMonthCount',
            'companyPayoutDue',
            'companyPayoutDone',
            'companyPayoutRemaining',
            'companyPayoutCount',
            'companyNetExpected',
            'companyNetActual',
            'totalInExpected',
            'totalInActual',
            'totalOutExpected',
            'totalOutActual',
            'netGeneralExpected',
            'netGeneralActual',
            'netExpectedThisMonth',
            'netActualThisMonth',
            'chartLabels',
            'chartCollection',
            'chartPayout',
            'chartNet',
            'jamias',
            'overdueInstallments',
            'globalStats',
            'membersList',
            'companyName',
            'instSystem1to10',
            'partnersMonthlyWithdrawals'
        ));
    }

    /**
     * حساب مجموع المسحوبات الشهرية الثابتة للشركاء النشطين من شاشة الشركاء (التزامات خارجة شهرياً).
     */
    private function computePartnersMonthlyWithdrawals(): array
    {
        $empty = [
            'total'          => 0.0,
            'partners_count' => 0,
            'partners'       => collect(),
        ];

        $flags = self::schemaFlags();
        if (!$flags['partners'] || !$flags['partners_monthly_fixed_withdrawal']) {
            return $empty;
        }

        $query = DB::table('partners')
            ->where('monthly_fixed_withdrawal', '>', 0);

        if ($flags['partners_status']) {
            $query->where('status', 'active');
        }
        if ($flags['partners_is_head']) {
            $query->where(function ($q) {
                $q->whereNull('is_head')->orWhere('is_head', 0);
            });
        }

        $partners = $query
            ->select('id', 'name', 'monthly_fixed_withdrawal')
            ->orderByDesc('monthly_fixed_withdrawal')
            ->orderBy('name')
            ->get();

        return [
            'total'          => round((float) $partners->sum('monthly_fixed_withdrawal'), 2),
            'partners_count' => $partners->count(),
            'partners'       => $partners,
        ];
    }

    /**
     * حساب إجمالي الأقساط المستحقة من منظومة العقود والأقساط (من يوم 1 إلى يوم 10) في الشهر المحدد.
     */
    private function computeInstallmentsSystem1To10(Carbon $monthDate): array
    {
        $result = [
            'total'           => 0.0,
            'collected'       => 0.0,
            'remaining'       => 0.0,
            'contracts_count' => 0,
            'customers_count' => 0,
        ];

        $flags = self::schemaFlags();
        if (!$flags['installments']) {
            return $result;
        }

        $query = DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('remaining_balance', '>', 0)
            ->whereBetween('due_day', [1, self::MAX_COLLECTION_DAY]);

        if ($flags['installments_status']) {
            $query->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
            });
        }

        $contracts = $query
            ->select('id', 'customer_name', 'customer_phone', 'monthly_installment')
            ->get();

        if ($contracts->isEmpty()) {
            return $result;
        }

        $total = (float) $contracts->sum('monthly_installment');

        $collected = 0.0;
        if ($flags['installment_payments']) {
            $ids = $contracts->pluck('id')->all();
            $mStart = $monthDate->copy()->startOfMonth()->startOfDay();
            $mNext  = $monthDate->copy()->startOfMonth()->addMonth()->startOfDay();
            $collected = (float) DB::table('installment_payments')
                ->whereIn('installment_id', $ids)
                ->where('amount_paid', '>', 0)
                ->where('payment_date', '>=', $mStart)
                ->where('payment_date', '<', $mNext)
                ->sum('amount_paid');
        }

        $uniqueCustomers = [];
        foreach ($contracts as $row) {
            $phone = trim((string) ($row->customer_phone ?? ''));
            $key = ($phone !== '' && $phone !== '—' && $phone !== '-' && $phone !== 'بدون')
                ? 'p:' . $phone
                : 'n:' . mb_strtolower(trim((string) ($row->customer_name ?? '')));
            $uniqueCustomers[$key] = true;
        }

        return [
            'total'           => $total,
            'collected'       => $collected,
            'remaining'       => max(0.0, $total - $collected),
            'contracts_count' => $contracts->count(),
            'customers_count' => count($uniqueCustomers),
        ];
    }

    /**
     * حالة قسط العضو بالنسبة لفترة التحصيل (من يوم البداية لغاية يوم النهاية بحد أقصى 10)
     * المفاتيح: paid | paid_late | upcoming | open | late
     */
    public static function installmentStatus($inst): array
    {
        $monthStart = Carbon::parse($inst->month_date)->startOfMonth();
        $startDay   = max(1, (int) ($inst->collection_start_day ?? 1));
        $endDay     = min(self::MAX_COLLECTION_DAY, max($startDay, (int) ($inst->collection_end_day ?? self::MAX_COLLECTION_DAY)));
        $windowFrom = $monthStart->copy()->day($startDay)->startOfDay();
        $windowTo   = $monthStart->copy()->day($endDay)->endOfDay();
        $today      = now();

        if ($inst->is_paid) {
            if ($inst->paid_at && Carbon::parse($inst->paid_at)->gt($windowTo)) {
                return ['key' => 'paid_late', 'label' => 'تم التحصيل (متأخر)', 'days' => 0, 'window_to' => $windowTo];
            }
            return ['key' => 'paid', 'label' => 'تم التحصيل', 'days' => 0, 'window_to' => $windowTo];
        }

        if ($today->lt($windowFrom)) {
            return ['key' => 'upcoming', 'label' => 'لم تبدأ فترة التحصيل', 'days' => 0, 'window_to' => $windowTo];
        }

        if ($today->lte($windowTo)) {
            $daysLeft = (int) $today->copy()->startOfDay()->diffInDays($windowTo->copy()->startOfDay());
            return ['key' => 'open', 'label' => 'جاري التحصيل', 'days' => $daysLeft, 'window_to' => $windowTo];
        }

        $daysLate = (int) $windowTo->copy()->startOfDay()->diffInDays($today->copy()->startOfDay());
        return ['key' => 'late', 'label' => 'متأخر', 'days' => $daysLate, 'window_to' => $windowTo];
    }

    /**
     * حساب وتوزيع نسب ومبالغ الشركاء في دور واحد بدقة (بدون فروق قروش)
     */
    private function resolveTurnPartners(array $rawPartners, float $monthlyInstallment, float $totalAmount, int $turn): array
    {
        $partners = array_values(array_filter($rawPartners, fn ($p) => is_array($p)));
        if (count($partners) < 2) {
            throw new \Exception("تقسيم الدور رقم {$turn} يتطلب مشتركَين على الأقل.");
        }

        $allHaveAmount = true;
        $sumAmounts    = 0.0;
        $sumPercents   = 0.0;

        foreach ($partners as $idx => $p) {
            $amt = isset($p['amount']) && $p['amount'] !== '' && $p['amount'] !== null ? (float) $p['amount'] : 0.0;
            $pct = isset($p['percent']) && $p['percent'] !== '' && $p['percent'] !== null ? (float) $p['percent'] : 0.0;

            if ($amt <= 0) {
                $allHaveAmount = false;
            }
            if ($amt <= 0 && $pct <= 0) {
                throw new \Exception("يرجى إدخال قيمة القسط أو النسبة لكل شريك في الدور رقم {$turn} (الشريك رقم " . ($idx + 1) . ").");
            }
            $sumAmounts  += max(0.0, $amt);
            $sumPercents += max(0.0, $pct);
        }

        $amtTol = max(0.05, $monthlyInstallment * 0.005);
        $useAmountMode = $allHaveAmount && $monthlyInstallment > 0 && abs($sumAmounts - $monthlyInstallment) <= $amtTol;

        if (!$useAmountMode) {
            // إذا كان المستخدم أدخل مبالغ فقط بدون نسب، نحسب النسب من المبالغ للتحقق
            if ($sumPercents <= 0 && $monthlyInstallment > 0 && $sumAmounts > 0) {
                $sumPercents = ($sumAmounts / $monthlyInstallment) * 100.0;
            }
            if (abs($sumPercents - 100.0) > 0.5) {
                $shownAmt = number_format($sumAmounts, 2);
                $shownInst = number_format($monthlyInstallment, 2);
                $shownPct = rtrim(rtrim(number_format($sumPercents, 2, '.', ''), '0'), '.');
                throw new \Exception("مجموع حصص الشركاء في الدور رقم {$turn} لازم يساوي 100% ({$shownInst} ج) — المجموع الحالي: {$shownAmt} ج ({$shownPct}%).");
            }
        }

        $count        = count($partners);
        $resolved     = [];
        $accFraction  = 0.0;
        $accMonthly   = 0.0;
        $accPayout    = 0.0;

        foreach ($partners as $i => $p) {
            $isLast    = ($i === $count - 1);
            $isCompany = !empty($p['is_company']);
            $rawName   = trim((string) ($p['name'] ?? ''));
            $label     = $rawName !== '' ? $rawName : ($isCompany ? self::COMPANY_NAME : "عضو {$turn} (" . ($i + 1) . ")");
            $phone     = !empty($p['phone']) ? trim((string) $p['phone']) : null;

            $amt = isset($p['amount']) && $p['amount'] !== '' && $p['amount'] !== null ? (float) $p['amount'] : 0.0;
            $pct = isset($p['percent']) && $p['percent'] !== '' && $p['percent'] !== null ? (float) $p['percent'] : 0.0;

            if ($isLast) {
                $fraction      = round(max(0.000001, 1.0 - $accFraction), 6);
                $monthlyAmount = round(max(0.01, $monthlyInstallment - $accMonthly), 2);
                $payoutAmount  = round(max(0.01, $totalAmount - $accPayout), 2);
            } else {
                if ($useAmountMode) {
                    $fraction      = round($amt / $monthlyInstallment, 6);
                    $monthlyAmount = round($amt, 2);
                } else {
                    $fraction      = round(($pct / $sumPercents), 6);
                    $monthlyAmount = round($monthlyInstallment * $fraction, 2);
                }
                $payoutAmount = round($totalAmount * $fraction, 2);

                $accFraction += $fraction;
                $accMonthly  += $monthlyAmount;
                $accPayout   += $payoutAmount;
            }

            $resolved[] = [
                'part'           => $i + 1,
                'name'           => $label,
                'phone'          => $phone,
                'is_company'     => $isCompany,
                'share_fraction' => $fraction,
                'monthly_amount' => $monthlyAmount,
                'payout_amount'  => $payoutAmount,
            ];
        }

        return $resolved;
    }

    /**
     * حفظ جمعية جديدة بأعضائها وأدوارهم وتوليد جدول التحصيل الشهري
     * يدعم اشتراك أكثر من شخص في نفس السهم بنسب أو مبالغ مخصصة مجموعها 100%.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'total_amount'                  => 'required|numeric|min:1',
            'duration_months'               => 'required|integer|min:1|max:60',
            'start_date'                    => 'required|date',
            'collection_start_day'          => 'nullable|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'collection_end_day'            => 'nullable|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'members'                       => 'required|array|min:1',
            'members.*.name'                => 'nullable|string|max:150',
            'members.*.phone'               => 'nullable|string|max:50',
            'members.*.turn'                => 'nullable|integer|min:1',
            'members.*.is_company'          => 'nullable',
            'members.*.is_split'            => 'nullable',
            'members.*.name2'               => 'nullable|string|max:150',
            'members.*.phone2'              => 'nullable|string|max:50',
            'members.*.is_company2'         => 'nullable',
            'members.*.partners'            => 'nullable|array',
            'members.*.partners.*.name'     => 'nullable|string|max:150',
            'members.*.partners.*.phone'    => 'nullable|string|max:50',
            'members.*.partners.*.amount'   => 'nullable|numeric|min:0',
            'members.*.partners.*.percent'  => 'nullable|numeric|min:0|max:100',
            'members.*.partners.*.is_company' => 'nullable',
        ], [
            'duration_months.min'    => 'عدد الشهور / الأدوار يجب أن يكون 1 على الأقل.',
            'members.min'            => 'يجب تسجيل دور واحد على الأقل في الجمعية.',
            'collection_end_day.max' => 'آخر يوم للتحصيل لا يمكن أن يتعدى يوم ' . self::MAX_COLLECTION_DAY . ' من الشهر.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $totalAmount = (float) $request->total_amount;
                $duration    = (int) $request->duration_months;
                $members     = array_values($request->members);
                $monthlyInstallment = round($totalAmount / $duration, 2);
                $startDate   = Carbon::parse($request->start_date)->startOfMonth();
                $startDay    = (int) ($request->collection_start_day ?: 1);
                $endDay      = (int) ($request->collection_end_day ?: self::MAX_COLLECTION_DAY);

                if ($endDay < $startDay) {
                    throw new \Exception('آخر يوم للتحصيل لازم يكون بعد أو يساوي يوم بداية التحصيل.');
                }
                if (count($members) !== $duration) {
                    throw new \Exception("عدد الأدوار (" . count($members) . ") لازم يساوي عدد شهور الجمعية ({$duration}) — كل دور يمثل شهر قبض.");
                }

                // تجهيز وحساب الشركاء لكل دور والتحقق من المجاميع قبل الإنشاء
                $preparedTurns = [];
                $companyCount = 0;
                $totalParticipants = 0;

                foreach ($members as $idx => $m) {
                    $turn    = $idx + 1;
                    $isSplit = !empty($m['is_split']);
                    $rawPartners = isset($m['partners']) && is_array($m['partners']) ? array_values($m['partners']) : [];

                    if ($isSplit && count($rawPartners) >= 2) {
                        $resolved = $this->resolveTurnPartners($rawPartners, $monthlyInstallment, $totalAmount, $turn);
                        foreach ($resolved as $rp) {
                            if ($rp['is_company']) $companyCount++;
                            $totalParticipants++;
                        }
                        $preparedTurns[] = ['turn' => $turn, 'parts' => $resolved];
                    } elseif ($isSplit) {
                        // توافق عكسي في حال إرسال النموذج القديم (50% / 50%)
                        $legacyPartners = [
                            [
                                'name'       => $m['name'] ?? '',
                                'phone'      => $m['phone'] ?? null,
                                'is_company' => !empty($m['is_company']),
                                'percent'    => 50,
                            ],
                            [
                                'name'       => $m['name2'] ?? '',
                                'phone'      => $m['phone2'] ?? null,
                                'is_company' => !empty($m['is_company2']),
                                'percent'    => 50,
                            ],
                        ];
                        $resolved = $this->resolveTurnPartners($legacyPartners, $monthlyInstallment, $totalAmount, $turn);
                        foreach ($resolved as $rp) {
                            if ($rp['is_company']) $companyCount++;
                            $totalParticipants++;
                        }
                        $preparedTurns[] = ['turn' => $turn, 'parts' => $resolved];
                    } else {
                        $isCompany = !empty($m['is_company']);
                        $name      = trim((string) ($m['name'] ?? ''));
                        if ($name === '') {
                            $name = $isCompany ? self::COMPANY_NAME : "عضو {$turn}";
                        }
                        if ($isCompany) $companyCount++;
                        $totalParticipants++;

                        $preparedTurns[] = [
                            'turn'  => $turn,
                            'parts' => [[
                                'part'           => 1,
                                'name'           => $name,
                                'phone'          => !empty($m['phone']) ? trim((string) $m['phone']) : null,
                                'is_company'     => $isCompany,
                                'share_fraction' => 1.000000,
                                'monthly_amount' => $monthlyInstallment,
                                'payout_amount'  => $totalAmount,
                            ]],
                        ];
                    }
                }

                $jamia = Jamia::create([
                    'name'                 => $request->name,
                    'organizer_name'       => $request->organizer_name ?: self::COMPANY_NAME,
                    'organizer_phone'      => $request->organizer_phone,
                    'total_amount'         => $totalAmount,
                    'duration_months'      => $duration,
                    'monthly_installment'  => $monthlyInstallment,
                    'company_shares_count' => $companyCount,
                    'start_date'           => $startDate->toDateString(),
                    'due_day'              => $endDay,
                    'collection_start_day' => $startDay,
                    'collection_end_day'   => $endDay,
                    'notes'                => $request->notes,
                    'status'               => 'active',
                ]);

                foreach ($preparedTurns as $pt) {
                    $turn        = $pt['turn'];
                    $payoutMonth = $startDate->copy()->addMonths($turn - 1)->toDateString();

                    foreach ($pt['parts'] as $p) {
                        $share = JamiaShare::create([
                            'jamia_id'       => $jamia->id,
                            'share_number'   => $turn,
                            'share_fraction' => $p['share_fraction'],
                            'split_part'     => $p['part'],
                            'share_label'    => $p['name'],
                            'member_phone'   => $p['phone'],
                            'is_company'     => $p['is_company'],
                            'payout_month'   => $payoutMonth,
                            'payout_amount'  => $p['payout_amount'],
                            'is_collected'   => false,
                        ]);

                        for ($i = 0; $i < $duration; $i++) {
                            JamiaInstallment::create([
                                'jamia_id'    => $jamia->id,
                                'share_id'    => $share->id,
                                'month_date'  => $startDate->copy()->addMonths($i)->toDateString(),
                                'month_index' => $i + 1,
                                'amount'      => $p['monthly_amount'],
                                'is_paid'     => false,
                                'is_waived'   => false,
                            ]);
                        }
                    }
                }

                $durationLabel = $duration === 1 ? 'شهر واحد' : "{$duration} شهور";
                $this->logActivity('create', 'jamia', "تم إنشاء جمعية جديدة: {$jamia->name} — قبض {$totalAmount} ج على {$durationLabel} ({$totalParticipants} مشترك، منهم {$companyCount} للشركة).");
            });

            return back()->with('success', 'تمت إضافة الجمعية وتسجيل الأعضاء وأدوار القبض وجدول التحصيل بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حفظ الجمعية: ' . $e->getMessage());
        }
    }

    /**
     * تعديل بيانات عضو (الاسم / الهاتف / الدور / سهم شركة)
     * تغيير الدور بيبدل مع العضو اللي واخد الدور ده.
     */
    public function updateMember(Request $request)
    {
        $request->validate([
            'share_id'   => 'required|integer',
            'name'       => 'nullable|string|max:150',
            'phone'      => 'nullable|string|max:50',
            'turn'       => 'required|integer|min:1',
            'is_company' => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');
                $jamia = Jamia::findOrFail($share->jamia_id);

                $newTurn   = (int) $request->turn;
                $isCompany = (bool) $request->boolean('is_company');
                if ($newTurn > $jamia->duration_months) {
                    throw new \Exception("الدور لازم يكون من 1 لغاية {$jamia->duration_months}.");
                }

                if ($isCompany !== (bool) $share->is_company) {
                    if ($share->is_transferred) {
                        throw new \Exception('هذا السهم متنازل عنه لعضو بديل؛ لإعادته للشركة استخدم خيار "فك التنازل" أولاً.');
                    }
                    $hasCollected = JamiaInstallment::where('share_id', $share->id)->where('is_paid', true)->exists();
                    if ($hasCollected || $share->is_collected || (float) ($share->collected_amount ?? 0) > 0 || $share->is_claim_recovered) {
                        throw new \Exception('لا يمكن تغيير نوع السهم (عضو/شركة) بعد وجود تحصيلات أو صرف قبض أو استرداد عليه. الغِ العمليات أولاً.');
                    }
                }

                // تبديل الدور مع العضو/الأعضاء في الدور الآخر
                if ($newTurn !== (int) $share->share_number) {
                    $oldTurn      = (int) $share->share_number;
                    $currentGroup = JamiaShare::where('jamia_id', $share->jamia_id)
                        ->where('share_number', $oldTurn)
                        ->lockForUpdate()
                        ->get();
                    $targetGroup  = JamiaShare::where('jamia_id', $share->jamia_id)
                        ->where('share_number', $newTurn)
                        ->lockForUpdate()
                        ->get();

                    $hasPayoutMove = fn ($grp) => $grp->contains(fn ($s) => $s->is_collected || (float) ($s->collected_amount ?? 0) > 0);
                    if ($hasPayoutMove($currentGroup) || $hasPayoutMove($targetGroup)) {
                        throw new \Exception('لا يمكن تبديل دور تم صرف القبض (كلياً أو جزئياً) فيه بالفعل.');
                    }

                    $oldMonth = Carbon::parse($jamia->start_date)->startOfMonth()->addMonths($oldTurn - 1)->toDateString();
                    $newMonth = Carbon::parse($jamia->start_date)->startOfMonth()->addMonths($newTurn - 1)->toDateString();

                    foreach ($targetGroup as $other) {
                        $other->update(['share_number' => $oldTurn, 'payout_month' => $oldMonth]);
                    }
                    foreach ($currentGroup as $cur) {
                        $cur->update(['share_number' => $newTurn, 'payout_month' => $newMonth]);
                    }
                    $share->share_number = $newTurn;
                    $share->payout_month = $newMonth;
                }

                $name = trim((string) $request->name);
                $share->share_label  = $name !== '' ? $name : ($isCompany ? self::COMPANY_NAME : "عضو {$share->share_number}");
                $share->member_phone = $request->phone ? trim($request->phone) : null;
                $share->is_company   = $isCompany;
                $share->save();

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                $this->logActivity('update', 'jamia', "تعديل بيانات عضو [{$share->share_label}] (دور {$share->share_number}) في جمعية {$jamia->name}.");
            });

            return back()->with('success', 'تم تعديل بيانات العضو بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * تقسيم سهم (دور) بين عدة شركاء بنسب أو مبالغ مخصصة مجموعها 100%
     * (يدعم التقسيم لأول مرة أو تعديل/إضافة شركاء لدور مقسّم قبل بدء التحصيل)
     */
    public function splitShare(Request $request)
    {
        $request->validate([
            'share_id'              => 'required|integer|exists:jamia_shares,id',
            'partners'              => 'required|array|min:2',
            'partners.*.name'       => 'nullable|string|max:150',
            'partners.*.phone'      => 'nullable|string|max:50',
            'partners.*.amount'     => 'nullable|numeric|min:0',
            'partners.*.percent'    => 'nullable|numeric|min:0|max:100',
            'partners.*.is_company' => 'nullable',
        ], [
            'partners.required' => 'يرجى إضافة مشتركَين على الأقل لتقسيم السهم.',
            'partners.min'      => 'تقسيم السهم يتطلب مشتركَين على الأقل.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');

                $siblings = JamiaShare::where('jamia_id', $share->jamia_id)
                    ->where('share_number', $share->share_number)
                    ->orderBy('split_part')
                    ->lockForUpdate()
                    ->get();

                $siblingIds = $siblings->pluck('id')->all();
                $hasPaid    = JamiaInstallment::whereIn('share_id', $siblingIds)->where('is_paid', true)->exists();
                $hasMoves   = $siblings->contains(fn ($s) => $s->is_collected || (float) ($s->collected_amount ?? 0) > 0)
                    || $siblings->where('is_transferred', true)->isNotEmpty()
                    || $siblings->where('is_claim_recovered', true)->isNotEmpty();

                if ($hasPaid || $hasMoves) {
                    throw new \Exception('لا يمكن تقسيم أو تعديل نسب دور تم تحصيل أقساط عليه أو صرف قبضه أو التنازل عنه. الغِ العمليات أولاً.');
                }

                $jamia              = Jamia::findOrFail($share->jamia_id);
                $monthlyInstallment = (float) $jamia->monthly_installment;
                $totalAmount        = (float) $jamia->total_amount;
                $turn               = (int) $share->share_number;
                $payoutMonth        = Carbon::parse($share->payout_month)->toDateString();
                $startDate          = Carbon::parse($jamia->start_date)->startOfMonth();
                $duration           = (int) $jamia->duration_months;

                $resolved = $this->resolveTurnPartners($request->partners, $monthlyInstallment, $totalAmount, $turn);

                // الإبقاء على أول صف وتحديثه كالشريك الأول، وحذف أي شركاء قدامى إضافيين ثم إنشاء الباقي
                $firstShare = $siblings->first();
                foreach ($siblings->where('id', '!=', $firstShare->id) as $oldSibling) {
                    JamiaInstallment::where('share_id', $oldSibling->id)->delete();
                    $oldSibling->delete();
                }

                $logParts = [];
                foreach ($resolved as $idx => $p) {
                    $pctText = JamiaShare::formatPercent($p['share_fraction']);
                    $logParts[] = "[{$p['name']}: {$pctText} = " . number_format($p['monthly_amount'], 2) . ' ج]';

                    if ($idx === 0) {
                        $firstShare->update([
                            'share_fraction' => $p['share_fraction'],
                            'split_part'     => 1,
                            'share_label'    => $p['name'],
                            'member_phone'   => $p['phone'],
                            'is_company'     => $p['is_company'],
                            'payout_amount'  => $p['payout_amount'],
                        ]);
                        JamiaInstallment::where('share_id', $firstShare->id)->update([
                            'amount' => $p['monthly_amount'],
                        ]);
                    } else {
                        $partnerShare = JamiaShare::create([
                            'jamia_id'       => $jamia->id,
                            'share_number'   => $turn,
                            'share_fraction' => $p['share_fraction'],
                            'split_part'     => $p['part'],
                            'share_label'    => $p['name'],
                            'member_phone'   => $p['phone'],
                            'is_company'     => $p['is_company'],
                            'payout_month'   => $payoutMonth,
                            'payout_amount'  => $p['payout_amount'],
                            'is_collected'   => false,
                        ]);

                        for ($i = 0; $i < $duration; $i++) {
                            JamiaInstallment::create([
                                'jamia_id'    => $jamia->id,
                                'share_id'    => $partnerShare->id,
                                'month_date'  => $startDate->copy()->addMonths($i)->toDateString(),
                                'month_index' => $i + 1,
                                'amount'      => $p['monthly_amount'],
                                'is_paid'     => false,
                                'is_waived'   => false,
                            ]);
                        }
                    }
                }

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                $this->logActivity('split_share', 'jamia', "تقسيم دور {$turn} في جمعية {$jamia->name} بين " . count($resolved) . ' شركاء: ' . implode('، ', $logParts));
            });

            return back()->with('success', 'تم حفظ تقسيم السهم ونسب الشركاء بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء تقسيم السهم ودمج جميع الشركاء في سهم كامل لمشترك واحد
     */
    public function unsplitShare(Request $request)
    {
        $request->validate([
            'share_id' => 'required|integer|exists:jamia_shares,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');

                $siblings = JamiaShare::where('jamia_id', $share->jamia_id)
                    ->where('share_number', $share->share_number)
                    ->orderBy('split_part')
                    ->lockForUpdate()
                    ->get();

                if ($siblings->count() < 2) {
                    throw new \Exception('هذا السهم غير مقسّم حالياً.');
                }

                $siblingIds = $siblings->pluck('id')->all();
                $hasPaid = JamiaInstallment::whereIn('share_id', $siblingIds)->where('is_paid', true)->exists();
                $hasMoves = $siblings->contains(fn ($s) => $s->is_collected || (float) ($s->collected_amount ?? 0) > 0)
                    || $siblings->where('is_transferred', true)->isNotEmpty()
                    || $siblings->where('is_claim_recovered', true)->isNotEmpty();

                if ($hasPaid || $hasMoves) {
                    throw new \Exception('لا يمكن دمج السهم المقسّم بعد وجود تحصيلات أو صرف قبض أو تنازل على أي من الشركاء. الغِ العمليات أولاً.');
                }

                $jamia = Jamia::findOrFail($share->jamia_id);

                // الإبقاء على الشريك الأول وحذف جميع الشركاء الآخرين في نفس الدور
                $keepShare = $siblings->first();
                foreach ($siblings->where('id', '!=', $keepShare->id) as $removeShare) {
                    JamiaInstallment::where('share_id', $removeShare->id)->delete();
                    $removeShare->delete();
                }

                $keepShare->update([
                    'share_fraction' => 1.000000,
                    'split_part'     => 1,
                    'payout_amount'  => (float) $jamia->total_amount,
                ]);

                JamiaInstallment::where('share_id', $keepShare->id)->update([
                    'amount' => (float) $jamia->monthly_installment,
                ]);

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                $this->logActivity('unsplit_share', 'jamia', "إلغاء تقسيم دور {$keepShare->share_number} في جمعية {$jamia->name} وإرجاعه سهماً كاملاً باسم [{$keepShare->share_label}].");
            });

            return back()->with('success', 'تم دمج السهم وإرجاعه سهماً كاملاً بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * تعديل بيانات الجمعية الأساسية وفترة التحصيل (1 إلى 10 بحد أقصى) وحالتها
     */
    public function updateJamia(Request $request)
    {
        $request->validate([
            'jamia_id'             => 'required|integer',
            'name'                 => 'required|string|max:255',
            'collection_start_day' => 'required|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'collection_end_day'   => 'required|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'status'               => 'required|in:active,completed,cancelled',
            'notes'                => 'nullable|string',
        ], [
            'collection_end_day.max' => 'آخر يوم للتحصيل لا يمكن أن يتعدى يوم ' . self::MAX_COLLECTION_DAY . ' من الشهر.',
        ]);

        try {
            $startDay = (int) $request->collection_start_day;
            $endDay   = (int) $request->collection_end_day;
            if ($endDay < $startDay) {
                throw new \Exception('آخر يوم للتحصيل لازم يكون بعد أو يساوي يوم بداية التحصيل.');
            }

            $jamia = Jamia::findOrFail($request->jamia_id);
            $jamia->update([
                'name'                 => trim($request->name),
                'collection_start_day' => $startDay,
                'collection_end_day'   => $endDay,
                'due_day'              => $endDay,
                'status'               => $request->status,
                'notes'                => $request->notes ? trim($request->notes) : null,
            ]);

            self::syncJamiaCompanyDebt((int) $jamia->id);

            $this->logActivity('update', 'jamia', "تعديل بيانات جمعية: {$jamia->name} (التحصيل من يوم {$startDay} إلى {$endDay}).");
            return back()->with('success', 'تم تحديث بيانات الجمعية بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * تحصيل قسط/أقساط من الأعضاء وإيداعها في محفظة/خزنة (يدعم التحصيل الجماعي)
     */
    public function collectInstallments(Request $request)
    {
        $request->validate([
            'installment_ids'   => 'required|array|min:1',
            'installment_ids.*' => 'integer|min:1',
            'account_id'        => 'required|integer|exists:accounts,id',
            'paid_at'           => 'nullable|date',
            'notes'             => 'nullable|string|max:500',
        ], [
            'installment_ids.required' => 'اختر قسطاً واحداً على الأقل للتحصيل.',
            'account_id.required'      => 'اختيار الخزنة أو المحفظة إجباري.',
            'account_id.exists'        => 'المحفظة / الخزنة المختارة غير موجودة.',
        ]);

        try {
            $count       = 0;
            $total       = 0.0;
            $accountName = '';

            DB::transaction(function () use ($request, &$count, &$total, &$accountName) {
                $account     = $this->lockActiveAccount((int) $request->account_id);
                $accountName = $account->account_name;

                $uniqueIds = array_values(array_unique(array_map('intval', $request->installment_ids)));
                $instMap   = JamiaInstallment::whereIn('id', $uniqueIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $shareIds = $instMap->pluck('share_id')->unique()->values()->all();
                $jamiaIds = $instMap->pluck('jamia_id')->unique()->values()->all();

                $sharesMap = !empty($shareIds)
                    ? DB::table('jamia_shares')->whereIn('id', $shareIds)->get()->keyBy('id')
                    : collect();
                $jamiasMap = !empty($jamiaIds)
                    ? DB::table('jamias')->whereIn('id', $jamiaIds)->get()->keyBy('id')
                    : collect();

                $paidAt          = $request->paid_at ? Carbon::parse($request->paid_at)->setTimeFrom(now()) : now();
                $now             = now();
                $runningBalance  = (float) $account->balance;
                $netBalanceDelta = 0.0;
                $cleanNotes      = $request->notes ? trim($request->notes) : null;

                foreach ($uniqueIds as $instId) {
                    $inst = $instMap->get($instId);
                    if (!$inst) throw new \Exception('القسط غير موجود.');
                    if ($inst->is_paid) continue; // متحصل بالفعل
                    if ($inst->is_waived) continue;

                    $share = $sharesMap->get($inst->share_id);
                    if (!$share) continue;

                    $jamia    = $jamiasMap->get($inst->jamia_id);
                    $monthStr = Carbon::parse($inst->month_date)->format('Y/m');
                    $amount   = (float) $inst->amount;

                    if (!empty($share->is_company)) {
                        // سهم الشركة: الشركة بتدفع حصتها في الجمعية (تخرج من الخزنة) عشان القبض يكمل
                        if ($runningBalance < $amount) {
                            throw new \Exception("رصيد الخزنة ({$account->account_name}) غير كافٍ لسداد حصة الشركة ({$inst->amount} ج). الرصيد الحالي: " . number_format($runningBalance, 2) . ' ج.');
                        }
                        $noteText = "سداد حصة الشركة ({$share->share_label}) في جمعية: {$jamia->name} - شهر ({$monthStr}) (دور {$share->share_number})";
                        if ($cleanNotes) {
                            $noteText .= ' | ' . $cleanNotes;
                        }

                        $runningBalance  -= $amount;
                        $netBalanceDelta -= $amount;

                        $txId = DB::table('financial_transactions')->insertGetId([
                            'type'            => 'expense',
                            'subtype'         => 'jamia_company_installment',
                            'amount'          => $inst->amount,
                            'from_account_id' => $account->id,
                            'to_account_id'   => null,
                            'ref_type'        => 'jamia_company_installment',
                            'ref_id'          => $inst->id,
                            'notes'           => $noteText,
                            'status'          => 'active',
                            'created_at'      => $paidAt,
                            'updated_at'      => $now,
                        ]);
                    } else {
                        // سهم عضو: تحصيل قسط العضو وإيداعه في الخزنة
                        $noteText = "تحصيل قسط شهر ({$monthStr}) - جمعية: {$jamia->name} - العضو: {$share->share_label} (دور {$share->share_number})";
                        if ($cleanNotes) {
                            $noteText .= ' | ' . $cleanNotes;
                        }

                        $runningBalance  += $amount;
                        $netBalanceDelta += $amount;

                        $txId = DB::table('financial_transactions')->insertGetId([
                            'type'            => 'income',
                            'subtype'         => 'jamia_collection',
                            'amount'          => $inst->amount,
                            'from_account_id' => null,
                            'to_account_id'   => $account->id,
                            'ref_type'        => 'jamia_collection',
                            'ref_id'          => $inst->id,
                            'notes'           => $noteText,
                            'status'          => 'active',
                            'created_at'      => $paidAt,
                            'updated_at'      => $now,
                        ]);
                    }

                    DB::table('jamia_installments')->where('id', $inst->id)->update([
                        'is_paid'         => true,
                        'paid_at'         => $paidAt,
                        'paid_account_id' => $account->id,
                        'tx_id'           => $txId,
                        'notes'           => $cleanNotes ?? $inst->notes,
                        'updated_at'      => $now,
                    ]);

                    $total += $amount;
                    $count++;
                }

                if ($count === 0) {
                    throw new \Exception('لا توجد أقساط صالحة للتحصيل (ممكن تكون متحصلة بالفعل).');
                }

                // تحديث رصيد الخزنة مرة واحدة بدل استعلام لكل قسط
                $netBalanceDelta = round($netBalanceDelta, 2);
                if ($netBalanceDelta > 0) {
                    DB::table('accounts')->where('id', $account->id)->increment('balance', $netBalanceDelta);
                } elseif ($netBalanceDelta < 0) {
                    DB::table('accounts')->where('id', $account->id)->decrement('balance', abs($netBalanceDelta));
                }
            });

            // تسجيل النشاط خارج المعاملة المالية حتى لا يحجز قفل الجداول
            $this->logActivity('payment', 'jamia', "تحصيل {$count} قسط جمعية بإجمالي " . number_format($total, 2) . " ج وإيداعها في {$accountName}.");

            return back()->with('success', "تم تحصيل {$count} قسط بنجاح وإيداع المبلغ في الخزنة وقيده في سجل العمليات.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء تحصيل قسط (خصم المبلغ من الخزنة وإلغاء القيد)
     */
    public function cancelInstallmentPayment(Request $request)
    {
        $request->validate(['installment_id' => 'required|integer']);

        try {
            DB::transaction(function () use ($request) {
                $inst = JamiaInstallment::where('id', $request->installment_id)->lockForUpdate()->first();
                if (!$inst || !$inst->is_paid) throw new \Exception('القسط غير محصل.');

                $tx = $inst->tx_id ? DB::table('financial_transactions')->where('id', $inst->tx_id)->first() : null;

                if ($inst->paid_account_id) {
                    // حركات قديمة (expense) كانت خصم من الخزنة => نرجعها. التحصيل (income) => نخصمه.
                    if ($tx && $tx->type === 'expense') {
                        DB::table('accounts')->where('id', $inst->paid_account_id)->increment('balance', $inst->amount);
                    } else {
                        $acc = DB::table('accounts')->where('id', $inst->paid_account_id)->lockForUpdate()->first();
                        if ($acc && $acc->balance < $inst->amount) {
                            throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من مبلغ القسط المطلوب عكسه.");
                        }
                        DB::table('accounts')->where('id', $inst->paid_account_id)->decrement('balance', $inst->amount);
                    }
                }

                if ($inst->tx_id) {
                    DB::table('financial_transactions')->where('id', $inst->tx_id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء تحصيل قسط جمعية',
                        'updated_at'    => now(),
                    ]);
                }

                $inst->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                ]);

                $this->logActivity('cancel', 'jamia', 'إلغاء تحصيل قسط جمعية بقيمة ' . number_format($inst->amount, 2) . ' ج وعكس أثره على الخزنة.');
            });

            return back()->with('success', 'تم إلغاء التحصيل بنجاح وعكس المبلغ من الخزنة.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * قفل والتحقق من أن الخزنة / المحفظة موجودة ونشطة
     */
    private function lockActiveAccount(int $accountId): object
    {
        $account = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->first();
        if (!$account || ($account->status ?? 'active') !== 'active') {
            throw new \Exception('المحفظة / الخزنة المختارة غير موجودة أو غير نشطة.');
        }
        return $account;
    }

    /**
     * تنفيذ استرداد حق الشركة من قبض السهم المتنازل عنه وإيداعه في الخزنة
     */
    private function executeClaimRecovery(JamiaShare $share, object $jamia, int $accountId, float $amount, Carbon $recoveredAt, ?string $notes = null): void
    {
        if (!$share->is_transferred) {
            throw new \Exception('هذا السهم غير متنازل عنه.');
        }
        if ($share->is_claim_recovered) {
            throw new \Exception('تم استرداد حق الشركة من هذا السهم بالفعل.');
        }
        $claimMax = (float) $share->company_claim_amount;
        if ($claimMax <= 0) {
            throw new \Exception('لا يوجد حق مالي مسجل للشركة على هذا السهم.');
        }
        if ($amount <= 0 || $amount > $claimMax || $amount > (float) $share->payout_amount) {
            throw new \Exception('المبلغ المسترد يجب أن يكون أكبر من صفر ولا يتجاوز حق الشركة المستحق (' . number_format($claimMax, 2) . ' ج).');
        }

        $account = $this->lockActiveAccount($accountId);

        DB::table('accounts')->where('id', $account->id)->increment('balance', $amount);

        $noteText = "استرداد حق " . self::COMPANY_NAME . " من جمعية: {$jamia->name} (سهم متنازل عنه إلى: {$share->share_label} - دور {$share->share_number} - حق {$share->paid_months_before_transfer} اسم)";
        if ($notes && trim($notes) !== '') {
            $noteText .= ' | ' . trim($notes);
        }

        $txId = DB::table('financial_transactions')->insertGetId([
            'type'            => 'income',
            'subtype'         => 'jamia_recovery',
            'amount'          => $amount,
            'from_account_id' => null,
            'to_account_id'   => $account->id,
            'ref_type'        => 'jamia_share_claim',
            'ref_id'          => $share->id,
            'notes'           => $noteText,
            'status'          => 'active',
            'created_at'      => $recoveredAt,
            'updated_at'      => now(),
        ]);

        $share->update([
            'is_claim_recovered'   => true,
            'recovered_at'         => $recoveredAt,
            'recovered_account_id' => $account->id,
            'recovered_tx_id'      => $txId,
            'recovered_amount'     => $amount,
        ]);

        $this->logActivity('recover', 'jamia', "استرداد حق الشركة من جمعية {$jamia->name} (عضو: {$share->share_label}) بمبلغ " . number_format($amount, 2) . " ج وإيداعه في {$account->account_name}.");
    }

    /**
     * صرف القبض لصاحب الدور (كلياً أو على دفعات جزئية بحد أقصى نصيب الدور)
     * أو تأكيد استلام الشركة لدورها (بدون زيادة رصيد الخزنة لأن أقساط الأعضاء دخلت الخزنة بالفعل وقت التحصيل)
     */
    public function payoutShare(Request $request)
    {
        $request->validate([
            'share_id'           => 'required|integer|exists:jamia_shares,id',
            'account_id'         => 'nullable|integer|exists:accounts,id',
            'amount'             => 'required|numeric|min:0.01',
            'paid_at'            => 'nullable|date',
            'notes'              => 'nullable|string|max:500',
            'also_recover_claim' => 'nullable|boolean',
            'recover_account_id' => 'nullable|integer|exists:accounts,id',
            'recover_amount'     => 'nullable|numeric|min:1',
        ], [
            'share_id.required'         => 'يجب تحديد الدور المراد تسجيله.',
            'share_id.exists'           => 'الدور المحدد غير موجود.',
            'account_id.exists'         => 'المحفظة / الخزنة المختارة غير موجودة.',
            'amount.required'           => 'يجب إدخال المبلغ.',
            'amount.min'                => 'المبلغ يجب أن يكون أكبر من صفر.',
            'recover_account_id.exists' => 'خزنة استرداد حق الشركة المختارة غير موجودة.',
        ]);

        $successMessage = '';
        $activityLogMsg = null;

        try {
            DB::transaction(function () use ($request, &$successMessage, &$activityLogMsg) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');

                $payoutAmount = round((float) $share->payout_amount, 2);
                $alreadyPaid  = round((float) ($share->collected_amount ?? 0), 2);
                $remaining    = max(0.0, round($payoutAmount - $alreadyPaid, 2));

                if ($remaining <= 0.005) {
                    if (!$share->is_collected) {
                        $share->update(['is_collected' => true]);
                    }
                    throw new \Exception('تم صرف نصيب هذا الدور بالكامل بالفعل (' . number_format($payoutAmount, 2) . ' ج).');
                }

                $jamia  = DB::table('jamias')->where('id', $share->jamia_id)->first();
                $paidAt = $request->paid_at ? Carbon::parse($request->paid_at)->setTimeFrom(now()) : now();
                $amount = round((float) $request->amount, 2);

                if ($amount <= 0 || $amount > ($remaining + 0.005)) {
                    if ($alreadyPaid > 0) {
                        throw new \Exception(
                            'المبلغ المدخل (' . number_format($amount, 2) . ' ج) يتجاوز المتبقي من نصيب صاحب الدور ('
                            . number_format($remaining, 2) . ' ج) — النصيب الكلي '
                            . number_format($payoutAmount, 2) . ' ج واتصرف منه '
                            . number_format($alreadyPaid, 2) . ' ج بالفعل.'
                        );
                    }
                    throw new \Exception(
                        'لا يمكن صرف مبلغ أكبر من نصيب صاحب الدور — المبلغ المدخل ('
                        . number_format($amount, 2) . ' ج) يتجاوز قيمة القبض ('
                        . number_format($payoutAmount, 2) . ' ج).'
                    );
                }

                $newCollected = min($payoutAmount, round($alreadyPaid + $amount, 2));
                $newRemaining = max(0.0, round($payoutAmount - $newCollected, 2));
                $isFullyPaid  = $newRemaining <= 0.005;

                if ($share->is_company) {
                    if ($share->is_transferred) {
                        throw new \Exception('تم التنازل عن هذا السهم لعضو بديل.');
                    }

                    // تأكيد استلام الشركة لدورها (كلياً أو جزئياً) بدون إضافة رصيد مكرر للخزنة
                    $share->update([
                        'is_collected'         => $isFullyPaid,
                        'collected_at'         => $paidAt,
                        'collected_account_id' => null,
                        'collected_tx_id'      => null,
                        'collected_amount'     => $newCollected,
                        'notes'                => $request->notes ? trim($request->notes) : $share->notes,
                    ]);

                    self::syncJamiaCompanyDebt((int) $share->jamia_id);

                    $activityLogMsg = "تأكيد استلام الشركة ({$share->share_label}) قبض جمعية {$jamia->name} (دور {$share->share_number}) بمبلغ " . number_format($amount, 2) . ' ج' . ($isFullyPaid ? ' (مكتمل)' : ' (جزئي — المتبقي ' . number_format($newRemaining, 2) . ' ج)') . '.';
                    $successMessage = $isFullyPaid
                        ? 'تم تأكيد استلام الشركة للدور بالكامل وتحديث موقف الجمعيات بنجاح.'
                        : 'تم تسجيل استلام جزئي للشركة بمبلغ ' . number_format($amount, 2) . ' ج — المتبقي للدور: ' . number_format($newRemaining, 2) . ' ج.';
                    return;
                }

                if (!$request->filled('account_id')) {
                    throw new \Exception('اختيار المحفظة / الخزنة للصرف إجباري.');
                }

                $account = $this->lockActiveAccount((int) $request->account_id);

                if ((float) $account->balance < $amount) {
                    throw new \Exception("رصيد الخزنة ({$account->account_name}) غير كافٍ. الرصيد الحالي: " . number_format($account->balance, 2) . ' ج.');
                }

                DB::table('accounts')->where('id', $account->id)->decrement('balance', $amount);

                $trancheSuffix = (!$isFullyPaid || $alreadyPaid > 0)
                    ? ' [دفعة: إجمالي المصروف ' . number_format($newCollected, 2) . ' من ' . number_format($payoutAmount, 2) . ' ج' . ($newRemaining > 0 ? ' — باقي ' . number_format($newRemaining, 2) . ' ج' : ' — مكتمل') . ']'
                    : '';

                $noteText = "صرف قبض جمعية: {$jamia->name} - للعضو: {$share->share_label} (دور {$share->share_number}){$trancheSuffix}";
                if ($request->notes) {
                    $noteText .= ' | ' . trim($request->notes);
                }

                $txId = DB::table('financial_transactions')->insertGetId([
                    'type'            => 'expense',
                    'subtype'         => 'jamia_payout_out',
                    'amount'          => $amount,
                    'from_account_id' => $account->id,
                    'to_account_id'   => null,
                    'ref_type'        => 'jamia_payout_out',
                    'ref_id'          => $share->id,
                    'notes'           => $noteText,
                    'status'          => 'active',
                    'created_at'      => $paidAt,
                    'updated_at'      => now(),
                ]);

                $share->update([
                    'is_collected'         => $isFullyPaid,
                    'collected_at'         => $paidAt,
                    'collected_account_id' => $account->id,
                    'collected_tx_id'      => $txId,
                    'collected_amount'     => $newCollected,
                    'notes'                => $request->notes ? trim($request->notes) : $share->notes,
                ]);

                self::syncJamiaCompanyDebt((int) $share->jamia_id);

                $activityLogMsg = "صرف قبض جمعية {$jamia->name} للعضو {$share->share_label} بمبلغ " . number_format($amount, 2) . " ج من {$account->account_name}" . ($isFullyPaid ? ' (اكتمل الدور)' : ' (متبقي ' . number_format($newRemaining, 2) . ' ج)') . '.';
                $successMessage = $isFullyPaid
                    ? 'تم صرف ' . number_format($amount, 2) . ' ج واكتمال صرف نصيب صاحب الدور بالكامل (' . number_format($payoutAmount, 2) . ' ج).'
                    : 'تم صرف دفعة ' . number_format($amount, 2) . ' ج بنجاح — إجمالي المصروف: ' . number_format($newCollected, 2) . ' ج والمتبقي لصاحب الدور: ' . number_format($newRemaining, 2) . ' ج.';

                if ($share->is_transferred && !$share->is_claim_recovered && $request->boolean('also_recover_claim')) {
                    if (!$request->filled('recover_account_id')) {
                        throw new \Exception('اختيار خزنة إيداع حق الشركة إجباري عند تفعيل الاسترداد مع الصرف.');
                    }
                    $recoverAmount = round((float) ($request->recover_amount ?: $share->company_claim_amount), 2);
                    $this->executeClaimRecovery($share->fresh(), $jamia, (int) $request->recover_account_id, $recoverAmount, $paidAt, $request->notes);
                }
            });

            if ($activityLogMsg) {
                $this->logActivity('payment', 'jamia', $activityLogMsg);
            }

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * التنازل عن سهم الشركة لعضو بديل يدخل مكانها ويتحمل الأقساط القادمة، وتثبيت حق الشركة عن الشهور السابقة
     */
    public function transferShare(Request $request)
    {
        $request->validate([
            'share_id'                    => 'required|integer|exists:jamia_shares,id',
            'transferred_to_name'         => 'required|string|max:150',
            'member_phone'                => 'nullable|string|max:50',
            'transferred_from_month'      => 'required|date',
            'paid_months_before_transfer' => 'required|integer|min:1',
            'transferred_notes'           => 'nullable|string|max:500',
        ], [
            'share_id.required'                    => 'يجب تحديد السهم المراد التنازل عنه.',
            'share_id.exists'                      => 'السهم المحدد غير موجود.',
            'transferred_to_name.required'         => 'يجب إدخال اسم العضو الجديد الذي سيدخل مكان الشركة.',
            'transferred_from_month.required'      => 'يجب تحديد الشهر الذي يبدأ العضو الجديد السداد منه.',
            'paid_months_before_transfer.required' => 'يجب تحديد عدد الأسماء (الشهور) التي دفعتها الشركة قبل التنازل.',
            'paid_months_before_transfer.min'      => 'عدد الأسماء المدفوعة من الشركة يجب أن يكون 1 على الأقل.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');
                if (!$share->is_company) throw new \Exception('التنازل عن الدور وإثبات حق الشركة متاح فقط لأسهم الشركة.');
                if ($share->is_transferred) throw new \Exception('تم التنازل عن هذا السهم مسبقاً.');
                if ($share->is_collected || (float) ($share->collected_amount ?? 0) > 0) throw new \Exception('لا يمكن التنازل عن سهم تم صرف قبضه (كلياً أو جزئياً) بالفعل.');

                $newName = trim((string) $request->transferred_to_name);
                if ($newName === '' || $newName === self::COMPANY_NAME) {
                    throw new \Exception('اسم العضو البديل غير صالح — اكتب اسم الشخص الذي سيدخل مكان الشركة.');
                }

                $jamia      = Jamia::findOrFail($share->jamia_id);
                if ((int) $jamia->duration_months < 2) {
                    throw new \Exception('التنازل عن الدور وإثبات حق الشركة يتطلب جمعية مدتها شهرين على الأقل. يمكنك تعديل بيانات العضو مباشرة من دليل الجمعيات.');
                }
                $jamiaStart = Carbon::parse($jamia->start_date)->startOfMonth();
                $jamiaEnd   = $jamiaStart->copy()->addMonths($jamia->duration_months - 1)->startOfMonth();
                $fromMonth  = Carbon::parse($request->transferred_from_month)->startOfMonth();

                if ($fromMonth->lt($jamiaStart) || $fromMonth->gt($jamiaEnd)) {
                    throw new \Exception('الشهر المختار لدخول العضو البديل لازم يكون داخل شهور الجمعية.');
                }

                $paidMonths = (int) $request->paid_months_before_transfer;
                if ($paidMonths < 1 || $paidMonths > (int) $jamia->duration_months) {
                    throw new \Exception("عدد الأسماء اللي دفعتها الشركة لازم يكون من 1 لغاية {$jamia->duration_months}.");
                }

                $fromMonthStr   = $fromMonth->toDateString();
                $remainingCount = JamiaInstallment::where('share_id', $share->id)
                    ->whereDate('month_date', '>=', $fromMonthStr)
                    ->count();

                if ($paidMonths + $remainingCount > (int) $jamia->duration_months) {
                    throw new \Exception("عدد الأسماء المدفوعة من الشركة ({$paidMonths}) + الأقساط المتبقية على العضو الجديد ({$remainingCount}) يتجاوز مدة الجمعية ({$jamia->duration_months} شهر). اختَر شهر بداية لاحقاً أو راجع عدد الأسماء.");
                }

                $hasPaidAfterFrom = JamiaInstallment::where('share_id', $share->id)
                    ->whereDate('month_date', '>=', $fromMonthStr)
                    ->where('is_paid', true)
                    ->exists();
                if ($hasPaidAfterFrom) {
                    throw new \Exception('يوجد أقساط مدفوعة مسجلة على هذا السهم في الشهور التابعة للعضو الجديد — الغِ تحصيلها أولاً.');
                }

                $fraction    = (float) ($share->share_fraction ?? 1.0);
                $claimAmount = round($paidMonths * (float) $jamia->monthly_installment * $fraction, 2);
                if ($claimAmount > (float) $share->payout_amount) {
                    throw new \Exception('حق الشركة المحسوب يتجاوز قيمة قبض الجمعية.');
                }

                $origLabel = $share->share_label;

                // الشهور قبل دخول البديل: معفية عن التحصيل لأن الشركة شالتها ومثبتة كحق مسترد
                JamiaInstallment::where('share_id', $share->id)
                    ->whereDate('month_date', '<', $fromMonthStr)
                    ->update([
                        'is_waived'     => true,
                        'waived_reason' => "تحملتها {$origLabel} قبل دخول {$newName}",
                    ]);

                // الشهور من بداية دخول البديل: مفتوحة للتحصيل الشهري من العضو الجديد
                JamiaInstallment::where('share_id', $share->id)
                    ->whereDate('month_date', '>=', $fromMonthStr)
                    ->update([
                        'is_waived'     => false,
                        'waived_reason' => null,
                    ]);

                $share->update([
                    'share_label'                 => $newName,
                    'member_phone'                => $request->member_phone ? trim($request->member_phone) : $share->member_phone,
                    'is_company'                  => false,
                    'is_transferred'              => true,
                    'transferred_at'              => now(),
                    'transferred_from_month'      => $fromMonthStr,
                    'transferred_to_name'         => $newName,
                    'transferred_notes'           => $request->transferred_notes ? trim($request->transferred_notes) : null,
                    'paid_months_before_transfer' => $paidMonths,
                    'company_claim_amount'        => $claimAmount,
                ]);

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                self::syncJamiaCompanyDebt((int) $jamia->id);

                $this->logActivity('transfer', 'jamia', "تنازل {$origLabel} عن دور {$share->share_number} في جمعية {$jamia->name} لصالح [{$newName}] من شهر {$fromMonthStr} — حق الشركة: {$paidMonths} اسم (" . number_format($claimAmount, 2) . " ج).");
            });

            return back()->with('success', 'تم تسجيل دخول العضو البديل مكان الشركة وتثبيت حق الشركة بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * فك التنازل وإرجاع الدور لسهم الشركة (ممنوع لو اتسدد أقساط أو اتصرف قبض أو اتسترد حق الشركة)
     */
    public function undoTransfer(Request $request)
    {
        $request->validate(['share_id' => 'required|integer|exists:jamia_shares,id']);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share || !$share->is_transferred) {
                    throw new \Exception('هذا السهم غير متنازل عنه.');
                }
                if ($share->is_claim_recovered) {
                    throw new \Exception('لا يمكن فك التنازل بعد استرداد حق الشركة — الغِ الاسترداد أولاً.');
                }
                if ($share->is_collected || (float) ($share->collected_amount ?? 0) > 0) {
                    throw new \Exception('لا يمكن فك التنازل بعد صرف القبض (كلياً أو جزئياً) — الغِ الصرف أولاً.');
                }
                if (JamiaInstallment::where('share_id', $share->id)->where('is_paid', true)->exists()) {
                    throw new \Exception('لا يمكن فك التنازل لوجود أقساط محصّلة من العضو البديل — الغِ التحصيل أولاً.');
                }

                $jamia = Jamia::findOrFail($share->jamia_id);
                $oldMember = $share->share_label;

                JamiaInstallment::where('share_id', $share->id)->update([
                    'is_waived'     => false,
                    'waived_reason' => null,
                ]);

                $share->update([
                    'share_label'                 => self::COMPANY_NAME,
                    'member_phone'                => null,
                    'is_company'                  => true,
                    'is_transferred'              => false,
                    'transferred_at'              => null,
                    'transferred_from_month'      => null,
                    'transferred_to_name'         => null,
                    'transferred_notes'           => null,
                    'paid_months_before_transfer' => 0,
                    'company_claim_amount'        => 0,
                ]);

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                self::syncJamiaCompanyDebt((int) $jamia->id);

                $this->logActivity('undo_transfer', 'jamia', "فك التنازل عن دور {$share->share_number} ({$oldMember}) وإعادته لـ" . self::COMPANY_NAME . " في جمعية {$jamia->name}.");
            });

            return back()->with('success', 'تم فك التنازل وإرجاع الدور لـ' . self::COMPANY_NAME . '.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * استرداد حق الشركة من العضو البديل وإيداع الفلوس في الخزنة المختارة (إجباري)
     */
    public function recoverClaim(Request $request)
    {
        $request->validate([
            'share_id'     => 'required|integer|exists:jamia_shares,id',
            'account_id'   => 'required|integer|exists:accounts,id',
            'amount'       => 'required|numeric|min:1',
            'recovered_at' => 'nullable|date',
            'notes'        => 'nullable|string|max:500',
        ], [
            'share_id.required'   => 'يجب تحديد السهم المتنازل عنه.',
            'share_id.exists'     => 'السهم المحدد غير موجود.',
            'account_id.required' => 'اختيار المحفظة / الخزنة لإيداع حق الشركة إجباري.',
            'account_id.exists'   => 'المحفظة / الخزنة المختارة غير موجودة.',
            'amount.required'     => 'يجب إدخال المبلغ المسترد.',
            'amount.min'          => 'المبلغ المسترد يجب أن يكون أكبر من صفر.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');

                $jamia = DB::table('jamias')->where('id', $share->jamia_id)->first();
                $recoveredAt = $request->recovered_at ? Carbon::parse($request->recovered_at)->setTimeFrom(now()) : now();

                $this->executeClaimRecovery($share, $jamia, (int) $request->account_id, round((float) $request->amount, 2), $recoveredAt, $request->notes);
            });

            return back()->with('success', 'تم تحصيل حق الشركة وإيداع المبلغ في الخزنة وقيده في سجل العمليات بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء تحصيل حق الشركة من الخزنة وإرجاع الحالة لغير مسترد
     */
    public function cancelRecovery(Request $request)
    {
        $request->validate(['share_id' => 'required|integer|exists:jamia_shares,id']);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share || !$share->is_claim_recovered) {
                    throw new \Exception('لم يتم تسجيل استرداد حق الشركة لهذا السهم.');
                }

                if ($share->recovered_account_id && $share->recovered_amount > 0) {
                    $acc = DB::table('accounts')->where('id', $share->recovered_account_id)->lockForUpdate()->first();
                    if ($acc && $acc->balance < $share->recovered_amount) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب عكسه.");
                    }
                    DB::table('accounts')->where('id', $share->recovered_account_id)->decrement('balance', $share->recovered_amount);
                }

                if ($share->recovered_tx_id) {
                    DB::table('financial_transactions')->where('id', $share->recovered_tx_id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء استرداد حق الشركة من جمعية',
                        'updated_at'    => now(),
                    ]);
                }

                $share->update([
                    'is_claim_recovered'   => false,
                    'recovered_at'         => null,
                    'recovered_account_id' => null,
                    'recovered_tx_id'      => null,
                    'recovered_amount'     => 0,
                ]);

                $this->logActivity('cancel_recovery', 'jamia', "إلغاء استرداد حق الشركة عن سهم [{$share->share_label}] وعكس الأثر المالي.");
            });

            return back()->with('success', 'تم إلغاء تحصيل حق الشركة وخصم المبلغ من الخزنة.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إعادة حساب إجمالي المصروف وحالة الاكتمال لسهم جمعية بناءً على حركات financial_transactions النشطة.
     * يُستخدم عند إلغاء دفعة واحدة من الشاشة أو من سجل العمليات أو أثناء المزامنة.
     */
    public static function recalculateSharePayoutFromTransactions(int $shareId, ?int $excludingTxId = null): void
    {
        $share = DB::table('jamia_shares')->where('id', $shareId)->first();
        if (!$share) {
            return;
        }

        $txQuery = DB::table('financial_transactions')
            ->whereIn('ref_type', ['jamia_payout_out', 'jamia_share'])
            ->where('ref_id', $shareId)
            ->where('status', 'active');

        if ($excludingTxId !== null) {
            $txQuery->where('id', '!=', $excludingTxId);
        }

        $activeTxs    = $txQuery->orderBy('id')->get();
        $totalPaid    = round((float) $activeTxs->sum('amount'), 2);
        $lastTx       = $activeTxs->last();
        $payoutAmount = round((float) $share->payout_amount, 2);
        $isFull       = $totalPaid > 0 && $totalPaid >= ($payoutAmount - 0.005);

        DB::table('jamia_shares')->where('id', $shareId)->update([
            'is_collected'         => $isFull,
            'collected_at'         => $lastTx?->created_at,
            'collected_account_id' => $lastTx ? ($lastTx->from_account_id ?? $lastTx->to_account_id) : null,
            'collected_tx_id'      => $lastTx?->id,
            'collected_amount'     => min($payoutAmount, $totalPaid),
            'updated_at'           => now(),
        ]);
    }

    /**
     * إلغاء صرف القبض (دفعة واحدة محددة عبر tx_id أو كل دفعات الدور) أو إلغاء استلام الشركة وإرجاع الأثر المالي للخزنة
     */
    public function cancelPayout(Request $request)
    {
        $request->validate([
            'share_id' => 'required|integer',
            'tx_id'    => 'nullable|integer',
        ]);

        $successMessage = '';
        $activityLogMsg = null;

        try {
            DB::transaction(function () use ($request, &$successMessage, &$activityLogMsg) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');
                if (!$share->is_collected && (float) ($share->collected_amount ?? 0) <= 0) {
                    throw new \Exception('لم يتم صرف أي مبلغ لهذا الدور.');
                }

                $isCompanyShare = (bool) $share->is_company;
                $targetTxId     = $request->filled('tx_id') ? (int) $request->tx_id : null;

                // Helper لعكس أثر حركة مالية واحدة على الخزنة وتعليمها كملغاة
                $reverseTx = function (object $tx) {
                    $txAmount = round((float) $tx->amount, 2);
                    if ($tx->type === 'income') {
                        $accId = $tx->to_account_id;
                        if ($accId && $txAmount > 0) {
                            $acc = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                            if ($acc && (float) $acc->balance < $txAmount) {
                                throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب عكسه.");
                            }
                            DB::table('accounts')->where('id', $accId)->decrement('balance', $txAmount);
                        }
                    } else {
                        $accId = $tx->from_account_id;
                        if ($accId && $txAmount > 0) {
                            DB::table('accounts')->where('id', $accId)->increment('balance', $txAmount);
                        }
                    }

                    DB::table('financial_transactions')->where('id', $tx->id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء صرف/استلام قبض جمعية',
                        'updated_at'    => now(),
                    ]);
                };

                if ($targetTxId) {
                    $tx = DB::table('financial_transactions')
                        ->where('id', $targetTxId)
                        ->whereIn('ref_type', ['jamia_payout_out', 'jamia_share'])
                        ->where('ref_id', $share->id)
                        ->where('status', 'active')
                        ->lockForUpdate()
                        ->first();

                    if (!$tx) {
                        throw new \Exception('دفعة الصرف المحددة غير موجودة أو تم إلغاؤها بالفعل.');
                    }

                    $reverseTx($tx);
                    self::recalculateSharePayoutFromTransactions((int) $share->id);
                    self::syncJamiaCompanyDebt((int) $share->jamia_id);

                    $freshShare = $share->fresh();
                    $remAfter   = max(0.0, round((float) $freshShare->payout_amount - (float) $freshShare->collected_amount, 2));

                    $activityLogMsg = "إلغاء دفعة صرف بمبلغ " . number_format((float) $tx->amount, 2) . " ج عن سهم [{$share->share_label}] وإرجاعها للخزنة.";
                    $successMessage = 'تم إلغاء دفعة الصرف (' . number_format((float) $tx->amount, 2) . ' ج) وإرجاعها للخزنة — المتبقي لصاحب الدور الآن: ' . number_format($remAfter, 2) . ' ج.';
                    return;
                }

                // إلغاء كامل دفعات الصرف النشطة لهذا السهم
                $activeTxs = DB::table('financial_transactions')
                    ->whereIn('ref_type', ['jamia_payout_out', 'jamia_share'])
                    ->where('ref_id', $share->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->get();

                if ($activeTxs->isNotEmpty()) {
                    foreach ($activeTxs as $tx) {
                        $reverseTx($tx);
                    }
                } elseif ($share->collected_tx_id) {
                    // توافق عكسي لو الحركة القديمة محفوظة بـ ref_type مختلف
                    $legacyTx = DB::table('financial_transactions')->where('id', $share->collected_tx_id)->lockForUpdate()->first();
                    if ($legacyTx && ($legacyTx->status ?? 'active') === 'active') {
                        $reverseTx($legacyTx);
                    }
                }

                $share->update([
                    'is_collected'         => false,
                    'collected_at'         => null,
                    'collected_account_id' => null,
                    'collected_tx_id'      => null,
                    'collected_amount'     => 0,
                ]);

                self::syncJamiaCompanyDebt((int) $share->jamia_id);

                $activityLogMsg = "إلغاء قبض [{$share->share_label}] وعكس الأثر المالي بالكامل.";
                $successMessage = $isCompanyShare
                    ? 'تم إلغاء تأكيد استلام الشركة وتحديث موقف الجمعيات بنجاح.'
                    : 'تم إلغاء صرف القبض وعكس المبلغ وتحديث موقف الجمعيات بنجاح.';
            });

            if ($activityLogMsg) {
                $this->logActivity('cancel', 'jamia', $activityLogMsg);
            }

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * مزامنة أي حركات تم إلغاؤها من سجل العمليات لضمان مطابقة الحالة (يدعم الصرف الجزئي والكلي بدون N+1)
     */
    private function syncWithCancelledTransactions()
    {
        $now = now();

        // 1أ. تصحيح أي سهم قديم متعلّم is_collected=1 رغم أن المصروف أقل من نصيب الدور (مثلاً صرف 50 ألف من 300 ألف)
        DB::table('jamia_shares')
            ->where('is_collected', true)
            ->where('collected_amount', '>', 0)
            ->whereRaw('collected_amount < (payout_amount - 0.005)')
            ->update([
                'is_collected' => false,
                'updated_at'   => $now,
            ]);

        // 1ب. مطابقة الأسهم المرتبطة بحركات صرف مع مجموع الحركات النشطة في financial_transactions باستعلام واحد
        $txTrackedShares = DB::table('jamia_shares as js')
            ->leftJoin('financial_transactions as latest_ft', 'js.collected_tx_id', '=', 'latest_ft.id')
            ->whereNotNull('js.collected_tx_id')
            ->where(function ($q) {
                $q->where('js.is_collected', true)->orWhere('js.collected_amount', '>', 0);
            })
            ->select('js.id', 'js.jamia_id', 'js.payout_amount', 'js.collected_amount', 'js.is_collected', 'js.collected_tx_id', 'latest_ft.status as latest_status')
            ->get();

        if ($txTrackedShares->isNotEmpty()) {
            $shareIds = $txTrackedShares->pluck('id')->all();
            $activeSums = DB::table('financial_transactions')
                ->whereIn('ref_type', ['jamia_payout_out', 'jamia_share'])
                ->whereIn('ref_id', $shareIds)
                ->where('status', 'active')
                ->groupBy('ref_id')
                ->selectRaw('ref_id, SUM(amount) as active_total')
                ->pluck('active_total', 'ref_id');

            $affectedJamiaIds = [];
            foreach ($txTrackedShares as $cs) {
                $expectedTotal = round((float) ($activeSums[$cs->id] ?? 0.0), 2);
                $recordedTotal = round((float) $cs->collected_amount, 2);
                $latestMissingOrCancelled = ($cs->latest_status === null || $cs->latest_status === 'cancelled');

                if ($latestMissingOrCancelled || abs($expectedTotal - $recordedTotal) > 0.005) {
                    self::recalculateSharePayoutFromTransactions((int) $cs->id);
                    $affectedJamiaIds[] = (int) $cs->jamia_id;
                }
            }
            foreach (array_unique($affectedJamiaIds) as $jid) {
                self::syncJamiaCompanyDebt($jid);
            }
        }

        // 2. (قديم) الأسهم المسترد حقها التي أُلغيت حركتها
        $cancelledRecoveredIds = DB::table('jamia_shares as js')
            ->leftJoin('financial_transactions as ft', 'js.recovered_tx_id', '=', 'ft.id')
            ->where('js.is_claim_recovered', true)
            ->whereNotNull('js.recovered_tx_id')
            ->where(function ($q) {
                $q->whereNull('ft.id')->orWhere('ft.status', 'cancelled');
            })
            ->pluck('js.id')
            ->all();

        if (!empty($cancelledRecoveredIds)) {
            DB::table('jamia_shares')
                ->whereIn('id', $cancelledRecoveredIds)
                ->update([
                    'is_claim_recovered'   => false,
                    'recovered_at'         => null,
                    'recovered_account_id' => null,
                    'recovered_tx_id'      => null,
                    'recovered_amount'     => 0,
                    'updated_at'           => $now,
                ]);
        }

        // 3. الأقساط المحصلة التي أُلغيت حركتها
        $cancelledInstIds = DB::table('jamia_installments as ji')
            ->leftJoin('financial_transactions as ft', 'ji.tx_id', '=', 'ft.id')
            ->where('ji.is_paid', true)
            ->whereNotNull('ji.tx_id')
            ->where(function ($q) {
                $q->whereNull('ft.id')->orWhere('ft.status', 'cancelled');
            })
            ->pluck('ji.id')
            ->all();

        if (!empty($cancelledInstIds)) {
            DB::table('jamia_installments')
                ->whereIn('id', $cancelledInstIds)
                ->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                    'updated_at'      => $now,
                ]);
        }
    }

    /**
     * طباعة كشف التحصيل والصرف لشهر محدد
     */
    public function printMonth(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $monthDate  = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();

        $monthInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->leftJoin('accounts as a', 'ji.paid_account_id', '=', 'a.id')
            ->whereDate('ji.month_date', $monthStart)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->select(
                'ji.*',
                'j.name as jamia_name',
                'j.collection_start_day',
                'j.collection_end_day',
                'js.share_label',
                'js.share_number',
                'js.share_fraction',
                'js.split_part',
                'js.member_phone',
                'js.is_company',
                'a.account_name as paid_account_name'
            )
            ->orderBy('j.name')
            ->orderBy('js.share_number')
            ->orderBy('js.split_part')
            ->get()
            ->map(function ($inst) {
                $inst->status = self::installmentStatus($inst);
                return $inst;
            });

        $monthShares = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->leftJoin('accounts as ca', 'js.collected_account_id', '=', 'ca.id')
            ->leftJoin('accounts as ra', 'js.recovered_account_id', '=', 'ra.id')
            ->whereDate('js.payout_month', $monthStart)
            ->where('j.status', 'active')
            ->select(
                'js.*',
                'j.name as jamia_name',
                'ca.account_name as collected_account_name',
                'ra.account_name as recovered_account_name'
            )
            ->orderBy('j.name')
            ->orderBy('js.share_number')
            ->orderBy('js.split_part')
            ->get();

        $totalDue       = (float) $monthInstallments->sum('amount');
        $totalCollected = (float) $monthInstallments->where('is_paid', true)->sum('amount');
        $memberPayouts  = $monthShares->where('is_company', false);
        $totalPayout    = (float) $memberPayouts->sum('payout_amount');
        $totalPaidOut   = (float) $memberPayouts->sum('collected_amount');
        $companyName    = self::COMPANY_NAME;

        return view('cashflows_print', compact(
            'selectedMonth',
            'monthDate',
            'monthInstallments',
            'monthShares',
            'totalDue',
            'totalCollected',
            'totalPayout',
            'totalPaidOut',
            'companyName'
        ));
    }

    /**
     * طباعة كشف تفصيلي لجمعية واحدة (الأعضاء + مصفوفة التحصيل)
     */
    public function printJamia($id)
    {
        $jamia = Jamia::with([
            'shares' => fn ($q) => $q->orderBy('share_number')->orderBy('split_part'),
            'installments',
        ])->findOrFail($id);

        $start  = Carbon::parse($jamia->start_date)->startOfMonth();
        $months = [];
        for ($i = 0; $i < $jamia->duration_months; $i++) {
            $months[] = $start->copy()->addMonths($i);
        }

        // مصفوفة: [share_id][Y-m-d] => installment
        $matrix = [];
        foreach ($jamia->installments as $inst) {
            $inst->collection_start_day = $jamia->collection_start_day;
            $inst->collection_end_day   = $jamia->collection_end_day;
            $inst->status = self::installmentStatus($inst);
            $matrix[$inst->share_id][Carbon::parse($inst->month_date)->toDateString()] = $inst;
        }

        $companyName = self::COMPANY_NAME;

        return view('cashflows_jamia_print', compact('jamia', 'months', 'matrix', 'companyName'));
    }

    /**
     * كشف حساب العضو عبر كل الجمعيات المشترك بها
     * يعرض:
     *  - داخل بكام اسم في كام جمعية
     *  - متى وقيمة ما سيقبضه في كل شهر وحالة الصرف
     *  - جدول الأقساط الشهرية المطلوبة، المسدد منها، المتبقي، والمتأخرات
     */
    public function memberStatement(Request $request)
    {
        $shareId = $request->input('share_id');
        $name    = trim((string) $request->input('name', ''));
        $phone   = trim((string) $request->input('phone', ''));
        $jamiaId = $request->input('jamia_id');

        if (!$shareId && empty($name) && empty($phone)) {
            if ($request->wantsJson() || $request->input('format') === 'json') {
                return response()->json(['error' => 'يرجى تحديد العضو المطلوب.'], 422);
            }
            return back()->with('error', 'يرجى تحديد العضو المطلوب.');
        }

        // لو محدد سهم معين: استخرج الاسم والهاتف منه كمرجع
        if ($shareId) {
            $baseShare = JamiaShare::with('jamia')->find($shareId);
            if ($baseShare) {
                if (empty($name)) {
                    $name = trim($baseShare->share_label);
                }
                if (empty($phone) && !empty($baseShare->member_phone)) {
                    $phone = trim($baseShare->member_phone);
                }
            }
        }

        // جلب كل أسهم العضو في الجمعيات
        $sharesQuery = JamiaShare::with([
            'jamia',
            'collectedAccount',
            'recoveredAccount',
            'installments.account'
        ])->where('is_company', false);

        if (!empty($jamiaId)) {
            $sharesQuery->where('jamia_id', $jamiaId);
        }

        $sharesQuery->where(function ($q) use ($name, $phone) {
            $hasCondition = false;
            if (!empty($phone)) {
                $q->where('member_phone', $phone);
                $hasCondition = true;
            }
            if (!empty($name)) {
                $norm = $this->normalizeArabicTerm($name);
                if ($hasCondition) {
                    $q->orWhere('share_label', $name)
                      ->orWhereRaw($this->normalizeArabicColumn('share_label') . ' = ?', [$norm]);
                } else {
                    $q->where(function ($sub) use ($name, $norm) {
                        $sub->where('share_label', $name)
                            ->orWhereRaw($this->normalizeArabicColumn('share_label') . ' = ?', [$norm]);
                    });
                }
            }
        });

        $shares = $sharesQuery->get()->sortBy(function ($sh) {
            return [$sh->jamia->name ?? '', $sh->share_number];
        })->values();

        // لو ملقاش بالاسم لكن مبعوت share_id، استرجع السهم نفسه عالأقل
        if ($shares->isEmpty() && $shareId) {
            $fallback = JamiaShare::with([
                'jamia',
                'collectedAccount',
                'recoveredAccount',
                'installments.account'
            ])->find($shareId);
            if ($fallback) {
                $shares = collect([$fallback]);
            }
        }

        if ($shares->isEmpty()) {
            if ($request->wantsJson() || $request->input('format') === 'json') {
                return response()->json(['error' => 'لم يتم العثور على أي اشتراكات لهذا العضو.'], 404);
            }
            return back()->with('error', 'لم يتم العثور على أي اشتراكات لهذا العضو.');
        }

        $displayPhone = $phone;
        if (empty($displayPhone)) {
            $displayPhone = $shares->pluck('member_phone')->filter()->first() ?: '';
        }
        $displayName = $name ?: ($shares->first()->share_label ?? 'عضو الجمعية');

        // جمع كل أقساط الأسهم وتحديد حالتها الزمنية
        $allInstallments = collect();
        foreach ($shares as $sh) {
            $jamia = $sh->jamia;
            foreach ($sh->installments as $inst) {
                $inst->collection_start_day = $jamia->collection_start_day ?? 1;
                $inst->collection_end_day   = $jamia->collection_end_day ?? self::MAX_COLLECTION_DAY;
                $inst->status               = self::installmentStatus($inst);
                $inst->jamia_name           = $jamia->name ?? '—';
                $inst->share_label          = $sh->share_label;
                $inst->share_number         = $sh->share_number;
                $allInstallments->push($inst);
            }
        }

        // ترتيب الأقساط زمنياً حسب شهر الاستحقاق
        $sortedInstallments = $allInstallments->sortBy(function ($i) {
            return [Carbon::parse($i->month_date)->format('Y-m-d'), $i->jamia_name, $i->share_number];
        })->values();

        // إحصائيات عامة للعضو
        $sharesCount = $shares->count();
        $jamiaIds    = $shares->pluck('jamia_id')->unique();
        $jamiasCount = $jamiaIds->count();
        $jamiasNames = $shares->pluck('jamia.name')->unique()->values()->all();

        // إحصائيات القبض (تشمل الصرف الجزئي والكلي)
        $totalPayout          = (float) $shares->sum('payout_amount');
        $collectedPayout      = (float) $shares->sum('collected_amount');
        $remainingPayout      = max(0.0, round($totalPayout - $collectedPayout, 2));
        $collectedSharesCount = $shares->where('is_collected', true)->count();
        $pendingSharesCount   = $shares->where('is_collected', false)->count();

        // إحصائيات الأقساط
        $activeInsts           = $sortedInstallments->where('is_waived', false);
        $totalInstallmentsDue  = (float) $activeInsts->sum('amount');
        $totalPaid             = (float) $activeInsts->where('is_paid', true)->sum('amount');
        $totalRemaining        = max(0.0, $totalInstallmentsDue - $totalPaid);
        
        $lateInsts             = $activeInsts->filter(fn ($i) => ($i->status['key'] ?? '') === 'late');
        $lateCount             = $lateInsts->count();
        $lateAmount            = (float) $lateInsts->sum('amount');

        $paidInstsCount        = $activeInsts->where('is_paid', true)->count();
        $unpaidInstsCount      = $activeInsts->where('is_paid', false)->count();

        $netPosition           = $collectedPayout - $totalPaid;

        // مصفوفة بيانات الحصص والقبض
        $sharesData = $shares->map(function ($sh) {
            $pMonth    = Carbon::parse($sh->payout_month);
            $paidAmt   = round((float) ($sh->collected_amount ?? 0), 2);
            $payoutAmt = round((float) $sh->payout_amount, 2);
            $remAmt    = max(0.0, round($payoutAmt - $paidAmt, 2));
            $isFull    = (bool) $sh->is_collected || ($payoutAmt > 0 && $remAmt <= 0.005);
            $isPartial = !$isFull && $paidAmt > 0;
            $isDue     = !$isFull && $pMonth->lte(now()->endOfMonth());

            $statusLabel = $isFull
                ? 'تم الصرف بالكامل'
                : ($isPartial
                    ? 'صرف جزئي (' . number_format($paidAmt, 0) . ' من ' . number_format($payoutAmt, 0) . ' — باقي ' . number_format($remAmt, 0) . ' ج)'
                    : ($isDue ? 'مستحق الصرف الآن' : 'بانتظار حلول الشهر'));

            $statusClass = $isFull
                ? 'success'
                : ($isPartial ? 'warning' : ($isDue ? 'warning' : 'secondary'));

            return [
                'id'                => $sh->id,
                'jamia_id'          => $sh->jamia_id,
                'jamia_name'        => $sh->jamia->name ?? '—',
                'share_number'      => $sh->share_number,
                'share_fraction'    => (float) ($sh->share_fraction ?? 1.0),
                'split_part'        => (int) ($sh->split_part ?? 1),
                'share_label'       => $sh->share_label,
                'payout_month'      => $pMonth->format('Y-m'),
                'payout_month_ar'   => $pMonth->locale('ar')->isoFormat('MMMM YYYY'),
                'payout_amount'     => $payoutAmt,
                'is_collected'      => $isFull,
                'is_partial_payout' => $isPartial,
                'collected_at'      => $sh->collected_at ? Carbon::parse($sh->collected_at)->format('Y/m/d') : null,
                'collected_amount'  => $paidAmt,
                'remaining_amount'  => $remAmt,
                'collected_account' => $sh->collectedAccount->account_name ?? null,
                'is_transferred'    => (bool) $sh->is_transferred,
                'status_label'      => $statusLabel,
                'status_class'      => $statusClass,
            ];
        })->values();

        // مصفوفة بيانات الأقساط
        $installmentsData = $sortedInstallments->map(function ($inst) {
            $mDate = Carbon::parse($inst->month_date);
            return [
                'id'            => $inst->id,
                'jamia_id'      => $inst->jamia_id,
                'jamia_name'    => $inst->jamia_name,
                'share_id'      => $inst->share_id,
                'share_number'  => $inst->share_number,
                'month_date'    => $mDate->format('Y-m'),
                'month_date_ar' => $mDate->locale('ar')->isoFormat('MMMM YYYY'),
                'month_index'   => $inst->month_index,
                'amount'        => (float) $inst->amount,
                'is_paid'       => (bool) $inst->is_paid,
                'paid_at'       => $inst->paid_at ? Carbon::parse($inst->paid_at)->format('Y/m/d') : null,
                'paid_account'  => $inst->account->account_name ?? null,
                'is_waived'     => (bool) $inst->is_waived,
                'status_key'    => $inst->status['key'] ?? 'unknown',
                'status_label'  => $inst->status['label'] ?? '',
                'days_late'     => $inst->status['days'] ?? 0,
            ];
        })->values();

        $summary = [
            'display_name'           => $displayName,
            'display_phone'          => $displayPhone,
            'shares_count'           => $sharesCount,
            'jamias_count'           => $jamiasCount,
            'jamias_names'           => $jamiasNames,
            'total_payout'           => $totalPayout,
            'collected_payout'       => $collectedPayout,
            'remaining_payout'       => $remainingPayout,
            'collected_shares_count' => $collectedSharesCount,
            'pending_shares_count'   => $pendingSharesCount,
            'total_installments_due' => $totalInstallmentsDue,
            'total_paid'             => $totalPaid,
            'total_remaining'        => $totalRemaining,
            'late_count'             => $lateCount,
            'late_amount'            => $lateAmount,
            'paid_insts_count'       => $paidInstsCount,
            'unpaid_insts_count'     => $unpaidInstsCount,
            'net_position'           => $netPosition,
        ];

        $companyName = self::COMPANY_NAME;

        if ($request->input('format') === 'print' || $request->routeIs('cashflows.print_member')) {
            return view('cashflows_member_print', compact(
                'summary',
                'sharesData',
                'installmentsData',
                'companyName'
            ));
        }

        return response()->json([
            'success'      => true,
            'summary'      => $summary,
            'shares'       => $sharesData,
            'installments' => $installmentsData,
            'companyName'  => $companyName,
        ]);
    }

    /**
     * طباعة كشف حساب عضو مخصص
     */
    public function printMember(Request $request)
    {
        $request->merge(['format' => 'print']);
        return $this->memberStatement($request);
    }

    /**
     * حذف جمعية بالكامل
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $jamia = Jamia::findOrFail($id);
                $hasPaid = JamiaInstallment::where('jamia_id', $id)->where('is_paid', true)->exists();
                $hasCollected = JamiaShare::where('jamia_id', $id)->where(function ($q) {
                    $q->where('is_collected', true)
                        ->orWhere('collected_amount', '>', 0)
                        ->orWhere('is_claim_recovered', true);
                })->exists();

                if ($hasPaid || $hasCollected) {
                    throw new \Exception('لا يمكن حذف جمعية فيها تحصيلات أو صرف قبض. الغِ العمليات أولاً إذا رغبت.');
                }

                $shareIds = JamiaShare::where('jamia_id', $id)->pluck('id')->all();
                if (!empty($shareIds)) {
                    DB::table('company_debts')
                        ->where('source_type', 'jamia_company_payout')
                        ->whereIn('source_id', $shareIds)
                        ->delete();
                }

                $jamia->delete();
                $this->logActivity('delete', 'jamia', "تم حذف جمعية: {$jamia->name}.");
            });

            return back()->with('success', 'تم حذف الجمعية بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * تم إلغاء نزول التزامات الجمعيات في جدول company_debts واستبدالها ببلوك "موقف الجمعيات"
     * الموحّد في شاشة الخزانة ومعادلة رأس المال؛ تكتفي هذه الدالة بتنظيف أي سجلات قديمة فقط.
     */
    public static function syncJamiaCompanyDebt(int $jamiaId): void
    {
        if (!Schema::hasTable('company_debts')) {
            return;
        }

        $shareIds = DB::table('jamia_shares')
            ->where('jamia_id', $jamiaId)
            ->pluck('id')
            ->all();

        DB::table('company_debts')
            ->where('source_type', 'jamia_company_payout')
            ->where(function ($q) use ($jamiaId, $shareIds) {
                $q->where('transaction_code', 'like', "JAM-{$jamiaId}-S%");
                if (!empty($shareIds)) {
                    $q->orWhereIn('source_id', $shareIds);
                }
            })
            ->delete();
    }

    /**
     * مزامنة دين الجمعية بناءً على معرف السهم (تنظيف أي بقايا قديمة في company_debts)
     */
    public static function syncJamiaCompanyDebtByShareId(int $shareId): void
    {
        if ($shareId <= 0 || !Schema::hasTable('company_debts')) {
            return;
        }
        DB::table('company_debts')
            ->where('source_type', 'jamia_company_payout')
            ->where('source_id', $shareId)
            ->delete();
    }

    /**
     * تنظيف أي قيود قديمة للجمعيات في جدول الديون (بعد نقل الجمعيات لبلوك موقف الجمعيات المستقل)
     */
    public static function syncAllJamiaCompanyDebts(): void
    {
        if (!self::schemaFlags()['company_debts']) {
            return;
        }
        if (DB::table('company_debts')->where('source_type', 'jamia_company_payout')->exists()) {
            DB::table('company_debts')
                ->where('source_type', 'jamia_company_payout')
                ->delete();
        }
    }
}
