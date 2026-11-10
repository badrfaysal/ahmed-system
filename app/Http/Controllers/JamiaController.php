<?php

namespace App\Http\Controllers;

use App\Models\Jamia;
use App\Models\JamiaShare;
use App\Models\JamiaInstallment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JamiaController extends SystemController
{
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

        // الشهر المختار (الافتراضي: الشهر الحالي)
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $monthDate = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();
        $monthEnd   = $monthDate->copy()->endOfMonth()->toDateString();

        // 1. حسابات المحافظ / الخزن المتاحة
        $accounts = DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->where('status', 'active')
            ->orderBy('account_name')
            ->get();

        // 2. الأقساط المستحقة على الشركة لهذا الشهر (المطالب به / الخارج)
        // لا تشمل الأقساط المعفية بسبب التنازل (is_waived = 0)
        $monthInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->leftJoin('accounts as a', 'ji.paid_account_id', '=', 'a.id')
            ->where('ji.month_date', $monthStart)
            ->where('ji.is_waived', false)
            ->where('j.status', 'active')
            ->select(
                'ji.*',
                'j.name as jamia_name',
                'j.due_day',
                'js.share_label',
                'js.share_number',
                'a.account_name as paid_account_name'
            )
            ->orderBy('ji.is_paid')
            ->orderBy('j.name')
            ->get();

        $totalDueThisMonth = $monthInstallments->sum('amount');
        $totalPaidThisMonth = $monthInstallments->where('is_paid', true)->sum('amount');
        $remainingDueThisMonth = $totalDueThisMonth - $totalPaidThisMonth;

        // 3. المقبوضات المتوقعة للشركة لهذا الشهر (الداخل / المداخل)
        // تشمل:
        // أ) الأسهم التي حل موعد قبضها هذا الشهر ولم يتم التنازل عنها (قيمة القبض كاملة)
        // ب) الأسهم المتنازل عنها التي حل موعد قبضها هذا الشهر (حق الشركة المسترد company_claim_amount)
        $monthShares = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->leftJoin('accounts as ca', 'js.collected_account_id', '=', 'ca.id')
            ->leftJoin('accounts as ra', 'js.recovered_account_id', '=', 'ra.id')
            ->where('js.payout_month', $monthStart)
            ->where('j.status', 'active')
            ->select(
                'js.*',
                'j.name as jamia_name',
                'j.duration_months',
                'j.monthly_installment',
                'ca.account_name as collected_account_name',
                'ra.account_name as recovered_account_name'
            )
            ->get();

        $expectedInflowThisMonth = 0;
        $collectedInflowThisMonth = 0;

        foreach ($monthShares as $s) {
            if (!$s->is_transferred) {
                // سهم عادي للشركة: المبلغ المتوقع = قيمة القبض
                $expectedInflowThisMonth += (float) $s->payout_amount;
                if ($s->is_collected) {
                    $collectedInflowThisMonth += (float) ($s->collected_amount ?: $s->payout_amount);
                }
            } else {
                // سهم متنازل عنه: المبلغ المتوقع = حق الشركة المسترد فقط
                $expectedInflowThisMonth += (float) $s->company_claim_amount;
                if ($s->is_claim_recovered) {
                    $collectedInflowThisMonth += (float) ($s->recovered_amount ?: $s->company_claim_amount);
                }
            }
        }

        $remainingInflowThisMonth = $expectedInflowThisMonth - $collectedInflowThisMonth;

        // 4. صافي تدفق الشهر (Net Flow)
        $netExpectedThisMonth = $expectedInflowThisMonth - $totalDueThisMonth;
        $netActualThisMonth   = $collectedInflowThisMonth - $totalPaidThisMonth;

        // 5. شارت التدفقات النقدية السنوي (شهور السنة المختارة من 1 إلى 12)
        $year = $monthDate->year;
        $chartLabels = [];
        $chartPayable = [];
        $chartReceivable = [];
        $chartNet = [];

        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        for ($m = 1; $m <= 12; $m++) {
            $mStart = sprintf('%04d-%02d-01', $year, $m);
            $chartLabels[] = $arabicMonths[$m];

            // المطلوب دفعه في هذا الشهر
            $dueSum = (float) DB::table('jamia_installments as ji')
                ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
                ->where('ji.month_date', $mStart)
                ->where('ji.is_waived', false)
                ->where('j.status', 'active')
                ->sum('ji.amount');
            $chartPayable[] = round($dueSum, 2);

            // المتوقع دخوله في هذا الشهر
            $sharesM = DB::table('jamia_shares as js')
                ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
                ->where('js.payout_month', $mStart)
                ->where('j.status', 'active')
                ->get();

            $inflowSum = 0;
            foreach ($sharesM as $sm) {
                if (!$sm->is_transferred) {
                    $inflowSum += (float) $sm->payout_amount;
                } else {
                    $inflowSum += (float) $sm->company_claim_amount;
                }
            }
            $chartReceivable[] = round($inflowSum, 2);
            $chartNet[] = round($inflowSum - $dueSum, 2);
        }

        // 6. قائمة الجمعيات المسجلة النشطة والإحصائيات الكلية
        $jamias = Jamia::with(['shares', 'installments'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($jamia) {
                $totalInstallments = $jamia->installments->where('is_waived', false)->sum('amount');
                $totalPaid = $jamia->installments->where('is_paid', true)->sum('amount');
                $totalExpectedPayout = 0;
                $totalCollectedPayout = 0;

                foreach ($jamia->shares as $sh) {
                    if (!$sh->is_transferred) {
                        $totalExpectedPayout += (float) $sh->payout_amount;
                        if ($sh->is_collected) $totalCollectedPayout += (float) ($sh->collected_amount ?: $sh->payout_amount);
                    } else {
                        $totalExpectedPayout += (float) $sh->company_claim_amount;
                        if ($sh->is_claim_recovered) $totalCollectedPayout += (float) ($sh->recovered_amount ?: $sh->company_claim_amount);
                    }
                }

                $jamia->calc_total_due = $totalInstallments;
                $jamia->calc_total_paid = $totalPaid;
                $jamia->calc_remaining_due = $totalInstallments - $totalPaid;
                $jamia->calc_expected_payout = $totalExpectedPayout;
                $jamia->calc_collected_payout = $totalCollectedPayout;
                $jamia->calc_remaining_payout = $totalExpectedPayout - $totalCollectedPayout;
                $jamia->paid_progress = $totalInstallments > 0 ? round(($totalPaid / $totalInstallments) * 100) : 0;

                return $jamia;
            });

        // 7. جميع الأسهم لعرض جدول الأسهم الكامل وحالات التنازل
        $allShares = JamiaShare::with(['jamia', 'collectedAccount', 'recoveredAccount'])
            ->orderBy('payout_month')
            ->get();

        // 8. إحصائيات عامة للمنظومة بالكامل
        $globalStats = [
            'active_jamias_count'  => $jamias->where('status', 'active')->count(),
            'total_company_shares' => JamiaShare::count(),
            'transferred_shares'   => JamiaShare::where('is_transferred', true)->count(),
            'total_invested_paid'  => (float) DB::table('jamia_installments')->where('is_paid', true)->sum('amount'),
            'total_collected_all'  => (float) DB::table('jamia_shares')->where('is_collected', true)->sum('collected_amount')
                                    + (float) DB::table('jamia_shares')->where('is_claim_recovered', true)->sum('recovered_amount'),
        ];

        return view('cashflows', compact(
            'selectedMonth',
            'monthDate',
            'accounts',
            'monthInstallments',
            'totalDueThisMonth',
            'totalPaidThisMonth',
            'remainingDueThisMonth',
            'monthShares',
            'expectedInflowThisMonth',
            'collectedInflowThisMonth',
            'remainingInflowThisMonth',
            'netExpectedThisMonth',
            'netActualThisMonth',
            'chartLabels',
            'chartPayable',
            'chartReceivable',
            'chartNet',
            'jamias',
            'allShares',
            'globalStats'
        ));
    }

    /**
     * حفظ جمعية جديدة وتوليد أسهمها وأقساطها
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'total_amount'         => 'required|numeric|min:1',
            'duration_months'      => 'required|integer|min:1',
            'company_shares_count' => 'required|integer|min:1',
            'start_date'           => 'required|date',
            'due_day'              => 'nullable|integer|min:1|max:31',
            'shares'               => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $totalAmount = (float) $request->total_amount;
                $duration = (int) $request->duration_months;
                $sharesCount = (int) $request->company_shares_count;
                $monthlyInstallment = round($totalAmount / $duration, 2);
                $startDate = Carbon::parse($request->start_date)->startOfMonth();
                $dueDay = (int) ($request->due_day ?: 1);

                $jamia = Jamia::create([
                    'name'                 => $request->name,
                    'organizer_name'       => $request->organizer_name,
                    'organizer_phone'      => $request->organizer_phone,
                    'total_amount'         => $totalAmount,
                    'duration_months'      => $duration,
                    'monthly_installment'  => $monthlyInstallment,
                    'company_shares_count' => $sharesCount,
                    'start_date'           => $startDate->toDateString(),
                    'due_day'              => $dueDay,
                    'notes'                => $request->notes,
                    'status'               => 'active',
                ]);

                // إنشاء الأسهم وأقساط كل سهم
                foreach ($request->shares as $index => $shareData) {
                    $shareNumber = $index + 1;
                    $shareLabel = !empty($shareData['label']) ? trim($shareData['label']) : "الاسم " . $shareNumber;
                    $payoutMonth = !empty($shareData['payout_month'])
                        ? Carbon::parse($shareData['payout_month'] . '-01')->startOfMonth()
                        : $startDate->copy()->addMonths($index)->startOfMonth();

                    $share = JamiaShare::create([
                        'jamia_id'      => $jamia->id,
                        'share_number'  => $shareNumber,
                        'share_label'   => $shareLabel,
                        'payout_month'  => $payoutMonth->toDateString(),
                        'payout_amount' => $totalAmount,
                        'is_collected'  => false,
                    ]);

                    // توليد جدول أقساط هذا السهم على مدار أشهر الجمعية
                    for ($m = 0; $m < $duration; $m++) {
                        $installmentMonth = $startDate->copy()->addMonths($m)->startOfMonth();
                        JamiaInstallment::create([
                            'jamia_id'    => $jamia->id,
                            'share_id'    => $share->id,
                            'month_date'  => $installmentMonth->toDateString(),
                            'month_index' => $m + 1,
                            'amount'      => $monthlyInstallment,
                            'is_paid'     => false,
                            'is_waived'   => false,
                        ]);
                    }
                }

                $this->logActivity('create', 'jamia', "تم إنشاء جمعية جديدة: {$jamia->name} بقبض {$totalAmount} ج وبعدد {$sharesCount} أسهم للشركة.");
            });

            return back()->with('success', 'تمت إضافة الجمعية وتوليد جدول الأقساط والأسهم بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حفظ الجمعية: ' . $e->getMessage());
        }
    }

    /**
     * سداد قسط جمعية من محفظة/خزنة وخصمه من الرصيد والربط بسجل العمليات
     */
    public function payInstallment(Request $request)
    {
        $request->validate([
            'installment_id' => 'required|integer',
            'account_id'     => 'required|integer',
            'paid_at'        => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $installment = JamiaInstallment::where('id', $request->installment_id)->lockForUpdate()->first();
                if (!$installment) throw new \Exception('القسط غير موجود.');
                if ($installment->is_paid) throw new \Exception('هذا القسط مسدد بالفعل.');
                if ($installment->is_waived) throw new \Exception('هذا القسط معفي عنه بسبب التنازل.');

                $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                if (!$account) throw new \Exception('المحفظة / الخزنة غير موجودة.');
                if ($account->balance < $installment->amount) {
                    throw new \Exception("رصيد المحفظة ({$account->account_name}) غير كافٍ. الرصيد الحالي: " . number_format($account->balance, 2) . " ج.");
                }

                $jamia = DB::table('jamias')->where('id', $installment->jamia_id)->first();
                $share = DB::table('jamia_shares')->where('id', $installment->share_id)->first();

                // 1. خصم المبلغ من المحفظة
                DB::table('accounts')->where('id', $account->id)->decrement('balance', $installment->amount);

                // 2. تسجيل الحركة في financial_transactions (المصروفات)
                $monthStr = Carbon::parse($installment->month_date)->format('Y/m');
                $noteText = "سداد قسط شهر ({$monthStr}) - جمعية: {$jamia->name} - سهم: {$share->share_label}";
                if ($request->notes) {
                    $noteText .= " | " . trim($request->notes);
                }

                $txId = DB::table('financial_transactions')->insertGetId([
                    'type'            => 'expense',
                    'subtype'         => 'jamia_installment',
                    'amount'          => $installment->amount,
                    'from_account_id' => $account->id,
                    'to_account_id'   => null,
                    'ref_type'        => 'jamia_installment',
                    'ref_id'          => $installment->id,
                    'notes'           => $noteText,
                    'status'          => 'active',
                    'created_at'      => $request->paid_at ? Carbon::parse($request->paid_at) : now(),
                    'updated_at'      => now(),
                ]);

                // 3. تحديث القسط
                $installment->update([
                    'is_paid'         => true,
                    'paid_at'         => $request->paid_at ? Carbon::parse($request->paid_at) : now(),
                    'paid_account_id' => $account->id,
                    'tx_id'           => $txId,
                    'notes'           => $request->notes ? trim($request->notes) : $installment->notes,
                ]);

                $this->logActivity('pay', 'jamia', "سداد قسط جمعية: {$jamia->name} بمبلغ " . number_format($installment->amount, 2) . " ج من خزنة {$account->account_name}.");
            });

            return back()->with('success', 'تم سداد القسط بنجاح وخصم المبلغ من المحفظة وقيده في سجل العمليات.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * قبض سهم الجمعية وإيداعه في محفظة وقيده في سجل العمليات
     */
    public function collectPayout(Request $request)
    {
        $request->validate([
            'share_id'   => 'required|integer',
            'account_id' => 'required|integer',
            'amount'     => 'required|numeric|min:1',
            'collected_at' => 'nullable|date',
            'notes'      => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');
                if ($share->is_collected) throw new \Exception('هذا السهم مقبوض بالفعل.');
                if ($share->is_transferred) throw new \Exception('هذا السهم متنازل عنه، يتم استرداد حق الشركة منه عبر زر استرداد الحق.');

                $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                if (!$account) throw new \Exception('المحفظة / الخزنة غير موجودة.');

                $jamia = DB::table('jamias')->where('id', $share->jamia_id)->first();
                $amount = (float) $request->amount;

                // 1. زيادة رصيد المحفظة
                DB::table('accounts')->where('id', $account->id)->increment('balance', $amount);

                // 2. تسجيل الحركة في financial_transactions (الإيرادات/المقبوضات)
                $noteText = "قبض سهم جمعية: {$jamia->name} - سهم: {$share->share_label}";
                if ($request->notes) {
                    $noteText .= " | " . trim($request->notes);
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
                    'created_at'      => $request->collected_at ? Carbon::parse($request->collected_at) : now(),
                    'updated_at'      => now(),
                ]);

                // 3. تحديث السهم
                $share->update([
                    'is_collected'         => true,
                    'collected_at'         => $request->collected_at ? Carbon::parse($request->collected_at) : now(),
                    'collected_account_id' => $account->id,
                    'collected_tx_id'      => $txId,
                    'collected_amount'     => $amount,
                    'notes'                => $request->notes ? trim($request->notes) : $share->notes,
                ]);

                $this->logActivity('collect', 'jamia', "قبض سهم جمعية: {$jamia->name} بمبلغ " . number_format($amount, 2) . " ج وإيداعه في {$account->account_name}.");
            });

            return back()->with('success', 'تم تسجيل قبض الجمعية بنجاح وإيداع المبلغ في المحفظة وتوثيقه في سجل العمليات.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * التنازل عن سهم لطرف آخر في أي وقت (إسقاط الأقساط القادمة وحفظ حق الشركة في الشهور السابقة)
     */
    public function transferShare(Request $request)
    {
        $request->validate([
            'share_id'                => 'required|integer',
            'transferred_from_month'  => 'nullable|date',
            'transferred_to_name'     => 'nullable|string|max:255',
            'transferred_notes'       => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');
                if ($share->is_transferred) throw new \Exception('تم التنازل عن هذا السهم مسبقاً.');
                if ($share->is_collected) throw new \Exception('لا يمكن التنازل عن سهم تم قبضه بالفعل.');

                $jamia = DB::table('jamias')->where('id', $share->jamia_id)->first();

                // 1. حساب ما دفعته الشركة فعلياً لهذا السهم حتى الآن
                $paidInstallments = JamiaInstallment::where('share_id', $share->id)
                    ->where('is_paid', true)
                    ->get();
                $paidMonthsCount = $paidInstallments->count();
                $companyClaimAmount = (float) $paidInstallments->sum('amount');

                // 2. إسقاط الأقساط القادمة عن الشركة (ابتداءً من شهر التنازل أو كل الأقساط غير المسددة)
                $unpaidQuery = JamiaInstallment::where('share_id', $share->id)
                    ->where('is_paid', false);

                if ($request->transferred_from_month) {
                    $fromMonthDate = Carbon::parse($request->transferred_from_month)->startOfMonth()->toDateString();
                    $unpaidQuery->where('month_date', '>=', $fromMonthDate);
                }

                $unpaidQuery->update([
                    'is_waived'     => true,
                    'waived_reason' => 'تنازل عن السهم لطرف آخر' . ($request->transferred_to_name ? " ({$request->transferred_to_name})" : ''),
                ]);

                // 3. تحديث بيانات السهم كمتنازل عنه وحفظ حق الشركة
                $share->update([
                    'is_transferred'              => true,
                    'transferred_at'              => now(),
                    'transferred_from_month'      => $request->transferred_from_month ? Carbon::parse($request->transferred_from_month)->toDateString() : null,
                    'transferred_to_name'         => $request->transferred_to_name ? trim($request->transferred_to_name) : null,
                    'transferred_notes'           => $request->transferred_notes ? trim($request->transferred_notes) : null,
                    'paid_months_before_transfer' => $paidMonthsCount,
                    'company_claim_amount'        => $companyClaimAmount,
                ]);

                $this->logActivity('transfer', 'jamia', "تم التنازل عن سهم [{$share->share_label}] في جمعية {$jamia->name}. سددت الشركة {$paidMonthsCount} شهور بمبلغ " . number_format($companyClaimAmount, 2) . " ج مستحق استرداده عند القبض.");
            });

            return back()->with('success', 'تم تسجيل التنازل بنجاح، وإسقاط الأقساط القادمة عن الشركة مع تثبيت حق الشركة المسترد.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * استرداد حق الشركة من قبض السهم المتنازل عنه وإيداعه في المحفظة
     */
    public function recoverClaim(Request $request)
    {
        $request->validate([
            'share_id'     => 'required|integer',
            'account_id'   => 'required|integer',
            'amount'       => 'required|numeric|min:1',
            'recovered_at' => 'nullable|date',
            'notes'        => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');
                if (!$share->is_transferred) throw new \Exception('هذا السهم غير متنازل عنه.');
                if ($share->is_claim_recovered) throw new \Exception('تم استرداد حق الشركة من هذا السهم بالفعل.');

                $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                if (!$account) throw new \Exception('المحفظة / الخزنة غير موجودة.');

                $jamia = DB::table('jamias')->where('id', $share->jamia_id)->first();
                $amount = (float) $request->amount;

                // 1. زيادة رصيد المحفظة
                DB::table('accounts')->where('id', $account->id)->increment('balance', $amount);

                // 2. تسجيل الحركة في financial_transactions
                $noteText = "استرداد حق الشركة من قبض جمعية: {$jamia->name} (سهم متنازل عنه: {$share->share_label} - حق {$share->paid_months_before_transfer} شهور)";
                if ($request->notes) {
                    $noteText .= " | " . trim($request->notes);
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
                    'created_at'      => $request->recovered_at ? Carbon::parse($request->recovered_at) : now(),
                    'updated_at'      => now(),
                ]);

                // 3. تحديث السهم
                $share->update([
                    'is_claim_recovered'   => true,
                    'recovered_at'         => $request->recovered_at ? Carbon::parse($request->recovered_at) : now(),
                    'recovered_account_id' => $account->id,
                    'recovered_tx_id'      => $txId,
                    'recovered_amount'     => $amount,
                ]);

                $this->logActivity('recover', 'jamia', "استرداد حق الشركة من جمعية {$jamia->name} بمبلغ " . number_format($amount, 2) . " ج وإيداعه في {$account->account_name}.");
            });

            return back()->with('success', 'تم استرداد حق الشركة بنجاح وتوريد المبلغ في المحفظة وتوثيقه في سجل العمليات.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء سداد قسط (إرجاع المبلغ للمحفظة وإلغاء القيد)
     */
    public function cancelInstallmentPayment(Request $request)
    {
        $request->validate(['installment_id' => 'required|integer']);

        try {
            DB::transaction(function () use ($request) {
                $inst = JamiaInstallment::where('id', $request->installment_id)->lockForUpdate()->first();
                if (!$inst || !$inst->is_paid) throw new \Exception('القسط غير مسدد.');

                if ($inst->paid_account_id) {
                    DB::table('accounts')->where('id', $inst->paid_account_id)->increment('balance', $inst->amount);
                }

                if ($inst->tx_id) {
                    DB::table('financial_transactions')->where('id', $inst->tx_id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء سداد قسط جمعية',
                        'updated_at'    => now(),
                    ]);
                }

                $inst->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                ]);

                $this->logActivity('cancel', 'jamia', "إلغاء سداد قسط جمعية بقيمة " . number_format($inst->amount, 2) . " ج وإعادة المبلغ للمحفظة.");
            });

            return back()->with('success', 'تم إلغاء السداد بنجاح واسترجاع المبلغ للمحفظة.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء قبض سهم أو استرداد حق وإرجاع المبلغ للمحفظة
     */
    public function cancelPayout(Request $request)
    {
        $request->validate(['share_id' => 'required|integer']);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');

                // إلغاء القبض العادي
                if ($share->is_collected) {
                    if ($share->collected_account_id && $share->collected_amount > 0) {
                        $acc = DB::table('accounts')->where('id', $share->collected_account_id)->lockForUpdate()->first();
                        if ($acc && $acc->balance < $share->collected_amount) {
                            throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب عكسه.");
                        }
                        DB::table('accounts')->where('id', $share->collected_account_id)->decrement('balance', $share->collected_amount);
                    }

                    if ($share->collected_tx_id) {
                        DB::table('financial_transactions')->where('id', $share->collected_tx_id)->update([
                            'status'        => 'cancelled',
                            'cancelled_at'  => now(),
                            'cancel_reason' => 'إلغاء قبض سهم جمعية',
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
                }

                // إلغاء استرداد المقاصة
                if ($share->is_claim_recovered) {
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
                            'cancel_reason' => 'إلغاء استرداد حق سهم جمعية',
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
                }

                $this->logActivity('cancel_payout', 'jamia', "إلغاء قبض سهم جمعية [{$share->share_label}] وعكس الأثر المالي.");
            });

            return back()->with('success', 'تم إلغاء قبض السهم بنجاح وخصم المبلغ من المحفظة.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * مزامنة أي حركات تم إلغاؤها من سجل العمليات لضمان مطابقة الحالة
     */
    private function syncWithCancelledTransactions()
    {
        // 1. الأسهم المقبوضة التي أُلغيت حركتها
        $collectedShares = DB::table('jamia_shares')->where('is_collected', true)->whereNotNull('collected_tx_id')->get();
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
            }
        }

        // 2. الأسهم المسترد حقها التي أُلغيت حركتها
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

        // 3. الأقساط المسددة التي أُلغيت حركتها
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
     * طباعة كشف التدفقات لشهر محدد
     */
    public function printMonth(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $monthDate = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();

        $monthInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->leftJoin('accounts as a', 'ji.paid_account_id', '=', 'a.id')
            ->where('ji.month_date', $monthStart)
            ->where('ji.is_waived', false)
            ->select('ji.*', 'j.name as jamia_name', 'j.due_day', 'js.share_label', 'a.account_name as paid_account_name')
            ->orderBy('ji.is_paid')
            ->get();

        $monthShares = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->leftJoin('accounts as ca', 'js.collected_account_id', '=', 'ca.id')
            ->leftJoin('accounts as ra', 'js.recovered_account_id', '=', 'ra.id')
            ->where('js.payout_month', $monthStart)
            ->select('js.*', 'j.name as jamia_name', 'ca.account_name as collected_account_name', 'ra.account_name as recovered_account_name')
            ->get();

        $totalDue = $monthInstallments->sum('amount');
        $totalPaid = $monthInstallments->where('is_paid', true)->sum('amount');

        $totalInflow = 0;
        $totalCollected = 0;
        foreach ($monthShares as $s) {
            $amt = $s->is_transferred ? (float) $s->company_claim_amount : (float) $s->payout_amount;
            $totalInflow += $amt;
            if ($s->is_transferred && $s->is_claim_recovered) $totalCollected += (float) ($s->recovered_amount ?: $amt);
            if (!$s->is_transferred && $s->is_collected) $totalCollected += (float) ($s->collected_amount ?: $amt);
        }

        return view('cashflows_print', compact(
            'selectedMonth',
            'monthDate',
            'monthInstallments',
            'monthShares',
            'totalDue',
            'totalPaid',
            'totalInflow',
            'totalCollected'
        ));
    }

    /**
     * طباعة كشف تفصيلي لجمعية واحدة
     */
    public function printJamia($id)
    {
        $jamia = Jamia::with(['shares', 'installments.share'])->findOrFail($id);
        return view('cashflows_jamia_print', compact('jamia'));
    }

    /**
     * حذف جمعية بالكامل
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $jamia = Jamia::findOrFail($id);
                // تحقق هل هناك أقساط مدفوعة من محافظ
                $hasPaid = JamiaInstallment::where('jamia_id', $id)->where('is_paid', true)->exists();
                $hasCollected = JamiaShare::where('jamia_id', $id)->where(function($q) {
                    $q->where('is_collected', true)->orWhere('is_claim_recovered', true);
                })->exists();

                if ($hasPaid || $hasCollected) {
                    throw new \Exception('لا يمكن حذف جمعية تم سداد أقساط أو قبض مبالغ فيها بالفعل من المحافظ. يمكنك إلغاء العمليات أولاً إذا رغبت.');
                }

                $jamia->delete();
                $this->logActivity('delete', 'jamia', "تم حذف جمعية: {$jamia->name}.");
            });

            return back()->with('success', 'تم حذف الجمعية بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
