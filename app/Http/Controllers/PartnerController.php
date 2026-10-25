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
        $totalCompanyCapital = round((float) ($summary['capital'] ?? 0.0), 2);
        $totalLiquidity = round((float) ($summary['liquidity'] ?? 0.0), 2);

        // الحصص المعتمدة للشركاء وحساب الإدارة (ثابتة ولا تتأثر بتقلبات الربح اللحظي إلا عند الإقفال أو الإيداع/السحب المباشر)
        $sharesSummary = PartnerProfitService::approvedSharesSummary();
        $activePartners       = $sharesSummary['active_partners'];
        $totalPartnersCapital = $sharesSummary['total_partners_capital'];
        $ahmedCapital         = $sharesSummary['ahmed_capital'];
        $totalApprovedCapital = $sharesSummary['total_approved_capital'];
        $ahmedSharePct        = $sharesSummary['ahmed_share_pct'];
        $partnersSharePct     = $sharesSummary['partners_share_pct'];

        // عرض جميع الشركاء في نفس الجدول بدون تقسيم صفحات
        $partners = PartnerProfitService::regularPartnersQuery()
            ->orderBy('status', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // حساب النسب المعتمدة لكل شريك من إجمالي الحصص المعتمدة (وليس من رأس مال الشركة اللحظي المتغير)
        $partners->transform(function ($p) use ($totalApprovedCapital) {
            $cap = (float) $p->capital;
            $ahmedRatio = (float) ($p->ahmed_ratio ?? 30.00);
            $p->capital_share_pct = ($p->status === 'active' && $totalApprovedCapital > 0)
                ? round(($cap / $totalApprovedCapital) * 100, 4)
                : 0.0;
            $p->effective_profit_pct = round($p->capital_share_pct * (1 - ($ahmedRatio / 100)), 4);
            $p->total_earned = (float) DB::table('partner_period_lines')->where('partner_id', $p->id)->sum('net_share');
            $p->total_withdrawn_profits = (float) DB::table('partner_period_lines')->where('partner_id', $p->id)->sum('withdrawn_amount');
            return $p;
        });

        // أرشيف التقفيلات: 10 دورات لكل صفحة
        $periods = DB::table('partner_periods')
            ->orderBy('end_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'periods_page');

        // سجل تاريخ تغير النسب (النسب كانت كام وبقت كام)
        $shareHistories = DB::table('partner_share_histories')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'history_page');

        $historyIds = $shareHistories->pluck('id')->all();
        $historyLinesGrouped = collect();
        if (!empty($historyIds)) {
            $historyLinesGrouped = DB::table('partner_share_history_lines')
                ->whereIn('history_id', $historyIds)
                ->orderByDesc('is_head')
                ->orderBy('partner_id', 'asc')
                ->get()
                ->groupBy('history_id');
        }

        // الخزائن النشطة للإيداع والسحب
        $accounts = DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->get();

        // إجمالي أرباح حساب الإدارة التراكمية من نسب الشغل + حصة الرصيد المدور
        $totalAhmedCommissionsEarned = (float) DB::table('partner_periods')->where('status', 'closed')->sum('total_ahmed_commission');
        $totalAhmedOwnProfitEarned   = (float) DB::table('partner_periods')->where('status', 'closed')->sum('ahmed_own_profit');
        $totalAhmedTotalEarned       = round($totalAhmedCommissionsEarned + $totalAhmedOwnProfitEarned, 2);
        $totalAhmedWithdrawn         = (float) DB::table('partner_periods')->where('status', 'closed')->sum('ahmed_withdrawn_amount');

        // معاينة سريعة لصافي ربح الدورة الحالية غير المقفلة (تحت التوزيع لحين الإقفال)
        $lastPeriod = DB::table('partner_periods')->where('status', 'closed')->orderBy('end_date', 'desc')->orderBy('id', 'desc')->first();
        $defaultStartDate = $lastPeriod ? $lastPeriod->end_date : now()->startOfMonth()->format('Y-m-d');
        $livePreview = PartnerProfitService::previewClosing($defaultStartDate, now()->format('Y-m-d'), $totalCompanyCapital);
        $unclosedPeriodProfit = (float) ($livePreview['gross_profit'] ?? 0.0);

        return view('partners.index', compact(
            'partners',
            'activePartners',
            'totalCompanyCapital',
            'totalApprovedCapital',
            'totalPartnersCapital',
            'ahmedCapital',
            'ahmedSharePct',
            'partnersSharePct',
            'totalLiquidity',
            'periods',
            'shareHistories',
            'historyLinesGrouped',
            'accounts',
            'totalAhmedCommissionsEarned',
            'totalAhmedOwnProfitEarned',
            'totalAhmedTotalEarned',
            'totalAhmedWithdrawn',
            'unclosedPeriodProfit'
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

        // حساب الإدارة له شاشة مستقلة
        if (!empty($partner->is_head) && $partner->status === 'active') {
            return redirect()->route('partners.ahmed');
        }

        $summary = InstallmentFinanceService::treasurySummary();
        $totalCompanyCapital = round((float) ($summary['capital'] ?? 0.0), 2);

        $sharesSummary = PartnerProfitService::approvedSharesSummary();
        $totalApprovedCapital = $sharesSummary['total_approved_capital'];

        $cap = (float) $partner->capital;
        $ahmedRatio = (float) ($partner->ahmed_ratio ?? 30.00);
        $capitalSharePct = ($partner->status === 'active' && $totalApprovedCapital > 0)
            ? round(($cap / $totalApprovedCapital) * 100, 4)
            : 0.0;
        $effectiveProfitPct = round($capitalSharePct * (1 - ($ahmedRatio / 100)), 4);

        // سجل مشاركاته في التقفيلات السابقة (لكل الشارتات والإجماليات)
        $allLines = DB::table('partner_period_lines as l')
            ->join('partner_periods as p', 'l.period_id', '=', 'p.id')
            ->where('l.partner_id', $id)
            ->select('l.*', 'p.period_name', 'p.start_date', 'p.end_date', 'p.status as period_status')
            ->orderBy('p.end_date', 'asc')
            ->orderBy('p.id', 'asc')
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
            ->orderBy('p.id', 'desc')
            ->paginate(10, ['*'], 'lines_page');

        // الحركات المالية المباشرة (إيداعات وسحوبات) مقسمة 10 لكل صفحة
        $movements = DB::table('partner_movements as m')
            ->leftJoin('accounts as a', 'm.account_id', '=', 'a.id')
            ->where('m.partner_id', $id)
            ->select('m.*', 'a.account_name')
            ->orderBy('m.created_at', 'desc')
            ->orderBy('m.id', 'desc')
            ->paginate(10, ['*'], 'movements_page');

        // سجل تاريخ تغير نسبة هذا الشريك (النسبة كانت كام وبقت كام)
        $shareHistoryLines = DB::table('partner_share_history_lines as hl')
            ->join('partner_share_histories as h', 'hl.history_id', '=', 'h.id')
            ->where('hl.partner_id', $id)
            ->select(
                'hl.*',
                'h.event_type',
                'h.title as event_title',
                'h.total_capital_before',
                'h.total_capital_after',
                'h.notes as event_notes',
                'h.created_at as event_date'
            )
            ->orderBy('h.created_at', 'desc')
            ->orderBy('h.id', 'desc')
            ->paginate(10, ['*'], 'share_history_page');

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
            $chartShares[] = (float) ($line->closing_share_pct > 0 ? $line->closing_share_pct : $line->capital_share_pct);
        }

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        return view('partners.show', compact(
            'partner',
            'capitalSharePct',
            'effectiveProfitPct',
            'totalCompanyCapital',
            'totalApprovedCapital',
            'lines',
            'movements',
            'shareHistoryLines',
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
     * صفحة حساب الإدارة التفصيلية (أرباح نسب الشغل والحصة المدورة)
     */
    public function ahmedProfile()
    {
        $summary = InstallmentFinanceService::treasurySummary();
        $totalCompanyCapital = round((float) ($summary['capital'] ?? 0.0), 2);

        $sharesSummary = PartnerProfitService::approvedSharesSummary();
        $partners             = $sharesSummary['active_partners'];
        $totalPartnersCapital = $sharesSummary['total_partners_capital'];
        $ahmedCapital         = $sharesSummary['ahmed_capital'];
        $totalApprovedCapital = $sharesSummary['total_approved_capital'];
        $ahmedSharePct        = $sharesSummary['ahmed_share_pct'];
        $headPartner          = $sharesSummary['head_partner'];

        // سجل التقفيلات السابقة (للشارتات والمجاميع)
        $allPeriods = DB::table('partner_periods')
            ->where('status', 'closed')
            ->orderBy('end_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // التقفيلات مقسمة 10 لكل صفحة للجدول
        $periods = DB::table('partner_periods')
            ->where('status', 'closed')
            ->orderBy('end_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'periods_page');

        // سجل تغير نسبة حساب الإدارة (كانت كام وبقت كام)
        $shareHistoryLines = collect();
        if ($headPartner) {
            $shareHistoryLines = DB::table('partner_share_history_lines as hl')
                ->join('partner_share_histories as h', 'hl.history_id', '=', 'h.id')
                ->where('hl.partner_id', $headPartner->id)
                ->select(
                    'hl.*',
                    'h.event_type',
                    'h.title as event_title',
                    'h.total_capital_before',
                    'h.total_capital_after',
                    'h.notes as event_notes',
                    'h.created_at as event_date'
                )
                ->orderBy('h.created_at', 'desc')
                ->orderBy('h.id', 'desc')
                ->paginate(10, ['*'], 'share_history_page');
        }

        $chartLabels = [];
        $chartAhmedCapital = [];
        $chartAhmedCommissions = [];
        $chartAhmedOwnProfit = [];
        $chartAhmedTotalProfit = [];

        $totalCommissions = 0.0;
        $totalOwnProfits = 0.0;

        foreach ($allPeriods as $p) {
            $ahmedOwnProf = (float) ($p->ahmed_own_profit ?? 0.0);
            $ahmedComm    = (float) ($p->total_ahmed_commission ?? 0.0);
            $ahmedTot     = (float) ($p->ahmed_total_profit ?: ($ahmedOwnProf + $ahmedComm));
            $ahmedClosCap = (float) ($p->ahmed_closing_capital ?? 0.0);

            $totalCommissions += $ahmedComm;
            $totalOwnProfits  += $ahmedOwnProf;

            $chartLabels[] = $p->period_name ?: $p->end_date;
            $chartAhmedCapital[] = $ahmedClosCap;
            $chartAhmedCommissions[] = $ahmedComm;
            $chartAhmedOwnProfit[] = $ahmedOwnProf;
            $chartAhmedTotalProfit[] = $ahmedTot;
        }

        return view('partners.ahmed', compact(
            'totalCompanyCapital',
            'totalApprovedCapital',
            'ahmedCapital',
            'ahmedSharePct',
            'totalPartnersCapital',
            'partners',
            'periods',
            'shareHistoryLines',
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

            // حساب إدارة واحد فقط في نفس الوقت
            if ($isHead) {
                DB::table('partners')->update(['is_head' => 0]);
            }

            $partnerId = DB::table('partners')->insertGetId([
                'name'                  => trim($request->name),
                'phone'                 => trim($request->phone ?? ''),
                'capital'               => 0, // سيتم زيادته عبر depositCapital للتأثير على الخزنة وتسجيل هيستوري النسب
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
                return redirect()->route('partners.ahmed')->with('success', "تم تعيين «{$request->name}» كحساب للإدارة بنجاح.");
            }

            return redirect()->route('partners.show', $partnerId)->with('success', "تمت إضافة الشريك «{$request->name}» بنجاح وتم تحديث النسب وتسجيلها في الهيستوري.");
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

            return back()->with('success', 'تم تحديث البيانات بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', 'خطأ في التحديث: ' . $e->getMessage());
        }
    }

    /**
     * زيادة رأس مال شريك حالي (تودع في الخزنة وتحدث النسب والهيستوري)
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

            return back()->with('success', 'تمت إضافة المبلغ وتزويد الخزنة، وتم تحديث نسب المساهمة وتسجيلها في سجل الهيستوري.');
        } catch (\Throwable $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * سحب من رصيد / رأس مال الشريك (يخصم من الخزنة ويحدث النسب والهيستوري)
     */
    public function withdrawCapital(Request $request, $id)
    {
        $request->validate([
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'notes'      => 'nullable|string',
        ]);

        try {
            PartnerProfitService::withdrawCapital(
                (int) $id,
                (float) $request->amount,
                (int) $request->account_id,
                $request->notes,
                session('auth_user')?->id ?? 1,
                false
            );

            return back()->with('success', 'تم سحب المبلغ وخصمه من الخزنة، وتم تحديث نسب المساهمة وتسجيلها في سجل الهيستوري.');
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
                throw new \Exception('لا يمكن تصفية حساب الإدارة الأساسي.');
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

            return redirect()->route('partners.index')->with('success', "تمت تصفية الشريك «{$partner->name}» وتخارجه من الشركة وتحديث النسب بنجاح.");
        } catch (\Throwable $e) {
            return back()->with('error', 'خطأ في التخارج: ' . $e->getMessage());
        }
    }

    /**
     * شاشة إعداد وتقفيل الفترة الشهرية (مقفولة تلقائياً على رأس مال آخر إقفال حتى اللحظة الحالية)
     */
    public function closingView(Request $request)
    {
        $lastPeriod = DB::table('partner_periods')->where('status', 'closed')->orderBy('id', 'desc')->first();
        $startDate  = $lastPeriod ? $lastPeriod->end_date : now()->startOfMonth()->format('Y-m-d');
        $endDate    = now()->format('Y-m-d');

        $summary = InstallmentFinanceService::treasurySummary();
        $liveClosingCapital = (float) ($summary['capital'] ?? 0.0);

        $preview = PartnerProfitService::previewClosing($startDate, $endDate, $liveClosingCapital);

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        return view('partners.closing', compact('preview', 'accounts', 'startDate', 'endDate', 'lastPeriod'));
    }

    /**
     * تنفيذ واعتماد تقفيل الفترة
     */
    public function storePeriod(Request $request)
    {
        $request->validate([
            'period_name' => 'required|string|max:255',
            'decisions'   => 'required|array',
            'notes'       => 'nullable|string',
        ]);

        try {
            $lastPeriod = DB::table('partner_periods')->where('status', 'closed')->orderBy('id', 'desc')->first();
            $startDate  = $lastPeriod ? $lastPeriod->end_date : now()->startOfMonth()->format('Y-m-d');
            $endDate    = now()->format('Y-m-d');

            $summary = InstallmentFinanceService::treasurySummary();
            $liveClosingCapital = (float) ($summary['capital'] ?? 0.0);

            $preview = PartnerProfitService::previewClosing($startDate, $endDate, $liveClosingCapital);

            $periodId = PartnerProfitService::executeClosing(
                $preview,
                $request->decisions,
                $request->period_name,
                $request->notes,
                session('auth_user')?->id ?? 1
            );

            return redirect()->route('partners.period.show', $periodId)->with('success', "تم اعتماد تقفيل الفترة «{$request->period_name}» وتوزيع الأرباح وتحديث سجل النسب بنجاح.");
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
            ->orderBy('l.partner_id', 'asc')
            ->get();

        $ahmedPayoutAccountName = null;
        if (!empty($period->ahmed_payout_account_id)) {
            $ahmedAcc = DB::table('accounts')->where('id', $period->ahmed_payout_account_id)->first();
            $ahmedPayoutAccountName = $ahmedAcc?->account_name;
        }

        return view('partners.period_show', compact('period', 'lines', 'ahmedPayoutAccountName'));
    }
}
