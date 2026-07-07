<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

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
        $snapPeriod   = $request->input('snap_period', '3months');

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

        // تفصيل الأرباح حسب المصدر — بيتجمّع من نتائج التابات نفسها (صفر queries إضافية)
        // عشان الأرقام تطابق كل تاب بالظبط، ويظهر في تاب الحركة المالية.
        $profitBreakdown = self::assembleProfitBreakdown($inv, $services, $inst, $gas);

        // ─── نطاق زمني منفصل لشارت السنابات ───
        $snapEndDate   = Carbon::now()->endOfDay();
        $snapStartDate = match($snapPeriod) {
            '1month'  => Carbon::now()->subMonth()->startOfDay(),
            '6months' => Carbon::now()->subMonths(6)->startOfDay(),
            'year'    => Carbon::now()->subYear()->startOfDay(),
            'custom'  => $snapFrom ? Carbon::parse($snapFrom)->startOfDay() : Carbon::now()->subMonths(3)->startOfDay(),
            default   => Carbon::now()->subMonths(3)->startOfDay(),
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
            'inv', 'services', 'inst', 'gas', 'fin', 'profitBreakdown',
            'snapPeriod', 'snapFrom', 'snapTo', 'capitalTrendFiltered'
        ));
    }

    /**
     * تفصيل الأرباح حسب المصدر من نتائج التابات الجاهزة — بدون أي queries إضافية،
     * فالأرقام مضمون تطابق كل تاب بالظبط.
     */
    public static function assembleProfitBreakdown(array $inv, array $services, array $inst, array $gas): array
    {
        $rows = [
            'installmentInterest' => (float) ($inst['interestProfit'] ?? 0),   // ربح النسبة (الفايدة)
            'installmentProduct'  => (float) ($inst['productProfit'] ?? 0),    // ربح منتجات الأقساط
            'inventory'           => (float) ($inv['invSalesProfit'] ?? 0),    // ربح المخزن (بيع − شراء)
            'services'            => (float) ($services['servicesProfit'] ?? 0), // ربح الخدمات
            'gas'                 => (float) ($gas['netProfit'] ?? 0),         // صافي عمولة البنزينة
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
            $this->gasReport($range)
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
                'label'   => $period->format('m-d'),
                'count'   => $daySales->count(),
                'value'   => (float) $daySales->sum('total_after_interest'),
            ];
            $period->addDay();
            if (count($dailyTrend) > 60) break;
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
                'label'   => $period->format('m-d'),
                'revenue' => (float) $day->sum('total_after_interest'),
                'profit'  => (float) $day->sum('profit'),
                'count'   => $day->count(),
            ];
            $period->addDay();
            if (count($dailyTrend) > 60) break;
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
                'label'    => $period->format('m-d'),
                'new'      => $newC,
                'paid'     => (float) $paid,
            ];
            $period->addDay();
            if (count($dailyTrend) > 60) break;
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
                'label'  => $period->format('m-d'),
                'liters' => (float) $day->sum('liters'),
                'profit' => (float) $day->sum('ahmed_profit'),
            ];
            $period->addDay();
            if (count($dailyTrend) > 60) break;
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
                'label'    => $period->format('m-d'),
                'income'   => (float) $dayTx->where('type', 'income')->sum('amount'),
                'expense'  => (float) $dayTx->whereIn('type', ['general_expense', 'salary_expense', 'discount'])->sum('amount'),
            ];
            $period->addDay();
            if (count($dailyTrend) > 60) break;
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

    public function sendDailyReport(Request $request)
    {
        return back()->with('success', 'تم إرسال التقرير اليومي.');
    }
}
