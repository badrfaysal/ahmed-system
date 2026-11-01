<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\SystemSetting;

class ReportController extends SystemController
{
    /**
     * 💡 مصدر واحد لتحديد النطاق الزمني، مستخدم في شاشة التقارير وفي تصدير الطباعة
     * عشان الاتنين يشتغلوا بنفس الفلتر بالظبط ومايحصلش فرق أرقام.
     */
    public static function resolveDateRange(string $dateFilter, ?string $customFrom, ?string $customTo): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();
        $rangeLabel = 'هذا الشهر';

        switch ($dateFilter) {
            case 'today':
                $startDate = Carbon::now()->startOfDay(); $endDate = Carbon::now()->endOfDay();
                $rangeLabel = 'اليوم'; break;
            case 'yesterday':
                $startDate = Carbon::now()->subDay()->startOfDay(); $endDate = Carbon::now()->subDay()->endOfDay();
                $rangeLabel = 'أمس'; break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek(Carbon::SATURDAY); $endDate = Carbon::now()->endOfWeek(Carbon::FRIDAY);
                $rangeLabel = 'هذا الأسبوع'; break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth(); $endDate = Carbon::now()->endOfMonth();
                $rangeLabel = 'هذا الشهر'; break;
            case 'year':
                $startDate = Carbon::now()->startOfYear(); $endDate = Carbon::now()->endOfYear();
                $rangeLabel = 'هذا العام'; break;
            case 'all':
                $startDate = Carbon::createFromDate(2000, 1, 1)->startOfDay(); $endDate = Carbon::now()->endOfDay();
                $rangeLabel = 'كل الفترات'; break;
            case 'custom':
                if ($customFrom) $startDate = Carbon::parse($customFrom)->startOfDay();
                if ($customTo)   $endDate   = Carbon::parse($customTo)->endOfDay();
                $rangeLabel = "من {$customFrom} إلى {$customTo}";
                break;
        }

        return [[$startDate, $endDate], $rangeLabel];
    }

    public function reports(Request $request)
    {
        $dateFilter   = $request->input('date_filter', 'month');
        $customFrom   = $request->input('custom_from');
        $customTo     = $request->input('custom_to');
        $tab          = $request->input('tab', 'inventory');
        $snapFrom     = $request->input('snap_from');
        $snapTo       = $request->input('snap_to');
        $snapPeriod   = $request->input('snap_period', '6months');

        // ─── 1. تحديد النطاق الزمني ───
        [$range, $rangeLabel] = self::resolveDateRange($dateFilter, $customFrom, $customTo);
        [$startDate, $endDate] = $range;

        // ════════════════════════════════════════════════════════════
        // 📦 تاب المخزن (Inventory)
        // ════════════════════════════════════════════════════════════
        $inv = $this->inventoryReport($range);

        // ════════════════════════════════════════════════════════════
        // 🔧 تاب الخدمات (Services — صيانة/تركيب/خدمات)
        // ════════════════════════════════════════════════════════════
        $services = $this->servicesReport($range);

        // ════════════════════════════════════════════════════════════
        // 📝 تاب الأقساط (Installments)
        // ════════════════════════════════════════════════════════════
        $inst = $this->installmentsReport($range);

        // ════════════════════════════════════════════════════════════
        // ⛽ تاب البنزينة (Gas Station)
        // ════════════════════════════════════════════════════════════
        $gas = $this->gasReport($range);

        // ════════════════════════════════════════════════════════════
        // 💰 تاب الحركة المالية (Financials)
        // ════════════════════════════════════════════════════════════
        $fin = $this->financialReport($range);
        $ac = $this->acReport($range);
        $contracting = $this->contractingReport($range);

        // تفصيل الأرباح حسب المصدر — بيتجمّع من نتائج التابات نفسها (شامل المقاولات)
        $profitBreakdown = self::assembleProfitBreakdown($inv, $services, $inst, $gas, $ac, $contracting);

        // نسب الأرباح المتوقعة لشهور السنة لشارت محاكاة نمو الشركة وفرض قيود السحب
        $defaultProjectionRates = [
            ['month' => 'يناير',   'rate' => 1.5],
            ['month' => 'فبراير',  'rate' => 1.5],
            ['month' => 'مارس',    'rate' => 1.5],
            ['month' => 'أبريل',   'rate' => 2.0],
            ['month' => 'مايو',    'rate' => 4.5],
            ['month' => 'يونيو',   'rate' => 6.0],
            ['month' => 'يوليو',   'rate' => 4.0],
            ['month' => 'أغسطس',   'rate' => 3.0],
            ['month' => 'سبتمبر',  'rate' => 2.0],
            ['month' => 'أكتوبر',  'rate' => 1.5],
            ['month' => 'نوفمبر',  'rate' => 1.5],
            ['month' => 'ديسمبر',  'rate' => 1.0],
        ];

        $savedRates = SystemSetting::get('partner_projection_monthly_rates', $defaultProjectionRates);
        if (is_string($savedRates)) {
            $decoded = json_decode($savedRates, true);
            $seasonalMonthlyRates = (is_array($decoded) && count($decoded) === 12) ? $decoded : $defaultProjectionRates;
        } elseif (is_array($savedRates) && count($savedRates) === 12) {
            $seasonalMonthlyRates = $savedRates;
        } else {
            $seasonalMonthlyRates = $defaultProjectionRates;
        }

        $totalCompanyCapital = (float) DB::table('partners')->where('status', 'active')->sum('capital');
        if ($totalCompanyCapital <= 0) {
            $totalCompanyCapital = (float) ($fin['capitalEnd'] ?? 0);
        }
        if ($totalCompanyCapital <= 0) {
            $totalCompanyCapital = 1000000;
        }

        // ─── نطاق زمني منفصل لشارت السنابات ───
        $snapEndDate   = Carbon::now()->endOfDay();
        $snapStartDate = match($snapPeriod) {
            '1month'  => Carbon::now()->subMonth()->startOfDay(),
            '3months' => Carbon::now()->subMonths(3)->startOfDay(),
            '6months' => Carbon::now()->subMonths(6)->startOfDay(),
            'year'    => Carbon::now()->subYear()->startOfDay(),
            'custom'  => $snapFrom ? Carbon::parse($snapFrom)->startOfDay() : Carbon::now()->subMonths(6)->startOfDay(),
            default   => Carbon::now()->subMonths(6)->startOfDay(),
        };
        if ($snapPeriod === 'custom' && $snapTo) {
            $snapEndDate = Carbon::parse($snapTo)->endOfDay();
        }

        $snapshots = DB::table('capital_snapshots')
            ->whereBetween('created_at', [$snapStartDate, $snapEndDate])
            ->orderBy('created_at')
            ->get();

        $capitalTrendFiltered = $snapshots->map(fn($s) => [
            'label' => Carbon::parse($s->created_at)->format('d/m H:i'),
            'value' => (float) $s->total_capital,
            'notes' => $s->notes ?? '',
        ])->values();

        return view('reports', compact(
            'dateFilter', 'customFrom', 'customTo', 'startDate', 'endDate',
            'rangeLabel', 'tab',
            'inv', 'services', 'inst', 'gas', 'fin', 'ac', 'contracting', 'profitBreakdown',
            'snapPeriod', 'snapFrom', 'snapTo', 'capitalTrendFiltered',
            'seasonalMonthlyRates', 'totalCompanyCapital'
        ));
    }

    /**
     * تفصيل الأرباح حسب المصدر من نتائج التابات الجاهزة — بدون أي queries إضافية،
     * فالأرقام مضمون تطابق كل تاب بالظبط.
     */
    public static function assembleProfitBreakdown(array $inv, array $services, array $inst, array $gas, array $ac, array $contracting = []): array
    {
        $rows = [
            'installmentInterest' => (float) ($inst['interestProfit'] ?? 0),   // ربح النسبة (الفايدة)
            'installmentProduct'  => (float) ($inst['productProfit'] ?? 0),    // ربح منتجات الأقساط
            'inventory'           => (float) ($inv['invSalesProfit'] ?? 0),    // ربح المخزن (بيع − شراء)
            'services'            => (float) ($services['servicesProfit'] ?? 0), // ربح الخدمات
            'gas'                 => (float) ($gas['netProfit'] ?? 0),         // صافي عمولة البنزينة
            'ac'                  => (float) ($ac['globalStats']['net_profit'] ?? 0), // ربح التكييفات
            'contracting'         => (float) ($contracting['grandProfit'] ?? 0), // ربح المقاولات والتشطيبات
        ];
        $rows['total'] = array_sum($rows);
        return $rows;
    }

    /**
     * نفس التفصيل لكن بيحسب التابات المطلوبة بنفسه (للطباعة، اللي بتحسب تاب واحد بس).
     */
    public function profitBreakdown(array $range): array
    {
        return self::assembleProfitBreakdown(
            $this->inventoryReport($range),
            $this->servicesReport($range),
            $this->installmentsReport($range),
            $this->gasReport($range),
            $this->acReport($range),
            $this->contractingReport($range)
        );
    }

    // ══════════════════════════════════════════════════════════
    // 📦 تقرير المخزن
    // ══════════════════════════════════════════════════════════
    public function inventoryReport(array $range): array
    {
        [$start, $end] = $range;

        // مشتريات الفترة (كل البضاعة المضافة للمخزن)
        $purchases = DB::table('sales')
            ->whereBetween('created_at', [$start, $end])
            ->where('inventory_status', 'to_inventory')
            ->get();

        $purchasesCount  = $purchases->count();
        $purchasedItems  = (float) $purchases->sum('quantity');
        $purchasedValue  = (float) $purchases->sum(fn($i) => $i->quantity * $i->purchase_price);

        // المخزون الحالي
        $currentStock = DB::table('sales')
            ->where('inventory_status', 'to_inventory')
            ->where('remaining_quantity', '>', 0)
            ->get();

        $currentStockItems    = (float) $currentStock->sum('remaining_quantity');
        $currentStockProducts = $currentStock->count();
        $currentStockCost     = (float) $currentStock->sum(fn($i) => $i->remaining_quantity * $i->purchase_price);
        $currentStockSell     = (float) $currentStock->sum(fn($i) => $i->remaining_quantity * $i->selling_price);
        $expectedProfit       = $currentStockSell - $currentStockCost;

        // مبيعات المخزن (من جدول installments بـ sale_type = inventory)
        // 🔒 استبعاد البنزينة بشكل صريح حتى لو حصل خطأ في الـ sale_type
        $invSales = DB::table('installments')
            ->where('sale_type', 'inventory')
            ->where('category', '!=', 'بنزينة')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        // 💡 ربح المخزن = فرق الشراء والبيع فقط (الفايدة تخص منظومة الأقساط ولا تُحتسب هنا)
        //    inv_margin = الربح المخزَّن − فايدة التقسيط = ربح السلعة الصافي
        $invSales = $invSales->map(function ($i) {
            $interest = max(0, (float) $i->total_after_interest - ((float) $i->cash_price - (float) ($i->discount ?? 0)));
            $i->inv_margin = (float) $i->profit - $interest;
            return $i;
        });

        $invSalesCount  = $invSales->count();
        $invSalesValue  = (float) $invSales->sum('total_after_interest');
        $invSalesProfit = (float) $invSales->sum('inv_margin');

        // أكتر المنتجات مبيعاً
        $topProducts = $invSales->groupBy('product_name')
            ->map(fn($g) => [
                'name'     => $g->first()->product_name,
                'qty'      => (float) $g->sum('quantity'),
                'revenue'  => (float) $g->sum('total_after_interest'),
                'profit'   => (float) $g->sum('inv_margin'),
            ])
            ->sortByDesc('qty')
            ->take(10)
            ->values();

        $topProduct = $topProducts->first();

        // أقل منتج مبيع
        $leastProduct = $invSales->groupBy('product_name')
            ->map(fn($g) => [
                'name' => $g->first()->product_name,
                'qty'  => (float) $g->sum('quantity'),
            ])
            ->sortBy('qty')
            ->first();

        // تصنيفات
        $categories = $invSales->groupBy('category')
            ->map(fn($g) => [
                'name'   => $g->first()->category ?: 'بدون تصنيف',
                'count'  => $g->count(),
                'value'  => (float) $g->sum('total_after_interest'),
                'profit' => (float) $g->sum('inv_margin'),
            ])
            ->sortByDesc('value')
            ->values();

        // مرتجعات
        $returns = DB::table('sale_returns')
            ->whereBetween('created_at', [$start, $end])
            ->get();
        $returnsCount = $returns->count();
        $returnsQty   = (float) $returns->sum('quantity_returned');
        $returnsLoss  = (float) $returns->sum('loss_amount');

        $mostReturned = $returns->groupBy('product_name')
            ->map(fn($g) => [
                'name' => $g->first()->product_name,
                'qty'  => (float) $g->sum('quantity_returned'),
                'loss' => (float) $g->sum('loss_amount'),
            ])
            ->sortByDesc('qty')
            ->first();

        // منتجات قاربت على النفاد (3 قطع أو أقل)
        $lowStock = DB::table('sales')
            ->where('inventory_status', 'to_inventory')
            ->where('remaining_quantity', '>', 0)
            ->where('remaining_quantity', '<=', 3)
            ->orderBy('remaining_quantity')
            ->limit(15)
            ->get();

        // أكتر موردين توريد للمخزن
        $topSuppliers = $purchases->groupBy('supplier_name')
            ->map(fn($g) => [
                'name'  => $g->first()->supplier_name ?: 'بدون مورد',
                'count' => $g->count(),
                'value' => (float) $g->sum(fn($i) => $i->quantity * $i->purchase_price),
            ])
            ->sortByDesc('value')
            ->take(8)
            ->values();

        // حركة يومية
        $dailyTrend = [];
        $period = $start->copy();
        while ($period->lte($end)) {
            $dayStart = $period->copy()->startOfDay();
            $dayEnd   = $period->copy()->endOfDay();
            $daySales = $invSales->filter(fn($i) => Carbon::parse($i->created_at)->between($dayStart, $dayEnd));
            $dailyTrend[] = [
                'date'    => $period->format('Y-m-d'),
                'label'   => $period->format('m-d'),
                'count'   => $daySales->count(),
                'value'   => (float) $daySales->sum('total_after_interest'),
            ];
            $period->addDay();
        }

        // 💡 مصاريف التكييفات الإضافية لمبيعات المخزن (نقل/تركيب/خامات)
        // مصدر: installment_expenses ↔ installments بـ sale_type='inventory' في نفس الفترة
        $acExtras = (object) DB::table('installment_expenses as ie')
            ->join('installments as i', 'i.id', '=', 'ie.installment_id')
            ->where('i.sale_type', 'inventory')
            ->whereBetween('i.created_at', [$start, $end])
            ->selectRaw('
                COALESCE(SUM(ie.transport_cost), 0)    as transport,
                COALESCE(SUM(ie.installation_cost), 0) as installation,
                COALESCE(SUM(ie.materials_cost), 0)    as materials,
                COUNT(*)                                as operations
            ')
            ->first();
        $acExtras->total = (float) $acExtras->transport + (float) $acExtras->installation + (float) $acExtras->materials;

        return compact(
            'purchasesCount', 'purchasedItems', 'purchasedValue',
            'currentStockItems', 'currentStockProducts', 'currentStockCost', 'currentStockSell', 'expectedProfit',
            'invSalesCount', 'invSalesValue', 'invSalesProfit',
            'topProducts', 'topProduct', 'leastProduct', 'categories',
            'returnsCount', 'returnsQty', 'returnsLoss', 'mostReturned',
            'lowStock', 'topSuppliers', 'dailyTrend', 'acExtras'
        );
    }

    // ══════════════════════════════════════════════════════════
    // 🔧 تقرير الخدمات (صيانة/تركيب/خدمات — منفصلة عن المبيعات)
    // ══════════════════════════════════════════════════════════
    public function servicesReport(array $range): array
    {
        [$start, $end] = $range;

        $services = DB::table('installments')
            ->where('category', 'خدمات')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $servicesCount   = $services->count();
        $servicesRevenue = (float) $services->sum('total_after_interest');
        $servicesProfit  = (float) $services->sum('profit');
        $servicesCost    = (float) $services->sum('purchase_cost');
        $avgPerService   = $servicesCount > 0 ? round($servicesRevenue / $servicesCount, 2) : 0;
        $avgProfitPct    = $servicesRevenue > 0 ? round(($servicesProfit / $servicesRevenue) * 100, 1) : 0;

        // نقدي vs آجل
        $cashServices   = $services->where('remaining_balance', '<=', 0);
        $creditServices = $services->where('remaining_balance', '>', 0);
        $cashCount      = $cashServices->count();
        $creditCount    = $creditServices->count();
        $cashValue      = (float) $cashServices->sum('total_after_interest');
        $creditValue    = (float) $creditServices->sum('remaining_balance');

        // أكتر الخدمات تنفيذاً
        $topServices = $services->groupBy('product_name')
            ->map(fn($g) => [
                'name'    => $g->first()->product_name,
                'count'   => $g->count(),
                'revenue' => (float) $g->sum('total_after_interest'),
                'profit'  => (float) $g->sum('profit'),
            ])
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // أكتر العملاء طلباً للخدمة
        $topCustomers = $services->groupBy('customer_name')
            ->map(fn($g) => [
                'name'    => $g->first()->customer_name,
                'count'   => $g->count(),
                'revenue' => (float) $g->sum('total_after_interest'),
            ])
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // أكتر الفنيين شغلاً (من sales table بـ category='خدمات')
        $techsRaw = DB::table('sales')
            ->where('inventory_status', 'sold')
            ->where('category', 'خدمات')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $topTechs = $techsRaw->groupBy('supplier_name')
            ->map(fn($g) => [
                'name'  => $g->first()->supplier_name ?: 'بدون فني',
                'count' => $g->count(),
                'paid'  => (float) $g->sum('purchase_price'),
            ])
            ->sortByDesc('paid')
            ->take(10)
            ->values();

        // حركة يومية
        $dailyTrend = [];
        $period = $start->copy();
        while ($period->lte($end)) {
            $dayStart = $period->copy()->startOfDay();
            $dayEnd   = $period->copy()->endOfDay();
            $day = $services->filter(fn($i) => Carbon::parse($i->created_at)->between($dayStart, $dayEnd));
            $dailyTrend[] = [
                'date'    => $period->format('Y-m-d'),
                'label'   => $period->format('m-d'),
                'revenue' => (float) $day->sum('total_after_interest'),
                'profit'  => (float) $day->sum('profit'),
                'count'   => $day->count(),
            ];
            $period->addDay();
        }

        return compact(
            'servicesCount', 'servicesRevenue', 'servicesProfit', 'servicesCost',
            'avgPerService', 'avgProfitPct',
            'cashCount', 'creditCount', 'cashValue', 'creditValue',
            'topServices', 'topCustomers', 'topTechs',
            'dailyTrend'
        );
    }

    // ══════════════════════════════════════════════════════════
    // 📝 تقرير الأقساط
    // ══════════════════════════════════════════════════════════
    public function installmentsReport(array $range): array
    {
        [$start, $end] = $range;

        // العقود الجديدة في الفترة (تستبعد البنزينة لأنها تدفعات وقود مش عقود تقسيط حقيقية)
        $newContracts = DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('category', '!=', 'بنزينة')
            ->where(function($q){ $q->where('sale_type','!=','fuel')->orWhereNull('sale_type'); })
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $contractsCount   = $newContracts->count();
        $contractsValue   = (float) $newContracts->sum('total_after_interest');
        $interestProfit   = (float) $newContracts->sum(fn($i) => max(0, $i->total_after_interest - $i->cash_price));
        $productProfit    = (float) $newContracts->sum(fn($i) => max(0, $i->cash_price - $i->purchase_cost));
        $totalContractProfit = $interestProfit + $productProfit;

        $totalDownPayments = (float) $newContracts->sum('down_payment');
        $avgMonths         = $newContracts->count() > 0 ? round($newContracts->avg('installment_months'), 1) : 0;
        $avgContractValue  = $newContracts->count() > 0 ? round($newContracts->avg('total_after_interest'), 2) : 0;

        // المحصّل في الفترة (الدفعات)
        $payments = DB::table('installment_payments')
            ->whereBetween('payment_date', [$start, $end])
            ->get();

        $paymentsCount  = $payments->count();
        $paymentsValue  = (float) $payments->sum('amount_paid');
        $discountsGiven = Schema::hasColumn('installment_payments', 'discount_applied')
            ? (float) $payments->sum('discount_applied') : 0;

        // الحالة العامة لكل الأقساط
        $allActive = DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->get();

        $activeContracts   = $allActive->where('remaining_balance', '>', 0)->count();
        $closedContracts   = $allActive->where('remaining_balance', '<=', 0)->count();
        $totalOutstanding  = (float) $allActive->where('remaining_balance', '>', 0)->sum('remaining_balance');
        $writtenOff        = $allActive->filter(fn($i) => ($i->close_reason ?? '') === 'written_off');
        $writtenOffCount   = $writtenOff->count();
        $writtenOffValue   = (float) $writtenOff->sum('remaining_balance');

        // متأخرات: آخر دفعة أو العقد عمره أكتر من 35 يوم
        $overdue = DB::table('installments as i')
            ->where('i.installment_months', '>', 0)
            ->where('i.remaining_balance', '>', 0)
            ->where('i.status', '!=', 'cancelled')
            ->leftJoin('installment_payments as ip', 'ip.installment_id', '=', 'i.id')
            ->select('i.id', 'i.customer_name', 'i.product_name', 'i.remaining_balance', 'i.monthly_installment',
                     'i.created_at as contract_date', DB::raw('MAX(ip.payment_date) as last_payment'))
            ->groupBy('i.id', 'i.customer_name', 'i.product_name', 'i.remaining_balance', 'i.monthly_installment', 'i.created_at')
            ->get()
            ->filter(function($r) {
                $ref = $r->last_payment ?: $r->contract_date;
                if (!$ref) return false;
                try { return Carbon::parse($ref)->diffInDays(now()) > 35; }
                catch (\Throwable $e) { return false; }
            })
            ->values();

        $overdueCount = $overdue->count();
        $overdueValue = (float) $overdue->sum('remaining_balance');

        // أكتر العملاء عقوداً
        $topCustomers = $newContracts->groupBy('customer_name')
            ->map(fn($g) => [
                'name'    => $g->first()->customer_name,
                'count'   => $g->count(),
                'value'   => (float) $g->sum('total_after_interest'),
                'remaining' => (float) $g->sum('remaining_balance'),
            ])
            ->sortByDesc('count')
            ->take(10)
            ->values();

        $topCustomer = $topCustomers->first();

        // أكتر منتج تقسيط
        $topProducts = $newContracts->groupBy('product_name')
            ->map(fn($g) => [
                'name'    => $g->first()->product_name,
                'count'   => $g->count(),
                'value'   => (float) $g->sum('total_after_interest'),
                'profit'  => (float) $g->sum('profit'),
            ])
            ->sortByDesc('count')
            ->take(10)
            ->values();

        // متوسط الفائدة
        $avgInterestPct = 0;
        if ($newContracts->count() > 0) {
            $rates = $newContracts->filter(fn($i) => $i->cash_price > 0)
                ->map(fn($i) => (($i->total_after_interest - $i->cash_price) / $i->cash_price) * 100);
            $avgInterestPct = $rates->count() > 0 ? round($rates->avg(), 2) : 0;
        }

        // حركة يومية: عقود جديدة + محصّل
        $dailyTrend = [];
        $period = $start->copy();
        while ($period->lte($end)) {
            $dayStart = $period->copy()->startOfDay();
            $dayEnd   = $period->copy()->endOfDay();
            $newC = $newContracts->filter(fn($i) => Carbon::parse($i->created_at)->between($dayStart, $dayEnd))->count();
            $paid = $payments->filter(fn($p) => Carbon::parse($p->payment_date)->between($dayStart, $dayEnd))
                             ->sum('amount_paid');
            $dailyTrend[] = [
                'date'     => $period->format('Y-m-d'),
                'label'    => $period->format('m-d'),
                'new'      => $newC,
                'paid'     => (float) $paid,
            ];
            $period->addDay();
        }

        // 💡 مصاريف التكييفات الإضافية لعقود التقسيط (نقل/تركيب/خامات)
        // مصدر: batch_expenses بـ created_at في نفس الفترة (تُسجَّل وقت إنشاء العقد)
        $acExtras = (object) DB::table('batch_expenses')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COALESCE(SUM(transport_cost), 0)    as transport,
                COALESCE(SUM(installation_cost), 0) as installation,
                COALESCE(SUM(materials_cost), 0)    as materials,
                COUNT(*)                             as operations
            ')
            ->first();
        $acExtras->total = (float) $acExtras->transport + (float) $acExtras->installation + (float) $acExtras->materials;

        return compact(
            'contractsCount', 'contractsValue', 'interestProfit', 'productProfit', 'totalContractProfit',
            'totalDownPayments', 'avgMonths', 'avgContractValue', 'avgInterestPct',
            'paymentsCount', 'paymentsValue', 'discountsGiven',
            'activeContracts', 'closedContracts', 'totalOutstanding',
            'writtenOffCount', 'writtenOffValue',
            'overdueCount', 'overdueValue', 'overdue',
            'topCustomers', 'topCustomer', 'topProducts',
            'dailyTrend', 'acExtras'
        );
    }

    // ══════════════════════════════════════════════════════════
    // ⛽ تقرير البنزينة
    // ══════════════════════════════════════════════════════════
    public function gasReport(array $range): array
    {
        [$start, $end] = $range;

        $fuel = DB::table('fuel_transactions')
            ->whereNull('superseded_by')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $opsCount       = $fuel->count();
        $totalLiters    = (float) $fuel->sum('liters');
        $totalAdvances  = (float) $fuel->sum('cash_advance');
        $totalToStation = (float) $fuel->sum('total_to_station');
        $totalOnCompany = (float) $fuel->sum('total_on_company');
        $netProfit      = (float) $fuel->sum('ahmed_profit');
        $avgProfit      = $opsCount > 0 ? round($netProfit / $opsCount, 2) : 0;

        // الشركات
        $companies = DB::table('transport_companies')->get()->keyBy('id');
        $stations  = DB::table('gas_stations')->get()->keyBy('id');

        $topCompanies = $fuel->groupBy('company_id')
            ->map(function($g, $id) use ($companies) {
                $c = $companies->get($id);
                return [
                    'name'    => $c ? ($c->company_name ?? $c->name ?? "شركة #{$id}") : "شركة #{$id}",
                    'count'   => $g->count(),
                    'liters'  => (float) $g->sum('liters'),
                    'on_them' => (float) $g->sum('total_on_company'),
                    'profit'  => (float) $g->sum('ahmed_profit'),
                ];
            })
            ->sortByDesc('on_them')
            ->take(10)
            ->values();

        $topStations = $fuel->groupBy('station_id')
            ->map(function($g, $id) use ($stations) {
                $s = $stations->get($id);
                return [
                    'name'   => $s ? ($s->station_name ?? $s->name ?? "محطة #{$id}") : "محطة #{$id}",
                    'count'  => $g->count(),
                    'liters' => (float) $g->sum('liters'),
                    'paid'   => (float) $g->sum('total_to_station'),
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values();

        $topDrivers = $fuel->groupBy('driver_name')
            ->map(fn($g) => [
                'name'   => $g->first()->driver_name ?: 'غير محدد',
                'count'  => $g->count(),
                'liters' => (float) $g->sum('liters'),
            ])
            ->sortByDesc('liters')
            ->take(8)
            ->values();

        $topFuelTypes = $fuel->groupBy('fuel_type')
            ->map(fn($g) => [
                'name'   => $g->first()->fuel_type ?: 'غير محدد',
                'count'  => $g->count(),
                'liters' => (float) $g->sum('liters'),
                'value'  => (float) $g->sum('total_to_station'),
            ])
            ->sortByDesc('liters')
            ->values();

        // المستحقات والمديونيات الحالية للبنزينة (مجمعة، مش بفلتر زمني)
        $gasReceivables = (float) DB::table('installments')
            ->where('category', 'بنزينة')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        $gasPayablesStations = (float) DB::table('company_debts')
            ->where('category', 'وقود')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        $gasPayablesDeductions = (float) DB::table('company_debts')
            ->where('category', 'استقطاعات')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        // حركة يومية
        $dailyTrend = [];
        $period = $start->copy();
        while ($period->lte($end)) {
            $dayStart = $period->copy()->startOfDay();
            $dayEnd   = $period->copy()->endOfDay();
            $day = $fuel->filter(fn($i) => Carbon::parse($i->created_at)->between($dayStart, $dayEnd));
            $dailyTrend[] = [
                'date'   => $period->format('Y-m-d'),
                'label'  => $period->format('m-d'),
                'liters' => (float) $day->sum('liters'),
                'profit' => (float) $day->sum('ahmed_profit'),
            ];
            $period->addDay();
        }

        return compact(
            'opsCount', 'totalLiters', 'totalAdvances', 'totalToStation', 'totalOnCompany',
            'netProfit', 'avgProfit',
            'topCompanies', 'topStations', 'topDrivers', 'topFuelTypes',
            'gasReceivables', 'gasPayablesStations', 'gasPayablesDeductions',
            'dailyTrend'
        );
    }

    // ══════════════════════════════════════════════════════════
    // 💰 تقرير الحركة المالية
    // ══════════════════════════════════════════════════════════
    public function financialReport(array $range): array
    {
        [$start, $end] = $range;

        $tx = DB::table('financial_transactions')
            ->where('status', 'active')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $incomes  = $tx->where('type', 'income');
        // 💡 نفس تعريف "المصروفات" المستخدم في شاشة إدارة المصروفات (FinanceController::expenses):
        // بيستبعد العُهد (person_name) وإعدامات الديون وإهلاك الأصول وخسارة فرق السعر
        // لأن دي بنود بتتعرض كبطاقات منفصلة، ومكانش المفروض تتحسب مرتين جوه "إجمالي المصروفات".
        $expenseTypeTx = $tx->whereIn('type', ['general_expense', 'salary_expense', 'discount']);
        $expenses = $expenseTypeTx->filter(function($t) {
            if (!empty($t->person_name)) return false; // عُهد الموظفين — ليها بند منفصل
            $note = $t->notes ?? '';
            foreach (['إعدام ديون', 'إهلاك أصل ثابت', 'خسارة فرق سعر'] as $skip) {
                if (str_contains($note, $skip)) return false;
            }
            return true;
        });
        $transfers = $tx->where('type', 'transfer');
        $settlements = $tx->where('type', 'settlement');

        $totalIncomes     = (float) $incomes->sum('amount');
        $totalExpenses    = (float) $expenses->sum('amount');
        $totalTransfers   = (float) $transfers->sum('amount');
        $totalSettlements = (float) $settlements->sum('amount');
        // 💡 نفس رقم "حجم التدفقات الخارجة" في شاشة العمليات المالية: كل فلوس خرجت فعلياً.
        // لازم نضيف type='expense' هنا (بعكس $expenseTypeTx فوق) لإنه النوع الأساسي المستخدم في
        // أغلب الكونترولرز التانية (شراء أصول، عهد بنزينة، رواتب HR، مرتجعات مخزن، مبيعات، شركاء...)
        // وكان ناقص من الحساب، وده سبب الفرق الكبير عن شاشة العمليات المالية.
        $totalExpensesGross = (float) $tx->whereIn('type', ['expense', 'general_expense', 'salary_expense', 'discount'])->sum('amount');
        $netCashFlow      = $totalIncomes - $totalExpenses;

        $salaries    = (float) $tx->where('type', 'salary_expense')->sum('amount');
        $discounts   = (float) $tx->where('type', 'discount')->sum('amount');
        // البنود دي مستبعدة من $expenses أعلاه، فبنحسبها من $expenseTypeTx الكامل عشان تفضل ظاهرة كأرقام منفصلة
        // 💡 نفس تعريف تصنيف "عمولات المحافظ" في شاشة إدارة المصروفات: مش بس العمولة التلقائية،
        // كمان أي بند اتحط عليه تصنيف [عمولات المحافظ] يدوياً أو فيه كلمة "عمولة" في الملاحظات
        $commissions   = (float) $expenseTypeTx->filter(function($t) {
            $note = $t->notes ?? '';
            return str_contains($note, 'عمولة') || str_contains($note, '[عمولات المحافظ]');
        })->sum('amount');
        $depreciation  = (float) $expenseTypeTx->filter(fn($t) => str_contains($t->notes ?? '', 'إهلاك أصل ثابت'))->sum('amount');
        $badDebts      = (float) $expenseTypeTx->filter(fn($t) => str_contains($t->notes ?? '', 'إعدام ديون'))->sum('amount');
        $priceDiffLoss = (float) $expenseTypeTx->filter(fn($t) => str_contains($t->notes ?? '', 'خسارة فرق سعر'))->sum('amount');
        $advancesTotal = (float) $expenseTypeTx->filter(fn($t) => !empty($t->person_name))->sum('amount');

        // تصنيف المصروفات (general_expense بدون التصنيفات الخاصة)
        $generalExpenses = $expenses->filter(function($t) {
            if ($t->type !== 'general_expense') return false;
            $note = $t->notes ?? '';
            foreach (['عمولة تلقائية', 'إهلاك أصل ثابت', 'إعدام ديون', 'راتب', 'salary', 'استقطاع'] as $skip) {
                if (str_contains($note, $skip)) return false;
            }
            return true;
        });

        $expensesByCategory = $generalExpenses->groupBy(function($t) {
            $note = $t->notes ?? '';
            if (preg_match('/^\[(.*?)\]/', $note, $m)) return $m[1];
            return 'مصروفات عامة';
        })->map(function($g, $key) {
            return [
                'name'  => $key,
                'count' => $g->count(),
                'value' => (float) $g->sum('amount'),
            ];
        })->sortByDesc('value')->values();

        // مصاريف الموظفين (مصروفات فقط — بدون إيرادات أو تحويلات)
        $byPerson = $tx->whereIn('type', ['general_expense', 'salary_expense', 'discount'])
            ->whereNotNull('person_name')
            ->groupBy('person_name')
            ->map(fn($g) => [
                'name'   => $g->first()->person_name,
                'count'  => $g->count(),
                'total'  => (float) $g->sum('amount'),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // الديون الحالية (إجمالية، مش بفلتر)
        $debtsForUs = (float) DB::table('installments')
            ->where('remaining_balance', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
            ->sum('remaining_balance');

        // 💡 لازم orWhereNull زي $debtsForUs فوق: != بيستبعد صفوف التصنيف NULL تلقائياً في SQL،
        // وده كان بيسبب اختلاف الرقم هنا عن شاشة الخزينة (اللي بتحسبها: الإجمالي الكامل ناقص وقود بس)
        $debtsOnUs = (float) DB::table('company_debts')
            ->where('remaining_balance', '>', 0)
            ->where(function($q) { $q->where('category', '!=', 'وقود')->orWhereNull('category'); })
            ->sum('remaining_balance');

        // أرصدة الحسابات الحالية
        $accounts = DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->orderBy('account_name')
            ->get();
        $totalLiquidity = (float) $accounts->sum('balance');

        // ── نمو رأس المال ──
        // اللقطة التلقائية بتتاخد كل يوم حوالي الساعة 00:00:0X (كام ثانية بعد منتصف الليل)،
        // فلقطة أول يوم في الفترة = رأس المال وهو داخل على الفترة. المنطق:
        //   • الافتتاحي = أول لقطة من بداية الفترة (>= بداية الفترة). لازم نبص "من بداية الفترة
        //     لقدّام" مش "قبلها": لأن لقطة منتصف الليل بتيجي بعد الساعة 12 بثواني، ولو دوّرنا على
        //     آخر لقطة *قبل* الفترة هنرجع لآخر لقطة في الشهر اللي فات بالغلط (سبب ظهور 30 يونيو
        //     بدل 1 يوليو — الفرق كان 5 ثواني بس).
        //   • الختامي: لو الفترة شاملة "دلوقتي" → رأس المال الحالي؛ غير كده → أول لقطة بعد نهاية
        //     الفترة (= لقطة منتصف ليل اليوم التالي = رأس المال آخر الفترة).
        $snapshots = DB::table('capital_snapshots')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        // الافتتاحي: أول لقطة من بداية الفترة، وإلا (fallback) آخر لقطة قبلها
        $openingSnap = DB::table('capital_snapshots')
            ->where('created_at', '>=', $start)
            ->orderBy('created_at')
            ->first()
            ?: DB::table('capital_snapshots')
                ->where('created_at', '<', $start)
                ->orderByDesc('created_at')
                ->first();
        $capitalStart = (float) ($openingSnap->total_capital ?? 0);

        if (Carbon::now()->between($start, $end)) {
            $capitalEnd = (float) \App\Services\InstallmentFinanceService::treasurySummary()['capital'];
        } else {
            // أول لقطة بعد نهاية الفترة = رأس المال آخر الفترة؛ وإلا آخر لقطة جواها
            $closingSnap = DB::table('capital_snapshots')
                ->where('created_at', '>', $end)
                ->orderBy('created_at')
                ->first();
            $capitalEnd = (float) ($closingSnap->total_capital
                ?? ($snapshots->last()->total_capital ?? $capitalStart));
        }

        $capitalDiff  = $capitalEnd - $capitalStart;
        $capitalPct   = $capitalStart > 0 ? round(($capitalDiff / $capitalStart) * 100, 2) : 0;

        $capitalTrend = $snapshots->map(fn($s) => [
            'label' => Carbon::parse($s->created_at)->format('m-d'),
            'value' => (float) $s->total_capital,
        ])->values();

        // حركة يومية
        $dailyTrend = [];
        $period = $start->copy();
        while ($period->lte($end)) {
            $dayStart = $period->copy()->startOfDay();
            $dayEnd   = $period->copy()->endOfDay();
            $dayTx = $tx->filter(fn($t) => Carbon::parse($t->created_at)->between($dayStart, $dayEnd));
            $dailyTrend[] = [
                'date'     => $period->format('Y-m-d'),
                'label'    => $period->format('m-d'),
                'income'   => (float) $dayTx->where('type', 'income')->sum('amount'),
                'expense'  => (float) $dayTx->whereIn('type', ['general_expense', 'salary_expense', 'discount'])->sum('amount'),
            ];
            $period->addDay();
        }

        // آخر العمليات
        $recentTx = $tx->sortByDesc('created_at')->take(20)->values();

        return compact(
            'totalIncomes', 'totalExpenses', 'totalExpensesGross', 'totalTransfers', 'totalSettlements', 'netCashFlow',
            'salaries', 'discounts', 'commissions', 'depreciation', 'badDebts', 'priceDiffLoss', 'advancesTotal',
            'expensesByCategory', 'byPerson',
            'debtsForUs', 'debtsOnUs',
            'accounts', 'totalLiquidity',
            'capitalStart', 'capitalEnd', 'capitalDiff', 'capitalPct', 'capitalTrend',
            'dailyTrend', 'recentTx'
        );
    }

    // ══════════════════════════════════════════════════════════
    // ❄️ تقرير التكييفات
    // ══════════════════════════════════════════════════════════
    public function acReport(array $range): array
    {
        [$startDate, $endDate] = $range;
        $clientId = request('client_id') ?: request('ac_client_id');
        
        $query = DB::table('ac_operations')
            ->leftJoin('ac_clients', 'ac_operations.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_floors', 'ac_operations.ac_floor_id', '=', 'ac_floors.id')
            ->leftJoin('ac_classes', 'ac_operations.ac_class_id', '=', 'ac_classes.id')
            ->select('ac_operations.*', 'ac_clients.name as client_name', 'ac_floors.name as floor_name', 'ac_classes.name as class_name')
            ->whereBetween('ac_operations.date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($clientId) {
            $query->where('ac_operations.ac_client_id', $clientId);
        }

        $operations = $query->orderBy('date', 'desc')->get();

        // تحميل أسماء الأصناف لكل عملية
        $opIds = $operations->pluck('id')->toArray();
        $allItems = DB::table('ac_operation_items')
            ->join('sales', 'ac_operation_items.item_id', '=', 'sales.id')
            ->whereIn('ac_operation_items.ac_operation_id', $opIds)
            ->select('ac_operation_items.ac_operation_id', 'sales.product_name', 'ac_operation_items.quantity')
            ->get()
            ->groupBy('ac_operation_id');

        $operations->transform(function($op) use ($allItems) {
            $items = $allItems->get($op->id);
            if ($items && $items->count() > 0) {
                $op->items_text = $items->map(function($i) {
                    $qty = (int) $i->quantity;
                    return $i->product_name . ($qty > 1 ? " (×{$qty})" : '');
                })->implode(' + ');
            } elseif (!empty($op->maintenance_type_name)) {
                $op->items_text = $op->maintenance_type_name;
            } else {
                $op->items_text = $op->type === 'maintenance' ? 'صيانة تكييفات' : 'بيع وتركيب تكييفات';
            }
            return $op;
        });

        $activeOps = $operations->where('status', 'active');
        $clients = $operations->groupBy('client_name');

        $directDiscountsQuery = DB::table('ac_discounts')
            ->leftJoin('ac_clients', 'ac_discounts.ac_client_id', '=', 'ac_clients.id')
            ->select('ac_discounts.*', 'ac_clients.name as client_name')
            ->whereDate('ac_discounts.date', '>=', $startDate)
            ->whereDate('ac_discounts.date', '<=', $endDate);
        if ($clientId) {
            $directDiscountsQuery->where('ac_discounts.ac_client_id', $clientId);
        }
        $allDirectDiscounts = $directDiscountsQuery->get();
        $directDiscountsByClient = $allDirectDiscounts->groupBy('client_name');

        $reports = [];
        foreach ($clients as $clientName => $ops) {
            $activeClientOps = $ops->where('status', 'active');
            $salesOps = $activeClientOps->where('type', 'sale');
            $maintOps = $activeClientOps->where('type', 'maintenance');
            $clientDirectDisc = (float) $directDiscountsByClient->get($clientName, collect())->sum('amount');
            $totalClientDisc = (float) $activeClientOps->sum('discount_amount') + $clientDirectDisc;

            $reports[] = [
                'client_name' => $clientName,
                'total_deals' => $activeClientOps->sum('total_amount'),
                'total_cost' => $activeClientOps->sum('cost_amount'),
                'sales_count' => $salesOps->count(),
                'maint_count' => $maintOps->count(),
                'sales_total' => $salesOps->sum('total_amount'),
                'sales_profit' => $salesOps->sum('profit_amount'),
                'maint_total' => $maintOps->sum('total_amount'),
                'maint_profit' => $maintOps->sum('profit_amount'),
                'total_discounts' => $totalClientDisc,
                'most_active_class' => $activeClientOps->whereNotNull('class_name')->groupBy('class_name')->map->count()->sortDesc()->keys()->first() ?? 'لا يوجد',
                'operations' => $ops
            ];
        }

        $reports = collect($reports)->sortByDesc('total_deals')->values()->all();

        $topClients = collect($reports)->take(10)->map(function($r) {
            return [
                'name' => $r['client_name'],
                'count' => $r['sales_count'] + $r['maint_count'],
                'revenue' => $r['total_deals'],
                'cost' => $r['total_cost'] ?? 0,
                'discount' => $r['total_discounts'],
                'profit' => $r['sales_profit'] + $r['maint_profit'] - $r['total_discounts']
            ];
        })->toArray();

        $totalDirectDiscounts = (float) $allDirectDiscounts->sum('amount');
        $globalStats = [
            'ops_count'   => $activeOps->count(),
            'sales_count' => $activeOps->where('type', 'sale')->count(),
            'maint_count' => $activeOps->where('type', 'maintenance')->count(),
            'sales_total' => $activeOps->where('type', 'sale')->sum('total_amount'),
            'sales_profit' => $activeOps->where('type', 'sale')->sum('profit_amount'),
            'maint_total' => $activeOps->where('type', 'maintenance')->sum('total_amount'),
            'maint_profit' => $activeOps->where('type', 'maintenance')->sum('profit_amount'),
            'discounts' => $activeOps->sum('discount_amount') + $totalDirectDiscounts,
            'net_profit' => ($activeOps->sum('profit_amount') - ($activeOps->sum('discount_amount') + $totalDirectDiscounts)),
        ];

        $expensesQuery = DB::table('ac_expenses')
            ->leftJoin('ac_clients', 'ac_expenses.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_expense_categories', 'ac_expenses.ac_expense_category_id', '=', 'ac_expense_categories.id')
            ->select('ac_expenses.*', 'ac_clients.name as client_name', 'ac_expense_categories.name as category_name')
            ->whereDate('ac_expenses.date', '>=', $startDate)
            ->whereDate('ac_expenses.date', '<=', $endDate);
        if ($clientId) {
            $expensesQuery->where('ac_expenses.ac_client_id', $clientId);
        }
        $expenses = $expensesQuery->get();
            
        $globalStats['total_expenses'] = $expenses->sum('amount');
        $globalStats['net_profit'] -= $globalStats['total_expenses'];

        $expensesByClientName = $expenses->groupBy('client_name')->map(function($exps) {
            return $exps->sum('amount');
        })->toArray();

        foreach ($topClients as &$c) {
            $clientExp = $expensesByClientName[$c['name']] ?? 0;
            $c['expenses'] = $clientExp;
            $c['profit'] -= $clientExp;
        }
        unset($c);

        // حركة يومية
        $dailyTrend = [];
        $period = $startDate->copy();
        while ($period->lte($endDate)) {
            $dayStr = $period->toDateString();
            $dayOps = $activeOps->where('date', $dayStr);
            $dailyTrend[] = [
                'date'    => $period->format('Y-m-d'),
                'label'   => $period->format('m-d'),
                'revenue' => (float) $dayOps->sum('total_amount'),
                'profit'  => (float) $dayOps->sum('profit_amount') - (float) $dayOps->sum('discount_amount'),
            ];
            $period->addDay();
        }

        return compact('reports', 'globalStats', 'dailyTrend', 'topClients', 'expenses');
    }

    // ══════════════════════════════════════════════════════════
    // 🏗️ تقرير المقاولات والتشطيبات
    // ══════════════════════════════════════════════════════════
    public function contractingReport(array $range): array
    {
        [$start, $end] = $range;

        $projects = DB::table('cont_projects')->orderBy('name')->get();
        $projectsMap = $projects->keyBy('id');

        $repOps = DB::table('cont_operations')->whereBetween('created_at', [$start, $end])->get();
        $repDiscounts = DB::table('cont_discounts')->whereBetween('created_at', [$start, $end])->get();
        $repInvoices = DB::table('cont_invoices')->whereBetween('created_at', [$start, $end])->get();

        $reportProjectMetrics = [];
        foreach ($projects as $p) {
            $pOps = $repOps->where('project_id', $p->id);
            $pDiscounts = floatval($repDiscounts->where('project_id', $p->id)->sum('amount'));
            $pInvoices = $repInvoices->where('project_id', $p->id);

            $pCost = floatval($pOps->sum('total_cost'));
            $pBillableSelling = floatval($pOps->where('is_billable', 1)->sum('total_selling'));
            $pBillableCost = floatval($pOps->where('is_billable', 1)->sum('total_cost'));
            $pSupervisionProfit = max(0, $pBillableSelling - $pBillableCost);
            $pNetSelling = max(0, $pBillableSelling - $pDiscounts);
            $pNetProfit = $pNetSelling - $pCost;
            $pClientPaid = floatval($pInvoices->sum('paid_amount'));
            $pClientRemaining = max(0, $pNetSelling - $pClientPaid);
            $pMargin = $pNetSelling > 0 ? round(($pNetProfit / $pNetSelling) * 100, 1) : 0;

            $reportProjectMetrics[$p->id] = [
                'project_id'         => $p->id,
                'project_name'       => $p->name,
                'client_name'        => $p->client_name,
                'client_phone'       => $p->client_phone,
                'supervision_pct'    => $p->supervision_pct,
                'ops_count'          => $pOps->count(),
                'total_cost'         => $pCost,
                'billable_selling'   => $pBillableSelling,
                'supervision_profit' => $pSupervisionProfit,
                'discounts'          => $pDiscounts,
                'net_selling'        => $pNetSelling,
                'client_paid'        => $pClientPaid,
                'client_remaining'   => $pClientRemaining,
                'net_profit'         => $pNetProfit,
                'margin_pct'         => $pMargin,
            ];
        }

        $grandCost = collect($reportProjectMetrics)->sum('total_cost');
        $grandSelling = collect($reportProjectMetrics)->sum('net_selling');
        $grandProfit = $grandSelling - $grandCost;
        $avgMargin = $grandSelling > 0 ? round(($grandProfit / $grandSelling) * 100, 1) : 0;
        $grandPaid = collect($reportProjectMetrics)->sum('client_paid');
        $grandRemaining = collect($reportProjectMetrics)->sum('client_remaining');
        $collectionRate = $grandSelling > 0 ? round(($grandPaid / $grandSelling) * 100, 1) : 0;
        $supplierDebts = floatval(DB::table('company_debts')->where('source_type', 'like', 'cont_%')->sum('remaining_balance'));

        // 1. Most profitable project
        $topProfitProj = collect($reportProjectMetrics)->filter(fn($x) => $x['net_profit'] > 0)->sortByDesc('net_profit')->first();

        // 2. Most spending project
        $topSpendingProj = collect($reportProjectMetrics)->filter(fn($x) => $x['total_cost'] > 0)->sortByDesc('total_cost')->first();

        // 3. Most discount project
        $topDiscountProj = collect($reportProjectMetrics)->filter(fn($x) => $x['discounts'] > 0)->sortByDesc('discounts')->first();

        // 4. Top marketer
        $topMarketer = $repOps->where('op_type', 'commission')->whereNotNull('person_name')->where('person_name', '!=', '')->groupBy('person_name')->map(function($rows, $name) {
            return [
                'name'             => $name,
                'total_commission' => floatval($rows->sum('total_cost')),
                'ops_count'        => $rows->count(),
            ];
        })->sortByDesc('total_commission')->first();

        // 5. Top cost operation
        $topCostOpRaw = $repOps->sortByDesc('total_cost')->first();
        $topCostOp = null;
        if ($topCostOpRaw && $topCostOpRaw->total_cost > 0) {
            $p = $projectsMap[$topCostOpRaw->project_id] ?? null;
            $topCostOp = [
                'title'        => $topCostOpRaw->name_or_title,
                'cost'         => floatval($topCostOpRaw->total_cost),
                'qty'          => floatval($topCostOpRaw->quantity),
                'unit'         => $topCostOpRaw->unit,
                'op_type'      => $topCostOpRaw->op_type,
                'project_id'   => $topCostOpRaw->project_id,
                'project_name' => $p ? $p->name : 'مشروع عام',
                'client_name'  => $p ? $p->client_name : '',
            ];
        }

        // 6. Top profit operation
        $topProfitOpRaw = $repOps->filter(fn($x) => $x->profit_amount > 0)->sortByDesc('profit_amount')->first();
        $topProfitOp = null;
        if ($topProfitOpRaw) {
            $p = $projectsMap[$topProfitOpRaw->project_id] ?? null;
            $topProfitOp = [
                'title'        => $topProfitOpRaw->name_or_title,
                'profit'       => floatval($topProfitOpRaw->profit_amount),
                'cost'         => floatval($topProfitOpRaw->total_cost),
                'selling'      => floatval($topProfitOpRaw->total_selling),
                'qty'          => floatval($topProfitOpRaw->quantity),
                'unit'         => $topProfitOpRaw->unit,
                'project_id'   => $topProfitOpRaw->project_id,
                'project_name' => $p ? $p->name : 'مشروع عام',
                'client_name'  => $p ? $p->client_name : '',
            ];
        }

        // 7. Top technician
        $topTech = $repOps->where('op_type', 'technician')->whereNotNull('person_name')->where('person_name', '!=', '')->groupBy('person_name')->map(function($rows, $name) {
            return [
                'name'           => $name ?: 'فني عام',
                'total_cost'     => floatval($rows->sum('total_cost')),
                'paid_amount'    => floatval($rows->sum('paid_amount')),
                'remaining_debt' => floatval($rows->sum('remaining_debt')),
                'jobs_count'     => $rows->count(),
            ];
        })->sortByDesc('total_cost')->first();

        // 8. Top material
        $topMaterial = $repOps->where('op_type', 'material')->groupBy('name_or_title')->map(function($rows, $name) {
            return [
                'name'       => $name,
                'total_cost' => floatval($rows->sum('total_cost')),
                'total_qty'  => floatval($rows->sum('quantity')),
                'count'      => $rows->count(),
                'unit'       => $rows->first()->unit ?? '',
            ];
        })->sortByDesc('total_cost')->first();

        // 9. Expenses by category
        $opTypeLabels = [
            'material'   => 'خامات ومشتريات',
            'labor'      => 'مصنعيات وعمالة',
            'technician' => 'فنيين متخصصين',
            'commission' => 'عمولات تسويق',
            'transport'  => 'نقل ومشال',
            'buffet'     => 'بوفيه ونثريات',
            'maintenance'=> 'صيانة ومعدات',
            'other'      => 'أخرى عامة',
        ];

        $expensesByCategory = [];
        foreach ($opTypeLabels as $k => $lbl) {
            $matchingOps = $repOps->where('op_type', $k);
            $c = floatval($matchingOps->sum('total_cost'));
            if ($c > 0 || $matchingOps->count() > 0) {
                $expensesByCategory[] = [
                    'key'   => $k,
                    'label' => $lbl,
                    'cost'  => $c,
                    'count' => $matchingOps->count(),
                    'pct'   => $grandCost > 0 ? round(($c / $grandCost) * 100, 1) : 0,
                ];
            }
        }
        usort($expensesByCategory, fn($a, $b) => $b['cost'] <=> $a['cost']);

        // 10. Recent Operations
        $recentOperations = $repOps->sortByDesc('created_at')->take(20)->map(function($op) use ($projectsMap) {
            $p = $projectsMap[$op->project_id] ?? null;
            return [
                'id'           => $op->id,
                'date'         => substr($op->created_at, 0, 10),
                'project_name' => $p ? $p->name : 'عام',
                'title'        => $op->name_or_title,
                'op_type'      => $op->op_type,
                'cost'         => floatval($op->total_cost),
                'selling'      => floatval($op->total_selling),
                'profit'       => floatval($op->profit_amount),
            ];
        })->values();

        // 11. Daily Trend
        $dailyTrend = [];
        $period = $start->copy();
        while ($period->lte($end)) {
            $dayStart = $period->copy()->startOfDay();
            $dayEnd   = $period->copy()->endOfDay();
            $dayOps = $repOps->filter(fn($o) => Carbon::parse($o->created_at)->between($dayStart, $dayEnd));
            $dailyTrend[] = [
                'date'    => $period->format('Y-m-d'),
                'label'   => $period->format('m-d'),
                'revenue' => (float) $dayOps->sum('total_selling'),
                'cost'    => (float) $dayOps->sum('total_cost'),
                'profit'  => (float) $dayOps->sum('profit_amount'),
                'count'   => $dayOps->count(),
            ];
            $period->addDay();
        }

        return [
            'grandCost'            => $grandCost,
            'grandSelling'         => $grandSelling,
            'grandProfit'          => $grandProfit,
            'avgMargin'            => $avgMargin,
            'grandPaid'            => $grandPaid,
            'grandRemaining'       => $grandRemaining,
            'collectionRate'       => $collectionRate,
            'supplierDebts'        => $supplierDebts,
            'topProfitProj'        => $topProfitProj,
            'topSpendingProj'      => $topSpendingProj,
            'topDiscountProj'      => $topDiscountProj,
            'topMarketer'          => $topMarketer,
            'topCostOp'            => $topCostOp,
            'topProfitOp'          => $topProfitOp,
            'topTech'              => $topTech,
            'topMaterial'          => $topMaterial,
            'expensesByCategory'   => $expensesByCategory,
            'reportProjectMetrics' => $reportProjectMetrics,
            'recentOperations'     => $recentOperations,
            'dailyTrend'           => $dailyTrend,
        ];
    }

    public function sendDailyReport(Request $request)
    {
        return back()->with('success', 'تم إرسال التقرير اليومي.');
    }
}
