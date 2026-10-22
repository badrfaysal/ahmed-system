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

        $partners = DB::table('partners')
            ->orderBy('status', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $activePartners = $partners->where('status', 'active');
        $totalPartnersCapital = (float) $activePartners->sum('capital');
        $ahmedCapital = max(0.0, $totalCompanyCapital - $totalPartnersCapital);
        $ahmedSharePct = $totalCompanyCapital > 0 ? ($ahmedCapital / $totalCompanyCapital) * 100 : 100.0;

        // حساب النسب التراكمية لكل شريك
        $partners = $partners->map(function ($p) use ($totalCompanyCapital) {
            $cap = (float) $p->capital;
            $ahmedRatio = (float) ($p->ahmed_ratio ?? 30.00);
            $p->capital_share_pct = $totalCompanyCapital > 0 ? round(($cap / $totalCompanyCapital) * 100, 4) : 0.0;
            $p->effective_profit_pct = round($p->capital_share_pct * (1 - ($ahmedRatio / 100)), 4);
            $p->total_earned = (float) DB::table('partner_period_lines')->where('partner_id', $p->id)->sum('net_share');
            $p->total_withdrawn_profits = (float) DB::table('partner_period_lines')->where('partner_id', $p->id)->sum('withdrawn_amount');
            return $p;
        });

        // آخر التقفيلات المسجلة
        $periods = DB::table('partner_periods')
            ->orderBy('end_date', 'desc')
            ->limit(12)
            ->get();

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

        $summary = InstallmentFinanceService::treasurySummary();
        $totalCompanyCapital = (float) ($summary['capital'] ?? 0.0);

        $cap = (float) $partner->capital;
        $ahmedRatio = (float) ($partner->ahmed_ratio ?? 30.00);
        $capitalSharePct = $totalCompanyCapital > 0 ? round(($cap / $totalCompanyCapital) * 100, 4) : 0.0;
        $effectiveProfitPct = round($capitalSharePct * (1 - ($ahmedRatio / 100)), 4);

        // سجل مشاركاته في التقفيلات السابقة
        $lines = DB::table('partner_period_lines as l')
            ->join('partner_periods as p', 'l.period_id', '=', 'p.id')
            ->where('l.partner_id', $id)
            ->select('l.*', 'p.period_name', 'p.start_date', 'p.end_date', 'p.status as period_status')
            ->orderBy('p.end_date', 'asc')
            ->get();

        $totalNetEarned = (float) $lines->sum('net_share');
        $totalWithdrawn = (float) $lines->sum('withdrawn_amount');
        $totalReinvested = (float) $lines->sum('reinvested_amount');

        // الحركات المالية المباشرة (إيداعات وسحوبات)
        $movements = DB::table('partner_movements as m')
            ->leftJoin('accounts as a', 'm.account_id', '=', 'a.id')
            ->where('m.partner_id', $id)
            ->select('m.*', 'a.account_name')
            ->orderBy('m.created_at', 'desc')
            ->get();

        // بيانات الشارتات: نمو رأس المال + الأرباح مقابل المسحوبات
        $chartLabels = [];
        $chartCapitalGrowth = [];
        $chartProfits = [];
        $chartWithdrawn = [];
        $chartShares = [];

        foreach ($lines as $line) {
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

        $partners = DB::table('partners')->where('status', 'active')->get();
        $totalPartnersCapital = (float) $partners->sum('capital');
        $ahmedCapital = max(0.0, $totalCompanyCapital - $totalPartnersCapital);
        $ahmedSharePct = $totalCompanyCapital > 0 ? round(($ahmedCapital / $totalCompanyCapital) * 100, 4) : 100.0;

        // سجل التقفيلات السابقة لمعرفة أرباح أحمد وعمولاته
        $periods = DB::table('partner_periods')
            ->where('status', 'closed')
            ->orderBy('end_date', 'asc')
            ->get();

        $chartLabels = [];
        $chartAhmedCapital = [];
        $chartAhmedCommissions = [];
        $chartAhmedOwnProfit = [];
        $chartAhmedTotalProfit = [];

        $totalCommissions = 0.0;
        $totalOwnProfits = 0.0;

        foreach ($periods as $p) {
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
        $request->validate([
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|string|max:50',
            'capital'               => 'required|numeric|min:0',
            'ahmed_ratio'           => 'required|numeric|min:0|max:100',
            'joined_at'             => 'required|date',
            'account_id'            => 'required_if:capital,>0|nullable|exists:accounts,id',
            'notes'                 => 'nullable|string',
        ], [
            'account_id.required_if' => 'يرجى تحديد الخزنة التي تم إيداع رأس المال فيها.',
        ]);

        try {
            $capital = (float) $request->capital;
            $partnerId = DB::table('partners')->insertGetId([
                'name'                  => trim($request->name),
                'phone'                 => trim($request->phone ?? ''),
                'capital'               => 0, // سيتم زيادته عبر depositCapital للتأثير على الخزنة
                'initial_capital'       => $capital,
                'ahmed_ratio'           => (float) $request->ahmed_ratio,
                'default_payout_action' => 'reinvest',
                'joined_at'             => $request->joined_at,
                'status'                => 'active',
                'notes'                 => $request->notes,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            if ($capital > 0 && $request->filled('account_id')) {
                PartnerProfitService::depositCapital(
                    $partnerId,
                    $capital,
                    (int) $request->account_id,
                    "إيداع رأس مال تأسيسي للشريك: " . trim($request->name),
                    session('auth_user')?->id ?? 1,
                    true
                );
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
        $request->validate([
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|string|max:50',
            'ahmed_ratio'           => 'required|numeric|min:0|max:100',
            'notes'                 => 'nullable|string',
        ]);

        try {
            DB::table('partners')->where('id', $id)->update([
                'name'                  => trim($request->name),
                'phone'                 => trim($request->phone ?? ''),
                'ahmed_ratio'           => (float) $request->ahmed_ratio,
                'notes'                 => $request->notes,
                'updated_at'            => now(),
            ]);

            return back()->with('success', 'تم تحديث بيانات الشريك بنجاح.');
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
        $defaultStartDate = $lastPeriod ? $lastPeriod->end_date : '2026-09-25';
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

        return view('partners.period_show', compact('period', 'lines'));
    }
}
