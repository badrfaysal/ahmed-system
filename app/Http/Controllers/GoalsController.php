<?php
// ════════════════════════════════════════════════════════
// GoalsController
// الملف: app/Http/Controllers/GoalsController.php
// ════════════════════════════════════════════════════════

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoalsController extends SystemController
{
    // ══════════════════════════════════════════════════════
    // الفئات المتاحة — تُقرأ ديناميكياً من السيستم
    // ══════════════════════════════════════════════════════

    /**
     * يرجع كل مصادر الإيراد والمصروف المتاحة من السيستم
     * لو أضيف مصدر جديد في FinanceController يظهر هنا تلقائياً
     */
    public static function getAvailableSources(): array
    {
        // ── مصادر الإيراد الثابتة (مطابقة لـ FinanceController) ──
        $incomeSources = [
            ['key' => 'installments',    'label' => 'أرباح التقسيط (الفوائد)',       'icon' => 'fa-file-contract',     'color' => '#1a56db'],
            ['key' => 'inventory',       'label' => 'أرباح مبيعات المخزن',           'icon' => 'fa-warehouse',          'color' => '#059669'],
            ['key' => 'direct_sales',    'label' => 'أرباح البيع المباشر',           'icon' => 'fa-shopping-cart',     'color' => '#7c3aed'],
            ['key' => 'services',        'label' => 'أرباح الخدمات (صيانة/تركيب)',  'icon' => 'fa-tools',             'color' => '#d97706'],
            ['key' => 'gas',             'label' => 'أرباح محطة الوقود',             'icon' => 'fa-gas-pump',           'color' => '#dc2626'],
            ['key' => 'asset_sales',     'label' => 'أرباح بيع الأصول',             'icon' => 'fa-landmark',           'color' => '#0891b2'],
        ];

        // ── مصادر المصروف ──
        $expenseSources = [
            ['key' => 'expense_general',  'label' => 'المصاريف التشغيلية والعامة',  'icon' => 'fa-receipt',           'color' => '#ef4444'],
            ['key' => 'expense_salaries', 'label' => 'رواتب الموظفين',              'icon' => 'fa-users',             'color' => '#f59e0b'],
            ['key' => 'expense_total',    'label' => 'إجمالي المصروفات',            'icon' => 'fa-money-bill-wave',   'color' => '#dc2626'],
        ];

        // ── مصادر ديناميكية من expense_categories (لو أضاف المستخدم فئات جديدة) ──
        $dynamicExpenseCategories = DB::table('expense_categories')
            ->orderBy('name')
            ->get(['id', 'name']);

        foreach ($dynamicExpenseCategories as $cat) {
            $key = 'expense_cat_' . $cat->id;
            // تجنب التكرار
            $exists = collect($expenseSources)->where('key', $key)->isNotEmpty();
            if (!$exists) {
                $expenseSources[] = [
                    'key'   => $key,
                    'label' => 'مصروف: ' . $cat->name,
                    'icon'  => 'fa-tag',
                    'color' => '#64748b',
                ];
            }
        }

        return [
            'income'  => $incomeSources,
            'expense' => $expenseSources,
        ];
    }

    // ══════════════════════════════════════════════════════
    // حساب القيمة الفعلية لأي مصدر في فترة زمنية محددة
    // ══════════════════════════════════════════════════════

    public static function calculateActual(string $sourceKey, Carbon $from, Carbon $to): float
    {
        $applyDate = function ($query) use ($from, $to) {
            return $query->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()]);
        };

        switch ($sourceKey) {

            // ── إيرادات ──
            case 'installments':
                return (float) $applyDate(DB::table('installments'))
                    ->where('installment_months', '>', 0)
                    ->where(function ($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
                    ->selectRaw('COALESCE(SUM(GREATEST(0, total_after_interest - (cash_price - COALESCE(`discount`,0)))), 0) as total')
                    ->value('total');

            case 'inventory':
                return (float) $applyDate(DB::table('financial_transactions'))
                    ->where('type', 'income')->where('status', 'active')
                    ->where(function ($q) {
                        $q->where('notes', 'like', '%مخزن%')
                          ->orWhere('notes', 'like', '%ربح مبيعات مباشرة%');
                    })->sum('amount');

            case 'direct_sales':
                return (float) $applyDate(DB::table('installments'))
                    ->where('installment_months', 0)->where('category', 'مبيعات مباشرة')
                    ->sum('profit');

            case 'services':
                return (float) $applyDate(DB::table('financial_transactions'))
                    ->where('type', 'income')->where('status', 'active')
                    ->where(function ($q) {
                        $q->where('notes', 'like', '%خدمة%')
                          ->orWhere('notes', 'like', '%صيانة%')
                          ->orWhere('notes', 'like', '%تركيب%');
                    })->sum('amount');

            case 'gas':
                return (float) $applyDate(DB::table('fuel_transactions'))->sum('ahmed_profit');

            case 'asset_sales':
                // ⚡ subtype مع index بدل LIKE '%...%' الـ full-scan
                return (float) $applyDate(DB::table('financial_transactions'))
                    ->where('type', 'income')->where('status', 'active')
                    ->where('subtype', 'asset_sale_gain')->sum('amount');

            // ── مصروفات ──
            case 'expense_general':
                return (float) $applyDate(DB::table('financial_transactions'))
                    ->where('type', 'general_expense')->where('status', 'active')->whereNull('person_name')
                    ->where('notes', 'not like', '%إعدام ديون%')
                    ->where('notes', 'not like', '%عمولة تلقائية%')
                    ->where('notes', 'not like', '%إهلاك أصل ثابت%')
                    ->where('notes', 'not like', '%راتب%')
                    ->where('notes', 'not like', '%بنزينة%')
                    ->sum('amount');

            case 'expense_salaries':
                return (float) $applyDate(DB::table('financial_transactions'))
                    ->where('type', 'salary_expense')->where('status', 'active')->sum('amount');

            case 'expense_total':
                $gen = self::calculateActual('expense_general', $from, $to);
                $sal = self::calculateActual('expense_salaries', $from, $to);
                return $gen + $sal;

            default:
                // مصروف ديناميكي من expense_categories
                if (str_starts_with($sourceKey, 'expense_cat_')) {
                    $catId = (int) str_replace('expense_cat_', '', $sourceKey);
                    $cat   = DB::table('expense_categories')->find($catId);
                    if ($cat) {
                        return (float) $applyDate(DB::table('financial_transactions'))
                            ->where('type', 'general_expense')->where('status', 'active')
                            ->where('notes', 'like', '%[' . $cat->name . ']%')
                            ->sum('amount');
                    }
                }
                return 0.0;
        }
    }

    // ══════════════════════════════════════════════════════
    // عرض الصفحة الرئيسية
    // ══════════════════════════════════════════════════════

    public function index()
    {
        // إغلاق الأهداف المنتهية تلقائياً
        $this->autoCloseExpiredGoals();

        $sources  = self::getAvailableSources();
        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        // الأهداف النشطة مع حساب التقدم الحالي
        $activeGoals = DB::table('goals')
            ->where('status', 'active')
            ->orderBy('end_date')
            ->get()
            ->map(function ($goal) {
                $from   = Carbon::parse($goal->start_date);
                $to     = Carbon::parse($goal->end_date);
                $actual = self::calculateActual($goal->source_key, $from, $to);
                $pct    = $goal->target_amount > 0
                    ? min(999, round(($actual / $goal->target_amount) * 100, 1))
                    : 0;

                $daysTotal     = max(1, $from->diffInDays($to));
                $daysElapsed   = min($daysTotal, Carbon::today()->diffInDays($from));
                $daysRemaining = max(0, Carbon::today()->diffInDays($to, false));

                return (object) array_merge((array) $goal, [
                    'actual_amount'  => $actual,
                    'actual_pct'     => $pct,
                    'days_remaining' => $daysRemaining,
                    'days_total'     => $daysTotal,
                    'time_pct'       => min(100, round(($daysElapsed / $daysTotal) * 100)),
                    'surplus'        => max(0, $actual - $goal->target_amount),
                    'shortage'       => max(0, $goal->target_amount - $actual),
                ]);
            });

        // الأهداف المنتهية (محققة + فاشلة)
        $closedGoals = DB::table('goals')
            ->whereIn('status', ['achieved', 'failed'])
            ->orderByDesc('end_date')
            ->limit(50)
            ->get();

        // إحصائيات سريعة
        $stats = [
            'active_count'   => $activeGoals->count(),
            'achieved_count' => DB::table('goals')->where('status', 'achieved')->count(),
            'failed_count'   => DB::table('goals')->where('status', 'failed')->count(),
            'avg_pct'        => $activeGoals->count()
                ? round($activeGoals->avg('actual_pct'), 1)
                : 0,
        ];

        return view('goals', compact('activeGoals', 'closedGoals', 'sources', 'accounts', 'stats'));
    }

    // ══════════════════════════════════════════════════════
    // إنشاء هدف جديد
    // ══════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $request->validate([
            'source_key'    => 'required|string|max:80',
            'source_label'  => 'required|string|max:120',
            'type'          => 'required|in:income,expense',
            'target_amount' => 'required|numeric|min:1',
            'period_type'   => 'required|in:weekly,monthly,custom',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'notes'         => 'nullable|string|max:255',
        ]);

        // لا تسمح بهدفين نشطين لنفس المصدر في نفس الفترة
        $conflict = DB::table('goals')
            ->where('source_key', $request->source_key)
            ->where('status', 'active')
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date',   [$request->start_date, $request->end_date]);
            })->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'يوجد هدف نشط بالفعل لنفس المصدر في هذه الفترة. أغلق الهدف القديم أولاً.')->withInput();
        }

        DB::table('goals')->insert([
            'type'          => $request->type,
            'source_key'    => $request->source_key,
            'source_label'  => $request->source_label,
            'target_amount' => floatval($request->target_amount),
            'period_type'   => $request->period_type,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'status'        => 'active',
            'notes'         => $request->notes ?? '',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        if (method_exists($this, 'logActivity')) {
            $this->logActivity('create', 'goals',
                "🎯 هدف جديد: [{$request->source_label}] — المستهدف: " . number_format($request->target_amount, 0) . " ج"
            );
        }

        return back()->with('success', '✅ تم إنشاء الهدف بنجاح. يمكنك متابعة التقدم من شريط الأهداف.');
    }

    // ══════════════════════════════════════════════════════
    // إغلاق هدف يدوياً
    // ══════════════════════════════════════════════════════

   public function close(Request $request)
{
    $request->validate(['goal_id' => 'required|integer|exists:goals,id']);

    $goal = DB::table('goals')->where('id', $request->goal_id)->first();
    if (!$goal || $goal->status !== 'active') {
        return back()->withInput()->with('error', 'الهدف غير موجود أو مغلق بالفعل.');
    }

    $from   = Carbon::parse($goal->start_date);
    $to     = Carbon::parse($goal->end_date);

    // ✅ الـ actual يتحسب لحد النهارده مش لحد end_date
    $actualTo = Carbon::today()->lt($to) ? Carbon::today() : $to;
    $actual   = self::calculateActual($goal->source_key, $from, $actualTo);

    $pct = $goal->target_amount > 0
        ? round(($actual / $goal->target_amount) * 100, 2)
        : 0;

    if ($goal->type === 'expense') {
        $achieved = $actual <= $goal->target_amount;
    } else {
        $achieved = $actual >= $goal->target_amount;
    }

    DB::table('goals')->where('id', $goal->id)->update([
        'status'          => $achieved ? 'achieved' : 'failed',
        'achieved_amount' => $actual,
        'achieved_pct'    => $pct,
        'updated_at'      => now(),
    ]);

    $msg = $achieved
        ? "🏆 تم إغلاق الهدف بنجاح! تحقق بنسبة {$pct}%"
        : "📊 تم إغلاق الهدف. تحقق بنسبة {$pct}% فقط.";

    return back()->with('success', $msg);
}
    // ══════════════════════════════════════════════════════
    // حذف هدف
    // ══════════════════════════════════════════════════════

    public function destroy(Request $request)
    {
        $request->validate(['goal_id' => 'required|integer|exists:goals,id']);
        DB::table('goals')->where('id', $request->goal_id)->delete();
        return back()->with('success', 'تم حذف الهدف.');
    }

    // ══════════════════════════════════════════════════════
    // API: بيانات شريط التقدم (للـ sidebar أو الداشبورد)
    // ══════════════════════════════════════════════════════

    public function progressBar()
    {
        $activeGoals = DB::table('goals')
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('end_date')
            ->get();

        $result = $activeGoals->map(function ($goal) {
            $from   = Carbon::parse($goal->start_date);
            $to     = Carbon::parse($goal->end_date);
            $actual = self::calculateActual($goal->source_key, $from, $to);
            $pct    = $goal->target_amount > 0
                ? min(100, round(($actual / $goal->target_amount) * 100, 1))
                : 0;

            return [
                'id'            => $goal->id,
                'label'         => $goal->source_label,
                'type'          => $goal->type,
                'target'        => $goal->target_amount,
                'actual'        => $actual,
                'pct'           => $pct,
                'days_left'     => max(0, Carbon::today()->diffInDays(Carbon::parse($goal->end_date), false)),
                'achieved'      => $goal->type === 'expense'
                    ? $actual <= $goal->target_amount
                    : $actual >= $goal->target_amount,
            ];
        });

        return response()->json(['goals' => $result]);
    }

    // ══════════════════════════════════════════════════════
    // إغلاق تلقائي للأهداف المنتهية
    // ══════════════════════════════════════════════════════

    private function autoCloseExpiredGoals(): void
    {
        $expired = DB::table('goals')
            ->where('status', 'active')
            ->where('end_date', '<', now()->toDateString())
            ->get();

        foreach ($expired as $goal) {
            $from   = Carbon::parse($goal->start_date);
            $to     = Carbon::parse($goal->end_date);
            $actual = self::calculateActual($goal->source_key, $from, $to);
            $pct    = $goal->target_amount > 0 ? round(($actual / $goal->target_amount) * 100, 2) : 0;

            $achieved = $goal->type === 'expense'
                ? $actual <= $goal->target_amount
                : $actual >= $goal->target_amount;

            DB::table('goals')->where('id', $goal->id)->update([
                'status'          => $achieved ? 'achieved' : 'failed',
                'achieved_amount' => $actual,
                'achieved_pct'    => $pct,
                'updated_at'      => now(),
            ]);
        }
    }
}