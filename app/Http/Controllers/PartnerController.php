<?php

namespace App\Http\Controllers;

use App\Services\InstallmentFinanceService;
use App\Services\PartnerProfitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends SystemController
{
    /**
     * لوحة الشركاء الرئيسية
     */
    public function index()
    {
        $summary = InstallmentFinanceService::treasurySummary();
        $totalCompanyCapital = (float) ($summary['capital'] ?? 0.0);
        $totalLiquidity = (float) ($summary['liquidity'] ?? 0.0);

        $activePartners = PartnerProfitService::regularPartnersQuery()
            ->where('status', 'active')
            ->get();
        $totalPartnersCapital = (float) $activePartners->sum('capital');
        $ahmedCapital = max(0.0, $totalCompanyCapital - $totalPartnersCapital);
        $ahmedSharePct = $totalCompanyCapital > 0 ? ($ahmedCapital / $totalCompanyCapital) * 100 : 100.0;

        // عرض جميع الشركاء في نفس الجدول بدون تقسيم صفحات
        $partners = PartnerProfitService::regularPartnersQuery()
            ->orderBy('status', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // حساب النسب التراكمية لكل شريك
        $partners->transform(function ($p) use ($totalCompanyCapital) {
            $cap = (float) $p->capital;
            $ahmedRatio = (float) ($p->ahmed_ratio ?? 30.00);
            $p->capital_share_pct = $totalCompanyCapital > 0 ? round(($cap / $totalCompanyCapital) * 100, 4) : 0.0;
            $p->effective_profit_pct = round($p->capital_share_pct * (1 - ($ahmedRatio / 100)), 4);
            $p->total_earned = (float) DB::table('partner_period_lines')->where('partner_id', $p->id)->sum('net_share');
            $p->total_withdrawn_profits = (float) DB::table('partner_period_lines')->where('partner_id', $p->id)->sum('withdrawn_amount');
            return $p;
        });

        // أرشيف التقفيلات: 10 دورات لكل صفحة
        $periods = DB::table('partner_periods')
            ->orderBy('end_date', 'desc')
            ->paginate(10, ['*'], 'periods_page');

        // الخزائن النشطة للإيداع والسحب
        $accounts = DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->get();

        // إجمالي أرباح أحمد التراكمية من الشركاء
        $totalAhmedCommissionsEarned = (float) DB::table('partner_periods')->where('status', 'closed')->sum('total_ahmed_commission');

        return view('partners.index', compact(
            'partners',
            'activePartners',
            'totalCompanyCapital',
            'totalPartnersCapital',
            'ahmedCapital',
            'ahmedSharePct',
            'totalLiquidity',
            'periods',
            'accounts',
            'totalAhmedCommissionsEarned'
        ));
    }

    /**
     * صفحة بروفايل الشريك الفردية والشارتات
     */
    public function show($id)
    {
        $partner = DB::table('partners')->where('id', $id)->first();
        if (!$partner) {
            return redirect()->route('partners.index')->with('error', 'الشريك غير موجود');
        }

        // رئيس مجلس الإدارة له شاشة حساب مستقلة
        if (!empty($partner->is_head) && $partner->status === 'active') {
            return redirect()->route('partners.ahmed');
        }

        $summary = InstallmentFinanceService::treasurySummary();
        $totalCompanyCapital = (float) ($summary['capital'] ?? 0.0);

        $cap = (float) $partner->capital;
        $ahmedRatio = (float) ($partner->ahmed_ratio ?? 30.00);
        $capitalSharePct = $totalCompanyCapital > 0 ? round(($cap / $totalCompanyCapital) * 100, 4) : 0.0;
        $effectiveProfitPct = round($capitalSharePct * (1 - ($ahmedRatio / 100)), 4);

        // سجل مشاركاته في التقفيلات السابقة (لكل الشارتات والإجماليات)
        $allLines = DB::table('partner_period_lines as l')
            ->join('partner_periods as p', 'l.period_id', '=', 'p.id')
            ->where('l.partner_id', $id)
            ->select('l.*', 'p.period_name', 'p.start_date', 'p.end_date', 'p.status as period_status')
            ->orderBy('p.end_date', 'asc')
            ->get();

        $totalNetEarned = (float) $allLines->sum('net_share');
        $totalWithdrawn = (float) $allLines->sum('withdrawn_amount');
        $totalReinvested = (float) $allLines->sum('reinvested_amount');

        // جدول التقفيلات مع تقسيم 10 لكل صفحة
        $lines = DB::table('partner_period_lines as l')
            ->join('partner_periods as p', 'l.period_id', '=', 'p.id')
            ->where('l.partner_id', $id)
            ->select('l.*', 'p.period_name', 'p.start_date', 'p.end_date', 'p.status as period_status')
            ->orderBy('p.end_date', 'desc')
            ->paginate(10, ['*'], 'lines_page');

        // الحركات المالية المباشرة (إيداعات وسحوبات) مقسمة 10 لكل صفحة
        $movements = DB::table('partner_movements as m')
            ->leftJoin('accounts as a', 'm.account_id', '=', 'a.id')
            ->where('m.partner_id', $id)
            ->select('m.*', 'a.account_name')
            ->orderBy('m.created_at', 'desc')
            ->paginate(10, ['*'], 'movements_page');

        // بيانات الشارتات: نمو رأس المال + الأرباح مقابل المسحوبات
        $chartLabels = [];
        $chartCapitalGrowth = [];
        $chartProfits = [];
        $chartWithdrawn = [];
        $chartShares = [];

        foreach ($allLines as $line) {
            $chartLabels[] = $line->period_name ?: $line->end_date;
            $chartCapitalGrowth[] = (float) $line->closing_capital;
            $chartProfits[] = (float) $line->net_share;
            $chartWithdrawn[] = (float) $line->withdrawn_amount;
            $chartShares[] = (float) $line->capital_share_pct;
        }

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        return view('partners.show', compact(
            'partner',
            'capitalSharePct',
            'effectiveProfitPct',
            'totalCompanyCapital',
            'lines',
            'movements',
            'totalNetEarned',
            'totalWithdrawn',
            'totalReinvested',
            'chartLabels',
            'chartCapitalGrowth',
            'chartProfits',
            'chartWithdrawn',
            'chartShares',
            'accounts'
        ));
    }

    /**
     * صفحة بروفايل أحمد (المؤسس) التفصيلية
     */
    public function ahmedProfile()
    {
        $summary = InstallmentFinanceService::treasurySummary();
        $totalCompanyCapital = (float) ($summary['capital'] ?? 0.0);

        $partners = PartnerProfitService::regularPartnersQuery()->where('status', 'active')->get();
        $totalPartnersCapital = (float) $partners->sum('capital');
        $ahmedCapital = max(0.0, $totalCompanyCapital - $totalPartnersCapital);
        $ahmedSharePct = $totalCompanyCapital > 0 ? round(($ahmedCapital / $totalCompanyCapital) * 100, 4) : 100.0;

        // سجل التقفيلات السابقة (للشارتات والمجاميع)
        $allPeriods = DB::table('partner_periods')
            ->where('status', 'closed')
            ->orderBy('end_date', 'asc')
            ->get();

        // التقفيلات مقسمة 10 لكل صفحة للجدول
        $periods = DB::table('partner_periods')
            ->where('status', 'closed')
            ->orderBy('end_date', 'desc')
            ->paginate(10, ['*'], 'periods_page');

        $chartLabels = [];
        $chartAhmedCapital = [];
        $chartAhmedCommissions = [];
        $chartAhmedOwnProfit = [];
        $chartAhmedTotalProfit = [];

        $totalCommissions = 0.0;
        $totalOwnProfits = 0.0;

        foreach ($allPeriods as $p) {
            $lines = DB::table('partner_period_lines')->where('period_id', $p->id)->get();
            $pPartnersCapital = (float) $lines->sum('opening_capital');
            $pAhmedCap = max(0.0, (float) $p->opening_capital - $pPartnersCapital);
            $ahmedShare = (float) $p->opening_capital > 0 ? ($pAhmedCap / (float) $p->opening_capital) : 1.0;
            $ahmedOwnProf = round((float) $p->gross_profit * $ahmedShare, 2);
            $ahmedComm = (float) $p->total_ahmed_commission;
            $ahmedTot = $ahmedOwnProf + $ahmedComm;

            $totalCommissions += $ahmedComm;
            $totalOwnProfits += $ahmedOwnProf;

            $chartLabels[] = $p->period_name ?: $p->end_date;
            $chartAhmedCapital[] = max(0.0, (float) $p->closing_capital - (float) $lines->sum('closing_capital'));
            $chartAhmedCommissions[] = $ahmedComm;
            $chartAhmedOwnProfit[] = $ahmedOwnProf;
            $chartAhmedTotalProfit[] = $ahmedTot;
        }

        return view('partners.ahmed', compact(
            'totalCompanyCapital',
            'ahmedCapital',
            'ahmedSharePct',
            'totalPartnersCapital',
            'partners',
            'periods',
            'totalCommissions',
            'totalOwnProfits',
            'chartLabels',
            'chartAhmedCapital',
            'chartAhmedCommissions',
            'chartAhmedOwnProfit',
            'chartAhmedTotalProfit'
        ));
    }

    /**
     * حفظ شريك جديد
     */
    public function storePartner(Request $request)
    {
        $isHead = $request->boolean('is_head');

        $request->validate([
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|string|max:50',
            'capital'               => ($isHead ? 'nullable' : 'required') . '|numeric|min:0',
            'ahmed_ratio'           => ($isHead ? 'nullable' : 'required') . '|numeric|min:0|max:100',
            'joined_at'             => 'required|date',
            'account_id'            => 'required_if:capital,>0|nullable|exists:accounts,id',
            'notes'                 => 'nullable|string',
        ], [
            'account_id.required_if' => 'يرجى تحديد الخزنة التي تم إيداع رأس المال فيها.',
        ]);

        try {
            $capital = (float) ($request->capital ?? 0);

            // رئيس مجلس إدارة واحد فقط في نفس الوقت
            if ($isHead) {
                DB::table('partners')->update(['is_head' => 0]);
            }

            $partnerId = DB::table('partners')->insertGetId([
                'name'                  => trim($request->name),
                'phone'                 => trim($request->phone ?? ''),
                'capital'               => 0, // سيتم زيادته عبر depositCapital للتأثير على الخزنة
                'initial_capital'       => $capital,
                'ahmed_ratio'           => $isHead ? 0 : (float) $request->ahmed_ratio,
                'default_payout_action' => 'reinvest',
                'joined_at'             => $request->joined_at,
                'status'                => 'active',
                'is_head'               => $isHead ? 1 : 0,
                'notes'                 => $request->notes,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            if ($capital > 0 && $request->filled('account_id')) {
                PartnerProfitService::depositCapital(
                    $partnerId,
                    $capital,
                    (int) $request->account_id,
                    "إيداع رأس مال تأسيسي للمساهم: " . trim($request->name),
                    session('auth_user')?->id ?? 1,
                    true
                );
            }

            if ($isHead) {
                return redirect()->route('partners.ahmed')->with('success', "تم تعيين «{$request->name}» رئيساً لمجلس الإدارة بنجاح.");
            }

            return redirect()->route('partners.show', $partnerId)->with('success', "تمت إضافة الشريك «{$request->name}» بنجاح وتم إيداع رأس ماله في الخزنة.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'حدث خطأ أثناء إضافة الشريك: ' . $e->getMessage());
        }
    }

    /**
     * تعديل بيانات الشريك
     */
    public function updatePartner(Request $request, $id)
    {
        $isHead = $request->boolean('is_head');

        $request->validate([
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|string|max:50',
            'ahmed_ratio'           => ($isHead ? 'nullable' : 'required') . '|numeric|min:0|max:100',
            'notes'                 => 'nullable|string',
        ]);

        try {
            if ($isHead) {
                DB::table('partners')->where('id', '!=', $id)->update(['is_head' => 0]);
            }

            DB::table('partners')->where('id', $id)->update([
                'name'                  => trim($request->name),
                'phone'                 => trim($request->phone ?? ''),
                'ahmed_ratio'           => $isHead ? 0 : (float) $request->ahmed_ratio,
                'is_head'               => $isHead ? 1 : 0,
                'notes'                 => $request->notes,
                'updated_at'            => now(),
            ]);

            return back()->with('success', 'تم تحديث بيانات المساهم بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', 'خطأ في التحديث: ' . $e->getMessage());
        }
    }

    /**
     * زيادة رأس مال شريك حالي (تودع في الخزنة)
     */
    public function addCapital(Request $request, $id)
    {
        $request->validate([
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'notes'      => 'nullable|string',
        ]);

        try {
            PartnerProfitService::depositCapital(
                (int) $id,
                (float) $request->amount,
                (int) $request->account_id,
                $request->notes,
                session('auth_user')?->id ?? 1,
                false
            );

            return back()->with('success', 'تمت إضافة المبلغ لرأس مال الشريك وتم تزويد رصيد الخزنة بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * سحب من رأس مال الشريك (يخصم من الخزنة)
     */
    public function withdrawCapital(Request $request, $id)
    {
        $request->validate([
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'notes'      => 'nullable|string',
        ]);

        try {
            if (DB::table('partners')->where('id', $id)->value('is_head')) {
                throw new \Exception('رأس مال رئيس مجلس الإدارة يُحسب تلقائياً (المتبقي بعد حصص الشركاء)، ويتم سحب أرباحه من شاشة إقفال الدورة.');
            }

            PartnerProfitService::withdrawCapital(
                (int) $id,
                (float) $request->amount,
                (int) $request->account_id,
                $request->notes,
                session('auth_user')?->id ?? 1,
                false
            );

            return back()->with('success', 'تم سحب المبلغ من رأس مال الشريك وخصمه من الخزنة بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * تخارج وتصفية شريك نهائياً
     */
    public function exitPartner(Request $request, $id)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'notes'      => 'nullable|string',
        ]);

        try {
            $partner = DB::table('partners')->where('id', $id)->first();
            if (!$partner) throw new \Exception('الشريك غير موجود');
            if (!empty($partner->is_head)) {
                throw new \Exception('لا يمكن تخارج رئيس مجلس الإدارة. قم أولاً بتعيين مساهم آخر رئيساً لمجلس الإدارة.');
            }

            $capital = (float) $partner->capital;
            if ($capital > 0) {
                PartnerProfitService::withdrawCapital(
                    (int) $id,
                    $capital,
                    (int) $request->account_id,
                    $request->notes ?: "تصفية وتخارج نهائي للشريك: {$partner->name}",
                    session('auth_user')?->id ?? 1,
                    true
                );
            } else {
                DB::table('partners')->where('id', $id)->update(['status' => 'exited', 'updated_at' => now()]);
            }

            return redirect()->route('partners.index')->with('success', "تمت تصفية الشريك «{$partner->name}» وتخارجه من الشركة بنجاح.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطأ في التخارج: ' . $e->getMessage());
        }
    }

    /**
     * شاشة إعداد وتقفيل الفترة الشهرية
     */
    public function closingView(Request $request)
    {
        // التواريخ الافتراضية للتقفيل
        $lastPeriod = DB::table('partner_periods')->where('status', 'closed')->orderBy('end_date', 'desc')->first();
        $defaultStartDate = $lastPeriod ? $lastPeriod->end_date : now()->startOfMonth()->format('Y-m-d');
        $defaultEndDate   = now()->format('Y-m-d');

        $startDate = $request->query('start_date', $defaultStartDate);
        $endDate   = $request->query('end_date', $defaultEndDate);

        // هل يرغب في أخذ لقطة لحظية الآن لرأس المال الختامي؟
        $useLiveCapital = $request->query('use_live', '1') === '1';
        $customClosingCapital = null;
        if ($useLiveCapital) {
            $summary = InstallmentFinanceService::treasurySummary();
            $customClosingCapital = (float) ($summary['capital'] ?? 0.0);
        }

        $preview = PartnerProfitService::previewClosing($startDate, $endDate, $customClosingCapital);

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        return view('partners.closing', compact('preview', 'accounts', 'startDate', 'endDate', 'useLiveCapital'));
    }

    /**
     * تنفيذ واعتماد تقفيل الفترة
     */
    public function storePeriod(Request $request)
    {
        $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'decisions'   => 'required|array',
            'notes'       => 'nullable|string',
        ]);

        try {
            $customClosingCapital = $request->filled('closing_capital') ? (float) $request->closing_capital : null;
            $preview = PartnerProfitService::previewClosing($request->start_date, $request->end_date, $customClosingCapital);

            $periodId = PartnerProfitService::executeClosing(
                $preview,
                $request->decisions,
                $request->period_name,
                $request->notes,
                session('auth_user')?->id ?? 1
            );

            return redirect()->route('partners.period.show', $periodId)->with('success', "تم اعتماد تقفيل الفترة «{$request->period_name}» وتوزيع الحصص بنجاح.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'حدث خطأ أثناء اعتماد التقفيل: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل تقفيل فترة معتمدة سابقة
     */
    public function showPeriod($id)
    {
        $period = DB::table('partner_periods')->where('id', $id)->first();
        if (!$period) {
            return redirect()->route('partners.index')->with('error', 'التقفيلة غير موجودة');
        }

        $lines = DB::table('partner_period_lines as l')
            ->join('partners as p', 'l.partner_id', '=', 'p.id')
            ->leftJoin('accounts as a', 'l.payout_account_id', '=', 'a.id')
            ->where('l.period_id', $id)
            ->select('l.*', 'p.name as partner_name', 'p.phone as partner_phone', 'a.account_name as payout_account_name')
            ->get();

        $ahmedPayoutAccountName = null;
        if (!empty($period->ahmed_payout_account_id)) {
            $ahmedAcc = DB::table('accounts')->where('id', $period->ahmed_payout_account_id)->first();
            $ahmedPayoutAccountName = $ahmedAcc?->account_name;
        }

        return view('partners.period_show', compact('period', 'lines', 'ahmedPayoutAccountName'));
    }
}
