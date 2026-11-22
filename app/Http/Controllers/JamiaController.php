<?php

namespace App\Http\Controllers;

use App\Models\Jamia;
use App\Models\JamiaShare;
use App\Models\JamiaInstallment;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
     * عرض شاشة التدفقات النقدية للجمعيات
     */
    public function index(Request $request)
    {
        if (function_exists('isDeveloperUser') && !isDeveloperUser()) {
            return view('coming_soon', [
                'screenTitle' => 'منظومة التدفقات النقدية (الجمعيات)',
                'screenIcon'  => 'fa-hand-holding-dollar',
            ]);
        }

        // مزامنة أي حركات تم إلغاؤها من سجل العمليات لضمان مطابقة الحالة 100%
        $this->syncWithCancelledTransactions();
        self::syncAllJamiaCompanyDebts();

        // الشهر المختار (الافتراضي: الشهر الحالي)
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $monthDate  = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();

        // 1. حسابات المحافظ / الخزن المتاحة
        $accounts = DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->where('status', 'active')
            ->orderBy('account_name')
            ->get();

        // 2. أقساط المشتركين المطلوب تحصيلها هذا الشهر (نستخرج أقساط الأعضاء فقط للتحصيل، وأسهم الشركة لبلوك التزاماتي)
        $allMonthInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->leftJoin('accounts as a', 'ji.paid_account_id', '=', 'a.id')
            ->whereDate('ji.month_date', $monthStart)
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
            ->whereDate('js.payout_month', $monthStart)
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

        // إجمالي القبض المستحق (الخارج للأعضاء فقط بدون دور الشركة)
        $memberPayouts       = $monthShares->where('is_company', false)->values();
        $payoutDue           = (float) $memberPayouts->sum('payout_amount');
        $payoutDone          = (float) $memberPayouts->where('is_collected', true)->sum('collected_amount');
        $payoutRemaining     = (float) $memberPayouts->where('is_collected', false)->sum('payout_amount');
        $payoutMembersCount  = $memberPayouts->count();
        $payoutDoneCount     = $memberPayouts->where('is_collected', true)->count();
        $payoutPendingCount  = $memberPayouts->where('is_collected', false)->count();

        $memberPayoutDue     = $payoutDue;
        $memberPayoutDone    = $payoutDone;

        // قبض الشركة هذا الشهر (لو للشركة دور قبض في جمعية هذا الشهر)
        $companyPayouts          = $monthShares->where('is_company', true)->values();
        $companyPayoutDue        = (float) $companyPayouts->sum('payout_amount');
        $companyPayoutDone       = (float) $companyPayouts->where('is_collected', true)->sum('collected_amount');
        $companyPayoutRemaining  = (float) $companyPayouts->where('is_collected', false)->sum('payout_amount');
        $companyPayoutCount      = $companyPayouts->count();

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

        // 5. الجمعيات النشطة في الشهر المختار مجمعة (تحصيل + دور القبض)
        $monthJamias = Jamia::where('status', 'active')
            ->with(['shares' => fn ($q) => $q->orderBy('share_number')->orderBy('split_part')])
            ->orderBy('name')
            ->get()
            ->map(function ($jamia) use ($monthDate, $monthInstallments, $monthShares) {
                $start = Carbon::parse($jamia->start_date)->startOfMonth();
                $jamia->month_index = (($monthDate->year - $start->year) * 12) + ($monthDate->month - $start->month) + 1;
                $jamia->month_installments = $monthInstallments->where('jamia_id', $jamia->id)->values();
                $jamia->month_payouts = $monthShares->where('jamia_id', $jamia->id)->values();
                $jamia->company_shares_in = $jamia->shares->where('is_company', true)->count();
                return $jamia;
            })
            ->filter(fn ($j) => $j->month_index >= 1 && $j->month_index <= $j->duration_months)
            ->values();

        // 6. شارت التدفقات السنوي (التحصيل المتوقع من الأعضاء vs القبض المصروف لهم)
        $year = $monthDate->year;
        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        $collectionByMonth = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->whereYear('ji.month_date', $year)
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
            ->whereYear('js.payout_month', $year)
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

        // 7. دليل الجمعيات بالأعضاء والإحصائيات الكلية
        $jamias = Jamia::with([
                'shares' => fn ($q) => $q->with(['collectedAccount', 'recoveredAccount'])->orderBy('share_number')->orderBy('split_part'),
                'installments',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($jamia) {
                $memberIds   = $jamia->shares->where('is_company', false)->pluck('id')->all();
                $memberInsts = $jamia->installments
                    ->where('is_waived', false)
                    ->whereIn('share_id', $memberIds);

                $jamia->calc_total_due       = (float) $memberInsts->sum('amount');
                $jamia->calc_total_collected = (float) $memberInsts->where('is_paid', true)->sum('amount');
                $jamia->calc_remaining       = $jamia->calc_total_due - $jamia->calc_total_collected;
                $jamia->calc_progress        = $jamia->calc_total_due > 0
                    ? round(($jamia->calc_total_collected / $jamia->calc_total_due) * 100)
                    : 0;
                $jamia->calc_has_cash_moves  = $jamia->installments->whereNotNull('tx_id')->isNotEmpty()
                    || $jamia->shares->where('is_collected', true)->isNotEmpty()
                    || $jamia->shares->where('is_transferred', true)->isNotEmpty()
                    || $jamia->shares->where('is_claim_recovered', true)->isNotEmpty();
                $jamia->members_count        = $jamia->shares->count();
                $jamia->company_shares_in    = $jamia->shares->where('is_company', true)->count();
                $jamia->transferred_count    = $jamia->shares->where('is_transferred', true)->count();
                $jamia->collected_count      = $jamia->shares->where('is_collected', true)->count();

                // عدد الشهور المدفوعة والمتأخرة لكل عضو (بدون أسهم الشركة)
                $lateByShare = [];
                $paidByShare = [];
                foreach ($memberInsts->where('is_paid', true) as $inst) {
                    $paidByShare[$inst->share_id] = ($paidByShare[$inst->share_id] ?? 0) + 1;
                }
                foreach ($memberInsts->where('is_paid', false) as $inst) {
                    $inst->collection_start_day = $jamia->collection_start_day;
                    $inst->collection_end_day   = $jamia->collection_end_day;
                    if (self::installmentStatus($inst)['key'] === 'late') {
                        $lateByShare[$inst->share_id] = ($lateByShare[$inst->share_id] ?? 0) + 1;
                    }
                }
                $jamia->late_by_share    = $lateByShare;
                $jamia->paid_by_share    = $paidByShare;
                $jamia->late_total_count = array_sum($lateByShare);

                return $jamia;
            });

        // 8. متابعة المتأخرين في السداد (الأعضاء فقط في كل الشهور اللي فترة تحصيلها خلصت)
        $overdueInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->where('ji.is_paid', false)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->whereDate('ji.month_date', '<=', now()->startOfMonth()->toDateString())
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

        // 9. إحصائيات عامة
        $globalStats = [
            'active_jamias_count' => $jamias->where('status', 'active')->count(),
            'members_count'       => JamiaShare::where('is_company', false)->count(),
            'company_shares'      => JamiaShare::where('is_company', true)->count(),
            'overdue_count'       => $overdueInstallments->count(),
            'overdue_amount'      => (float) $overdueInstallments->sum('amount'),
        ];

        // 10. قائمة الأعضاء الفريدة لكشف الحساب والاختيار السريع
        $allMemberShares = JamiaShare::with('jamia')
            ->where('is_company', false)
            ->get();

        $membersIndex = [];
        foreach ($allMemberShares as $sh) {
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
            if ($sh->jamia) {
                $membersIndex[$key]['jamias'][$sh->jamia_id] = $sh->jamia->name;
            }
            if (empty($membersIndex[$key]['phone']) && !empty($phone)) {
                $membersIndex[$key]['phone'] = $phone;
            }
        }

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

        return view('cashflows', compact(
            'selectedMonth',
            'monthDate',
            'accounts',
            'monthInstallments',
            'monthShares',
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
            'instSystem1to10'
        ));
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

        if (!Schema::hasTable('installments')) {
            return $result;
        }

        $query = DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('remaining_balance', '>', 0)
            ->whereBetween('due_day', [1, self::MAX_COLLECTION_DAY]);

        if (Schema::hasColumn('installments', 'status')) {
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
        if (Schema::hasTable('installment_payments')) {
            $ids = $contracts->pluck('id')->all();
            $collected = (float) DB::table('installment_payments')
                ->whereIn('installment_id', $ids)
                ->where('amount_paid', '>', 0)
                ->whereYear('payment_date', $monthDate->year)
                ->whereMonth('payment_date', $monthDate->month)
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
            'duration_months'               => 'required|integer|min:2|max:60',
            'start_date'                    => 'required|date',
            'collection_start_day'          => 'nullable|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'collection_end_day'            => 'nullable|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'members'                       => 'required|array|min:2',
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

                $this->logActivity('create', 'jamia', "تم إنشاء جمعية جديدة: {$jamia->name} — قبض {$totalAmount} ج على {$duration} شهور ({$totalParticipants} مشترك، منهم {$companyCount} للشركة).");
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
                    if ($hasCollected || $share->is_collected || $share->is_claim_recovered) {
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

                    if ($currentGroup->where('is_collected', true)->isNotEmpty() || $targetGroup->where('is_collected', true)->isNotEmpty()) {
                        throw new \Exception('لا يمكن تبديل دور تم صرف القبض فيه بالفعل.');
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
                $hasMoves   = $siblings->where('is_collected', true)->isNotEmpty()
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
                $hasMoves = $siblings->where('is_collected', true)->isNotEmpty()
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
            'installment_ids.*' => 'integer|exists:jamia_installments,id',
            'account_id'        => 'required|integer|exists:accounts,id',
            'paid_at'           => 'nullable|date',
            'notes'             => 'nullable|string|max:500',
        ], [
            'installment_ids.required' => 'اختر قسطاً واحداً على الأقل للتحصيل.',
            'account_id.required'      => 'اختيار الخزنة أو المحفظة إجباري.',
            'account_id.exists'        => 'المحفظة / الخزنة المختارة غير موجودة.',
        ]);

        try {
            $count = 0;
            DB::transaction(function () use ($request, &$count) {
                $account = $this->lockActiveAccount((int) $request->account_id);

                $paidAt = $request->paid_at ? Carbon::parse($request->paid_at)->setTimeFrom(now()) : now();
                $total  = 0;

                foreach (array_unique($request->installment_ids) as $instId) {
                    $inst = JamiaInstallment::where('id', $instId)->lockForUpdate()->first();
                    if (!$inst) throw new \Exception('القسط غير موجود.');
                    if ($inst->is_paid) continue; // متحصل بالفعل
                    if ($inst->is_waived) continue;

                    $share = DB::table('jamia_shares')->where('id', $inst->share_id)->first();
                    if (!$share) continue;

                    $jamia    = DB::table('jamias')->where('id', $inst->jamia_id)->first();
                    $monthStr = Carbon::parse($inst->month_date)->format('Y/m');

                    if (!empty($share->is_company)) {
                        // سهم الشركة: الشركة بتدفع حصتها في الجمعية (تخرج من الخزنة) عشان القبض يكمل
                        if ($account->balance < $inst->amount) {
                            throw new \Exception("رصيد الخزنة ({$account->account_name}) غير كافٍ لسداد حصة الشركة ({$inst->amount} ج). الرصيد الحالي: " . number_format($account->balance, 2) . ' ج.');
                        }
                        $noteText = "سداد حصة الشركة ({$share->share_label}) في جمعية: {$jamia->name} - شهر ({$monthStr}) (دور {$share->share_number})";
                        if ($request->notes) {
                            $noteText .= ' | ' . trim($request->notes);
                        }

                        DB::table('accounts')->where('id', $account->id)->decrement('balance', $inst->amount);
                        $account->balance -= $inst->amount;

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
                            'updated_at'      => now(),
                        ]);
                    } else {
                        // سهم عضو: تحصيل قسط العضو وإيداعه في الخزنة
                        $noteText = "تحصيل قسط شهر ({$monthStr}) - جمعية: {$jamia->name} - العضو: {$share->share_label} (دور {$share->share_number})";
                        if ($request->notes) {
                            $noteText .= ' | ' . trim($request->notes);
                        }

                        DB::table('accounts')->where('id', $account->id)->increment('balance', $inst->amount);
                        $account->balance += $inst->amount;

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
                            'updated_at'      => now(),
                        ]);
                    }

                    $inst->update([
                        'is_paid'         => true,
                        'paid_at'         => $paidAt,
                        'paid_account_id' => $account->id,
                        'tx_id'           => $txId,
                        'notes'           => $request->notes ? trim($request->notes) : $inst->notes,
                    ]);

                    $total += (float) $inst->amount;
                    $count++;
                }

                if ($count === 0) {
                    throw new \Exception('لا توجد أقساط صالحة للتحصيل (ممكن تكون متحصلة بالفعل).');
                }

                $this->logActivity('collect', 'jamia', "تحصيل {$count} قسط جمعية بإجمالي " . number_format($total, 2) . " ج وإيداعها في {$account->account_name}.");
            });

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
     * صرف القبض لصاحب الدور (خارج من الخزنة) أو تسجيل استلام الشركة (داخل للخزنة إجبارياً)
     */
    public function payoutShare(Request $request)
    {
        $request->validate([
            'share_id'           => 'required|integer|exists:jamia_shares,id',
            'account_id'         => 'required|integer|exists:accounts,id',
            'amount'             => 'required|numeric|min:1',
            'paid_at'            => 'nullable|date',
            'notes'              => 'nullable|string|max:500',
            'also_recover_claim' => 'nullable|boolean',
            'recover_account_id' => 'nullable|integer|exists:accounts,id',
            'recover_amount'     => 'nullable|numeric|min:1',
        ], [
            'share_id.required'   => 'يجب تحديد الدور المراد تسجيله.',
            'share_id.exists'     => 'الدور المحدد غير موجود.',
            'account_id.required' => 'اختيار المحفظة / الخزنة إجباري.',
            'account_id.exists'   => 'المحفظة / الخزنة المختارة غير موجودة.',
            'amount.required'     => 'يجب إدخال المبلغ.',
            'amount.min'          => 'المبلغ يجب أن يكون أكبر من صفر.',
            'recover_account_id.exists' => 'خزنة استرداد حق الشركة المختارة غير موجودة.',
        ]);

        $isCompanyShare = false;

        try {
            DB::transaction(function () use ($request, &$isCompanyShare) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');
                if ($share->is_collected) throw new \Exception('تم تسجيل قبض هذا الدور بالفعل.');

                $isCompanyShare = (bool) $share->is_company;
                $jamia  = DB::table('jamias')->where('id', $share->jamia_id)->first();
                $paidAt = $request->paid_at ? Carbon::parse($request->paid_at)->setTimeFrom(now()) : now();
                $amount = round((float) $request->amount, 2);

                if ($amount <= 0 || $amount > (float) $share->payout_amount) {
                    throw new \Exception('المبلغ المدخل (' . number_format($amount, 2) . ' ج) يجب أن يكون بين 1 وقيمة قبض الدور (' . number_format((float) $share->payout_amount, 2) . ' ج).');
                }

                $account = $this->lockActiveAccount((int) $request->account_id);

                if ($share->is_company) {
                    if ($share->is_transferred) {
                        throw new \Exception('تم التنازل عن هذا السهم لعضو بديل.');
                    }

                    DB::table('accounts')->where('id', $account->id)->increment('balance', $amount);

                    $noteText = "قبض جمعية (" . self::COMPANY_NAME . "): {$jamia->name} (دور {$share->share_number} - {$share->share_label})";
                    if ($request->notes) {
                        $noteText .= ' | ' . trim($request->notes);
                    }

                    $txId = DB::table('financial_transactions')->insertGetId([
                        'type'            => 'income',
                        'subtype'         => 'jamia_payout',
                        'amount'          => $amount,
                        'from_account_id' => null,
                        'to_account_id'   => $account->id,
                        'ref_type'        => 'jamia_share',
                        'ref_id'          => $share->id,
                        'notes'           => $noteText,
                        'status'          => 'active',
                        'created_at'      => $paidAt,
                        'updated_at'      => now(),
                    ]);

                    $share->update([
                        'is_collected'         => true,
                        'collected_at'         => $paidAt,
                        'collected_account_id' => $account->id,
                        'collected_tx_id'      => $txId,
                        'collected_amount'     => $amount,
                        'notes'                => $request->notes ? trim($request->notes) : $share->notes,
                    ]);

                    self::syncJamiaCompanyDebt((int) $share->jamia_id);

                    $this->logActivity('payout', 'jamia', "استلام الشركة ({$share->share_label}) قبض جمعية {$jamia->name} (دور {$share->share_number}) بمبلغ " . number_format($amount, 2) . " ج وإيداعه في {$account->account_name}.");
                    return;
                }

                if ($account->balance < $amount) {
                    throw new \Exception("رصيد الخزنة ({$account->account_name}) غير كافٍ. الرصيد الحالي: " . number_format($account->balance, 2) . ' ج.');
                }

                DB::table('accounts')->where('id', $account->id)->decrement('balance', $amount);

                $noteText = "صرف قبض جمعية: {$jamia->name} - للعضو: {$share->share_label} (دور {$share->share_number})";
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
                    'is_collected'         => true,
                    'collected_at'         => $paidAt,
                    'collected_account_id' => $account->id,
                    'collected_tx_id'      => $txId,
                    'collected_amount'     => $amount,
                    'notes'                => $request->notes ? trim($request->notes) : $share->notes,
                ]);

                self::syncJamiaCompanyDebt((int) $share->jamia_id);

                $this->logActivity('payout', 'jamia', "صرف قبض جمعية {$jamia->name} للعضو {$share->share_label} بمبلغ " . number_format($amount, 2) . " ج من {$account->account_name}.");

                if ($share->is_transferred && $request->boolean('also_recover_claim')) {
                    if (!$request->filled('recover_account_id')) {
                        throw new \Exception('اختيار خزنة إيداع حق الشركة إجباري عند تفعيل الاسترداد مع الصرف.');
                    }
                    $recoverAmount = round((float) ($request->recover_amount ?: $share->company_claim_amount), 2);
                    $this->executeClaimRecovery($share->fresh(), $jamia, (int) $request->recover_account_id, $recoverAmount, $paidAt, $request->notes);
                }
            });

            return back()->with('success', $isCompanyShare ? 'تم تسجيل استلام الشركة وإيداع المبلغ في الخزنة وإثبات الالتزام في شاشة الديون بنجاح.' : 'تم تسجيل صرف القبض وتحديث الالتزامات المرتبطة بنجاح.');
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
                if ($share->is_collected) throw new \Exception('لا يمكن التنازل عن سهم تم قبضه بالفعل.');

                $newName = trim((string) $request->transferred_to_name);
                if ($newName === '' || $newName === self::COMPANY_NAME) {
                    throw new \Exception('اسم العضو البديل غير صالح — اكتب اسم الشخص الذي سيدخل مكان الشركة.');
                }

                $jamia      = Jamia::findOrFail($share->jamia_id);
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
                if ($share->is_collected) {
                    throw new \Exception('لا يمكن فك التنازل بعد صرف القبض — الغِ الصرف أولاً.');
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
     * إلغاء صرف القبض أو استلام الشركة وإرجاع الأثر المالي للخزنة
     */
    public function cancelPayout(Request $request)
    {
        $request->validate(['share_id' => 'required|integer']);

        $isCompanyShare = false;

        try {
            DB::transaction(function () use ($request, &$isCompanyShare) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');
                if (!$share->is_collected) throw new \Exception('لم يتم صرف القبض لهذا الدور.');

                $isCompanyShare = (bool) $share->is_company;

                if ($share->collected_tx_id) {
                    $tx = DB::table('financial_transactions')->where('id', $share->collected_tx_id)->first();
                    if ($share->collected_account_id && $share->collected_amount > 0) {
                        if ($tx && $tx->type === 'income') {
                            // قبض الشركة كان إيداع => نخصمه من الخزنة
                            $acc = DB::table('accounts')->where('id', $share->collected_account_id)->lockForUpdate()->first();
                            if ($acc && $acc->balance < $share->collected_amount) {
                                throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب عكسه.");
                            }
                            DB::table('accounts')->where('id', $share->collected_account_id)->decrement('balance', $share->collected_amount);
                        } else {
                            DB::table('accounts')->where('id', $share->collected_account_id)->increment('balance', $share->collected_amount);
                        }
                    }

                    DB::table('financial_transactions')->where('id', $share->collected_tx_id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء صرف/استلام قبض جمعية',
                        'updated_at'    => now(),
                    ]);
                }

                $share->update([
                    'is_collected'         => false,
                    'collected_at'         => null,
                    'collected_account_id' => null,
                    'collected_tx_id'      => null,
                    'collected_amount'     => 0,
                ]);

                self::syncJamiaCompanyDebt((int) $share->jamia_id);

                $this->logActivity('cancel_payout', 'jamia', "إلغاء قبض [{$share->share_label}] وعكس الأثر المالي.");
            });

            return back()->with('success', $isCompanyShare ? 'تم إلغاء تسجيل استلام الشركة وخصم المبلغ من الخزنة وإلغاء التزامه من شاشة الديون.' : 'تم إلغاء صرف القبض وتحديث الالتزامات المرتبطة بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * مزامنة أي حركات تم إلغاؤها من سجل العمليات لضمان مطابقة الحالة
     */
    private function syncWithCancelledTransactions()
    {
        // 1. أدوار القبض المصروفة التي أُلغيت حركتها
        $collectedShares = DB::table('jamia_shares')->where('is_collected', true)->whereNotNull('collected_tx_id')->get();
        $affectedJamiaIds = [];
        foreach ($collectedShares as $cs) {
            $tx = DB::table('financial_transactions')->where('id', $cs->collected_tx_id)->first();
            if (!$tx || $tx->status === 'cancelled') {
                DB::table('jamia_shares')->where('id', $cs->id)->update([
                    'is_collected'         => false,
                    'collected_at'         => null,
                    'collected_account_id' => null,
                    'collected_tx_id'      => null,
                    'collected_amount'     => 0,
                    'updated_at'           => now(),
                ]);
                $affectedJamiaIds[] = (int) $cs->jamia_id;
            }
        }
        foreach (array_unique($affectedJamiaIds) as $jid) {
            self::syncJamiaCompanyDebt($jid);
        }

        // 2. (قديم) الأسهم المسترد حقها التي أُلغيت حركتها
        $recoveredShares = DB::table('jamia_shares')->where('is_claim_recovered', true)->whereNotNull('recovered_tx_id')->get();
        foreach ($recoveredShares as $rs) {
            $tx = DB::table('financial_transactions')->where('id', $rs->recovered_tx_id)->first();
            if (!$tx || $tx->status === 'cancelled') {
                DB::table('jamia_shares')->where('id', $rs->id)->update([
                    'is_claim_recovered'   => false,
                    'recovered_at'         => null,
                    'recovered_account_id' => null,
                    'recovered_tx_id'      => null,
                    'recovered_amount'     => 0,
                    'updated_at'           => now(),
                ]);
            }
        }

        // 3. الأقساط المحصلة التي أُلغيت حركتها
        $paidInsts = DB::table('jamia_installments')->where('is_paid', true)->whereNotNull('tx_id')->get();
        foreach ($paidInsts as $pi) {
            $tx = DB::table('financial_transactions')->where('id', $pi->tx_id)->first();
            if (!$tx || $tx->status === 'cancelled') {
                DB::table('jamia_installments')->where('id', $pi->id)->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                    'updated_at'      => now(),
                ]);
            }
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
        $totalPaidOut   = (float) $memberPayouts->where('is_collected', true)->sum('collected_amount');
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
        if (function_exists('isDeveloperUser') && !isDeveloperUser()) {
            if ($request->wantsJson() || $request->input('format') === 'json') {
                return response()->json(['error' => 'غير مصرح.'], 403);
            }
            return view('coming_soon', [
                'screenTitle' => 'كشف حساب عضو الجمعيات',
                'screenIcon'  => 'fa-file-invoice-dollar',
            ]);
        }

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

        // إحصائيات القبض
        $totalPayout          = (float) $shares->sum('payout_amount');
        $collectedPayout      = (float) $shares->where('is_collected', true)->sum('collected_amount');
        $remainingPayout      = max(0.0, $totalPayout - $collectedPayout);
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
            $pMonth = Carbon::parse($sh->payout_month);
            $isDue  = !$sh->is_collected && $pMonth->lte(now()->endOfMonth());
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
                'payout_amount'     => (float) $sh->payout_amount,
                'is_collected'      => (bool) $sh->is_collected,
                'collected_at'      => $sh->collected_at ? Carbon::parse($sh->collected_at)->format('Y/m/d') : null,
                'collected_amount'  => (float) $sh->collected_amount,
                'collected_account' => $sh->collectedAccount->account_name ?? null,
                'is_transferred'    => (bool) $sh->is_transferred,
                'status_label'      => $sh->is_collected
                    ? 'تم الصرف'
                    : ($isDue ? 'مستحق الصرف الآن' : 'بانتظار حلول الشهر'),
                'status_class'      => $sh->is_collected
                    ? 'success'
                    : ($isDue ? 'warning' : 'secondary'),
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
                    $q->where('is_collected', true)->orWhere('is_claim_recovered', true);
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
     * مزامنة التزامات قبض أسهم الشركة للجمعية داخل جدول company_debts:
     * - عند استلام قبض سهم شركة: ينزل كامل مبلغ القبض كالتزام دين على الشركة حتى لا يتضخم رأس المال وأرباح الشركاء مبكراً.
     * - عند صرف قبض أي عضو (غير الشركة): يقل التزام كل سهم شركة مقبوض بقيمة قسط أسهم الشركة،
     *   وباكتمال قبض جميع الأعضاء الآخرين يتصفّر أي باقي خاص بدور الشركة تلقائياً.
     */
    public static function syncJamiaCompanyDebt(int $jamiaId): void
    {
        $jamia = DB::table('jamias')->where('id', $jamiaId)->first();
        if (!$jamia) {
            DB::table('company_debts')
                ->where('source_type', 'jamia_company_payout')
                ->where('transaction_code', 'like', "JAM-{$jamiaId}-S%")
                ->delete();
            return;
        }

        $shares = DB::table('jamia_shares')
            ->where('jamia_id', $jamiaId)
            ->orderBy('share_number')
            ->orderBy('split_part')
            ->get();

        $collectedCompanyShares = $shares->filter(
            fn ($s) => !empty($s->is_company) && !empty($s->is_collected) && (float) ($s->collected_amount ?? 0) > 0
        );
        $collectedCompanyIds = $collectedCompanyShares->pluck('id')->all();

        $allShareIds = $shares->pluck('id')->all();
        if (!empty($allShareIds)) {
            $cleanupQ = DB::table('company_debts')
                ->where('source_type', 'jamia_company_payout')
                ->whereIn('source_id', $allShareIds);
            if (!empty($collectedCompanyIds)) {
                $cleanupQ->whereNotIn('source_id', $collectedCompanyIds);
            }
            $cleanupQ->delete();
        }

        if ($collectedCompanyShares->isEmpty()) {
            return;
        }

        $nonCompanyShares           = $shares->filter(fn ($s) => empty($s->is_company));
        $collectedNonCompanyShares  = $nonCompanyShares->filter(fn ($s) => !empty($s->is_collected));
        $uncollectedNonCompanyCount = $nonCompanyShares->filter(fn ($s) => empty($s->is_collected))->count();

        $collectedMemberTurnUnits = round((float) $collectedNonCompanyShares->sum(
            fn ($s) => (float) ($s->share_fraction ?? 1.0)
        ), 4);
        $collectedMembersCount = $collectedNonCompanyShares->count();
        $totalMembersCount     = $nonCompanyShares->count();
        $allMembersCollected   = $uncollectedNonCompanyCount === 0;

        $monthlyInstallment = round((float) $jamia->monthly_installment, 2);
        $creditorName       = 'جمعية: ' . $jamia->name;
        $now                = now();

        foreach ($collectedCompanyShares as $cs) {
            $shareFraction = (float) ($cs->share_fraction ?? 1.0);
            $totalDebt     = round((float) $cs->collected_amount, 2);
            $unitPerMember = round($monthlyInstallment * $shareFraction, 2);
            $paidByMembers = round($unitPerMember * $collectedMemberTurnUnits, 2);

            $paidAmount = $allMembersCollected
                ? $totalDebt
                : min($totalDebt, max(0.0, $paidByMembers));
            $remaining  = max(0.0, round($totalDebt - $paidAmount, 2));

            $fractionNote = JamiaShare::isPartial($shareFraction) ? ' (حصة ' . JamiaShare::formatPercent($shareFraction) . ')' : '';
            $reason = "قبض سهم الشركة في جمعية: {$jamia->name} | الدور {$cs->share_number}{$fractionNote} | قيمة قسط السهم: " . number_format($unitPerMember, 2) . ' ج';
            $reason .= " | قُبضت أدوار الأعضاء: {$collectedMembersCount} من {$totalMembersCount}";
            if ($allMembersCollected && $totalMembersCount > 0) {
                $reason .= ' | تم التصفير تلقائياً لاكتمال قبض جميع الأعضاء';
            } elseif ($allMembersCollected) {
                $reason .= ' | تم التصفير تلقائياً (لا يوجد أعضاء آخرون)';
            }

            $txCode    = "JAM-{$jamia->id}-S{$cs->id}";
            $createdAt = $cs->collected_at ?: $now;

            $existing = DB::table('company_debts')
                ->where('source_type', 'jamia_company_payout')
                ->where('source_id', $cs->id)
                ->first();

            if ($existing) {
                $needsUpdate = ($existing->transaction_code !== $txCode)
                    || ($existing->creditor_name !== $creditorName)
                    || ($existing->reason !== $reason)
                    || (round((float) $existing->total_amount, 2) !== $totalDebt)
                    || (round((float) $existing->paid_amount, 2) !== $paidAmount)
                    || (round((float) $existing->remaining_balance, 2) !== $remaining)
                    || ($existing->category !== 'جمعيات');

                if ($needsUpdate) {
                    DB::table('company_debts')->where('id', $existing->id)->update([
                        'transaction_code'  => $txCode,
                        'creditor_name'     => $creditorName,
                        'reason'            => $reason,
                        'total_amount'      => $totalDebt,
                        'paid_amount'       => $paidAmount,
                        'remaining_balance' => $remaining,
                        'category'          => 'جمعيات',
                        'updated_at'        => $now,
                    ]);
                }
            } else {
                DB::table('company_debts')->insert([
                    'transaction_code'  => $txCode,
                    'creditor_name'     => $creditorName,
                    'reason'            => $reason,
                    'total_amount'      => $totalDebt,
                    'paid_amount'       => $paidAmount,
                    'remaining_balance' => $remaining,
                    'category'          => 'جمعيات',
                    'source_type'       => 'jamia_company_payout',
                    'source_id'         => $cs->id,
                    'created_at'        => $createdAt,
                    'updated_at'        => $now,
                ]);
            }
        }
    }

    /**
     * مزامنة دين الجمعية بناءً على معرف السهم (مفيدة عند إلغاء الحركات من سجل العمليات)
     */
    public static function syncJamiaCompanyDebtByShareId(int $shareId): void
    {
        if ($shareId <= 0) {
            return;
        }
        $jamiaId = DB::table('jamia_shares')->where('id', $shareId)->value('jamia_id');
        if ($jamiaId) {
            self::syncJamiaCompanyDebt((int) $jamiaId);
        }
    }

    /**
     * التأكد من تزامن كافة الجمعيات التي فيها أسهم شركة مقبوضة مع جدول الديون
     */
    public static function syncAllJamiaCompanyDebts(): void
    {
        $jamiaIds = DB::table('jamia_shares')
            ->where('is_company', true)
            ->where('is_collected', true)
            ->distinct()
            ->pluck('jamia_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $debtShareIds = DB::table('company_debts')
            ->where('source_type', 'jamia_company_payout')
            ->whereNotNull('source_id')
            ->pluck('source_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!empty($debtShareIds)) {
            $existingShares = DB::table('jamia_shares')
                ->whereIn('id', $debtShareIds)
                ->get(['id', 'jamia_id']);

            $validShareIds  = $existingShares->pluck('id')->map(fn ($id) => (int) $id)->all();
            $orphanShareIds = array_diff($debtShareIds, $validShareIds);
            if (!empty($orphanShareIds)) {
                DB::table('company_debts')
                    ->where('source_type', 'jamia_company_payout')
                    ->whereIn('source_id', $orphanShareIds)
                    ->delete();
            }

            foreach ($existingShares as $es) {
                $jamiaIds[] = (int) $es->jamia_id;
            }
        }

        foreach (array_unique($jamiaIds) as $jid) {
            self::syncJamiaCompanyDebt((int) $jid);
        }
    }
}
