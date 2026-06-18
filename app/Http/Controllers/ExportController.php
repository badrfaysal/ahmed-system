<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends SystemController
{
    /**
     * تصدير الخزنة والأرباح كـ Excel (.xls).
     * يحتوي على: ملخص رأس المال، أرصدة الخزن، تفصيل الأرباح، الاستقطاعات،
     * مستحقات وديون البنزينة.
     */
    public function treasury(Request $request)
    {
        // ─── جلب نفس البيانات اللي بتظهر في شاشة الخزنة ───
        $projectIds = [12, 30];
        $all_liquidity_accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $liquidity_accounts     = $all_liquidity_accounts->whereNotIn('id', $projectIds);
        $projects               = DB::table('accounts')->where('category', 'project_sector')->orWhereIn('id', $projectIds)->get();

        $summary = \App\Services\InstallmentFinanceService::treasurySummary();

        $gas_receivables = (float) DB::table('installments')
            ->where('category', 'بنزينة')->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        $gas_payables_stations = (float) DB::table('company_debts')
            ->where('category', 'وقود')->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        $gas_payables_deductions = (float) DB::table('company_debts')
            ->where('category', 'استقطاعات')->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        // الفلتر الزمني للأرباح
        $pf     = $request->input('profit_filter', 'all');
        $pfFrom = $request->input('profit_from_date');
        $pfTo   = $request->input('profit_to_date');
        $rangeLabel = 'كل الفترات';
        $startDate = null;
        $endDate   = null;

        if ($pf !== 'all') {
            switch ($pf) {
                case 'today':     $startDate = now()->startOfDay(); $endDate = now()->endOfDay(); $rangeLabel = 'اليوم'; break;
                case 'yesterday': $startDate = now()->subDay()->startOfDay(); $endDate = now()->subDay()->endOfDay(); $rangeLabel = 'أمس'; break;
                case 'week':      $startDate = now()->startOfWeek(Carbon::SATURDAY); $endDate = now()->endOfWeek(Carbon::FRIDAY); $rangeLabel = 'هذا الأسبوع'; break;
                case 'month':     $startDate = now()->startOfMonth(); $endDate = now()->endOfMonth(); $rangeLabel = 'هذا الشهر'; break;
                case 'custom':
                    if ($pfFrom && $pfTo) {
                        $startDate = Carbon::parse($pfFrom)->startOfDay();
                        $endDate   = Carbon::parse($pfTo)->endOfDay();
                        $rangeLabel = "من {$pfFrom} إلى {$pfTo}";
                    }
                    break;
            }
        }

        // حساب الأرباح بناءً على الفلتر
        if ($startDate && $endDate) {
            $applyDate = fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]);

            $profit_installments = (float) $applyDate(DB::table('installments'))
                ->where('installment_months', '>', 0)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
                ->selectRaw('COALESCE(SUM(total_after_interest - cash_price), 0) as total')
                ->value('total');

            $profit_inventory = (float) $applyDate(DB::table('installments'))
                ->where('installment_months', 0)
                ->where(function ($q) { $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory'); })
                ->where('status', '!=', 'cancelled')->sum('profit');

            $profit_direct = (float) $applyDate(DB::table('installments'))
                ->where('installment_months', 0)->where('category', 'مبيعات مباشرة')
                ->where('status', '!=', 'cancelled')->sum('profit');

            $profit_services = (float) $applyDate(DB::table('installments'))
                ->where('category', 'خدمات')->where('status', '!=', 'cancelled')->sum('profit');

            $profit_gas = (float) $applyDate(DB::table('fuel_transactions')->whereNull('superseded_by'))
                ->sum('ahmed_profit');

            $fb = \App\Services\InstallmentFinanceService::financialBreakdown($startDate, $endDate);
            $profit_asset_sales = $fb['profitAssetSales'];
            $expenses_general   = $fb['expensesGeneral'];
            $expenses_salaries  = $fb['expensesSalaries'];
            $total_commissions  = $fb['totalCommissions'];
            $losses_depreciation= $fb['lossesDepreciation'];
            $losses_returns     = $fb['lossesReturns'];
            $losses_discounts   = $fb['lossesDiscounts'];
            $losses_bad_debts   = $fb['lossesBadDebts'];
            $losses_asset_sales = $fb['lossesAssetSales'];
            $losses_inventory_shortage = $fb['lossesInventoryShortage'];
        } else {
            $profit_installments = $summary['profitInstallments'];
            $profit_inventory    = $summary['profitInventory'];
            $profit_direct       = $summary['profitDirectProducts'];
            $profit_services     = $summary['profitServices'];
            $profit_gas          = $summary['profitGas'];
            $profit_asset_sales  = $summary['profitAssetSales'];
            $expenses_general    = $summary['expensesGeneral'];
            $expenses_salaries   = $summary['expensesSalaries'];
            $total_commissions   = $summary['totalCommissions'];
            $losses_depreciation = $summary['lossesDepreciation'];
            $losses_returns      = $summary['lossesReturns'];
            $losses_discounts    = $summary['lossesDiscounts'];
            $losses_bad_debts    = $summary['lossesBadDebts'];
            $losses_asset_sales  = $summary['lossesAssetSales'];
            $losses_inventory_shortage = $summary['lossesInventoryShortage'] ?? 0;
        }

        $total_revenue = $profit_installments + $profit_inventory + $profit_direct
                       + $profit_services + $profit_gas + $profit_asset_sales;
        $total_deductions = $expenses_general + $expenses_salaries + $total_commissions
                          + $losses_depreciation + $losses_returns + $losses_discounts
                          + $losses_bad_debts + $losses_asset_sales + $losses_inventory_shortage;
        $net_profit = $total_revenue - $total_deductions;

        $profit_breakdown = [
            ['label' => 'أرباح التقسيط (الآجل)',         'value' => $profit_installments],
            ['label' => 'أرباح مبيعات المخزن',           'value' => $profit_inventory],
            ['label' => 'أرباح البيع المباشر (منتجات)',  'value' => $profit_direct],
            ['label' => 'أرباح الخدمات (صيانة/تركيب)',   'value' => $profit_services],
            ['label' => 'أرباح محطة الوقود',             'value' => $profit_gas],
            ['label' => 'أرباح بيع الأصول',              'value' => $profit_asset_sales],
        ];

        $deductions_breakdown = [
            ['label' => 'مصاريف تشغيلية وعامة',  'value' => $expenses_general],
            ['label' => 'رواتب الموظفين',         'value' => $expenses_salaries],
            ['label' => 'عمولات المحافظ',         'value' => $total_commissions],
            ['label' => 'إهلاكات الأصول',         'value' => $losses_depreciation],
            ['label' => 'خسائر المرتجعات',        'value' => $losses_returns],
            ['label' => 'خصومات للعملاء',         'value' => $losses_discounts],
            ['label' => 'إعدام ديون وخسائر',      'value' => $losses_bad_debts],
            ['label' => 'خسائر بيع أصول',         'value' => $losses_asset_sales],
            ['label' => 'خسائر وعجز جرد المخزن',  'value' => $losses_inventory_shortage],
        ];

        $html = $this->renderTreasuryXls(
            $liquidity_accounts, $projects, $summary,
            $profit_breakdown, $deductions_breakdown,
            $gas_receivables, $gas_payables_stations, $gas_payables_deductions,
            $total_revenue, $total_deductions, $net_profit, $rangeLabel
        );

        return $this->xlsResponse($html, 'الخزنة_والأرباح_' . now()->format('Y-m-d_His'));
    }

    /**
     * تصدير التقارير كـ Excel (.xls) — كل تاب في قسم منفصل في الملف.
     */
    public function reports(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'month');
        $customFrom = $request->input('custom_from');
        $customTo   = $request->input('custom_to');

        $startDate = Carbon::now()->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();
        $rangeLabel = 'هذا الشهر';

        switch ($dateFilter) {
            case 'today':     $startDate = Carbon::now()->startOfDay(); $endDate = Carbon::now()->endOfDay(); $rangeLabel = 'اليوم'; break;
            case 'yesterday': $startDate = Carbon::now()->subDay()->startOfDay(); $endDate = Carbon::now()->subDay()->endOfDay(); $rangeLabel = 'أمس'; break;
            case 'week':      $startDate = Carbon::now()->startOfWeek(Carbon::SATURDAY); $endDate = Carbon::now()->endOfWeek(Carbon::FRIDAY); $rangeLabel = 'هذا الأسبوع'; break;
            case 'month':     $startDate = Carbon::now()->startOfMonth(); $endDate = Carbon::now()->endOfMonth(); $rangeLabel = 'هذا الشهر'; break;
            case 'year':      $startDate = Carbon::now()->startOfYear(); $endDate = Carbon::now()->endOfYear(); $rangeLabel = 'هذا العام'; break;
            case 'custom':
                if ($customFrom) $startDate = Carbon::parse($customFrom)->startOfDay();
                if ($customTo)   $endDate   = Carbon::parse($customTo)->endOfDay();
                $rangeLabel = "من {$customFrom} إلى {$customTo}";
                break;
        }

        // ═══════════════════════════════════════════════════════════════
        // 📦 المخزن — التوريد بالفئات
        // ═══════════════════════════════════════════════════════════════
        $purchasesRaw = DB::table('sales')->where('inventory_status', 'to_inventory')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('category', 'supplier_name',
                DB::raw('COUNT(*) as batches'),
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(purchase_price * quantity) as total_cost'))
            ->groupBy('category', 'supplier_name')->get();

        $invPurchasesCount = (int) $purchasesRaw->sum('batches');
        $invPurchasesCost  = (float) $purchasesRaw->sum('total_cost');

        // فئة → [batches, qty, cost, suppliers[]]
        $purchasesByCategory = [];
        foreach ($purchasesRaw as $r) {
            $cat = $r->category ?: 'بدون فئة';
            if (!isset($purchasesByCategory[$cat])) {
                $purchasesByCategory[$cat] = ['batches'=>0,'qty'=>0,'cost'=>0,'suppliers'=>[]];
            }
            $purchasesByCategory[$cat]['batches'] += (int)$r->batches;
            $purchasesByCategory[$cat]['qty']     += (float)$r->total_qty;
            $purchasesByCategory[$cat]['cost']    += (float)$r->total_cost;
            $sup = $r->supplier_name ?: 'غير محدد';
            if (!isset($purchasesByCategory[$cat]['suppliers'][$sup])) {
                $purchasesByCategory[$cat]['suppliers'][$sup] = 0;
            }
            $purchasesByCategory[$cat]['suppliers'][$sup] += (float)$r->total_cost;
        }
        uasort($purchasesByCategory, fn($a, $b) => $b['cost'] <=> $a['cost']);

        // ═══════════════════════════════════════════════════════════════
        // 🛒 المبيعات المباشرة
        // ═══════════════════════════════════════════════════════════════
        $directSales = DB::table('installments')->where('installment_months', 0)
            ->where('category', 'مبيعات مباشرة')->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])->get();
        $directCount   = $directSales->count();
        $directRevenue = (float) $directSales->sum('cash_price');
        $directProfit  = (float) $directSales->sum('profit');

        // أكثر منتجات البيع المباشر مبيعاً
        $directTopProducts = $directSales->groupBy('product_name')->map(fn($g) => [
            'count'   => $g->count(),
            'revenue' => (float) $g->sum('cash_price'),
            'profit'  => (float) $g->sum('profit'),
        ])->sortByDesc('revenue')->take(15);

        // ═══════════════════════════════════════════════════════════════
        // 🔧 الخدمات
        // ═══════════════════════════════════════════════════════════════
        $servicesData = DB::table('installments')->where('category', 'خدمات')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])->get();
        $servCount   = $servicesData->count();
        $servRevenue = (float) $servicesData->sum('cash_price');
        $servProfit  = (float) $servicesData->sum('profit');

        $servicesTopProducts = $servicesData->groupBy('product_name')->map(fn($g) => [
            'count'   => $g->count(),
            'revenue' => (float) $g->sum('cash_price'),
            'profit'  => (float) $g->sum('profit'),
        ])->sortByDesc('revenue')->take(10);

        // ═══════════════════════════════════════════════════════════════
        // 📝 الأقساط
        // ═══════════════════════════════════════════════════════════════
        $instData = DB::table('installments')->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])->get();
        $instCount     = $instData->count();
        $instContracted= (float) $instData->sum('total_after_interest');
        $instProfit    = (float) ($instData->sum('total_after_interest') - $instData->sum('cash_price'));

        // ═══════════════════════════════════════════════════════════════
        // 📤 مبيعات المخزن (parse inventory_items JSON → join with sales)
        // ═══════════════════════════════════════════════════════════════
        $invSales = DB::table('installments')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('inventory_items')
            ->where(function($q){ $q->where('sale_type', 'inventory')->orWhere('category', 'مبيعات مخزن'); })
            ->get();

        // اجمع كل sale_ids عشان ننزل query واحد للـ batches
        $saleIds = [];
        foreach ($invSales as $sale) {
            $items = json_decode($sale->inventory_items, true);
            if (is_array($items)) {
                foreach ($items as $it) $saleIds[] = (int)($it['sale_id'] ?? 0);
            }
        }
        $batchesById = DB::table('sales')
            ->whereIn('id', array_unique(array_filter($saleIds)))
            ->get()->keyBy('id');

        $invSalesByCategory = [];        // فئة → [qty, revenue, cost, profit]
        $invTopProducts     = [];        // product → [qty, revenue, profit]
        foreach ($invSales as $sale) {
            $items = json_decode($sale->inventory_items, true);
            if (!is_array($items)) continue;
            foreach ($items as $it) {
                $sid = (int)($it['sale_id'] ?? 0);
                $qty = (float)($it['qty'] ?? 0);
                $batch = $batchesById->get($sid);
                if (!$batch || $qty <= 0) continue;

                $cat   = $batch->category ?: 'بدون فئة';
                $name  = $batch->product_name ?: 'غير محدد';
                $price = (float)$batch->selling_price;
                $cost  = (float)$batch->purchase_price;
                $rev   = $qty * $price;
                $cst   = $qty * $cost;

                if (!isset($invSalesByCategory[$cat])) {
                    $invSalesByCategory[$cat] = ['qty'=>0,'revenue'=>0,'cost'=>0,'profit'=>0];
                }
                $invSalesByCategory[$cat]['qty']     += $qty;
                $invSalesByCategory[$cat]['revenue'] += $rev;
                $invSalesByCategory[$cat]['cost']    += $cst;
                $invSalesByCategory[$cat]['profit']  += ($rev - $cst);

                if (!isset($invTopProducts[$name])) {
                    $invTopProducts[$name] = ['qty'=>0,'revenue'=>0,'profit'=>0,'category'=>$cat];
                }
                $invTopProducts[$name]['qty']     += $qty;
                $invTopProducts[$name]['revenue'] += $rev;
                $invTopProducts[$name]['profit']  += ($rev - $cst);
            }
        }
        uasort($invSalesByCategory, fn($a,$b) => $b['revenue'] <=> $a['revenue']);
        uasort($invTopProducts, fn($a,$b) => $b['revenue'] <=> $a['revenue']);
        $invTopProducts = array_slice($invTopProducts, 0, 15, true);

        // ═══════════════════════════════════════════════════════════════
        // 🔄 المرتجعات (عملاء + موردين)
        // ═══════════════════════════════════════════════════════════════
        // مرتجعات العملاء
        $custReturns = DB::table('inventory_movements as m')
            ->leftJoin('sales as s', 'm.sale_id', '=', 's.id')
            ->where('m.type', 'customer_return')
            ->whereBetween('m.created_at', [$startDate, $endDate])
            ->select('m.quantity', 's.product_name', 's.category', 's.purchase_price')
            ->get();
        $custReturnsByProduct = [];
        foreach ($custReturns as $r) {
            $name = $r->product_name ?: 'غير معروف';
            if (!isset($custReturnsByProduct[$name])) {
                $custReturnsByProduct[$name] = ['qty'=>0, 'value'=>0, 'category'=>$r->category ?: 'بدون فئة'];
            }
            $custReturnsByProduct[$name]['qty']   += (float)$r->quantity;
            $custReturnsByProduct[$name]['value'] += (float)$r->quantity * (float)$r->purchase_price;
        }
        uasort($custReturnsByProduct, fn($a,$b) => $b['qty'] <=> $a['qty']);
        $custReturnsByProduct = array_slice($custReturnsByProduct, 0, 10, true);
        $custReturnsTotalQty   = (float) collect($custReturnsByProduct)->sum('qty');
        $custReturnsTotalValue = (float) collect($custReturnsByProduct)->sum('value');

        // مرتجعات الموردين
        $supReturns = DB::table('inventory_movements as m')
            ->leftJoin('sales as s', 'm.sale_id', '=', 's.id')
            ->where('m.type', 'supplier_return')
            ->whereBetween('m.created_at', [$startDate, $endDate])
            ->select('m.quantity', 's.product_name', 's.category', 's.supplier_name', 's.purchase_price')
            ->get();
        $supReturnsByProduct = [];
        foreach ($supReturns as $r) {
            $key = ($r->product_name ?: 'غير معروف') . ' • ' . ($r->supplier_name ?: 'غير محدد');
            if (!isset($supReturnsByProduct[$key])) {
                $supReturnsByProduct[$key] = [
                    'product'  => $r->product_name ?: 'غير معروف',
                    'supplier' => $r->supplier_name ?: 'غير محدد',
                    'category' => $r->category ?: 'بدون فئة',
                    'qty'      => 0, 'value' => 0,
                ];
            }
            $supReturnsByProduct[$key]['qty']   += (float)$r->quantity;
            $supReturnsByProduct[$key]['value'] += (float)$r->quantity * (float)$r->purchase_price;
        }
        uasort($supReturnsByProduct, fn($a,$b) => $b['qty'] <=> $a['qty']);
        $supReturnsByProduct = array_slice($supReturnsByProduct, 0, 10, true);
        $supReturnsTotalQty   = (float) collect($supReturnsByProduct)->sum('qty');
        $supReturnsTotalValue = (float) collect($supReturnsByProduct)->sum('value');

        // ═══════════════════════════════════════════════════════════════
        // ⛽ البنزينة
        // ═══════════════════════════════════════════════════════════════
        $fuel = DB::table('fuel_transactions')->whereNull('superseded_by')
            ->whereBetween('created_at', [$startDate, $endDate])->get();
        $fuelCount      = $fuel->count();
        $fuelLiters     = (float) $fuel->sum('liters');
        $fuelAdvances   = (float) $fuel->sum('cash_advance');
        $fuelToStation  = (float) $fuel->sum('total_to_station');
        $fuelOnCompany  = (float) $fuel->sum('total_on_company');
        $fuelNetProfit  = (float) $fuel->sum('ahmed_profit');

        $companies = DB::table('transport_companies')->get()->keyBy('id');
        $stations  = DB::table('gas_stations')->get()->keyBy('id');

        $topCompanies = $fuel->groupBy('company_id')->map(fn($g, $id) => [
            'name'    => $companies->get($id)->company_name ?? "شركة #{$id}",
            'count'   => $g->count(),
            'liters'  => (float) $g->sum('liters'),
            'on_them' => (float) $g->sum('total_on_company'),
            'profit'  => (float) $g->sum('ahmed_profit'),
        ])->sortByDesc('on_them')->take(15)->values();

        $topStations = $fuel->groupBy('station_id')->map(fn($g, $id) => [
            'name'   => $stations->get($id)->station_name ?? "محطة #{$id}",
            'count'  => $g->count(),
            'liters' => (float) $g->sum('liters'),
            'paid'   => (float) $g->sum('total_to_station'),
        ])->sortByDesc('paid')->take(15)->values();

        // ═══════════════════════════════════════════════════════════════
        // 💰 الماليات
        // ═══════════════════════════════════════════════════════════════
        $expensesGen = (float) DB::table('financial_transactions')
            ->whereIn('type', ['general_expense', 'expense'])->where('status', 'active')
            ->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $salaries = (float) DB::table('financial_transactions')
            ->where('type', 'salary_expense')->where('status', 'active')
            ->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $discountsTotal = (float) DB::table('financial_transactions')
            ->where('type', 'discount')->where('status', 'active')
            ->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $incomes = (float) DB::table('financial_transactions')
            ->where('type', 'income')->where('status', 'active')
            ->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $commissions = (float) DB::table('financial_transactions')
            ->where('type', 'commission')->where('status', 'active')
            ->whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        // ═══════════════════════════════════════════════════════════════
        // 📊 الإجماليات
        // ═══════════════════════════════════════════════════════════════
        $totalRevenue = $directRevenue + $servRevenue + (float)collect($invSalesByCategory)->sum('revenue');
        $totalProfit  = $directProfit + $servProfit + $instProfit + (float)collect($invSalesByCategory)->sum('profit') + $fuelNetProfit;
        $totalCost    = $invPurchasesCost;
        $totalDeductions = $expensesGen + $salaries + $discountsTotal + $commissions;
        $netResult   = $totalProfit - $totalDeductions;

        $html = $this->renderReportsXls([
            'rangeLabel' => $rangeLabel,
            'date_from'  => $startDate->format('Y/m/d'),
            'date_to'    => $endDate->format('Y/m/d'),
            'inv' => [
                'count'         => $invPurchasesCount,
                'cost'          => $invPurchasesCost,
                'by_category'   => $purchasesByCategory,
            ],
            'inv_sales' => [
                'by_category' => $invSalesByCategory,
                'top'         => $invTopProducts,
            ],
            'direct' => [
                'count' => $directCount, 'revenue' => $directRevenue, 'profit' => $directProfit,
                'top'   => $directTopProducts,
            ],
            'services' => [
                'count' => $servCount, 'revenue' => $servRevenue, 'profit' => $servProfit,
                'top'   => $servicesTopProducts,
            ],
            'installments' => ['count' => $instCount, 'contracted' => $instContracted, 'profit' => $instProfit],
            'gas' => [
                'count' => $fuelCount, 'liters' => $fuelLiters, 'advances' => $fuelAdvances,
                'to_station' => $fuelToStation, 'on_company' => $fuelOnCompany, 'profit' => $fuelNetProfit,
                'top_companies' => $topCompanies, 'top_stations' => $topStations,
            ],
            'returns' => [
                'customer' => [
                    'items' => $custReturnsByProduct,
                    'qty'   => $custReturnsTotalQty,
                    'value' => $custReturnsTotalValue,
                ],
                'supplier' => [
                    'items' => $supReturnsByProduct,
                    'qty'   => $supReturnsTotalQty,
                    'value' => $supReturnsTotalValue,
                ],
            ],
            'fin' => [
                'incomes'           => $incomes,
                'expenses_general'  => $expensesGen,
                'salaries'          => $salaries,
                'discounts'         => $discountsTotal,
                'commissions'       => $commissions,
            ],
            'totals' => [
                'revenue'    => $totalRevenue,
                'cost'       => $totalCost,
                'profit'     => $totalProfit,
                'deductions' => $totalDeductions,
                'net'        => $netResult,
            ],
        ]);

        return $this->xlsResponse($html, 'التقرير_التفصيلي_' . now()->format('Y-m-d_His'));
    }

    // ───────────────────────────────────────────────────────────────────
    // Helpers — تصدير PDF عبر تشغيل dialog الطباعة في المتصفح
    // (المستخدم يختار "حفظ كـ PDF" من dialog الطباعة)
    // ───────────────────────────────────────────────────────────────────

    private function xlsResponse(string $html, string $filename)
    {
        // الـ filename بيتسم بيه title الصفحة عشان لو حفظ PDF يبقى الاسم منطقي
        $safeTitle = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
        $page = '<!DOCTYPE html><html dir="rtl" lang="ar"><head>'
            . '<meta charset="UTF-8"><title>' . $safeTitle . '</title>'
            . '<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">'
            . '</head><body>' . $html
            . '<script>document.title=' . json_encode($filename) . ';'
            . 'window.addEventListener("load",function(){setTimeout(function(){window.print();},250);});'
            . '</script></body></html>';

        return response($page, 200, [
            'Content-Type'  => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function xlsHeader(string $title): string
    {
        $date = now()->format('Y-m-d h:i A');
        return '<style>'
            . '*{box-sizing:border-box;}'
            . 'body{font-family:"Cairo","Calibri",Arial,sans-serif;direction:rtl;margin:24px;color:#0f172a;background:#fff;}'
            . 'table{border-collapse:collapse;width:100%;margin-bottom:18px;page-break-inside:auto;}'
            . 'tr{page-break-inside:avoid;page-break-after:auto;}'
            . 'th,td{border:1px solid #94a3b8;padding:8px 10px;text-align:right;font-size:11pt;}'
            . 'th{background:#1e3a8a;color:#fff;font-weight:bold;}'
            . '.section-title{background:#fbbf24;color:#1f2937;font-size:13pt;font-weight:900;padding:9px;text-align:center;margin-top:14px;border-radius:6px;}'
            . '.total-row{background:#dcfce7;font-weight:bold;}'
            . '.neg{color:#dc2626;font-weight:bold;}'
            . '.pos{color:#16a34a;font-weight:bold;}'
            . '.page-title{font-size:20pt;font-weight:900;color:#1e3a8a;text-align:center;margin-bottom:4px;}'
            . '.subtitle{font-size:10pt;color:#64748b;text-align:center;margin-bottom:10px;font-weight:700;}'
            . '.print-bar{background:#0f172a;color:#fff;padding:10px 16px;border-radius:8px;text-align:center;margin-bottom:16px;font-weight:700;}'
            . '.print-bar button{background:#3b82f6;color:#fff;border:0;padding:6px 16px;border-radius:6px;font-weight:800;cursor:pointer;margin:0 4px;font-family:inherit;}'
            . '.print-bar button.gray{background:#475569;}'
            . '@media print{.print-bar{display:none;}body{margin:8mm;}}'
            . '@page{size:A4;margin:10mm;}'
            . '</style>'
            . '<div class="print-bar">📄 معاينة قبل الطباعة — اختار "حفظ كـ PDF" من قائمة الطابعة لتصدير الملف '
            . '<button onclick="window.print()">🖨️ طباعة / PDF</button>'
            . '<button class="gray" onclick="window.close()">إغلاق</button>'
            . '</div>'
            . '<div class="page-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div class="subtitle">تاريخ التصدير: ' . $date . '</div>';
    }

    private function xlsFooter(): string
    {
        return '';
    }

    private function renderTreasuryXls(
        $liquidity_accounts, $projects, $summary,
        $profit_breakdown, $deductions_breakdown,
        $gas_receivables, $gas_payables_stations, $gas_payables_deductions,
        $total_revenue, $total_deductions, $net_profit, $rangeLabel
    ): string {
        $h = $this->xlsHeader('تقرير الخزنة والأرباح — شركة الضبع');
        $h .= '<div class="subtitle">نطاق الأرباح: ' . htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8') . '</div>';

        $f = fn($n) => number_format((float) $n, 2);

        // ─── 1. ملخص رأس المال ───
        $h .= '<div class="section-title">ملخص رأس المال</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة (ج.م)</th></tr>';
        $h .= '<tr><td>السيولة في الخزن والبنوك</td><td>' . $f($summary['liquidity']) . '</td></tr>';
        $h .= '<tr><td>قيمة المشاريع</td><td>' . $f($summary['projectsValue']) . '</td></tr>';
        $h .= '<tr><td>قيمة المخزون</td><td>' . $f($summary['inventoryAssets']) . '</td></tr>';
        $h .= '<tr><td>الأصول الثابتة</td><td>' . $f($summary['fixedAssets']) . '</td></tr>';
        $h .= '<tr><td>المستحقات على العملاء</td><td>' . $f($summary['totalDebtsForUs']) . '</td></tr>';
        $h .= '<tr><td>مستحقات البنزينة (شركات النقل)</td><td>' . $f($summary['gasReceivables'] ?? $gas_receivables) . '</td></tr>';
        $h .= '<tr><td>الديون على الشركة</td><td class="neg">' . $f($summary['totalDebtsOnUs']) . '</td></tr>';
        $h .= '<tr class="total-row"><td>صافي رأس المال</td><td>' . $f($summary['capital']) . '</td></tr>';
        $h .= '</table>';

        // ─── 2. الخزن والبنوك ───
        $h .= '<div class="section-title">أرصدة الخزن والبنوك</div>';
        $h .= '<table><tr><th>الخزنة / البنك</th><th>الفئة</th><th>الرصيد (ج.م)</th></tr>';
        $totalAcc = 0;
        foreach ($liquidity_accounts as $a) {
            $cat = $a->category === 'bank_wallet' ? 'بنك / محفظة' : 'خزنة نقدية';
            $h .= '<tr><td>' . htmlspecialchars($a->account_name, ENT_QUOTES, 'UTF-8') . '</td><td>' . $cat . '</td><td>' . $f($a->balance) . '</td></tr>';
            $totalAcc += (float) $a->balance;
        }
        $h .= '<tr class="total-row"><td colspan="2">إجمالي السيولة</td><td>' . $f($totalAcc) . '</td></tr>';
        $h .= '</table>';

        if ($projects->count() > 0) {
            $h .= '<div class="section-title">المشاريع</div>';
            $h .= '<table><tr><th>المشروع</th><th>القيمة (ج.م)</th></tr>';
            $totalP = 0;
            foreach ($projects as $p) {
                $h .= '<tr><td>' . htmlspecialchars($p->account_name, ENT_QUOTES, 'UTF-8') . '</td><td>' . $f($p->balance) . '</td></tr>';
                $totalP += (float) $p->balance;
            }
            $h .= '<tr class="total-row"><td>الإجمالي</td><td>' . $f($totalP) . '</td></tr>';
            $h .= '</table>';
        }

        // ─── 3. الأرباح ───
        $h .= '<div class="section-title">تفصيل الأرباح</div>';
        $h .= '<table><tr><th>المصدر</th><th>الربح (ج.م)</th></tr>';
        foreach ($profit_breakdown as $row) {
            $h .= '<tr><td>' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '</td><td class="pos">' . $f($row['value']) . '</td></tr>';
        }
        $h .= '<tr class="total-row"><td>إجمالي الأرباح</td><td>' . $f($total_revenue) . '</td></tr>';
        $h .= '</table>';

        // ─── 4. الاستقطاعات ───
        $h .= '<div class="section-title">المصروفات والخسائر</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة (ج.م)</th></tr>';
        foreach ($deductions_breakdown as $row) {
            $h .= '<tr><td>' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '</td><td class="neg">' . $f($row['value']) . '</td></tr>';
        }
        $h .= '<tr class="total-row"><td>إجمالي المصروفات والخسائر</td><td>' . $f($total_deductions) . '</td></tr>';
        $h .= '</table>';

        // ─── 5. صافي الربح ───
        $h .= '<table><tr class="total-row" style="background:#fef3c7;font-size:14pt;"><td>صافي الربح للفترة</td><td>' . $f($net_profit) . '</td></tr></table>';

        // ─── 6. البنزينة ───
        $h .= '<div class="section-title">البنزينة — مستحقات وديون قائمة</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة (ج.م)</th></tr>';
        $h .= '<tr><td>مستحقات على شركات النقل</td><td class="pos">' . $f($gas_receivables) . '</td></tr>';
        $h .= '<tr><td>ديون للمحطات</td><td class="neg">' . $f($gas_payables_stations) . '</td></tr>';
        $h .= '<tr><td>استقطاعات مستحقة</td><td class="neg">' . $f($gas_payables_deductions) . '</td></tr>';
        $h .= '</table>';

        $h .= $this->xlsFooter();
        return $h;
    }

    private function renderReportsXls(array $data): string
    {
        $f  = fn($n) => number_format((float) $n, 2);
        $f0 = fn($n) => number_format((float) $n, 0);
        $h  = $this->xlsHeader('تقرير تفصيلي شامل — شركة الضبع');
        $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

        // ═══ Cover summary ═══
        $h .= '<div class="cover">'
            . '<div class="cover-row"><span class="cover-label">📅 الفترة</span><span class="cover-val">' . $esc($data['rangeLabel']) . '</span></div>'
            . '<div class="cover-row"><span class="cover-label">📆 من</span><span class="cover-val ltr">' . $esc($data['date_from']) . '</span></div>'
            . '<div class="cover-row"><span class="cover-label">📆 إلى</span><span class="cover-val ltr">' . $esc($data['date_to']) . '</span></div>'
            . '</div>';

        // ═══ KPI strip ═══
        $tot = $data['totals'];
        $h .= '<div class="kpi-grid">'
            . '<div class="kpi kpi-blue"><div class="kpi-label">إجمالي الإيرادات</div><div class="kpi-value">' . $f0($tot['revenue']) . ' <small>ج</small></div></div>'
            . '<div class="kpi kpi-red"><div class="kpi-label">إجمالي تكلفة التوريد</div><div class="kpi-value">' . $f0($tot['cost']) . ' <small>ج</small></div></div>'
            . '<div class="kpi kpi-green"><div class="kpi-label">إجمالي الأرباح</div><div class="kpi-value">' . $f0($tot['profit']) . ' <small>ج</small></div></div>'
            . '<div class="kpi kpi-amber"><div class="kpi-label">المصروفات والخصومات</div><div class="kpi-value">' . $f0($tot['deductions']) . ' <small>ج</small></div></div>'
            . '<div class="kpi kpi-navy"><div class="kpi-label">صافي النتيجة</div><div class="kpi-value">' . $f0($tot['net']) . ' <small>ج</small></div></div>'
            . '</div>';

        // ─── 1) المخزن — التوريد بالفئات ───
        $h .= '<div class="section-title">📦 1. المشتريات والتوريد للمخزن</div>';
        $h .= '<div class="summary-row">'
            . '<span><b>عدد الباتشات:</b> ' . $f0($data['inv']['count']) . '</span>'
            . '<span><b>إجمالي التكلفة:</b> <span class="neg">' . $f($data['inv']['cost']) . ' ج</span></span>'
            . '</div>';

        if (count($data['inv']['by_category']) > 0) {
            $h .= '<h3 class="subhead">تفصيل التوريد حسب الفئة</h3>';
            $h .= '<table><tr>'
                . '<th>الفئة</th><th>عدد الباتشات</th><th>إجمالي الكمية</th><th>إجمالي التكلفة (ج.م)</th><th>% من التوريد</th><th>أبرز المورد</th>'
                . '</tr>';
            foreach ($data['inv']['by_category'] as $cat => $row) {
                $pct = $data['inv']['cost'] > 0 ? ($row['cost'] / $data['inv']['cost']) * 100 : 0;
                arsort($row['suppliers']);
                $topSup = array_key_first($row['suppliers']);
                $h .= '<tr>'
                    . '<td class="cat-cell">' . $esc($cat) . '</td>'
                    . '<td>' . $f0($row['batches']) . '</td>'
                    . '<td>' . $f0($row['qty']) . '</td>'
                    . '<td class="neg">' . $f($row['cost']) . '</td>'
                    . '<td><span class="pct">' . number_format($pct, 1) . '%</span></td>'
                    . '<td class="muted">' . $esc($topSup ?: '—') . '</td>'
                    . '</tr>';
            }
            $h .= '<tr class="total-row"><td>الإجمالي</td><td>' . $f0($data['inv']['count']) . '</td><td>—</td><td>' . $f($data['inv']['cost']) . '</td><td>100%</td><td>—</td></tr>';
            $h .= '</table>';
        }

        // ─── 2) مبيعات المخزن — حسب الفئة ───
        if (count($data['inv_sales']['by_category']) > 0) {
            $h .= '<div class="section-title">📤 2. مبيعات المخزن حسب الفئة</div>';
            $h .= '<table><tr>'
                . '<th>الفئة</th><th>الكمية المباعة</th><th>الإيرادات (ج.م)</th><th>التكلفة (ج.م)</th><th>الربح (ج.م)</th><th>نسبة الربح</th>'
                . '</tr>';
            $totSalesRev = $totSalesCost = $totSalesProfit = $totSalesQty = 0;
            foreach ($data['inv_sales']['by_category'] as $cat => $row) {
                $margin = $row['revenue'] > 0 ? ($row['profit'] / $row['revenue']) * 100 : 0;
                $totSalesQty    += $row['qty'];
                $totSalesRev    += $row['revenue'];
                $totSalesCost   += $row['cost'];
                $totSalesProfit += $row['profit'];
                $h .= '<tr>'
                    . '<td class="cat-cell">' . $esc($cat) . '</td>'
                    . '<td>' . $f0($row['qty']) . '</td>'
                    . '<td>' . $f($row['revenue']) . '</td>'
                    . '<td class="neg">' . $f($row['cost']) . '</td>'
                    . '<td class="pos">' . $f($row['profit']) . '</td>'
                    . '<td><span class="pct ' . ($margin >= 20 ? 'pct-good' : ($margin >= 10 ? 'pct-ok' : 'pct-low')) . '">' . number_format($margin, 1) . '%</span></td>'
                    . '</tr>';
            }
            $totMargin = $totSalesRev > 0 ? ($totSalesProfit / $totSalesRev) * 100 : 0;
            $h .= '<tr class="total-row">'
                . '<td>الإجمالي</td>'
                . '<td>' . $f0($totSalesQty) . '</td>'
                . '<td>' . $f($totSalesRev) . '</td>'
                . '<td>' . $f($totSalesCost) . '</td>'
                . '<td>' . $f($totSalesProfit) . '</td>'
                . '<td>' . number_format($totMargin, 1) . '%</td>'
                . '</tr>';
            $h .= '</table>';
        }

        // ─── 3) أكثر المنتجات مبيعاً من المخزن ───
        if (count($data['inv_sales']['top']) > 0) {
            $h .= '<div class="section-title">🏆 3. أكثر منتجات المخزن مبيعاً</div>';
            $h .= '<table><tr><th>الترتيب</th><th>المنتج</th><th>الفئة</th><th>الكمية</th><th>الإيرادات (ج.م)</th><th>الربح (ج.م)</th></tr>';
            $rank = 1;
            foreach ($data['inv_sales']['top'] as $name => $row) {
                $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#' . $rank));
                $h .= '<tr>'
                    . '<td class="rank-cell">' . $medal . '</td>'
                    . '<td><b>' . $esc($name) . '</b></td>'
                    . '<td class="cat-cell">' . $esc($row['category']) . '</td>'
                    . '<td>' . $f0($row['qty']) . '</td>'
                    . '<td>' . $f($row['revenue']) . '</td>'
                    . '<td class="pos">' . $f($row['profit']) . '</td>'
                    . '</tr>';
                $rank++;
            }
            $h .= '</table>';
        }

        // ─── 4) المبيعات المباشرة ───
        $h .= '<div class="section-title">🛒 4. المبيعات المباشرة (غير المخزون)</div>';
        $h .= '<div class="summary-row">'
            . '<span><b>عدد العمليات:</b> ' . $f0($data['direct']['count']) . '</span>'
            . '<span><b>الإيرادات:</b> ' . $f($data['direct']['revenue']) . ' ج</span>'
            . '<span><b>الربح:</b> <span class="pos">' . $f($data['direct']['profit']) . ' ج</span></span>'
            . '</div>';
        if ($data['direct']['top']->count() > 0) {
            $h .= '<h3 class="subhead">أعلى منتجات البيع المباشر</h3>';
            $h .= '<table><tr><th>المنتج</th><th>عدد البيعات</th><th>الإيرادات (ج.م)</th><th>الربح (ج.م)</th></tr>';
            foreach ($data['direct']['top'] as $name => $row) {
                $h .= '<tr>'
                    . '<td><b>' . $esc($name) . '</b></td>'
                    . '<td>' . $f0($row['count']) . '</td>'
                    . '<td>' . $f($row['revenue']) . '</td>'
                    . '<td class="pos">' . $f($row['profit']) . '</td>'
                    . '</tr>';
            }
            $h .= '</table>';
        }

        // ─── 5) الخدمات ───
        $h .= '<div class="section-title">🔧 5. الخدمات (صيانة / تركيب)</div>';
        $h .= '<div class="summary-row">'
            . '<span><b>عدد الخدمات:</b> ' . $f0($data['services']['count']) . '</span>'
            . '<span><b>الإيرادات:</b> ' . $f($data['services']['revenue']) . ' ج</span>'
            . '<span><b>الربح:</b> <span class="pos">' . $f($data['services']['profit']) . ' ج</span></span>'
            . '</div>';
        if ($data['services']['top']->count() > 0) {
            $h .= '<h3 class="subhead">أعلى الخدمات تنفيذاً</h3>';
            $h .= '<table><tr><th>الخدمة</th><th>عدد المرات</th><th>الإيرادات (ج.م)</th><th>الربح (ج.م)</th></tr>';
            foreach ($data['services']['top'] as $name => $row) {
                $h .= '<tr>'
                    . '<td><b>' . $esc($name) . '</b></td>'
                    . '<td>' . $f0($row['count']) . '</td>'
                    . '<td>' . $f($row['revenue']) . '</td>'
                    . '<td class="pos">' . $f($row['profit']) . '</td>'
                    . '</tr>';
            }
            $h .= '</table>';
        }

        // ─── 6) الأقساط (التقسيط) ───
        $h .= '<div class="section-title">📝 6. عقود التقسيط الجديدة</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة</th></tr>'
            . '<tr><td>عدد العقود</td><td>' . $f0($data['installments']['count']) . '</td></tr>'
            . '<tr><td>قيمة العقود الكلية (بعد الفائدة) ج.م</td><td>' . $f($data['installments']['contracted']) . '</td></tr>'
            . '<tr class="total-row"><td>صافي الأرباح المتعاقد عليها ج.م</td><td class="pos">' . $f($data['installments']['profit']) . '</td></tr>'
            . '</table>';

        // ─── 7) المرتجعات ───
        $cust = $data['returns']['customer'];
        $sup  = $data['returns']['supplier'];
        $h .= '<div class="section-title">🔄 7. المرتجعات</div>';

        // 7-أ) مرتجعات العملاء
        $h .= '<h3 class="subhead">📥 مرتجعات من العملاء (دخلت للمخزن)</h3>';
        $h .= '<div class="summary-row">'
            . '<span><b>إجمالي القطع المرتجعة:</b> ' . $f0($cust['qty']) . '</span>'
            . '<span><b>قيمتها التقريبية (بسعر الشراء):</b> ' . $f($cust['value']) . ' ج</span>'
            . '</div>';
        if (count($cust['items']) > 0) {
            $h .= '<table><tr><th>الترتيب</th><th>المنتج</th><th>الفئة</th><th>الكمية المرتجعة</th><th>القيمة (ج.م)</th></tr>';
            $rank = 1;
            foreach ($cust['items'] as $name => $row) {
                $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#' . $rank));
                $h .= '<tr>'
                    . '<td class="rank-cell">' . $medal . '</td>'
                    . '<td><b>' . $esc($name) . '</b></td>'
                    . '<td class="cat-cell">' . $esc($row['category']) . '</td>'
                    . '<td>' . $f0($row['qty']) . '</td>'
                    . '<td>' . $f($row['value']) . '</td>'
                    . '</tr>';
                $rank++;
            }
            $h .= '</table>';
        } else {
            $h .= '<div class="empty-note">— لا توجد مرتجعات من العملاء في هذه الفترة —</div>';
        }

        // 7-ب) مرتجعات الموردين
        $h .= '<h3 class="subhead">📤 مرتجعات للموردين (خرجت من المخزن)</h3>';
        $h .= '<div class="summary-row">'
            . '<span><b>إجمالي القطع المرتجعة:</b> ' . $f0($sup['qty']) . '</span>'
            . '<span><b>قيمتها التقريبية:</b> ' . $f($sup['value']) . ' ج</span>'
            . '</div>';
        if (count($sup['items']) > 0) {
            $h .= '<table><tr><th>الترتيب</th><th>المنتج</th><th>المورد</th><th>الفئة</th><th>الكمية</th><th>القيمة (ج.م)</th></tr>';
            $rank = 1;
            foreach ($sup['items'] as $row) {
                $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#' . $rank));
                $h .= '<tr>'
                    . '<td class="rank-cell">' . $medal . '</td>'
                    . '<td><b>' . $esc($row['product']) . '</b></td>'
                    . '<td class="muted">' . $esc($row['supplier']) . '</td>'
                    . '<td class="cat-cell">' . $esc($row['category']) . '</td>'
                    . '<td>' . $f0($row['qty']) . '</td>'
                    . '<td>' . $f($row['value']) . '</td>'
                    . '</tr>';
                $rank++;
            }
            $h .= '</table>';
        } else {
            $h .= '<div class="empty-note">— لا توجد مرتجعات للموردين في هذه الفترة —</div>';
        }

        // ─── 8) البنزينة ───
        $g = $data['gas'];
        $h .= '<div class="section-title">⛽ 8. محطة الوقود</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة</th></tr>'
            . '<tr><td>عدد العمليات</td><td>' . $f0($g['count']) . '</td></tr>'
            . '<tr><td>إجمالي اللترات</td><td>' . $f($g['liters']) . '</td></tr>'
            . '<tr><td>إجمالي العهد المصروفة (ج.م)</td><td>' . $f($g['advances']) . '</td></tr>'
            . '<tr><td>إجمالي مدفوع للمحطات (ج.م)</td><td class="neg">' . $f($g['to_station']) . '</td></tr>'
            . '<tr><td>إجمالي على شركات النقل (ج.م)</td><td>' . $f($g['on_company']) . '</td></tr>'
            . '<tr class="total-row"><td>صافي الربح من الوقود (ج.م)</td><td class="pos">' . $f($g['profit']) . '</td></tr>'
            . '</table>';

        if ($g['top_companies']->count() > 0) {
            $h .= '<h3 class="subhead">أكبر شركات النقل (حسب القيمة المستحقة)</h3>';
            $h .= '<table><tr><th>الشركة</th><th>العمليات</th><th>اللترات</th><th>المستحق عليها (ج.م)</th><th>الربح (ج.م)</th></tr>';
            foreach ($g['top_companies'] as $c) {
                $h .= '<tr><td><b>' . $esc($c['name']) . '</b></td><td>' . $f0($c['count']) . '</td><td>' . $f($c['liters']) . '</td><td>' . $f($c['on_them']) . '</td><td class="pos">' . $f($c['profit']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        if ($g['top_stations']->count() > 0) {
            $h .= '<h3 class="subhead">أكبر المحطات (حسب المدفوع)</h3>';
            $h .= '<table><tr><th>المحطة</th><th>العمليات</th><th>اللترات</th><th>المستحق لها (ج.م)</th></tr>';
            foreach ($g['top_stations'] as $s) {
                $h .= '<tr><td><b>' . $esc($s['name']) . '</b></td><td>' . $f0($s['count']) . '</td><td>' . $f($s['liters']) . '</td><td class="neg">' . $f($s['paid']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        // ─── 9) الحركة المالية ───
        $h .= '<div class="section-title">💰 9. ملخص الحركة المالية</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة (ج.م)</th></tr>'
            . '<tr><td>إجمالي الإيرادات / التحصيلات</td><td class="pos">' . $f($data['fin']['incomes']) . '</td></tr>'
            . '<tr><td>المصروفات العامة</td><td class="neg">' . $f($data['fin']['expenses_general']) . '</td></tr>'
            . '<tr><td>رواتب الموظفين</td><td class="neg">' . $f($data['fin']['salaries']) . '</td></tr>'
            . '<tr><td>خصومات للعملاء</td><td class="neg">' . $f($data['fin']['discounts']) . '</td></tr>'
            . '<tr><td>عمولات</td><td class="neg">' . $f($data['fin']['commissions']) . '</td></tr>'
            . '</table>';

        // ─── 10) ملخص ختامي ───
        $h .= '<div class="section-title final">📈 10. الملخص الختامي</div>';
        $h .= '<table class="final-table">'
            . '<tr><td>إجمالي الإيرادات الإجمالي</td><td class="pos">' . $f($tot['revenue']) . ' ج</td></tr>'
            . '<tr><td>إجمالي تكلفة المشتريات</td><td class="neg">' . $f($tot['cost']) . ' ج</td></tr>'
            . '<tr><td>إجمالي الأرباح من النشاطات</td><td class="pos">' . $f($tot['profit']) . ' ج</td></tr>'
            . '<tr><td>إجمالي المصروفات والخصومات</td><td class="neg">' . $f($tot['deductions']) . ' ج</td></tr>'
            . '<tr class="final-row"><td>صافي النتيجة بعد كل المصروفات</td><td class="' . ($tot['net'] >= 0 ? 'pos' : 'neg') . '">' . $f($tot['net']) . ' ج</td></tr>'
            . '</table>';

        // Footer
        $h .= '<div class="report-footer">'
            . '— تم إنشاء هذا التقرير آلياً بواسطة نظام تخطيط موارد المؤسسات (ERP) — شركة الضبع · ' . now()->format('Y/m/d H:i') . ' —'
            . '</div>';

        // ═══ CSS إضافي خاص بالتقرير ═══
        $extraCss = '<style>'
            . '.cover{background:#1e3a8a;color:#fff;border-radius:10px;padding:18px 22px;margin-bottom:18px;display:flex;flex-wrap:wrap;gap:14px;justify-content:space-between;}'
            . '.cover-row{display:flex;flex-direction:column;gap:4px;}'
            . '.cover-label{font-size:10pt;font-weight:700;opacity:0.85;}'
            . '.cover-val{font-size:14pt;font-weight:900;}'
            . '.cover-val.ltr{direction:ltr;text-align:right;}'

            . '.kpi-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:24px;}'
            . '.kpi{padding:14px 12px;border-radius:10px;text-align:center;color:#fff;page-break-inside:avoid;}'
            . '.kpi-label{font-size:10pt;font-weight:700;opacity:0.95;margin-bottom:6px;}'
            . '.kpi-value{font-size:15pt;font-weight:900;letter-spacing:-0.5px;}'
            . '.kpi-value small{font-size:9pt;font-weight:700;opacity:0.85;}'
            . '.kpi-blue{background:#2563eb;}'
            . '.kpi-red{background:#dc2626;}'
            . '.kpi-green{background:#16a34a;}'
            . '.kpi-amber{background:#d97706;}'
            . '.kpi-navy{background:#0f172a;}'

            . '.subhead{font-size:12pt;font-weight:900;color:#1e3a8a;margin:14px 0 8px;border-right:4px solid #fbbf24;padding-right:10px;}'
            . '.summary-row{background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:10px 14px;margin-bottom:10px;display:flex;gap:18px;flex-wrap:wrap;font-size:11pt;}'
            . '.summary-row b{color:#0f172a;}'
            . '.cat-cell{font-weight:900;color:#1e3a8a;}'
            . '.muted{color:#64748b;font-weight:700;}'
            . '.rank-cell{font-size:13pt;font-weight:900;text-align:center;width:60px;}'
            . '.pct{display:inline-block;padding:2px 9px;border-radius:50px;background:#e0e7ff;color:#3730a3;font-weight:800;font-size:10pt;}'
            . '.pct-good{background:#dcfce7;color:#15803d;}'
            . '.pct-ok{background:#fef3c7;color:#92400e;}'
            . '.pct-low{background:#fee2e2;color:#991b1b;}'
            . '.empty-note{padding:14px;text-align:center;color:#94a3b8;font-weight:700;font-size:11pt;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;margin-bottom:14px;}'

            . '.section-title.final{background:linear-gradient(135deg,#0f172a,#1e3a8a);color:#fff;font-size:14pt;}'
            . '.final-table{font-size:12pt;}'
            . '.final-table td:first-child{font-weight:700;}'
            . '.final-table td:last-child{font-weight:900;text-align:left;}'
            . '.final-row{background:#fef3c7;font-size:14pt;}'
            . '.final-row td{padding:14px 12px !important;color:#0f172a;}'

            . '.report-footer{margin-top:30px;text-align:center;font-size:10pt;color:#64748b;font-weight:700;border-top:1px dashed #cbd5e1;padding-top:12px;}'

            . '@media print{.kpi-grid{grid-template-columns:repeat(5,1fr);}}'
            . '</style>';

        return $extraCss . $h . $this->xlsFooter();
    }
}
