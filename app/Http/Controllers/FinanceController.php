<?php
namespace App\Http\Controllers;
use App\Services\InstallmentFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceController extends SystemController
{
public function closeShift(Request $request)
    {
        $request->validate([
            'account_id'    => 'required|integer',
            'actual_amount' => 'required|numeric|min:0'
        ]);

        try {
            $diffMessage = '';
            
            DB::transaction(function() use ($request, &$diffMessage) {
                $account = DB::table('accounts')->where('id', $request->account_id)->first();
                if (!$account) throw new \Exception('الخزنة غير موجودة.');

                $expected = floatval($account->balance);
                $actual   = floatval($request->actual_amount);
                $diff     = $actual - $expected;
                
                $user     = session('auth_user');
                $userName = $user ? $user->name : 'غير معروف';
                $userId   = $user ? $user->id : 0;

                // 1. تسجيل التقفيلة في الأرشيف
                DB::table('shift_closures')->insert([
                    'user_id'         => $userId,
                    'user_name'       => $userName,
                    'account_id'      => $account->id,
                    'expected_amount' => $expected,
                    'actual_amount'   => $actual,
                    'difference'      => $diff,
                    'notes'           => $request->notes ?? 'بدون ملاحظات',
                    'created_at'      => now()
                ]);

                // 2. معالجة الفروقات وتعديل الخزنة فعلياً
                if ($diff < 0) {
                    // عجز (يتسجل مصروف/خسارة)
                    DB::table('financial_transactions')->insert([
                        'type'            => 'general_expense',
                        'amount'          => abs($diff),
                        'from_account_id' => $account->id,
                        'notes'           => "🔴 عجز بالخزنة أثناء تقفيل الوردية (الموظف: {$userName})",
                        'status'          => 'active',
                        'created_at'      => now()
                    ]);
                    $diffMessage = "تم التقفيل! يوجد عجز بقيمة " . abs($diff) . " ج، وتم خصمه من الدفاتر.";
                } elseif ($diff > 0) {
                    // زيادة (تتسجل إيراد إضافي)
                    DB::table('financial_transactions')->insert([
                        'type'            => 'income',
                        'amount'          => $diff,
                        'to_account_id'   => $account->id,
                        'notes'           => "🟡 زيادة بالخزنة أثناء تقفيل الوردية (الموظف: {$userName})",
                        'status'          => 'active',
                        'created_at'      => now()
                    ]);
                    $diffMessage = "تم التقفيل! يوجد زيادة بقيمة {$diff} ج، وتم إضافتها للإيرادات.";
                } else {
                    $diffMessage = "تم التقفيل بنجاح! العهدة متطابقة 100% ولا يوجد أي عجز أو زيادة 🟢.";
                }

                // 3. ضبط رصيد الخزنة ليكون هو الرصيد الفعلي اللي اتسلم
                if ($diff != 0) {
                    DB::table('accounts')->where('id', $account->id)->update(['balance' => $actual, 'updated_at' => now()]);
                }

                // 4. إشعار التليجرام
                if (method_exists($this, 'logActivity')) {
                    $diffText = $diff == 0 ? "متطابق 🟢" : ($diff < 0 ? "عجز " . abs($diff) . " ج 🔴" : "زيادة {$diff} ج 🟡");
                    $this->logActivity('create', 'finance', "🔐 تقفيل وردية لخزنة ({$account->account_name}) | الموظف: {$userName} | النتيجة: {$diffText}");
                }
            });

            return back()->with('success', $diffMessage);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    // ══════════════════════════════════════════════════════
    // الخزينة
    // ══════════════════════════════════════════════════════
// ══════════════════════════════════════════════════════
  // ══════════════════════════════════════════════════════
    // الخزينة والمركز المالي
    // ══════════════════════════════════════════════════════
   // ══════════════════════════════════════════════════════
    // الخزينة والمركز المالي
    // ══════════════════════════════════════════════════════
 public function treasury(\Illuminate\Http\Request $request)
    {
        // 💡 1. القيم الافتراضية للمتغيرات (لتجنب أي إيرور في الواجهة)
        $liquidity_accounts   = collect([]); 
        $projects             = collect([]);
        $profit_breakdown     = [];
        $deductions_breakdown = [];

        $liquidity                 = 0;
        $projects_value            = 0;
        $assets                    = 0;
        $inventory_assets          = 0;
        $fixed_assets              = 0;
        $capital                   = 0;
        $total_debts_for_us        = 0;
        $total_debts_on_us         = 0;
        $installments_system_debts = 0;
        $other_debts_for_us        = 0;

        $total_gross_revenue       = 0;
        $net_book_profit           = 0;
        $real_collected_profit     = 0;
        $uncollected_profit        = 0;
        $total_distributed_profits = 0;
        $remaining_company_profit  = 0;

        $total_deductions          = 0;
        $expenses_salaries         = 0;
        $total_commissions         = 0;
        $losses_depreciation       = 0;
        $losses_returns            = 0;
        $losses_inventory_shortage = 0;

        $gas_receivables           = 0;
        $gas_receivables_count     = 0;
        $gas_payables              = 0;
        $gas_payables_stations     = 0;
        $gas_payables_deductions   = 0;

        $projectIds = \App\Services\SystemSetting::get('project_account_ids', [12, 30]);
        if (!is_array($projectIds)) $projectIds = [12, 30];
        $all_liquidity_accounts = \Illuminate\Support\Facades\DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $liquidity_accounts     = $all_liquidity_accounts->whereNotIn('id', $projectIds);

        $projects = \Illuminate\Support\Facades\DB::table('accounts')->where('category', 'project_sector')->orWhereIn('id', $projectIds)->get();

        $summary = \App\Services\InstallmentFinanceService::treasurySummary();

        // مستحقات البنزينة: ما يدين به العملاء (شركات النقل) من عمليات الوقود
        $gas_receivables       = \Illuminate\Support\Facades\DB::table('installments')
            ->where('category', 'بنزينة')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance') ?? 0;
        $gas_receivables_count = \Illuminate\Support\Facades\DB::table('installments')
            ->where('category', 'بنزينة')
            ->where('remaining_balance', '>', 0)
            ->count();

        // ديون البنزينة على الشركة
        $gas_payables_stations   = \Illuminate\Support\Facades\DB::table('company_debts')
            ->where('category', 'وقود')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance') ?? 0;
        $gas_payables_deductions = \Illuminate\Support\Facades\DB::table('company_debts')
            ->where('category', 'استقطاعات')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance') ?? 0;
            
        $gas_payables = $gas_payables_stations;

        // ════════════════════════════════════════════════
        // فلتر الأرباح والمصروفات الذكي
        // ════════════════════════════════════════════════
        $pf       = $request->input('profit_filter', 'all');
        $pfFrom   = $request->input('profit_from_date', '');
        $pfTo     = $request->input('profit_to_date', '');

        $startDate = null;
        $endDate   = null;

        if ($pf !== 'all') {
            switch ($pf) {
                case 'today':
                    $startDate = now()->startOfDay(); $endDate = now()->endOfDay(); break;
                case 'yesterday':
                    $startDate = now()->subDay()->startOfDay(); $endDate = now()->subDay()->endOfDay(); break;
                case 'week':
                    $startDate = now()->startOfWeek(\Carbon\Carbon::SATURDAY); $endDate = now()->endOfWeek(\Carbon\Carbon::FRIDAY); break;
                case 'month':
                    $startDate = now()->startOfMonth(); $endDate = now()->endOfMonth(); break;
                case '3months':
                    $startDate = now()->subMonths(3)->startOfDay(); $endDate = now()->endOfDay(); break;
                case 'custom':
                    if ($pfFrom && $pfTo) {
                        $startDate = \Carbon\Carbon::parse($pfFrom)->startOfDay();
                        $endDate   = \Carbon\Carbon::parse($pfTo)->endOfDay();
                    }
                    break;
            }
        }

        $applyDate = function($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            return $query;
        };

        if ($startDate && $endDate) {
            // ═══ حساب الأرباح بالفلتر مباشرة من DB ═══

            $profit_installments = (float) $applyDate(\Illuminate\Support\Facades\DB::table('installments'))
                ->where('installment_months', '>', 0)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
                ->selectRaw('COALESCE(SUM(total_after_interest - cash_price), 0) as total')
                ->value('total');

            // أرباح المخزن: كاش + فرق سعر منتجات المخزن المباعة بالتقسيط
            $inventoryCashProfit = (float) $applyDate(\Illuminate\Support\Facades\DB::table('installments'))
                ->where('installment_months', 0)
                ->where(function ($q) { $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory'); })
                ->where('status', '!=', 'cancelled')
                ->sum('profit');

            $inventoryInstallmentProductProfit = (float) $applyDate(\Illuminate\Support\Facades\DB::table('installments'))
                ->where('installment_months', '>', 0)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) { $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory'); })
                ->selectRaw('COALESCE(SUM(cash_price - purchase_cost), 0) as total')
                ->value('total');

            $profit_inventory = $inventoryCashProfit + $inventoryInstallmentProductProfit;

            // أرباح المبيعات المباشرة: كاش + فرق سعر منتجات البيع المباشر المباعة بالتقسيط (مش من المخزن)
            $directCashProfit = (float) $applyDate(\Illuminate\Support\Facades\DB::table('installments'))
                ->where('installment_months', 0)
                ->where('category', 'مبيعات مباشرة')
                ->where('status', '!=', 'cancelled')
                ->sum('profit');

            $directInstallmentProductProfit = (float) $applyDate(\Illuminate\Support\Facades\DB::table('installments'))
                ->where('installment_months', '>', 0)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) { $q->where('sale_type', '!=', 'inventory')->orWhereNull('sale_type'); })
                ->where(function ($q) { $q->where('category', '!=', 'مبيعات مخزن')->orWhereNull('category'); })
                ->selectRaw('COALESCE(SUM(cash_price - purchase_cost), 0) as total')
                ->value('total');

            $profit_direct_products = $directCashProfit + $directInstallmentProductProfit;

            $profit_services = (float) $applyDate(\Illuminate\Support\Facades\DB::table('installments'))
                ->where('category', 'خدمات')
                ->where('status', '!=', 'cancelled')
                ->sum('profit');

            $profit_gas = (float) $applyDate(\Illuminate\Support\Facades\DB::table('fuel_transactions')->whereNull('superseded_by'))->sum('ahmed_profit');

            // ⚡ استعلام واحد بـ GROUP BY بدل 10 استعلامات منفصلة بـ LIKE.
            $fb = \App\Services\InstallmentFinanceService::financialBreakdown($startDate, $endDate);
            $profit_asset_sales        = $fb['profitAssetSales'];
            $additional_incomes        = $fb['additionalIncomes'];
            $expenses_general          = $fb['expensesGeneral'];
            $expenses_salaries         = $fb['expensesSalaries'];
            $total_commissions         = $fb['totalCommissions'];
            $losses_depreciation       = $fb['lossesDepreciation'];
            $losses_returns            = $fb['lossesReturns'];
            $losses_discounts          = $fb['lossesDiscounts'];
            $losses_bad_debts          = $fb['lossesBadDebts'];
            $losses_asset_sales        = $fb['lossesAssetSales'];
            $losses_inventory_shortage = $fb['lossesInventoryShortage'];

            $total_gross_revenue = $profit_installments + $profit_inventory + $profit_direct_products
                                 + $profit_services + $profit_gas + $profit_asset_sales;

            $total_deductions = $expenses_general + $expenses_salaries + $total_commissions
                              + $losses_depreciation + $losses_returns + $losses_discounts
                              + $losses_bad_debts + $losses_asset_sales + $losses_inventory_shortage;

            $net_book_profit = $total_gross_revenue - $total_deductions;

            $collectedInstallmentProfits = (float) $applyDate(\Illuminate\Support\Facades\DB::table('installment_payments'))
                ->join('installments', 'installment_payments.installment_id', '=', 'installments.id')
                ->where('installments.installment_months', '>', 0)
                ->selectRaw('SUM(installment_payments.amount_paid * (installments.profit / installments.total_after_interest)) as collected_profit')
                ->value('collected_profit');

            $real_collected_profit = ($profit_direct_products + $profit_inventory + $profit_services + $profit_gas + $profit_asset_sales + $collectedInstallmentProfits)
                                     - ($expenses_general + $total_commissions + $expenses_salaries);

            $uncollected_profit        = $summary['uncollectedProfit'];
            $total_distributed_profits = $summary['totalDistributedProfits'];
            $remaining_company_profit  = $summary['remainingCompanyProfit'];

        } else {
            // ═══ بدون فلتر: استخدام قيم الـ Service الكاملة ═══
            $profit_installments       = $summary['profitInstallments'];
            $profit_inventory          = $summary['profitInventory'];
            $profit_direct_products    = $summary['profitDirectProducts'];
            $profit_services           = $summary['profitServices'];
            $profit_gas                = $summary['profitGas'];
            $profit_asset_sales        = $summary['profitAssetSales'];
            $additional_incomes        = $summary['additionalIncomes'];
            $total_gross_revenue       = $summary['totalGrossRevenue'];
            
            $expenses_general          = $summary['expensesGeneral'];
            $total_commissions         = $summary['totalCommissions'];
            $losses_depreciation       = $summary['lossesDepreciation'];
            $losses_bad_debts          = $summary['lossesBadDebts'];
            $losses_returns            = $summary['lossesReturns'];
            $losses_discounts          = $summary['lossesDiscounts'];
            $losses_asset_sales        = $summary['lossesAssetSales'];
            $expenses_salaries         = $summary['expensesSalaries'];
            $losses_inventory_shortage = $summary['lossesInventoryShortage'] ?? 0;
            $total_deductions          = $summary['totalDeductions'];
            
            $net_book_profit           = $summary['netBookProfit'];
            $uncollected_profit        = $summary['uncollectedProfit'];
            $real_collected_profit     = $summary['realCollectedProfit'];
            $total_distributed_profits = $summary['totalDistributedProfits'];
            $remaining_company_profit  = $summary['remainingCompanyProfit'];
        }

        // ════════════════════════════════════════════════
        // فلتر المصروفات المستقل (مستقل عن فلتر الأرباح)
        // ════════════════════════════════════════════════
        $expFilter    = $request->input('exp_filter', 'month');
        $expStart     = null;
        $expEnd       = null;
        if ($expFilter !== 'all') {
            switch ($expFilter) {
                case 'today':   $expStart = now()->startOfDay(); $expEnd = now()->endOfDay(); break;
                case 'week':    $expStart = now()->startOfWeek(\Carbon\Carbon::SATURDAY); $expEnd = now()->endOfWeek(\Carbon\Carbon::FRIDAY); break;
                case 'month':   $expStart = now()->startOfMonth(); $expEnd = now()->endOfMonth(); break;
                case '3months': $expStart = now()->subMonths(3)->startOfDay(); $expEnd = now()->endOfDay(); break;
            }
        }
        $expFb          = \App\Services\InstallmentFinanceService::financialBreakdown($expStart, $expEnd);
        $total_deductions = $expFb['expensesGeneral'] + $expFb['expensesSalaries'] + $expFb['totalCommissions']
            + $expFb['lossesDepreciation'] + $expFb['lossesReturns'] + $expFb['lossesDiscounts']
            + $expFb['lossesBadDebts'] + ($expFb['lossesAssetSales'] ?? 0) + ($expFb['lossesInventoryShortage'] ?? 0);
        $expFilterLabel = match($expFilter) {
            'today'   => 'اليوم',
            'week'    => 'الأسبوع',
            'month'   => 'الشهر',
            '3months' => 'آخر 3 أشهر',
            default   => 'إجمالي الكل',
        };

        // ═══ البيانات الثابتة من السيرفيس (لا تتأثر بالفلتر الزمني) ═══
        $liquidity                 = $summary['liquidity'];
        $projects_value            = $summary['projectsValue'];
        $inventory_assets          = $summary['inventoryAssets'];
        $fixed_assets              = $summary['fixedAssets'];
        $installments_system_debts = $summary['installmentsSystemDebts'];
        $contracting_installments_debts = $summary['contractingInstallmentsDebts'] ?? 0;
        $other_debts_for_us        = $summary['otherDebtsForUs'];
        
        $total_debts_for_us        = $summary['totalDebtsForUs'] - $gas_receivables;
        $total_debts_on_us         = $summary['totalDebtsOnUs'] - $gas_payables_stations;
        
        $capital                   = $summary['capital'];
        $assets                    = $inventory_assets + $fixed_assets;

        $profit_breakdown = [
            ['label' => 'أرباح التقسيط (الآجل)',        'value' => $profit_installments, 'source' => 'النسبة والمبيعات الآجلة'],
            ['label' => 'أرباح مبيعات المخزن',          'value' => $profit_inventory,    'source' => 'مبيعات نقدية فورية'],
            ['label' => 'أرباح الخدمات (صيانة/تركيب)', 'value' => $profit_services,     'source' => 'أجور ومصنعيات الفنيين'],
            ['label' => 'أرباح محطة الوقود',            'value' => $profit_gas,          'source' => 'عمولات صرف البنزينة'],
            ['label' => 'أرباح بيع الأصول',             'value' => $profit_asset_sales,  'source' => 'تسييل الأصول'],
        ];
        $deductions_breakdown = [
            ['label' => 'مصاريف تشغيلية وعامة', 'value' => $expenses_general,          'note' => 'إيجار، مصروفات متنوعة',          'cash' => true],
            ['label' => 'رواتب الموظفين',        'value' => $expenses_salaries,         'note' => 'رواتب شهرية',                   'cash' => true],
            ['label' => 'عمولات المحافظ',        'value' => $total_commissions,         'note' => 'عمولات إيداع/تحويل',            'cash' => true],
            ['label' => 'إهلاكات الأصول',        'value' => $losses_depreciation,       'note' => 'خسارة دفترية',                  'cash' => false],
            ['label' => 'خسائر المرتجعات',       'value' => $losses_returns,            'note' => 'فرق التكلفة في المرتجعات',      'cash' => true],
            ['label' => 'خصومات للعملاء',        'value' => $losses_discounts,          'note' => 'تخفيضات على المبيعات',          'cash' => true],
            ['label' => 'إعدام ديون وخسائر',     'value' => $losses_bad_debts,          'note' => 'عقود تم إعدامها',               'cash' => false],
            ['label' => 'خسائر بيع أصول',        'value' => $losses_asset_sales,        'note' => 'بيع بأقل من قيمته',             'cash' => true],
            ['label' => 'خسائر وعجز جرد المخزن', 'value' => $losses_inventory_shortage, 'note' => 'بضاعة تالفة أو ناقصة',          'cash' => false], // 💡 البند الجديد
        ];

        // ── شارت نمو رأس المال (لقطات) ──
        $capitalChartPeriod = $request->input('cap_period', '3months');
        $capitalChartFrom   = $request->input('cap_from', '');
        $capitalChartTo     = $request->input('cap_to', '');

        $capEnd   = \Carbon\Carbon::now()->endOfDay();
        $capStart = match($capitalChartPeriod) {
            '1month'  => \Carbon\Carbon::now()->subMonth()->startOfDay(),
            '6months' => \Carbon\Carbon::now()->subMonths(6)->startOfDay(),
            'year'    => \Carbon\Carbon::now()->subYear()->startOfDay(),
            'custom'  => $capitalChartFrom ? \Carbon\Carbon::parse($capitalChartFrom)->startOfDay() : \Carbon\Carbon::now()->subMonths(3)->startOfDay(),
            default   => \Carbon\Carbon::now()->subMonths(3)->startOfDay(),
        };
        if ($capitalChartPeriod === 'custom' && $capitalChartTo) {
            $capEnd = \Carbon\Carbon::parse($capitalChartTo)->endOfDay();
        }

        $capitalChartData = DB::table('capital_snapshots')
            ->whereBetween('created_at', [$capStart, $capEnd])
            ->orderBy('created_at')
            ->get()
            ->map(fn($s) => [
                'label' => \Carbon\Carbon::parse($s->created_at)->format('d/m H:i'),
                'value' => (float) $s->total_capital,
                'notes' => $s->notes ?? '',
            ])
            ->values();

        $total_assets_value = (float) DB::table('assets')
            ->where('status', 'active')
            ->where('current_value', '>', 0)
            ->sum('current_value');

        // 🏗️ رأس مال المقاولات (الإجمالي والتفاصيل) - منطبق مع Dashboard المقاولات
        $construction_net_transactions = (float) DB::table('sy2_accounts')->where('status', 'active')->sum('balance'); // سيولة المقاولات

        // مستحقات المقاولات المباشرة
        $hasDiscount = \Illuminate\Support\Facades\Schema::hasColumn('sy2_projects', 'cached_discount');
        $discountCol = $hasDiscount ? 'cached_discount' : '0';
        
        $construction_direct_dues = (float) DB::table('sy2_projects')
            ->whereNotIn('id', function($q) {
                $q->select('project_id')->from('sy2_installment_contracts');
            })
            ->selectRaw("SUM(CASE WHEN cached_actual_total > (cached_collected + $discountCol) THEN cached_actual_total - (cached_collected + $discountCol) ELSE 0 END) as dues")
            ->value('dues');

        // المشاريع ذات الأقساط (زيادات خارج نطاق العقد)
        if (\Illuminate\Support\Facades\Schema::hasTable('sy2_client_payments')) {
            $construction_excess = (float) DB::table('sy2_projects as p')
                ->join('sy2_installment_contracts as c', 'p.id', '=', 'c.project_id')
                ->selectRaw("SUM(
                    CASE WHEN (p.cached_actual_total - (c.total_after_interest + c.discount)) > 0 THEN
                        CASE WHEN ((p.cached_actual_total - (c.total_after_interest + c.discount)) - (
                            SELECT COALESCE(SUM(amount + discount), 0) FROM sy2_client_payments cp WHERE cp.project_id = p.id
                        )) > 0 THEN
                            (p.cached_actual_total - (c.total_after_interest + c.discount)) - (
                                SELECT COALESCE(SUM(amount + discount), 0) FROM sy2_client_payments cp WHERE cp.project_id = p.id
                            )
                        ELSE 0 END
                    ELSE 0 END
                ) as excess")
                ->value('excess');
            $construction_direct_dues += $construction_excess;
        }

        // أقساط المقاولات
        $construction_installment_dues = (float) DB::table('sy2_installment_contracts')
            ->where('status', '!=', 'cancelled')
            ->sum('remaining_balance');

        // ديون الموردين للمقاولات
        $construction_supplier_debts = (float) DB::table('sy2_supplier_debts')
            ->where('status', '!=', 'paid')
            ->selectRaw("SUM(total_amount - paid_amount) as debts")
            ->value('debts');

        // مصنعيات الفنيين
        $construction_workers_total = (float) DB::table('sy2_band_workers')->sum('amount');
        $construction_workers_paid = (float) DB::table('sy2_worker_payments')->selectRaw("SUM(amount + discount) as total_paid")->value('total_paid');
        $construction_worker_fees = max(0, $construction_workers_total - $construction_workers_paid);

        // مدفوعات العملاء بالزيادة
        $clientOverpayments = (float) DB::table('sy2_projects')
            ->whereNotIn('id', function($q) { $q->select('project_id')->from('sy2_installment_contracts'); })
            ->selectRaw("SUM(CASE WHEN (cached_collected + $discountCol) > cached_actual_total THEN ((cached_collected + $discountCol) - cached_actual_total) ELSE 0 END) as overpaid")
            ->value('overpaid');

        $total_construction_capital = ($construction_net_transactions + $construction_direct_dues + $construction_installment_dues) - ($construction_supplier_debts + $construction_worker_fees + $clientOverpayments);

        return view('treasury', compact(
            'liquidity_accounts', 'projects', 'liquidity', 'projects_value',
            'assets', 'inventory_assets', 'fixed_assets', 'total_debts_for_us', 'total_debts_on_us', 'capital',
            'total_gross_revenue', 'profit_breakdown', 'deductions_breakdown',
            'total_deductions', 'losses_depreciation', 'losses_returns',
            'net_book_profit', 'real_collected_profit', 'total_commissions', 'uncollected_profit',
            'total_distributed_profits', 'remaining_company_profit', 'expenses_salaries',
            'installments_system_debts', 'contracting_installments_debts', 'other_debts_for_us',
            'gas_receivables', 'gas_receivables_count',
            'gas_payables', 'gas_payables_stations', 'gas_payables_deductions',
            'capitalChartData', 'capitalChartPeriod', 'capitalChartFrom', 'capitalChartTo',
            'expFilter', 'expFilterLabel', 'total_assets_value',
            'construction_net_transactions', 'construction_direct_dues', 'construction_installment_dues',
            'construction_supplier_debts', 'construction_worker_fees', 'total_construction_capital'
        ));
    }
    public function updateManualBalance(Request $request)
    {
        $request->validate([
            'account_id'  => 'required|integer',
            'new_balance' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $account = DB::table('accounts')->where('id', $request->account_id)->first();
                if (!$account) throw new \Exception('الحساب غير موجود.');

                $old_balance = floatval($account->balance);
                $new_balance = floatval($request->new_balance);
                $diff        = $new_balance - $old_balance;

                if ($diff != 0) {
                    DB::table('accounts')->where('id', $request->account_id)->update([
                        'balance'    => $new_balance,
                        'updated_at' => now(),
                    ]);

                    $type   = $diff > 0 ? 'settlement' : 'general_expense';
                    $amount = abs($diff);
                    $notes  = "تعديل رصيد يدوي لـ [{$account->account_name}]: كان " . number_format($old_balance, 2) . " ج، وأصبح " . number_format($new_balance, 2) . " ج";

                    DB::table('financial_transactions')->insert([
                        'type'            => $type,
                        'amount'          => $amount,
                        'from_account_id' => $diff < 0 ? $request->account_id : null,
                        'to_account_id'   => $diff > 0 ? $request->account_id : null,
                        'notes'           => $notes,
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                }
            });

            // 🔔 تسجيل نشاط
            $account = DB::table('accounts')->where('id', $request->account_id)->first();
            $this->logActivity('update', 'treasury',
                "💰 تعديل رصيد يدوي: [{$account->account_name}] → " . number_format($request->new_balance, 2) . " ج"
            );

            return back()->with('success', 'تم تعديل الرصيد وتوثيق العملية في السجل المالي بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════
    // المصروفات
    // ══════════════════════════════════════════════════════
 // ══════════════════════════════════════════════════════
    // المصروفات
    // ══════════════════════════════════════════════════════
    public function expenses(\Illuminate\Http\Request $request)
    {
        $dateFilter     = $request->input('date_filter', 'this_month');
        $customDate     = $request->input('custom_date', '');
        $customFrom     = $request->input('custom_from', '');
        $customTo       = $request->input('custom_to', '');
        $categoryFilter = $request->input('category_filter', '');

        $accounts = \Illuminate\Support\Facades\DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $expenseCategories = \Illuminate\Support\Facades\DB::table('expense_categories')->orderBy('name')->get();
        $expenseTypes = ['general_expense', 'salary_expense', 'discount'];

        // ⚡ استخراج التصنيفات الديناميكية في DB بـ DISTINCT SUBSTRING بدل تحميل
        // كل الـ notes للذاكرة وفـك [...] في PHP. يستفيد من idx_ft_type_status
        // ويرجع أسماء فريدة فقط (ممكن تكون 50 سطر بدل 50,000).
        $dynamicCategories = \Illuminate\Support\Facades\DB::table('financial_transactions')
            ->where('type', 'general_expense')
            ->where('notes', 'like', '[%') // prefix match — index-friendly
            ->where('notes', 'like', '%]%')
            ->selectRaw('DISTINCT SUBSTRING(notes, 2, LOCATE(\']\', notes) - 2) as cat')
            ->pluck('cat')
            ->filter()
            ->all();

        // 🚀 2. دمج جميع التصنيفات بدون تكرار (الأساسية + المستخرجة + المضافة يدوياً)
        $manualCategories = $expenseCategories->pluck('name')->toArray();
        $allAvailableCategories = array_unique(array_merge(
            ['رواتب وأجور', 'خصومات للعملاء', 'عمولات المحافظ'],
            $manualCategories,
            $dynamicCategories
        ));

        // 🚀 3. جلب المصروفات بناءً على الفلاتر
        $query = \Illuminate\Support\Facades\DB::table('financial_transactions as ft')
            ->leftJoin('accounts as a', 'ft.from_account_id', '=', 'a.id')
            ->whereIn('ft.type', $expenseTypes)
            ->where('ft.status', 'active')
            ->whereNull('ft.person_name') // إخفاء العهد
            ->where('ft.notes', 'not like', '%إعدام ديون%')
            ->where('ft.notes', 'not like', '%إهلاك أصل ثابت%')
            ->where('ft.notes', 'not like', '%خسارة فرق سعر%')
            ->select('ft.*', 'a.account_name');

        // تطبيق فلتر التاريخ
        if ($dateFilter === 'today') {
            $query->whereDate('ft.created_at', now()->toDateString());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('ft.created_at', now()->subDay()->toDateString());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('ft.created_at', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($dateFilter === 'this_month') {
            $query->whereMonth('ft.created_at', now()->month)->whereYear('ft.created_at', now()->year);
        } elseif ($dateFilter === 'custom' && !empty($customDate)) {
            $query->whereDate('ft.created_at', $customDate);
        } elseif ($dateFilter === 'range' && (!empty($customFrom) || !empty($customTo))) {
            if (!empty($customFrom)) $query->whereDate('ft.created_at', '>=', $customFrom);
            if (!empty($customTo))   $query->whereDate('ft.created_at', '<=', $customTo);
        }

        // تطبيق فلتر التصنيف (يقبل كل المسميات المدمجة الآن)
        // ملاحظة: بنطابق النوع الخاص (راتب/خصم) + الـ prefix الحرفي [التصنيف] في الملاحظات
        // عشان المصروفات المسجّلة يدوياً بنفس اسم التصنيف تظهر برضه (كانت بتختفي قبل كده).
        if (!empty($categoryFilter)) {
            if ($categoryFilter === 'رواتب الموظفين' || $categoryFilter === 'رواتب وأجور') {
                $query->where(function($q) use ($categoryFilter) {
                    $q->where('ft.type', 'salary_expense')
                      ->orWhere('ft.notes', 'like', '%[' . $categoryFilter . ']%');
                });
            } elseif ($categoryFilter === 'خصومات للعملاء') {
                $query->where(function($q) use ($categoryFilter) {
                    $q->where('ft.type', 'discount')
                      ->orWhere('ft.notes', 'like', '%[' . $categoryFilter . ']%');
                });
            } elseif ($categoryFilter === 'عمولات المحافظ') {
                $query->where(function($q) {
                    $q->where('ft.notes', 'like', '%عمولة تلقائية%')
                      ->orWhere('ft.notes', 'like', '%[عمولات المحافظ]%')
                      ->orWhere('ft.notes', 'like', '%عمولة%');
                });
            } else {
                $query->where('ft.notes', 'like', '%[' . $categoryFilter . ']%');
            }
        }

        $expenses = $query->orderBy('ft.created_at', 'desc')->get();
        $total_expenses = $expenses->sum('amount');

        // تحليل التصنيفات للرسم البياني
        $expensesByCategory = $expenses->groupBy(function ($exp) {
            if ($exp->type === 'salary_expense') return 'رواتب وأجور';
            if ($exp->type === 'discount') return 'خصومات للعملاء';
            if (str_contains($exp->notes, 'عمولة تلقائية') || str_contains($exp->notes, 'عمولة')) return 'عمولات المحافظ';
            if (preg_match('/^\[(.*?)\]/', $exp->notes, $m)) return $m[1];
            return 'أخرى متنوعة';
        })->map(fn($g) => [
            'total' => $g->sum('amount'),
            'count' => $g->count(),
        ])->sortByDesc('total');

        $topCategory       = $expensesByCategory->keys()->first();
        $topCategoryData   = $expensesByCategory->first();

        return view('expenses', compact(
            'accounts', 'expenses', 'dateFilter', 'customDate', 'customFrom', 'customTo', 'total_expenses',
            'categoryFilter', 'expensesByCategory', 'topCategory', 'topCategoryData',
            'expenseCategories', 'allAvailableCategories' // 👈 أرسلنا المصفوفة الشاملة للـ View
        ));
    }



public function writeOffDebt(Request $request)
    {
        $request->validate([
            'inst_id' => 'required|exists:installments,id',
            'notes'   => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $installment = DB::table('installments')->where('id', $request->inst_id)->first();
                
                if ($installment->remaining_balance <= 0) {
                    throw new \Exception('هذه العملية ليس عليها ديون متبقية لإعدامها.');
                }

                $lostAmount = $installment->remaining_balance;
                $customerName = $installment->customer_name;
                $note = $request->notes ? " - " . $request->notes : "بدون سبب";

                // 1. تصفير الدين على العميل وإغلاق العملية (تم إزالة updated_at و status لمنع الخطأ)
                DB::table('installments')->where('id', $installment->id)->update([
                    'remaining_balance' => 0
                ]);

                // 2. تسجيل المديونية كخسارة/إعدام دين في المصروفات
                DB::table('financial_transactions')->insert([
                    'type' => 'general_expense',
                    'amount' => $lostAmount,
                    'notes' => "إعدام ديون: عميل {$customerName} للعملية ({$installment->product_name}) - {$note}",
                    'status' => 'active',
                    'created_at' => now()
                ]);
                
                // 3. توثيق السجل
                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('delete', 'finance', "☠️ تم إعدام دين بقيمة {$lostAmount} ج للعميل {$customerName}");
                }
            });

            return back()->with('success', 'تم إعدام الدين وتصفية الحساب وتحويل المبلغ للمصروفات بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }


public function storeExpense(Request $request)
{
    $request->validate([
        'category'   => 'required|string',
        'notes'      => 'required|string',
        'amount'     => 'required|numeric|min:0.01',
        'account_id' => 'required|integer'
    ]);

    $lockKey = 'expense_lock_' . md5(auth()->id() . $request->amount . $request->account_id);
    $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
    if (!$lock->get()) {
        return back()->with('error', '⚠️ تم رصد محاولة تكرار! العملية قيد التنفيذ بالفعل.');
    }

    try {
        DB::transaction(function () use ($request) {
            $amount    = floatval($request->amount);
            $accountId = $request->account_id;
            $category  = $request->category ?? 'أخرى متنوعة';
            $notes     = trim($request->notes);
 
            $account = DB::table('accounts')->where('id', $accountId)->first();
            if (!$account) throw new \Exception('جهة الصرف المختارة غير موجودة.');
 
            // 🚀 التعديل: فحص رصيد الخزنة قبل إتمام الخصم
            if ($account->balance < $amount) {
                throw new \Exception("عذراً، رصيد الخزنة المتاح (" . number_format($account->balance, 2) . " ج) لا يكفي لتسجيل هذا المصروف!");
            }

            // خصم الفلوس من الخزنة
            DB::table('accounts')->where('id', $accountId)->decrement('balance', $amount);
 
            // تسجيل المصروف
            $formattedNotes = "[{$category}] {$notes}";
            $expenseDate = $request->expense_date ? $request->expense_date . ' ' . date('H:i:s') : now();

            DB::table('financial_transactions')->insert([
                'type'            => 'general_expense',
                'amount'          => $amount,
                'from_account_id' => $accountId,
                'notes'           => $formattedNotes,
                'status'          => 'active',
                'created_at'      => $expenseDate,
            ]);
 
            if (method_exists($this, 'applyAccountCommission')) {
                self::applyAccountCommission($accountId, $amount, "مصروف [{$category}]: {$notes}", 'out');
            }
        });

        $account = DB::table('accounts')->where('id', $request->account_id)->first();
        if (method_exists($this, 'logActivity')) {
            $this->logActivity('create', 'finance',
                "💸 مصروف جديد: " . number_format($request->amount, 2) . " ج | [{$request->category}] {$request->notes} | من: {$account->account_name}"
            );
        }

        return back()->with('success', 'تم اعتماد المصروف وتطبيق العمولة بنجاح.');
    } catch (\Exception $e) {
        // 🚀 التعديل: إضافة withInput() للحفاظ على البيانات المدخلة في حالة الخطأ
return back()->withInput()->with('error', $e->getMessage())->withInput()->with('open_modal', true);    }
}
    // 🔧 يحقن نفس بيانات الأقساط (دفعات + بنود تركيب) المستخدمة في شاشة الأقساط — لإعادة استخدامها في المودالات الكسولة
    private function hydrateInstallments($installments)
    {
        $ids = collect($installments)->pluck('id')->toArray();
        $payments = empty($ids) ? collect() : DB::table('installment_payments as p')
            ->leftJoin('accounts as a', 'p.payment_method_id', '=', 'a.id')
            ->whereIn('p.installment_id', $ids)
            ->select('p.*', 'a.account_name')
            ->orderBy('p.payment_date', 'desc')
            ->get()->groupBy('installment_id');

        $expenses = (empty($ids) || !Schema::hasTable('installment_expenses'))
            ? collect()
            : DB::table('installment_expenses')->whereIn('installment_id', $ids)->get()->keyBy('installment_id');

        foreach ($installments as $inst) {
            $exp = $expenses->get($inst->id);
            $inst->transport_cost    = $exp->transport_cost    ?? 0;
            $inst->installation_cost = $exp->installation_cost ?? 0;
            $inst->materials_cost    = $exp->materials_cost    ?? 0;
            $inst->extras_total      = (float) $inst->transport_cost + (float) $inst->installation_cost + (float) $inst->materials_cost;
            $inst->device_price      = max(0, (float) ($inst->cash_price ?? 0) - $inst->extras_total);
            $inst->payments          = $payments->get($inst->id, collect());
        }
        return $installments;
    }

    // 🚀 مودالات الإجراءات (سداد/تعديل/فسخ/إعدام) لعقد واحد — تُحمَّل عند الطلب (lazy)
    public function installmentActionModals($id)
    {
        $inst = DB::table('installments')->where('id', $id)->first();
        if (!$inst) abort(404);
        $this->hydrateInstallments(collect([$inst]));
        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        return view('partials._inst_action_modals', compact('inst', 'accounts'));
    }

    // 🚀 كشف حساب عميل واحد — يُحمَّل عند الطلب (lazy)
    public function customerStatementModal(Request $request)
    {
        $phone = trim((string) $request->query('phone', ''));
        $name  = trim((string) $request->query('name', ''));

        $q = DB::table('installments')->where('installment_months', '>', 0);
        if ($phone !== '' && $phone !== '—') {
            $q->where('customer_phone', $phone);
        } else {
            $q->where('customer_name', $name)->where(function ($w) {
                $w->whereNull('customer_phone')->orWhere('customer_phone', '')->orWhere('customer_phone', '—');
            });
        }
        $customerInsts = $q->orderByDesc('id')->get();
        if ($customerInsts->isEmpty()) abort(404);

        $this->hydrateInstallments($customerInsts);
        // المفتاح يُحسب من نفس مصدر التجميع في الشاشة
        $phoneKey = filled($customerInsts->first()->customer_phone) ? $customerInsts->first()->customer_phone : 'n:' . $customerInsts->first()->customer_name;

        return view('partials._inst_statement_modal', ['customerInsts' => $customerInsts, 'phone' => $phoneKey]);
    }

    // ══════════════════════════════════════════════════════
    // الأقساط
    // ══════════════════════════════════════════════════════
 public function installments(Request $request)
    {
        $search      = $request->search;
        $dayFilter   = $request->day;
        $timeFilter  = $request->time_filter;
        $customDate  = $request->custom_date;
        $rangeFrom   = $request->range_from;
        $rangeTo     = $request->range_to;

        $query = DB::table('installments')->where('installment_months', '>', 0)->orderBy('id', 'desc');

        if ($search) {
            $this->applyArabicSearch($query, ['customer_name', 'customer_phone'], $search);
        }
        if ($dayFilter) {
            $query->where('due_day', $dayFilter);
        }

        if ($timeFilter === 'today') {
            $query->whereDate('start_date', now()->toDateString());
        } elseif ($timeFilter === 'yesterday') {
            $query->whereDate('start_date', now()->subDay()->toDateString());
        } elseif ($timeFilter === 'month') {
            $query->whereMonth('start_date', now()->month)->whereYear('start_date', now()->year);
        } elseif ($timeFilter === 'custom' && $customDate) {
            $query->whereDate('start_date', $customDate);
        } elseif ($timeFilter === 'range' && $rangeFrom && $rangeTo) {
            // ✅ تحقق من صحة النطاق: from <= to + حد أقصى 12 يوم
            try {
                $from = \Carbon\Carbon::parse($rangeFrom)->startOfDay();
                $to   = \Carbon\Carbon::parse($rangeTo)->endOfDay();
            } catch (\Throwable $e) {
                return back()->with('error', '⛔ تاريخ غير صالح في نطاق التحصيل.');
            }
            if ($from->greaterThan($to)) {
                return back()->with('error', '⛔ النطاق غير صحيح: تاريخ "من" أكبر من تاريخ "إلى". صحّح التواريخ ثم حاول مرة أخرى.');
            }
            if ($from->diffInDays($to) > 12) {
                return back()->with('error', '⛔ النطاق غير مسموح: الحد الأقصى 12 يوم.');
            }
            $query->whereBetween('start_date', [$from->toDateString(), $to->toDateString()]);
        }

        $installments = $query->get();

        $installmentIds = $installments->pluck('id')->toArray();
        $allPayments = empty($installmentIds) ? collect() : DB::table('installment_payments as p')
            ->leftJoin('accounts as a', 'p.payment_method_id', '=', 'a.id')
            ->whereIn('p.installment_id', $installmentIds)
            ->select('p.*', 'a.account_name')
            ->orderBy('p.payment_date', 'desc')
            ->get()
            ->groupBy('installment_id');

        // 💡 بنود تركيب التكييف (نقل/تركيب/خامات) المرتبطة بكل عقد — لعرض «سعر الجهاز + بنود التركيب»
        $allExpenses = (empty($installmentIds) || !Schema::hasTable('installment_expenses'))
            ? collect()
            : DB::table('installment_expenses')
                ->whereIn('installment_id', $installmentIds)
                ->get()
                ->keyBy('installment_id');

        $total_expected_profit = 0;

        foreach ($installments as $inst) {
            $exp = $allExpenses->get($inst->id);
            $inst->transport_cost    = $exp->transport_cost    ?? 0;
            $inst->installation_cost = $exp->installation_cost ?? 0;
            $inst->materials_cost    = $exp->materials_cost    ?? 0;
            $inst->extras_total      = (float) $inst->transport_cost + (float) $inst->installation_cost + (float) $inst->materials_cost;
            // سعر الجهاز وحده = سعر الكاش الكلي − بنود التركيب
            $inst->device_price      = max(0, (float) ($inst->cash_price ?? 0) - $inst->extras_total);

            $inst->payments = $allPayments->get($inst->id, collect());
            $inst->total_paid = InstallmentFinanceService::paymentsSum($inst, $inst->payments);
            $inst->total_paid_by_customer = InstallmentFinanceService::totalPaidByCustomer($inst, $inst->payments);
            $inst->payment_progress = InstallmentFinanceService::paymentProgressPercent($inst, $inst->payments);
            $total_expected_profit += InstallmentFinanceService::contractProfit($inst);
        }

        $total_collected = InstallmentFinanceService::totalCollectedAmount($installmentIds);
        $total_remaining = $installments->sum('remaining_balance');
        $active_count    = $installments->where('remaining_balance', '>', 0)->count();
        $accounts        = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $inventoryItems  = DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0)->get();
        $uniqueCustomers = DB::table('installments')->select('customer_name', 'customer_phone')->whereNotNull('customer_phone')->distinct()->get();
        $suppliers       = DB::table('suppliers')->orderBy('name', 'asc')->get();

        // جلب قائمة العملاء المسجلين بالاسم والرقم لشاشة الأقساط
        $customersList = \Illuminate\Support\Facades\DB::table('installments')
            ->select('customer_name', 'customer_phone')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '-')
            ->distinct()
            ->get();

        return view('installments', compact(
            'installments', 'accounts', 'inventoryItems', 'uniqueCustomers', 'suppliers',
            'search', 'dayFilter', 'timeFilter', 'customDate', 'total_expected_profit', 'total_remaining', 'active_count', 'total_collected', 'customersList'
        ));
    }


    // 📸 دالة أخذ لقطة للمركز المالي وأرصدة الخزائن للتقارير
    public function takeCapitalSnapshot(\Illuminate\Http\Request $request)
    {
        try {
            // 1. جلب بيانات المركز المالي الحالي من السيرفيس
            $summary = \App\Services\InstallmentFinanceService::treasurySummary();
            $totalCapital = $summary['capital'];
            $liquidity = $summary['liquidity'];

            // 2. جلب كل الخزائن والمحافظ النشطة
            $wallets = \Illuminate\Support\Facades\DB::table('accounts')
                        ->whereIn('category', ['bank_wallet', 'safe_cash'])
                        ->get();

            // 3. تجميع الخزائن في مصفوفة لتخزينها كـ JSON
            $walletsData = [];
            foreach ($wallets as $w) {
                $walletsData[] = [
                    'id'      => $w->id,
                    'name'    => $w->account_name,
                    'balance' => $w->balance
                ];
            }

            // 4. حفظ اللقطة في الداتابيز
            \Illuminate\Support\Facades\DB::table('capital_snapshots')->insert([
                'total_capital'   => $totalCapital,
                'liquidity_total' => $liquidity,
                'wallets_data'    => json_encode($walletsData, JSON_UNESCAPED_UNICODE),
                'notes'           => $request->notes ?? 'لقطة يدوية للمركز المالي',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // 5. تسجيل العملية في الرادار (لو موجود)
            if (method_exists($this, 'logActivity')) {
                $this->logActivity('create', 'finance', '📸 تم أخذ لقطة لسجل رأس المال وأرصدة الخزائن (لأغراض التقارير).');
            }

            return back()->with('success', '✅ تم حفظ لقطة المركز المالي وأرصدة جميع الخزائن بنجاح في الأرشيف.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدث خطأ أثناء أخذ اللقطة: ' . $e->getMessage());
        }
    }

    public function storeInstallment(\Illuminate\Http\Request $request)
    {
        if ($request->has('installment_months') && !$request->has('months')) {
            $request->merge(['months' => $request->installment_months]);
        }

        try {
            $request->validate([
                'sale_type'           => 'required|in:inventory,direct',
                'customer_name'       => 'required|string|max:255',
                // 💡 product_name إجبارية للبيع المباشر فقط — في حالة المخزن بيتحسب تلقائياً من الأصناف المختارة
                'product_name'        => 'required_if:sale_type,direct|nullable|string|max:255',
                'cash_price'          => 'required|numeric|min:0',
                'down_payment'        => 'required|numeric|min:0',
                'months'              => 'required|integer|min:1',
                'monthly_installment' => 'required|numeric|min:0',
                'due_day'             => 'required|integer|min:1|max:30',
               'transport_cost'      => 'nullable|numeric|min:0',
                'installation_cost'   => 'nullable|numeric|min:0',
                'materials_cost'      => 'nullable|numeric|min:0',
                'ac_expense_account'  => 'nullable|integer',
            ]);

            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $isDirect    = $request->sale_type === 'direct';
                $downPayment = floatval($request->down_payment);
                $discount    = floatval($request->discount ?? $request->discount_amount ?? 0);
                $purchaseCost = 0;
                $qty          = max(1, (int) ($request->quantity ?? 1));
                $productName  = trim($request->product_name ?? '');
                $inventoryItemsJson = null; // 🧺 تفاصيل الأصناف (sale_id+qty+اسم) لعقود المخزن متعددة المنتجات — يستخدمها الفسخ لإرجاع كل صنف لدفعته الأصلية

                // بنود التكييف الإضافية (نقل/تركيب/خامات) — مصاريف تمريرية لا تُحسب ربحاً
                $transportCost    = 0;
                $installationCost = 0;
                $materialsCost    = 0;
                $totalExtras      = 0;

                $supplierNameForInst = ''; // 🏷️ اسم المورد الأصلي — يُحفظ على العقد لاستخدامه عند الفسخ
                $productCategoryForInst = ''; // 🏷️ فئة المنتج الأصلية — تُحفظ للرجوع لها عند الفسخ

                if ($isDirect) {
                    $purchaseCost = floatval($request->purchase_cost);
                    $productCategoryForInst = trim($request->product_category ?? $request->category ?? '');
                    $supPayType   = $request->supplier_pay_type ?? 'cash';
                    $creditorName = trim($request->supplier_name ?? 'معرض عام');
                    $supplierNameForInst = $creditorName;
                    $supPaid      = 0;

                    if ($supPayType === 'cash') $supPaid = $purchaseCost;
                    elseif ($supPayType === 'partial') {
                        $supPaid = floatval($request->supplier_paid_amount);
                        if ($supPaid > $purchaseCost) throw new \Exception("المبلغ المدفوع للمورد أكبر من التكلفة الإجمالية!");
                    }

                    if ($supPaid > 0) {
                        if (empty($request->withdrawal_account)) throw new \Exception("يجب اختيار خزنة لسداد المورد.");
                        
                        $withAcc = \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->withdrawal_account)->lockForUpdate()->first();
                        if (!$withAcc || $withAcc->balance < $supPaid) throw new \Exception("رصيد الخزنة لا يكفي لسداد المورد ({$creditorName})!");
                        
                        \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->withdrawal_account)->decrement('balance', $supPaid);

                        \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                            'type'            => 'expense',
                            'amount'          => $supPaid,
                            'from_account_id' => $request->withdrawal_account,
                            'notes'           => "سداد للمورد [{$creditorName}] — تقسيط مباشر: " . $productName,
                            'created_at'      => now()
                        ]);

                        if (method_exists($this, 'applyAccountCommission')) $this->applyAccountCommission($request->withdrawal_account, $supPaid, 'سداد معرض', 'out');
                    }

                    $supRemaining = $purchaseCost - $supPaid;
                    if ($supRemaining > 0) {
                        $debtReason = match($supPayType) {
                            'deferred' => "آجل كامل — تقسيط مباشر | " . $productName,
                            'partial'  => "آجل جزئي — تقسيط مباشر | " . $productName,
                            default    => "شراء وتقسيط مباشر | " . $productName,
                        };
                        \Illuminate\Support\Facades\DB::table('company_debts')->insert([
                            'creditor_name'     => $creditorName,
                            'reason'            => $debtReason,
                            'total_amount'      => $purchaseCost,
                            'paid_amount'       => $supPaid,
                            'remaining_balance' => $supRemaining,
                            'category'          => 'مورد',
                            'created_at'        => now()
                        ]);
                    }
                } else {
                    // 🧺 عقد بأكتر من منتج: sale_id[] و quantity[] مصفوفات (صف واحد على الأقل لكل صنف من المخزن)
                    $saleIds   = $request->sale_id ?? [];
                    $quantities = $request->quantity ?? [];
                    if (!is_array($saleIds)) $saleIds = [$saleIds];
                    if (!is_array($quantities)) $quantities = [$quantities];

                    $items = [];
                    $productNames = [];
                    $suppliersSet   = [];
                    $categoriesSet  = [];
                    $totalQty = 0;

                    for ($i = 0; $i < count($saleIds); $i++) {
                        $sId = (int) ($saleIds[$i] ?? 0);
                        $iq  = (float) ($quantities[$i] ?? 0);
                        if ($sId <= 0 || $iq <= 0) continue; // تجاهل صفوف فاضية

                        $item = \Illuminate\Support\Facades\DB::table('sales')->where('id', $sId)->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>=', $iq)->lockForUpdate()->first();
                        if (!$item) throw new \Exception("الصنف المختار (#{$sId}) غير متوفر بالكمية المطلوبة في المخزن.");

                        $purchaseCost += $item->purchase_price * $iq;
                        $totalQty     += $iq;
                        $productNames[] = "({$iq} × {$item->product_name})";
                        $suppliersSet[$item->supplier_name ?? '']  = true;
                        $categoriesSet[$item->category ?? '']      = true;

                        \Illuminate\Support\Facades\DB::table('sales')->where('id', $item->id)->update([
                            'remaining_quantity' => $item->remaining_quantity - $iq,
                            'inventory_status'   => ($item->remaining_quantity - $iq) <= 0 ? 'sold_from_inventory' : 'to_inventory'
                        ]);

                        $items[] = ['sale_id' => $sId, 'qty' => $iq, 'product_name' => $item->product_name];
                    }

                    if (empty($items)) throw new \Exception("يجب اختيار صنف واحد على الأقل من المخزن.");

                    $qty         = $totalQty;
                    $productName = implode(' + ', $productNames);
                    // مورد/فئة واحدة لو كل الأصناف منهم، وإلا "متعدد" — نفس منطق التجميع المستخدم في شاشة المخزن
                    $supplierNameForInst    = count($suppliersSet) === 1 ? array_key_first($suppliersSet) : ('متعدد (' . count($suppliersSet) . ')');
                    $productCategoryForInst = count($categoriesSet) === 1 ? array_key_first($categoriesSet) : ('متعدد (' . count($categoriesSet) . ')');
                    $inventoryItemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);

                  // ══ بنود التكييف الإضافية (خامات، نقل، تركيب) — مصاريف تمريرية تُحصَّل من العميل ولا تُحسب ربحاً ══
                    $transportCost    = floatval($request->transport_cost ?? 0);
                    $installationCost = floatval($request->installation_cost ?? 0);
                    $materialsCost    = floatval($request->materials_cost ?? 0);
                    $totalExtras      = $transportCost + $installationCost + $materialsCost;

                    if ($totalExtras > 0) {
                        $expenseAccId = $request->ac_expense_account;
                        if (empty($expenseAccId)) {
                            throw new \Exception("يجب اختيار خزنة لسحب مصاريف (النقل، التركيب، الخامات).");
                        }

                        // 1. فحص رصيد الخزنة وسحب المبلغ
                        $expAcc = \Illuminate\Support\Facades\DB::table('accounts')->where('id', $expenseAccId)->lockForUpdate()->first();
                        if (!$expAcc || $expAcc->balance < $totalExtras) {
                            throw new \Exception("عذراً، رصيد خزنة المصاريف المتاح لا يكفي لسحب مبلغ: {$totalExtras} ج.");
                        }

                        \Illuminate\Support\Facades\DB::table('accounts')->where('id', $expenseAccId)->decrement('balance', $totalExtras);

                        // 2. تسجيل المصروف في العمليات المالية
                        \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                            'type'            => 'general_expense',
                            'amount'          => $totalExtras,
                            'from_account_id' => $expenseAccId,
                            'balance_before'  => self::getTotalCapital(),
                            'balance_after'   => self::getTotalCapital(),
                            'notes'           => "[بنود التكييفات] نقل: {$transportCost}ج | تركيب: {$installationCost}ج | خامات: {$materialsCost}ج | الصنف: {$productName}",
                            'status'          => 'active',
                            'created_at'      => now(),
                        ]);

                        // 3. تُضاف بنود التركيب لتكلفة العقد الإجمالية فقط (سجل تكلفة)،
                        //    لكن صافي الربح يُحسب على الجهاز وحده بحيث لا تُحتسب هذه البنود ربحاً (انظر $directSalesProfit أدناه).
                        //    تخزين البنود مرتبطةً بالعقد يتم بعد إنشاء العقد في جدول installment_expenses.
                        $purchaseCost += $totalExtras;
                    }
                }

                if ($downPayment > 0) {
                    if (empty($request->deposit_account)) throw new \Exception("يجب اختيار خزنة لإيداع المقدم.");
                    
                    \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->deposit_account)->increment('balance', $downPayment);
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type' => 'income', 'amount' => $downPayment, 'to_account_id' => $request->deposit_account,
                        'notes' => 'مقدم تقسيط: ' . $productName, 'created_at' => now()
                    ]);

                    if (method_exists($this, 'applyAccountCommission')) $this->applyAccountCommission($request->deposit_account, $downPayment, 'مقدم تقسيط', 'in');
                }

                if ($discount > 0) {
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type' => 'discount', 'amount' => $discount, 'status' => 'active',
                        'notes' => "✂️ خصم تقسيط: {$productName} | للعميل: {$request->customer_name}", 'created_at' => now()
                    ]);
                }

                $cashPrice     = floatval($request->cash_price);
                $totalAfterInt = floatval($request->total_after_interest);
                $interestRate  = floatval($request->interest_rate ?? 0);

                // ── تقسيم الربح: ربح مباشر (فرق السعر) + ربح تقسيط (الفائدة فقط) ──
                // 💡 بنود التركيب (نقل/تركيب/خامات) مصاريف تمريرية: العميل يدفعها والشركة تصرفها،
                //    لذلك تُستبعد تماماً من الربح. نحسب الربح على الجهاز وحده باستبعاد البنود من السعر والتكلفة معاً:
                //    سعر بيع الجهاز = سعر الكاش الكلي − بنود التركيب، وتكلفة الجهاز = إجمالي التكلفة − بنود التركيب.
                $deviceCashPrice   = max(0, $cashPrice - $totalExtras);
                $deviceCost        = max(0, $purchaseCost - $totalExtras);
                $directSalesProfit = max(0, $deviceCashPrice - $deviceCost);

                // ربح التقسيط = الفائدة فقط = total_after_interest − cash_price (بعد الخصم)
                // total_after_interest = (cashPrice - discount) + interest
                // إذاً interest = totalAfterInt - (cashPrice - discount)
                $installmentInterestProfit = max(0, $totalAfterInt - ($cashPrice - $discount));

                // الربح الإجمالي المخزن في العقد = المجموع (للتقارير الكلية) — بدون بنود التركيب
                $profit = $directSalesProfit + $installmentInterestProfit;

                $commVal = floatval($request->commission_amount ?? 0);
                if ($request->deduct_from_profit) $profit -= $commVal;

                // تسجيل ربح البيع المباشر فوراً في المعاملات المالية (income) — على الجهاز فقط
                if ($directSalesProfit > 0) {
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type'       => 'income',
                        'amount'     => $directSalesProfit,
                        'notes'      => "💰 ربح بيع (فرق سعر الجهاز): {$productName} | سعر الجهاز: {$deviceCashPrice}ج - تكلفته: {$deviceCost}ج" . ($totalExtras > 0 ? " | بنود التركيب ({$totalExtras}ج) لا تُحسب ربحاً" : ''),
                        'status'     => 'active',
                        'created_at' => now(),
                    ]);
                }

                $instId = \Illuminate\Support\Facades\DB::table('installments')->insertGetId([
                    'customer_name'        => $request->customer_name,
                    'customer_phone'       => $request->customer_phone ?? '',
                    'product_name'         => $productName,
                    'supplier_name'        => $supplierNameForInst ?: null,
                    'product_category'     => $productCategoryForInst ?: null,
                    'category'             => $isDirect ? 'مبيعات مباشرة' : 'مبيعات مخزن',
                    'sale_type'            => $request->sale_type,
                    'purchase_cost'        => $purchaseCost,
                    'quantity'             => $qty,
                    'cash_price'           => $cashPrice,
                    'discount'             => $discount,
                    'down_payment'         => $downPayment,
                    'installment_months'   => $request->months,
                    'interest_rate'        => $request->interest_rate ?? 0,
                    'total_after_interest' => $totalAfterInt,
                    'monthly_installment'  => $request->monthly_installment,
                    'due_day'              => $request->due_day,
                    'remaining_balance'    => max(0, $totalAfterInt - $downPayment),
                    'profit'               => $profit,
                    'notes'                => trim($request->notes ?? ''),
                    'inventory_items'      => $inventoryItemsJson,
                    'start_date'           => $request->start_date ?? now()->toDateString(),
                    'created_at'           => now()
                ]);

                // 💡 تخزين بنود التركيب مرتبطةً بالعقد (نقل/تركيب/خامات) في installment_expenses
                //    — هذا الجدول هو المصدر الذي تقرأ منه سجلات المخزن والتقارير ولوحة التحكم.
                if ($totalExtras > 0 && \Illuminate\Support\Facades\Schema::hasTable('installment_expenses')) {
                    \Illuminate\Support\Facades\DB::table('installment_expenses')->insert([
                        'installment_id'    => $instId,
                        'transport_cost'    => $transportCost,
                        'installation_cost' => $installationCost,
                        'materials_cost'    => $materialsCost,
                        'created_at'        => now(),
                    ]);
                }

                // 💡 تـم إزالـة إدخال الـ down_payment في جدول installment_payments تماماً كما طلبت.

                if ($commVal > 0) {
                     \Illuminate\Support\Facades\DB::table('company_debts')->insert([
'creditor_name'     => 'عمولات البيع',                        'reason'            => "عمولة مبيعات تقسيط: {$productName}",
                        'total_amount'      => $commVal,
                        'paid_amount'       => 0,
                        'remaining_balance' => $commVal,
                        'category'          => 'عمولات',
                        'created_at'        => now(),
                     ]);
                }

                if (!empty($request->customer_phone)) {
                    $customerExists = \Illuminate\Support\Facades\DB::table('customers')->where('phone', trim($request->customer_phone))->exists();
                    if (!$customerExists) {
                        \Illuminate\Support\Facades\DB::table('customers')->insert([
                            'name' => trim($request->customer_name), 'phone' => trim($request->customer_phone),
                            'address' => 'غير مسجل', 'created_at' => now(),
                        ]);
                    }
                }
            });

            return back()->with('success', '✅ تم إنشاء عقد التقسيط وتسجيل الحسابات بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage())->with('open_modal', true)->withInput();
        }
    }
// public function storeInstallment(\Illuminate\Http\Request $request)
//     {
//         if ($request->has('installment_months') && !$request->has('months')) {
//             $request->merge(['months' => $request->installment_months]);
//         }

//         try {
//             $request->validate([
//                 'sale_type'           => 'required|in:inventory,direct',
//                 'customer_name'       => 'required|string|max:255',
//                 'product_name'        => 'required|string|max:255',
//                 'cash_price'          => 'required|numeric|min:0',
//                 'down_payment'        => 'required|numeric|min:0',
//                 'months'              => 'required|integer|min:1',
//                 'monthly_installment' => 'required|numeric|min:0',
//                 'due_day'             => 'required|integer|min:1|max:31',
//             ]);

//             \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
//                 $isDirect    = $request->sale_type === 'direct';
//                 $downPayment = floatval($request->down_payment);
//                 $discount    = floatval($request->discount ?? $request->discount_amount ?? 0);
//                 $purchaseCost = 0;
//                 $qty          = 1;
//                 $productName  = trim($request->product_name);

//                 // ════════ 1. حسابات التكلفة وسحب المخزن / المورد ════════
//                 if ($isDirect) {
//                     $purchaseCost = floatval($request->purchase_cost);
//                     $supPayType   = $request->supplier_pay_type ?? 'cash';
//                     $creditorName = trim($request->supplier_name ?? 'معرض عام');
//                     $supPaid      = 0;

//                     if ($supPayType === 'cash') {
//                         $supPaid = $purchaseCost;
//                     } elseif ($supPayType === 'partial') {
//                         $supPaid = floatval($request->supplier_paid_amount);
//                         if ($supPaid > $purchaseCost) throw new \Exception("المبلغ المدفوع للمورد أكبر من التكلفة الإجمالية!");
//                     }

//                     if ($supPaid > 0) {
//                         if (empty($request->withdrawal_account)) throw new \Exception("يجب اختيار خزنة لسداد المورد.");
                        
//                         $withAcc = \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->withdrawal_account)->lockForUpdate()->first();
//                         if (!$withAcc || $withAcc->balance < $supPaid) {
//                             throw new \Exception("رصيد الخزنة المتاح لا يكفي لسداد المورد ({$creditorName})!");
//                         }
                        
//                         \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->withdrawal_account)->decrement('balance', $supPaid);

//                         \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
//                             'type'            => 'expense',
//                             'amount'          => $supPaid,
//                             'from_account_id' => $request->withdrawal_account,
//                             'notes'           => "سداد للمورد [{$creditorName}] — تقسيط مباشر: " . $productName,
//                             'created_at'      => now()
//                         ]);

//                         if (method_exists($this, 'applyAccountCommission')) {
//                             $this->applyAccountCommission($request->withdrawal_account, $supPaid, 'سداد معرض (تقسيط مباشر)', 'out');
//                         }
//                     }

//                     $supRemaining = $purchaseCost - $supPaid;
//                     if ($supRemaining > 0) {
//                         \Illuminate\Support\Facades\DB::table('company_debts')->insert([
//                             'creditor_name'     => $creditorName,
//                             'reason'            => "آجل/متبقي — شراء وتقسيط مباشر | " . $productName,
//                             'total_amount'      => $purchaseCost,
//                             'paid_amount'       => $supPaid,
//                             'remaining_balance' => $supRemaining,
//                             'category'          => 'مورد',
//                             'created_at'        => now()
//                         ]);
//                     }
//                 } else {
//                     // السحب من المخزن بالاسم
//                     $item = \Illuminate\Support\Facades\DB::table('sales')
//                         ->where('product_name', 'LIKE', '%' . $productName . '%')
//                         ->where('inventory_status', 'to_inventory')
//                         ->where('remaining_quantity', '>=', $qty)
//                         ->lockForUpdate()->first();
                        
//                     if (!$item) {
//                         throw new \Exception("المنتج ({$productName}) غير متوفر في المخزن، تأكد من تطابق الاسم مع المخزن.");
//                     }
//                     $purchaseCost = $item->purchase_price * $qty;
//                     $productName  = $item->product_name; // توحيد الاسم

//                     \Illuminate\Support\Facades\DB::table('sales')->where('id', $item->id)->update([
//                         'remaining_quantity' => $item->remaining_quantity - $qty,
//                         'inventory_status'   => ($item->remaining_quantity - $qty) <= 0 ? 'sold_from_inventory' : 'to_inventory'
//                     ]);
//                 }

//                 // ════════ 2. إيداع مقدم العميل في الخزنة ════════
//                 if ($downPayment > 0) {
//                     if (empty($request->deposit_account)) throw new \Exception("يجب اختيار خزنة لإيداع المقدم.");
                    
//                     \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->deposit_account)->increment('balance', $downPayment);
                    
//                     \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
//                         'type' => 'income', 'amount' => $downPayment, 'to_account_id' => $request->deposit_account,
//                         'notes' => 'مقدم تقسيط: ' . $productName, 'created_at' => now()
//                     ]);

//                     if (method_exists($this, 'applyAccountCommission')) {
//                         $this->applyAccountCommission($request->deposit_account, $downPayment, 'مقدم تقسيط', 'in');
//                     }
//                 }

//                 // ════════ 3. تسجيل الخصم המمنوح للعميل ════════
//                 if ($discount > 0) {
//                     \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
//                         'type' => 'discount', 'amount' => $discount, 'status' => 'active',
//                         'notes' => "✂️ خصم تقسيط: {$productName} | للعميل: {$request->customer_name}", 'created_at' => now()
//                     ]);
//                 }

//                 // ════════ 4. إنشاء العقد ════════
//                 $totalAfterInt = floatval($request->total_after_interest);
//                 $profit        = max(0, ($totalAfterInt + $discount) - $purchaseCost); 
//                 $commVal       = floatval($request->commission_amount ?? 0);
                
//                 if ($request->deduct_from_profit) $profit -= $commVal;

//                 $instId = \Illuminate\Support\Facades\DB::table('installments')->insertGetId([
//                     'customer_name'        => $request->customer_name,
//                     'customer_phone'       => $request->customer_phone ?? '',
//                     'product_name'         => $productName,
//                     'category'             => $isDirect ? 'مبيعات مباشرة' : 'مبيعات مخزن',
//                     'sale_type'            => $request->sale_type,
//                     'purchase_cost'        => $purchaseCost,
//                     'quantity'             => $qty,
//                     'cash_price'           => floatval($request->cash_price),
//                     'discount'             => $discount,
//                     'down_payment'         => $downPayment,
//                     'installment_months'   => $request->months,
//                     'total_after_interest' => $totalAfterInt,
//                     'monthly_installment'  => $request->monthly_installment,
//                     'due_day'              => $request->due_day,
//                     'remaining_balance'    => max(0, $totalAfterInt - $downPayment),
//                     'profit'               => $profit,
//                     'start_date'           => $request->start_date ?? now()->toDateString(),
//                     'created_at'           => now()
//                 ]);

//                 if ($downPayment > 0) {
//                      \Illuminate\Support\Facades\DB::table('installment_payments')->insert([
//                         'installment_id' => $instId, 'amount_paid' => $downPayment, 
//                         'payment_method_id' => $request->deposit_account, 'payment_date' => now()
//                      ]);
//                 }

//                 if ($commVal > 0) {
//                      \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
//                         'type' => 'general_expense', 'amount' => $commVal, 'notes' => "عمولة مبيعات تقسيط: {$productName}", 'created_at' => now()
//                      ]);
//                 }
//             });

//             return back()->with('success', '✅ تم إنشاء عقد التقسيط وتسجيل الحسابات بنجاح.');
            
//         } catch (\Exception $e) {
//             return back()->withInput()->with('error', $e->getMessage())->withInput();
//         }
//     }

public function deleteDefaultedPayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:installment_payments,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $payment = DB::table('installment_payments')->where('id', $request->payment_id)->first();
                if (!$payment) throw new \Exception('السجل غير موجود أو تم حذفه مسبقاً.');

                $inst = DB::table('installments')->where('id', $payment->installment_id)->first();

                // لو الدفعة بفلوس، نردها للخزنة اللي اتدفع فيها
                if ($payment->amount_paid > 0) {
                    $acc = DB::table('accounts')->where('id', $payment->payment_method_id)->first();
                    if (!$acc || $acc->balance < $payment->amount_paid) {
                        throw new \Exception("لا يوجد رصيد كافٍ في الخزنة لرد المبلغ المسدد!");
                    }

                    DB::table('accounts')->where('id', $payment->payment_method_id)->decrement('balance', $payment->amount_paid);

                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $payment->amount_paid,
                        'from_account_id' => $payment->payment_method_id,
                        'notes'           => "🔄 استرداد نقدي لدفعة ملغية ورجوع المديونية: {$inst->customer_name}",
                        'created_at'      => now(),
                    ]);
                }

                InstallmentFinanceService::restoreInstallmentAfterPaymentReversal($inst->id, $payment);

                DB::table('installment_payments')->where('id', $request->payment_id)->delete();

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('delete', 'installments', "🗑️ إلغاء دفعة من {$inst->customer_name} وإرجاع المبلغ");
                }
            });

            return back()->with('success', '✅ تم حذف الدفعة واسترداد المبلغ للخزنة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }
    public function updateInstallment(Request $request)
    {
        $inst = DB::table('installments')->where('id', $request->inst_id)->first();
        DB::table('installments')->where('id', $request->inst_id)->update([
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'due_day'        => $request->due_day,
        ]);

        // 🔔 تسجيل نشاط
        $this->logActivity('update', 'installments',
            "✏️ تعديل عقد: " . ($inst->customer_name ?? $request->inst_id) . " → {$request->customer_name}"
        );

        return back()->with('success', 'تم تعديل بيانات العقد بنجاح.');
    }

public function deleteInstallment(Request $request)
    {
        try {
            $instName = '';
            DB::transaction(function () use ($request, &$instName) {
                $inst = DB::table('installments')->where('id', $request->inst_id)->first();
                if (!$inst) throw new \Exception('هذا العقد غير موجود بالسجلات.');
                
                $instName = $inst->customer_name . ' | ' . $inst->product_name;

                if ($request->delete_mode === 'refund') {
                    // 1. حساب المبالغ المدفوعة وإجمالي قيمة العقد الحقيقية
                    $totalPaidInstallments = DB::table('installment_payments')->where('installment_id', $inst->id)->sum('amount_paid');
                    $totalPaidByCustomer   = $totalPaidInstallments + floatval($inst->down_payment);
                    $totalContractAmount = InstallmentFinanceService::totalContractValue($inst);

                    if ($inst->remaining_balance <= 0) {
                        throw new \Exception('⚠️ عفوًا.. لا يمكن عمل مرتجع أو إلغاء لعقد تم سداده بالكامل.');
                    }
                    if ($totalPaidByCustomer > ($totalContractAmount / 2)) {
                        throw new \Exception('⚠️ عفوًا.. لا يمكن عمل مرتجع لأن العميل قام بالفعل بسداد أكثر من نصف قيمة العقد.');
                    }

                    $qty = floatval($inst->quantity ?? 1);
                    $purchaseCost = floatval($inst->purchase_cost ?? 0);

                    // 📦 3. إرجاع المنتج للمخزن כـ Batch (دفعة جديدة) بكامل بياناته ليعود متاحاً للبيع
                    DB::table('sales')->insert([
                        'store_id'           => 1, // المخزن الرئيسي
                        'product_name'       => $inst->product_name,
                        'category'           => $inst->category ?? 'مرتجعات عملاء',
                        'supplier_name'      => 'مرتجع عقد من: ' . $inst->customer_name,
                        'purchase_price'     => $purchaseCost,
                        'selling_price'      => $inst->cash_price ?? 0,
                        'quantity'           => $qty,
                        'remaining_quantity' => $qty,
                        'inventory_status'   => 'to_inventory',
                        'purchase_date'      => now()->toDateString(),
                        'created_at'         => now(),
                    ]);

                    // 🔄 4. تسجيل العملية في تاب "مرتجعات العملاء" (ليظهر في شاشة المخزن)
                    DB::table('sale_returns')->insert([
                        'sale_id'           => 0, // 0 لأنه تم تكوين دفعة مخزنية جديدة
                        'product_name'      => $inst->product_name,
                        'category'          => $inst->category ?? 'مرتجعات عملاء',
                        'quantity_returned' => $qty,
                        'purchase_price'    => $purchaseCost,
                        'return_price'      => $totalPaidByCustomer, // المبلغ الذي استرده العميل
                        'total_refunded'    => $totalPaidByCustomer,
                        'loss_amount'       => 0, // الخسارة الدفترية صفر لأن الأصل رجع للمخزن
                        'refund_account_id' => $request->refund_account_id,
                        'notes'             => "مرتجع نتيجة إلغاء عقد العميل: {$inst->customer_name}",
                        'created_at'        => now(),
                    ]);

                    // 💵 5. سحب الفلوس من الخزنة لردها للعميل وتسجيل الحركة المالية
                    if ($totalPaidByCustomer > 0 && $request->refund_account_id) {
                        DB::table('accounts')->where('id', $request->refund_account_id)->decrement('balance', $totalPaidByCustomer);
                        DB::table('financial_transactions')->insert([
                            'type'            => 'expense',
                            'amount'          => $totalPaidByCustomer,
                            'from_account_id' => $request->refund_account_id,
                            'notes'           => 'رد مبالغ (مقدم وأقساط) لعقد ملغي للعميل: ' . $inst->customer_name,
                            'status'          => 'active',
                            'created_at'      => now(),
                        ]);
                    }
                }

                // 6. حذف دفعات العقد ثم العقد نفسه من أرشيف الديون
                DB::table('installment_payments')->where('installment_id', $inst->id)->delete();
                DB::table('installments')->where('id', $inst->id)->delete();
            });

            if (method_exists($this, 'logActivity')) {
                $this->logActivity('delete', 'installments', "🗑️ إلغاء عقد ومرتجع: {$instName}");
            }
            
            return back()->with('success', '✅ تم إلغاء العقد، ورجوع المنتج للمخزن، وتسجيل المرتجع للعميل بنجاح!');
        } catch (\Exception $e) {
            // 💡 هنا بيتم اصطياد الأخطاء (لو دافع النص أو مخلص) وإرسالها كرسالة تحذير تظهر في الـ Blade
            return back()->withInput()->with('error', $e->getMessage()); 
        }
    }
  public function payInstallment(Request $request)
    {
        $request->validate([
            'inst_id'   => 'required|exists:installments,id',
            'amount'    => 'required|numeric|min:0', // ✅ تعديل: نقبل 0 عشان لو هنعمل خصم بس
            'method_id' => 'nullable|exists:accounts,id', // ✅ تعديل: مش إجباري لو مفيش كاش
            'discount'  => 'nullable|numeric|min:0'
        ]);

        $inst_id    = $request->inst_id;
        $amount     = floatval($request->amount);
        $method_id  = $request->method_id;
        $discount   = floatval($request->discount ?? 0);
        $notes      = trim($request->notes ?? '');
        $isDefaulted = ($notes === 'تعثر' || $notes === 'متعسر');
        $date       = $request->payment_date ? $request->payment_date . ' ' . now()->format('H:i:s') : now();

        $lockKey = 'pay_inst_' . md5(auth()->id() . $inst_id . $amount . $discount);
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return back()->with('error', '⚠️ العملية قيد التنفيذ بالفعل! الرجاء الانتظار.');
        }

        try {
            DB::transaction(function () use ($inst_id, $amount, $method_id, $discount, $date, $isDefaulted, $notes) {

                $inst = DB::table('installments')->where('id', $inst_id)->first();
                if (!$inst) throw new \Exception('العملية غير موجودة.');

                // ══════════════════════════════════
                // حالة التعثر: تسجيل قسط بقيمة صفر
                // ══════════════════════════════════
                if ($isDefaulted) {
                    $defRow = [
                        'installment_id'    => $inst_id,
                        'amount_paid'       => 0,
                        'payment_method_id' => null,
                        'payment_date'      => $date,
                        'notes'             => 'متعسر',
                    ];
                    if (Schema::hasColumn('installment_payments', 'discount_applied')) {
                        $defRow['discount_applied'] = 0;
                    }
                    DB::table('installment_payments')->insert($defRow);
                    return;
                }

                // ══════════════════════════════════
                // فاليديشن للسداد العادي
                // ══════════════════════════════════
                if ($amount == 0 && $discount == 0) {
                    throw new \Exception('يجب إدخال مبلغ للتحصيل أو قيمة للخصم.');
                }
                if ($amount > 0 && !$method_id) {
                    throw new \Exception('يجب اختيار الخزنة التي سيتم إيداع المبلغ الكاش بها.');
                }

                $totalDeduction = $amount + $discount;
                if ($totalDeduction > $inst->remaining_balance) {
                    throw new \Exception('إجمالي السداد والخصم أكبر من المتبقي على العميل.');
                }

                $payRow = [
                    'installment_id'    => $inst_id,
                    'amount_paid'       => $amount,
                    'payment_method_id' => $amount > 0 ? $method_id : null,
                    'payment_date'      => $date,
                ];
                if (Schema::hasColumn('installment_payments', 'discount_applied')) {
                    $payRow['discount_applied'] = $discount;
                }
                $payId = DB::table('installment_payments')->insertGetId($payRow);

                if ($amount > 0) {
                    DB::table('accounts')->where('id', $method_id)->increment('balance', $amount);
                    DB::table('financial_transactions')->insert([
                        'type'          => 'income',
                        'amount'        => $amount,
                        'to_account_id' => $method_id,
                        'notes'         => "تحصيل دفعة/قسط: {$inst->customer_name} | {$inst->product_name}",
                        'ref_id'        => $payId,
                        'ref_type'      => 'installment_payment',
                        'status'        => 'active',
                        'created_at'    => $date,
                    ]);
                }

                if ($discount > 0) {
                    DB::table('installments')->where('id', $inst_id)->decrement('profit', $discount);
                    DB::table('financial_transactions')->insert([
                        'type'       => 'discount',
                        'amount'     => $discount,
                        'notes'      => "✂️ خصم أثناء التحصيل: {$inst->customer_name} | {$inst->product_name}",
                        'status'     => 'active',
                        'created_at' => now(),
                    ]);
                }

                if ($totalDeduction > 0) {
                    DB::table('installments')->where('id', $inst_id)->decrement('remaining_balance', $totalDeduction);
                }

                if (Schema::hasColumn('installments', 'close_reason')) {
                    $newRem = (float) DB::table('installments')->where('id', $inst_id)->value('remaining_balance');
                    DB::table('installments')->where('id', $inst_id)->update([
                        'close_reason' => $newRem <= 0.001 ? 'paid' : null,
                    ]);
                }
            });

            $successMsg = $isDefaulted
                ? '⚠️ تم تسجيل القسط كمتعسر (قيمة صفر) في سجل الدفعات.'
                : '✅ تم تحصيل المبلغ وتوثيق الخصم في سجل الماليات بنجاح.';

            // نرجّع هاتف/اسم العميل عشان الواجهة تعيد فتح كشف حسابه تلقائياً (lazy)
            // (يسهّل سداد أكتر من عقد لنفس العميل من غير ما يدوّر تاني)
            $paidInst = DB::table('installments')->where('id', $request->inst_id)->first();
            return back()->with('success', $successMsg)
                ->with('reopen_phone', $paidInst->customer_phone ?? '')
                ->with('reopen_name', $paidInst->customer_name ?? '');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════
    // ══════════════════════════════════════════════════════
    // فسخ العقد (Terminate) — يرجّع البضاعة + يرد الفلوس + يحذف الأرباح
    // ══════════════════════════════════════════════════════
    public function terminateInstallment(Request $request)
    {
        $request->validate([
            'inst_id'           => 'required|exists:installments,id',
            'refund_amount'     => 'required|numeric|min:0',
            'refund_account_id' => 'required|integer|exists:accounts,id',
            'return_to_stock'   => 'required|in:yes,no',
            'reason'            => 'required|string|max:500',
        ]);

        $msgs = [];
        $unmatchedItems = false;

        try {
            DB::transaction(function () use ($request, &$msgs, &$unmatchedItems) {
                $inst = DB::table('installments')->where('id', $request->inst_id)->lockForUpdate()->first();
                if (!$inst) throw new \Exception('العقد غير موجود.');
                if (($inst->status ?? '') === 'cancelled') throw new \Exception('العقد ملغي بالفعل.');

                $refundAmount  = (float) $request->refund_amount;
                $accId         = (int)   $request->refund_account_id;
                $returnToStock = $request->return_to_stock === 'yes';
                $reason        = trim($request->reason);

                $createdAt = \Carbon\Carbon::parse($inst->created_at);
                $winStart  = $createdAt->copy()->subSeconds(5);
                $winEnd    = $createdAt->copy()->addSeconds(5);

                // ── إجمالي اللي العميل دفعه فعلاً ──
                $paymentsSum = (float) DB::table('installment_payments')->where('installment_id', $inst->id)->sum('amount_paid');
                $downPayment = (float) $inst->down_payment;
                $hasDownInPayments = DB::table('installment_payments')
                    ->where('installment_id', $inst->id)
                    ->where('amount_paid', $downPayment)
                    ->whereBetween('payment_date', [$winStart, $winEnd])
                    ->exists();
                $totalPaidByCustomer = $hasDownInPayments ? $paymentsSum : ($paymentsSum + $downPayment);

                if ($refundAmount > $totalPaidByCustomer + 0.01) {
                    throw new \Exception("لا يمكن رد مبلغ (" . number_format($refundAmount, 0) . " ج) أكبر من اللي العميل دفعه فعلاً (" . number_format($totalPaidByCustomer, 0) . " ج).");
                }

                // ── منع: لو عمولات مرتبطة متسددة ──
                $commissionsPaid = DB::table('company_debts')
                    ->where('category', 'عمولات')
                    ->whereBetween('created_at', [$winStart, $winEnd])
                    ->where('paid_amount', '>', 0)
                    ->exists();
                if ($commissionsPaid) {
                    throw new \Exception('في عمولة مبيع متسددة فعلاً على العقد ده. الغِ سداد العمولة الأول من شاشة الديون ثم اعمل الفسخ.');
                }

                // ── منع: لو ديون مورد مرتبطة متسددة ──
                $supplierPaid = DB::table('company_debts')
                    ->whereIn('category', ['مورد', 'موردين'])
                    ->whereBetween('created_at', [$winStart, $winEnd])
                    ->where('paid_amount', '>', 0)
                    ->exists();
                if ($supplierPaid) {
                    throw new \Exception('في دين مورد متسدد مرتبط بالعقد. الغِ السداد من شاشة الديون أولاً.');
                }

                // ── (1) سحب مبلغ الرد من الخزنة ──
                if ($refundAmount > 0) {
                    $acc = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                    if (!$acc) throw new \Exception('الخزنة المختارة غير موجودة.');
                    if ((float)$acc->balance < $refundAmount) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من مبلغ الرد. الرصيد المتاح: " . number_format($acc->balance, 0) . " ج");
                    }
                    DB::table('accounts')->where('id', $accId)->decrement('balance', $refundAmount);

                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $refundAmount,
                        'from_account_id' => $accId,
                        'notes'           => "فسخ عقد #{$inst->id} | رد للعميل {$inst->customer_name} | السبب: {$reason}",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                    $msgs[] = "تم رد " . number_format($refundAmount, 0) . " ج للعميل";
                }

                // ── (2) الفرق المحتجز (اللي العميل دفعه ناقص اللي اترد له) بيفضل في الخزنة تلقائياً ──
                //     الفلوس اللي العميل دفعها (المقدم + الأقساط) موجودة أصلاً في الخزنة من وقت الإنشاء.
                //     بمجرد سحب مبلغ الرد في الخطوة (1)، الفرق بيفضل محتجزاً بالضبط بدون أي تعديل إضافي.
                //
                //     🐞 إصلاح: قبل كده كان في هنا خصمين زيادة على الرصيد:
                //        (أ) إعادة إضافة الفرق كإيراد + increment للرصيد، و
                //        (ب) حذف إيراد "مقدم تقسيط" الأصلي مع decrement للرصيد بقيمة المقدم كامل.
                //        الاتنين مع خطوة الرد (1) كانوا بيعكسوا المقدم مرتين → بيسحبوا ضِعف المبلغ من
                //        الخزنة (مثال: عقد مقدمه 5000 كان بيطلّع 10000 من الخزنة بدل 5000).
                //
                //     🧾 محاسبياً: إيراد "مقدم تقسيط" الأصلي بيفضل كما هو ويقاصّه مصروف الرد،
                //        فصافي ربح العقد المفسوخ = الفرق المحتجز (صفر لو الرد كامل). ✅
                $diff = $totalPaidByCustomer - $refundAmount;
                if ($diff > 0.01) {
                    $msgs[] = "احتُجز فرق قدره " . number_format($diff, 0) . " ج للشركة";
                }

                // ── (4) إرجاع البضاعة للمخزن (لو المستخدم وافق) ──
                $qty = floatval($inst->quantity ?? 1);
                $purchaseCost = floatval($inst->purchase_cost ?? 0);
                $unitCost  = $qty > 0 ? $purchaseCost / $qty : $purchaseCost;
                $unitPrice = $qty > 0 ? floatval($inst->cash_price) / $qty : floatval($inst->cash_price);
                $isInvSale = ($inst->sale_type === 'inventory' || $inst->category === 'مبيعات مخزن');
                $isService = ($inst->category === 'خدمات');

                if ($returnToStock && !$isService) {
                    if ($isInvSale && !empty($inst->inventory_items)) {
                        $items = json_decode($inst->inventory_items, true);
                        if (is_array($items)) {
                            foreach ($items as $it) {
                                $sid = (int)($it['sale_id'] ?? 0);
                                $iq  = (float)($it['qty'] ?? 0);
                                if ($sid <= 0 || $iq <= 0) continue;
                                $exists = DB::table('sales')->where('id', $sid)->first();
                                if (!$exists) { $unmatchedItems = true; continue; }
                                DB::table('sales')->where('id', $sid)->update([
                                    'remaining_quantity' => DB::raw("remaining_quantity + {$iq}"),
                                    'inventory_status'   => 'to_inventory',
                                    'updated_at'         => now(),
                                ]);
                                DB::table('inventory_movements')->insert([
                                    'sale_id'    => $sid,
                                    'type'       => 'customer_return',
                                    'quantity'   => $iq,
                                    'notes'      => "فسخ عقد تقسيط #{$inst->id} — {$inst->customer_name}",
                                    'created_at' => now(),
                                ]);
                            }
                            $msgs[] = "البضاعة رجعت للمخزن";
                        }
                    } else {
                        // direct أو inventory قديم بدون ربط — ننشئ batch جديد بالفئة الأصلية + علامة مرتجع منفصلة
                        $newSid = DB::table('sales')->insertGetId([
                            'store_id'           => 1,
                            'product_name'       => $inst->product_name,
                            'category'           => trim($inst->product_category ?? '') ?: 'عام', // 🏷️ الفئة الأصلية (مش "مرتجعات عملاء")
                            'is_return'          => 1, // 🏷️ علامة منفصلة إن ده مرتجع عميل (للبادج)
                            'supplier_name'      => trim($inst->supplier_name ?? '') ?: 'غير محدد',
                            'purchase_price'     => $unitCost,
                            'selling_price'      => $unitPrice,
                            'quantity'           => $qty,
                            'remaining_quantity' => $qty,
                            'inventory_status'   => 'to_inventory',
                            'purchase_date'      => now()->toDateString(),
                            'created_at'         => now(),
                        ]);
                        DB::table('inventory_movements')->insert([
                            'sale_id'    => $newSid,
                            'type'       => 'customer_return',
                            'quantity'   => $qty,
                            'notes'      => "فسخ عقد #{$inst->id} — {$inst->customer_name}",
                            'created_at' => now(),
                        ]);
                        if ($isInvSale) $unmatchedItems = true;
                        $msgs[] = "تم إنشاء باتش جديد في المخزن (مرتجع)";
                    }

                    // sale_returns log (تاب مرتجعات العملاء)
                    if (Schema::hasTable('sale_returns')) {
                        DB::table('sale_returns')->insert([
                            'sale_id'           => 0,
                            'product_name'      => $inst->product_name,
                            'category'          => 'مرتجعات عملاء',
                            'quantity_returned' => $qty,
                            'purchase_price'    => $unitCost,
                            'return_price'      => $refundAmount,
                            'total_refunded'    => $refundAmount,
                            'loss_amount'       => 0,
                            'refund_account_id' => $accId,
                            'notes'             => "فسخ عقد تقسيط — {$inst->customer_name} | السبب: {$reason}",
                            'created_at'        => now(),
                        ]);
                    }
                } elseif (!$returnToStock && !$isService) {
                    // البضاعة ما رجعتش → تتسجل خسارة بقيمة التكلفة
                    DB::table('financial_transactions')->insert([
                        'type'            => 'general_expense',
                        'amount'          => $purchaseCost,
                        'from_account_id' => null,
                        'notes'           => "[خسائر فسخ عقود] فسخ #{$inst->id} ({$inst->customer_name}) — البضاعة ما رجعتش، تكلفتها: " . number_format($purchaseCost, 0) . " ج",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                    $msgs[] = "البضاعة ما رجعتش → تم تسجيل خسارة بـ " . number_format($purchaseCost, 0) . " ج";
                }

                // ── (5) حذف العمولات/الخصومات بس (مش ديون المورد!) ──
                // 🚨 ديون المورد (category='مورد'|'موردين') لا تُحذف — البضاعة لسه عندك في المخزن
                //     أو خسرتها، فالمورد لسه ليه فلوس واجبة السداد.
                DB::table('company_debts')
                    ->where(function ($q) {
                        $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                    })
                    ->whereNotIn('category', ['مورد', 'موردين']) // ⬅️ استثناء الموردين
                    ->whereBetween('created_at', [$winStart, $winEnd])
                    ->delete();

                // financial_transactions مرتبطة (خصومات/عمولات بس — مش مصاريف عامة لإن نقل/تركيب اتدفع فعلاً)
                $ftWindow = DB::table('financial_transactions')
                    ->whereIn('type', ['discount', 'commission'])
                    ->whereBetween('created_at', [$winStart, $winEnd])
                    ->get();
                foreach ($ftWindow as $ft) {
                    DB::table('financial_transactions')->where('id', $ft->id)->delete();
                }

                // installment_expenses (نقل/تركيب/خامات)
                if (Schema::hasTable('installment_expenses')) {
                    DB::table('installment_expenses')->where('installment_id', $inst->id)->delete();
                }

                // installment_payments (المقدم والأقساط)
                DB::table('installment_payments')->where('installment_id', $inst->id)->delete();

                // sales row للبيع المباشر (inventory_status='sold')
                if (!$isInvSale && !$isService) {
                    DB::table('sales')
                        ->where('inventory_status', 'sold')
                        ->where('product_name', $inst->product_name)
                        ->whereBetween('created_at', [$winStart, $winEnd])
                        ->delete();
                }

                // ── (6) حذف العقد نفسه (الربح ينمسح تلقائياً مع الحذف) ──
                DB::table('installments')->where('id', $inst->id)->delete();

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('cancel', 'installments',
                        "🔥 فسخ عقد #{$inst->id} — العميل: {$inst->customer_name} | المنتج: {$inst->product_name} | رد: " . number_format($refundAmount, 0) . " ج | السبب: {$reason}"
                    );
                }
            });

            $base = '✅ تم فسخ العقد بنجاح.';
            if (!empty($msgs)) $base .= ' (' . implode(' · ', $msgs) . ')';
            if ($unmatchedItems) $base .= ' ⚠️ في أصناف ما اتربطتش بالمخزن — راجع وأضف الكميات يدوياً.';
            return back()->with('success', $base);
        } catch (\Throwable $e) {
            return back()->with('error', '❌ فشل فسخ العقد: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════
    // إعدام دين العميل (شطبه كخسارة)
    // ══════════════════════════════════════════════════════
    public function writeOffInstallment(Request $request)
    {
        $request->validate([
            'inst_id'         => 'required|exists:installments,id',
            'writeoff_reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $inst = DB::table('installments')->where('id', $request->inst_id)->lockForUpdate()->first();
                if (!$inst) throw new \Exception('العقد غير موجود.');
                if ($inst->remaining_balance <= 0) throw new \Exception('هذا العقد مسدد بالفعل.');

                $writeoffAmount = floatval($inst->remaining_balance);
                $reason = $request->writeoff_reason;
                $notes  = trim($request->writeoff_notes ?? '');

                // تسجيل الإعدام كمصروف/خسارة في الماليات
                DB::table('financial_transactions')->insert([
                    'type'    => 'general_expense',
                    'amount'  => $writeoffAmount,
'notes'   => "💀 إعدام ديون — العميل: {$inst->customer_name} | {$inst->product_name} | السبب: {$reason}" . ($notes ? " | {$notes}" : ""),                    'status'  => 'active',
                    'created_at' => now(),
                ]);

                $writeoffUpdate = [
                    'remaining_balance' => 0,
                    'updated_at'        => now(),
                ];
                if (Schema::hasColumn('installments', 'close_reason')) {
                    $writeoffUpdate['close_reason'] = 'written_off';
                }
                DB::table('installments')->where('id', $inst->id)->update($writeoffUpdate);
                if ((float) ($inst->profit ?? 0) > 0) {
                    DB::table('installments')->where('id', $inst->id)->decrement(
                        'profit',
                        min((float) $inst->profit, $writeoffAmount)
                    );
                }

                // تسجيل دفعة إعدام بقيمة 0 في سجل الدفعات للتوثيق
                DB::table('installment_payments')->insert([
                    'installment_id'    => $inst->id,
                    'amount_paid'       => 0,
                    'payment_method_id' => null,
                    'payment_date'      => now(),
                    'notes'             => "إعدام | {$reason}",
                ]);

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('delete', 'installments',
                        "💀 إعدام دين: {$inst->customer_name} | مبلغ: " . number_format($writeoffAmount, 0) . " ج | سبب: {$reason}"
                    );
                }
            });

            return back()->with('success', '✅ تم إعدام الدين وتسجيله كخسارة في الماليات بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════
   
    // الديون (عملاء)
    // ══════════════════════════════════════════════════════
// ══════════════════════════════════════════════════════
    // الديون (مستحقات العملاء العادية - بدون الأقساط)
    // ══════════════════════════════════════════════════════
    // ══════════════════════════════════════════════════════
    // الديون (مستحقات العملاء العادية - بدون الأقساط)
    // ══════════════════════════════════════════════════════
  public function debts(Request $request)
{
    $search     = $request->input('search', '');
    // 💡 الافتراضي "الديون النشطة" عشان العملاء المسددين بالكامل ميتعرضوش افتراضيًا
    $status     = $request->input('status', 'active');

    // فلتر الوقت — الآن يدعم: today, yesterday, week, month, year, custom (نطاق تواريخ)
    // ✅ الافتراضي: كل السجلات (متسق مع الـ view الذي يستخدم default 'all')
    $timeFilter = $request->input('time_filter', 'all');
    $customFrom = $request->input('custom_from');
    $customTo   = $request->input('custom_to');
 
    $query = DB::table('installments')
        ->where('installment_months', '<=', 0)
        ->selectRaw('installments.*, (SELECT COALESCE(SUM(amount_paid),0) FROM installment_payments WHERE installment_id = installments.id) as calculated_paid')
        ->orderBy('installments.created_at', 'desc');
 
    if ($timeFilter === 'today') {
        $query->whereDate('installments.created_at', \Carbon\Carbon::today()->toDateString());
    } elseif ($timeFilter === 'yesterday') {
        $query->whereDate('installments.created_at', \Carbon\Carbon::yesterday()->toDateString());
    } elseif ($timeFilter === 'week') {
        $query->whereBetween('installments.created_at', [
            now()->startOfWeek(\Carbon\Carbon::SATURDAY),
            now()->endOfWeek(\Carbon\Carbon::FRIDAY)
        ]);
    } elseif ($timeFilter === 'month') {
        $query->whereMonth('installments.created_at', now()->month)
              ->whereYear('installments.created_at', now()->year);
    } elseif ($timeFilter === 'year') {
        $query->whereYear('installments.created_at', now()->year);
    } elseif ($timeFilter === 'custom' && !empty($customFrom)) {
        $to = !empty($customTo) ? $customTo : $customFrom;
        $query->whereDate('installments.created_at', '>=', $customFrom)
              ->whereDate('installments.created_at', '<=', $to);
    }
    // لو timeFilter = 'all' → لا يُطبَّق أي فلتر تاريخ
 
    if ($search) {
        $this->applyArabicSearch($query, ['customer_name', 'customer_phone'], $search);
    }

    $debtsRaw = $query->get();
 
    if ($status === 'active') {
        $debtsRaw = $debtsRaw->where('remaining_balance', '>', 0);
    } elseif ($status === 'paid') {
        $debtsRaw = $debtsRaw->where('remaining_balance', '<=', 0);
    }
 
    $debts    = $debtsRaw->values();
    $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
 
    return view('debts', compact('debts', 'accounts', 'search', 'timeFilter', 'customFrom', 'customTo', 'status'));
}
 
public function storeFuelDebt(Request $request)
{
    $request->validate([
        'customer_name'  => 'required|string|max:255',    // شركة النقل
        'driver_car'     => 'required|string|max:255',    // السائق/السيارة
        'cash_price'     => 'required|numeric|min:0',     // إجمالي تكلفة العملية
        'fuel_liters'    => 'required|numeric|min:0',     // عدد الليترات
        'cash_custody'   => 'required|numeric|min:0',     // العهدة النقدية
        'down_payment'   => 'nullable|numeric|min:0',     // مقدم (اختياري)
        'ahmed_profit'   => 'nullable|numeric|min:0',     // صافي الربح (اختياري)
        'station_name'   => 'nullable|string|max:255',    // اسم المحطة
    ], [
        'customer_name.required' => 'اسم شركة النقل مطلوب.',
        'driver_car.required'    => 'اسم السائق/السيارة مطلوب.',
        'cash_price.required'    => 'إجمالي التكلفة مطلوب.',
        'fuel_liters.required'   => 'عدد الليترات مطلوب.',
        'cash_custody.required'  => 'العهدة النقدية مطلوبة.',
    ]);
 
    try {
        DB::transaction(function () use ($request) {
            $downPayment  = floatval($request->down_payment ?? 0);
            $cashPrice    = floatval($request->cash_price);
            $fuelLiters   = floatval($request->fuel_liters);
            $cashCustody  = floatval($request->cash_custody);
            $ahmedProfit  = floatval($request->ahmed_profit ?? 0);
            $stationName  = trim($request->station_name ?? '');
 
            // اسم المنتج: السائق/السيارة (يظهر في جدول العمليات)
            $productName  = trim($request->driver_car);
            // notes تجمع المحطة لو موجودة
            $notes = $stationName ? "محطة: {$stationName}" : '';
 
            // تسجيل العملية في installments
            DB::table('installments')->insert([
                'customer_name'        => trim($request->customer_name),
                'customer_phone'       => $request->customer_phone ?? '',
                'product_name'         => $productName,
                'category'             => 'بنزينة',
                'sale_type'            => 'fuel',
                'purchase_cost'        => 0,
                'quantity'             => $fuelLiters,      // عدد اللترات في حقل الكمية
                'fuel_liters'          => $fuelLiters,      // الحقل الجديد الخاص
                'cash_custody'         => $cashCustody,     // الحقل الجديد للعهدة
                'cash_price'           => $cashPrice,
                'discount'             => 0,
                'down_payment'         => $downPayment,
                'installment_months'   => 0,
                'interest_rate'        => 0,
                'total_after_interest' => $cashPrice,
                'monthly_installment'  => $cashPrice,
                'due_day'              => intval(date('d')),
                'remaining_balance'    => max(0, $cashPrice - $downPayment),
                'profit'               => $ahmedProfit,
                'start_date'           => $request->operation_date ?? now()->toDateString(),
                'notes'                => $notes,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
 
            // لو في مقدم → يُسجَّل في الخزنة
            if ($downPayment > 0 && !empty($request->deposit_account)) {
                DB::table('accounts')->where('id', $request->deposit_account)->increment('balance', $downPayment);
                DB::table('financial_transactions')->insert([
                    'type'          => 'income',
                    'amount'        => $downPayment,
                    'to_account_id' => $request->deposit_account,
                    'notes'         => "مقدم بنزينة — {$productName} | " . trim($request->customer_name),
                    'status'        => 'active',
                    'created_at'    => now(),
                ]);
            }
        });
 
        if (method_exists($this, 'logActivity')) {
            $this->logActivity('create', 'fuel',
                "⛽ عملية بنزينة جديدة | شركة: {$request->customer_name} | سائق: {$request->driver_car} | لترات: {$request->fuel_liters} | إجمالي: " . number_format($request->cash_price, 2) . " ج"
            );
        }
 
        return back()->with('success', '✅ تم تسجيل عملية البنزينة بنجاح.');
    } catch (\Exception $e) {
        return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
    }
}
 


// 💡 دالة السداد الكلي للعملاء (مؤمنة ضد الدفع المتكرر)
    public function payBulkInstallments(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'account_id'    => 'required|integer'
        ]);

        $lockKey = 'bulk_inst_' . md5(auth()->id() . $request->customer_name . $request->account_id);
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return back()->with('error', '⚠️ العملية قيد التنفيذ بالفعل! الرجاء الانتظار.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $customer = $request->customer_name;
                
                // 🛡️ الحماية 1: lockForUpdate() بتمنع أي ضغطة تانية تسحب الداتا في نفس اللحظة
                $debts = \Illuminate\Support\Facades\DB::table('installments')
                    ->where('customer_name', $customer)
                    ->where('installment_months', '<=', 0)
                    ->where('remaining_balance', '>', 0)
                    ->lockForUpdate() // قفل السجل برمجياً
                    ->get();

                // 🛡️ الحماية 2: لو المديونية اتسددت في الضغطة الأولى، الضغطة التانية هتترفض فوراً
                if ($debts->isEmpty()) {
                    throw new \Exception('⚠️ لا توجد مديونية معلقة لهذا العميل أو تم تحصيلها بالفعل.');
                }

                $totalPaid = 0;
                $date = now();

                foreach ($debts as $debt) {
                    $rem = floatval($debt->remaining_balance);
                    
                    // 1. تسجيل الدفعة
                    \Illuminate\Support\Facades\DB::table('installment_payments')->insert([
                        'installment_id'    => $debt->id,
                        'amount_paid'       => $rem,
                        'payment_method_id' => $request->account_id,
                        'payment_date'      => $date,
                    ]);

                    // 2. تصفير رصيد العملية
                    $bulkUpdate = ['remaining_balance' => 0, 'updated_at' => $date];
                    if (Schema::hasColumn('installments', 'close_reason')) {
                        $bulkUpdate['close_reason'] = 'paid';
                    }
                    \Illuminate\Support\Facades\DB::table('installments')->where('id', $debt->id)->update($bulkUpdate);
                    
                    $totalPaid += $rem;
                }

                // 3. إيداع المبلغ في الخزنة
                \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->account_id)->increment('balance', $totalPaid);

                // 4. إثبات العملية في الماليات
                \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                    'type'          => 'income',
                    'amount'        => $totalPaid,
                    'to_account_id' => $request->account_id,
                    'notes'         => 'سداد مجمع لكامل ديون العميل: ' . $customer,
                    'status'        => 'active',
                    'created_at'    => $date,
                ]);

                // تسجيل في الرادار
                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('payment', 'installments', "💰 تحصيل مجمع بقيمة {$totalPaid} ج للعميل ({$customer})");
                }
            });

            return back()->with('success', '✅ تم تنفيذ التحصيل الكلي وتحديث الأرصدة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function payPartialInstallments(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'account_id'    => 'required|integer',
            'amount'        => 'required|numeric|min:0.01',
        ]);

        $lockKey = 'partial_inst_' . md5(auth()->id() . $request->customer_name . $request->amount);
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return back()->with('error', '⚠️ العملية قيد التنفيذ بالفعل! الرجاء الانتظار.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $customer = $request->customer_name;
                $amount   = floatval($request->amount);

                $debts = \Illuminate\Support\Facades\DB::table('installments')
                    ->where('customer_name', $customer)
                    ->where('installment_months', '<=', 0)
                    ->where('remaining_balance', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                if ($debts->isEmpty()) {
                    throw new \Exception('⚠️ لا توجد مديونية معلقة لهذا العميل.');
                }

                $totalRemaining = $debts->sum('remaining_balance');
                if ($amount > $totalRemaining) {
                    throw new \Exception('المبلغ المُدخل (' . number_format($amount, 2) . ' ج) أكبر من إجمالي المتبقي (' . number_format($totalRemaining, 2) . ' ج).');
                }

                $date      = now();
                $remaining = $amount;

                foreach ($debts as $debt) {
                    if ($remaining <= 0) break;
                    $deduct = min($remaining, floatval($debt->remaining_balance));

                    \Illuminate\Support\Facades\DB::table('installment_payments')->insert([
                        'installment_id'    => $debt->id,
                        'amount_paid'       => $deduct,
                        'payment_method_id' => $request->account_id,
                        'payment_date'      => $date,
                    ]);

                    $newBalance = floatval($debt->remaining_balance) - $deduct;
                    $upd = ['remaining_balance' => $newBalance, 'updated_at' => $date];
                    if ($newBalance <= 0 && Schema::hasColumn('installments', 'close_reason')) {
                        $upd['close_reason'] = 'paid';
                    }
                    \Illuminate\Support\Facades\DB::table('installments')->where('id', $debt->id)->update($upd);

                    $remaining -= $deduct;
                }

                \Illuminate\Support\Facades\DB::table('accounts')->where('id', $request->account_id)->increment('balance', $amount);

                \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                    'type'          => 'income',
                    'amount'        => $amount,
                    'to_account_id' => $request->account_id,
                    'notes'         => 'سداد جزئي للعميل: ' . $customer,
                    'status'        => 'active',
                    'created_at'    => $date,
                ]);

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('payment', 'installments', "💰 سداد جزئي بقيمة {$amount} ج للعميل ({$customer})");
                }
            });

            return back()->with('success', '✅ تم تنفيذ السداد الجزئي وتحديث الأرصدة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function applyDiscount(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $instId        = $request->inst_id;
                $discountType  = $request->discount_type;
                $discountValue = floatval($request->discount_value);
                $reason        = $request->discount_reason ?? 'خصم بدون سبب';

                $inst = DB::table('installments')->where('id', $instId)->first();
                if (!$inst) throw new \Exception('العقد غير موجود.');

                $discountAmount = ($discountType === 'percent') ? round($inst->remaining_balance * ($discountValue / 100), 2) : $discountValue;
                $discountAmount = min($discountAmount, $inst->remaining_balance);
                if ($discountAmount <= 0) throw new \Exception('قيمة الخصم صفر أو أقل من الصفر.');

                // ✅ يتم خصم المبلغ من المديونية + إنقاص ربح الشركة الدفتري بنفس القيمة
                DB::table('installments')->where('id', $instId)->decrement('remaining_balance', $discountAmount);
                DB::table('installments')->where('id', $instId)->decrement('profit', $discountAmount);

                if (Schema::hasColumn('installments', 'close_reason')) {
                    $newRem = (float) DB::table('installments')->where('id', $instId)->value('remaining_balance');
                    if ($newRem <= 0.001) {
                        DB::table('installments')->where('id', $instId)->update(['close_reason' => 'paid']);
                    }
                }

                DB::table('financial_transactions')->insert([
                    'type'       => 'discount',
                    'amount'     => $discountAmount,
                    'notes'      => "✂️ خصم: {$inst->customer_name} | {$inst->product_name} | السبب: {$reason} | القيمة: {$discountAmount} ج",
                    'status'     => 'active', // ✅ تفعيل الخصم للظهور في الماليات
                    'created_at' => now(),
                ]);
            });

            return back()->with('success', 'تم تطبيق الخصم وتحديث رصيد العميل بنجاح.');
        } catch (\Exception $e) { 
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage()); 
        }
    }
    // ══════════════════════════════════════════════════════
    // ديون الشركة (على الشركة)
    // ══════════════════════════════════════════════════════
  public function companyDebts(Request $request)
    {
        $search         = $request->input('search', '');
        $categoryFilter = $request->input('category', '');
        $timeFilter     = $request->input('time_filter', 'all');
        $customDate     = $request->input('custom_date');
        $rangeFrom      = $request->input('range_from');
        $rangeTo        = $request->input('range_to');
        $perPage        = 15;

        // ترتيب القائمة: الأحدث/الأقدم، المبلغ المتبقي، عدد العمليات، أو الأقرب للسداد الكامل
        $sortKey   = $request->input('sort', 'newest');
        $applySort = function ($q) use ($sortKey) {
            return match ($sortKey) {
                'remaining_desc' => $q->orderByDesc('remaining_balance'),
                'remaining_asc'  => $q->orderBy('remaining_balance'),
                'oldest'         => $q->orderBy('latest_op_at'),
                'count_desc'     => $q->orderByDesc('ops_count'),
                'count_asc'      => $q->orderBy('ops_count'),
                'progress_desc'  => $q->orderByRaw('(remaining_balance / NULLIF(total_amount, 0)) ASC'),
                default          => $q->orderByDesc('latest_op_at'), // newest
            };
        };

        $applyFilters = function ($q) use ($search, $categoryFilter, $timeFilter, $customDate, $rangeFrom, $rangeTo) {
            if (!empty($search))         $this->applyArabicSearch($q, ['creditor_name'], $search);
            if (!empty($categoryFilter)) $q->where('category', $categoryFilter);
            if ($timeFilter === 'today') {
                $q->whereDate('created_at', \Carbon\Carbon::today());
            } elseif ($timeFilter === 'yesterday') {
                $q->whereDate('created_at', \Carbon\Carbon::yesterday());
            } elseif ($timeFilter === 'month') {
                $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            } elseif ($timeFilter === 'year') {
                $q->whereYear('created_at', now()->year);
            } elseif ($timeFilter === 'custom' && !empty($customDate)) {
                $q->whereDate('created_at', $customDate);
            } elseif ($timeFilter === 'range') {
                if (!empty($rangeFrom)) $q->whereDate('created_at', '>=', $rangeFrom);
                if (!empty($rangeTo))   $q->whereDate('created_at', '<=', $rangeTo);
            }
            return $q;
        };

        // ─── Aggregation: لكل creditor — يستخدم idx_cd_creditor للـ GROUP BY ───
        $buildAggregatedQuery = function () use ($applyFilters) {
            $q = \Illuminate\Support\Facades\DB::table('company_debts');
            $applyFilters($q);
            return $q->selectRaw('
                    creditor_name,
                    MAX(category)              as category,
                    MAX(created_at)            as latest_op_at,
                    COUNT(*)                   as ops_count,
                    SUM(total_amount)          as total_amount,
                    SUM(paid_amount)           as paid_amount,
                    SUM(remaining_balance)     as remaining_balance
                ')
                ->groupBy('creditor_name');
        };

        // ─── النشطة (paginated) ───
        $activeQuery = $buildAggregatedQuery()->havingRaw('SUM(remaining_balance) > 0');
        $applySort($activeQuery);
        $activePaginated = $activeQuery->paginate($perPage, ['*'], 'active_page')->withQueryString();

        // ─── المسددة (paginated) ───
        $clearedQuery = $buildAggregatedQuery()->havingRaw('SUM(remaining_balance) <= 0');
        $applySort($clearedQuery);
        $clearedPaginated = $clearedQuery->paginate($perPage, ['*'], 'cleared_page')->withQueryString();

        // ─── نسخة كاملة (بدون تقسيم صفحات) لنفس الفلاتر — تُستخدم للطباعة فقط ───
        $printActiveQuery = $buildAggregatedQuery()->havingRaw('SUM(remaining_balance) > 0');
        $applySort($printActiveQuery);
        $printActiveData = $printActiveQuery->get(['creditor_name', 'ops_count', 'total_amount', 'paid_amount', 'remaining_balance']);

        $printClearedQuery = $buildAggregatedQuery()->havingRaw('SUM(remaining_balance) <= 0');
        $applySort($printClearedQuery);
        $printClearedData = $printClearedQuery->get(['creditor_name', 'ops_count', 'total_amount', 'paid_amount', 'remaining_balance']);

        // ─── تفاصيل الـ debts الخام — فقط للـ creditors الظاهرين في الصفحات الحالية ───
        // (Modal التفاصيل يحتاج كل العمليات للـ creditor)
        $visibleCreditors = collect()
            ->merge($activePaginated->pluck('creditor_name'))
            ->merge($clearedPaginated->pluck('creditor_name'))
            ->unique()
            ->values();

        $groupedCompanyDebts = collect();
        if ($visibleCreditors->isNotEmpty()) {
            // index على creditor_name يجعل الـ WHERE IN سريع
            $detailDebts = \Illuminate\Support\Facades\DB::table('company_debts')
                ->whereIn('creditor_name', $visibleCreditors->all())
                ->orderBy('created_at', 'desc')
                ->get();
            $groupedCompanyDebts = $detailDebts->groupBy('creditor_name');
        }

        // ─── إحصائيات عامة (نطلب رقم واحد فقط لكل إحصائية — استعلام O(1) بـ index) ───
        $totalDebtsQ = \Illuminate\Support\Facades\DB::table('company_debts');
        $applyFilters($totalDebtsQ);
        $total_debts_on_us = (float) $totalDebtsQ->where('remaining_balance', '>', 0)->sum('remaining_balance');

        $active_creditors_count = $activePaginated->total();
        $cleared_creditors_count = $clearedPaginated->total();

        $accounts = \Illuminate\Support\Facades\DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->get();

        // ─── الخصومات المكتسبة (تبويب التقرير) ───
        // إجمالي لكل جهة + إجمالي عام + آخر الحركات
        $earnedByCreditor = \Illuminate\Support\Facades\DB::table('financial_transactions')
            ->where('subtype', 'earned_discount')
            ->where('status', 'active')
            ->groupBy('person_name')
            ->selectRaw('person_name, COUNT(*) as ops_count, SUM(amount) as total_discount, MAX(created_at) as last_at')
            ->orderByDesc('total_discount')
            ->get();
        $earnedDiscountTotal = (float) $earnedByCreditor->sum('total_discount');
        $earnedDiscountRows = \Illuminate\Support\Facades\DB::table('financial_transactions')
            ->where('subtype', 'earned_discount')
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get(['id', 'person_name', 'amount', 'notes', 'created_at']);

        return view('debts2', compact(
            'accounts', 'search', 'categoryFilter', 'timeFilter', 'customDate', 'rangeFrom', 'rangeTo',
            'total_debts_on_us', 'active_creditors_count', 'cleared_creditors_count',
            'groupedCompanyDebts', 'activePaginated', 'clearedPaginated', 'sortKey',
            'earnedByCreditor', 'earnedDiscountTotal', 'earnedDiscountRows',
            'printActiveData', 'printClearedData'
        ));
    }
    public function storeCompanyDebt(Request $request)
    {
        DB::table('company_debts')->insert([
            'creditor_name'     => $request->creditor_name,
            'reason'            => $request->reason,
            'total_amount'      => $request->amount,
            'paid_amount'       => 0,
            'remaining_balance' => $request->amount,
            'created_at'        => now(),
        ]);

        // 🔔 تسجيل نشاط
        $this->logActivity('create', 'finance',
            "🏦 دين جديد على الشركة | دائن: {$request->creditor_name} | مبلغ: " . number_format($request->amount, 2) . " ج | سبب: {$request->reason}"
        );

        return back()->with('success', 'تم تسجيل الدين على الشركة بنجاح.');
    }

public function payCompanyDebtOnUs(Request $request)
    {
        $lockKey = 'pay_debt_' . md5(auth()->id() . $request->debt_id . $request->amount);
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return back()->with('error', '⚠️ العملية قيد التنفيذ بالفعل! الرجاء الانتظار.');
        }

        try {
            $debtName = '';
            DB::transaction(function () use ($request, &$debtName) {
                // قفل السجل برمجياً لمنع التداخل
                $debt = DB::table('company_debts')->where('id', $request->debt_id)->lockForUpdate()->first();
                if (!$debt) throw new \Exception('هذا الدين غير مسجل!');

                $amount   = floatval($request->amount);
                $debtName = $debt->creditor_name;

                if ($amount > $debt->remaining_balance) {
                    throw new \Exception('المبلغ المدخل أكبر من المتبقي في هذا الدين!');
                }

                // 💡 فحص رصيد الخزنة قبل السحب
                $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                if (!$account || $account->balance < $amount) {
                    throw new \Exception("عذراً، رصيد الخزنة المتاح (" . number_format($account->balance ?? 0, 2) . " ج) لا يكفي لسداد هذا المبلغ!");
                }

                // خصم الفلوس من الخزنة
                DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $amount);

                // تحديث الدين
                DB::table('company_debts')->where('id', $request->debt_id)->update([
                    'paid_amount'       => $debt->paid_amount + $amount,
                    'remaining_balance' => $debt->remaining_balance - $amount,
                    'updated_at'        => now(),
                ]);

                // تسجيل العملية المالية
                DB::table('financial_transactions')->insert([
                    'type'            => 'expense',
                    'amount'          => $amount,
                    'from_account_id' => $request->account_id,
                    'notes'           => 'سداد دين لـ: ' . $debt->creditor_name,
                    'ref_id'          => $request->debt_id,
                    'ref_type'        => 'company_debt_payment',
                    'status'          => 'active',
                    'created_at'      => now(),
                ]);

                if (method_exists($this, 'applyAccountCommission')) {
                    $this->applyAccountCommission($request->account_id, $amount, 'سداد دين مورد: ' . $debt->creditor_name, 'out');
                }
            });

            if (method_exists($this, 'logActivity')) {
                $this->logActivity('payment', 'finance', "💵 سداد دين | دائن: {$debtName} | مبلغ: " . number_format($request->amount, 2) . " ج");
            }
            
            return back()->with('success', 'تم تسجيل السداد بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage()); // الرسالة الحمراء هتظهر هنا
        }
    }
    public function deleteCompanyDebt(Request $request)
    {
        $debt = DB::table('company_debts')->where('id', $request->debt_id)->first();
        DB::table('company_debts')->where('id', $request->debt_id)->delete();

        // 🔔 تسجيل نشاط
        $this->logActivity('delete', 'finance',
            "🗑️ حذف دين شركة | دائن: " . ($debt->creditor_name ?? $request->debt_id)
        );

        return back()->with('success', 'تم حذف الدين نهائياً.');
    }

    // ══════════════════════════════════════════════════════
    // العمليات المالية
    // ══════════════════════════════════════════════════════
  public function financialOps(\Illuminate\Http\Request $request)
    {
        // 💡 الافتراضي "اليوم" لتجنب تحميل كل السجلات عند فتح الصفحة بدون فلتر؛ "الكل" بيتطلب اختيار صريح
        $quick      = $request->input('quick', $request->filled('custom_from') ? '' : 'today');
        $customFrom = $request->input('custom_from', '');
        $customTo   = $request->input('custom_to', $customFrom);

        $accounts = \Illuminate\Support\Facades\DB::table('accounts')->get();

        $query = \Illuminate\Support\Facades\DB::table('financial_transactions as ft')
            ->leftJoin('accounts as a_from', 'ft.from_account_id', '=', 'a_from.id')
            ->leftJoin('accounts as a_to',   'ft.to_account_id',   '=', 'a_to.id')
            ->select('ft.*', 'a_from.account_name as from_name', 'a_to.account_name as to_name');

        if ($quick === 'today') {
            $query->whereDate('ft.created_at', now()->toDateString());
        } elseif ($quick === 'yesterday') {
            $query->whereDate('ft.created_at', now()->subDay()->toDateString());
        } elseif ($quick === 'month') {
            $query->whereMonth('ft.created_at', now()->month)->whereYear('ft.created_at', now()->year);
        } elseif ($customFrom) {
            $query->whereDate('ft.created_at', '>=', $customFrom)->whereDate('ft.created_at', '<=', ($customTo ?: $customFrom));
        }

        // 💡 نسخة من الاستعلام قبل الترقيم الخاص بجدول العرض، عشان الإجماليات
        // تتحسب من كل الحركات المطابقة للفلتر مش بس صفحة العرض الحالية (كان ده سبب اختلاف
        // الأرقام هنا عن شاشة التقارير المتقدمة اللي بتحسب من غير أي limit).
        $statsQuery = clone $query;

        // 📄 ترقيم صفحات (30 صف) بدل تحميل الـ 300 حركة كلها مرة واحدة — أسرع في العرض والتصفّح
        $transactions = $query->orderBy('ft.id', 'desc')->paginate(30, ['*'], 'page')->withQueryString();

        // 🔖 تحديد الحركات اليدوية القابلة للإلغاء (إيداع/صرف يدوي فقط)
        $transactions->through(function ($tx) {
            $tx->can_cancel = $this->isManualCancellable($tx);
            return $tx;
        });

        $total_income    = (float) (clone $statsQuery)->where('ft.status', 'active')->whereIn('ft.type', ['settlement', 'income'])->sum('ft.amount');
        $total_expense   = (float) (clone $statsQuery)->where('ft.status', 'active')->whereIn('ft.type', ['expense', 'general_expense', 'salary_expense', 'discount'])->sum('ft.amount');
        $total_transfer  = (float) (clone $statsQuery)->where('ft.status', 'active')->where('ft.type', 'transfer')->sum('ft.amount');
        $cancelled_count = (clone $statsQuery)->where('ft.status', 'cancelled')->count();

        // 📡 السر هنا: جلب بيانات الرادار وإرسالها للشاشة
        $radar_logs = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            $radar_logs = \Illuminate\Support\Facades\DB::table('activity_logs')
                ->orderBy('created_at', 'desc')
                ->limit(200)
                ->get();
        }

        // ═══ سجل تعديلات رأس المال (فلتر مستقل) ═══
        $capQuick = $request->input('cap_quick', '');
        $capFrom  = $request->input('cap_from', '');
        $capTo    = $request->input('cap_to', $capFrom);

        $capQuery = \Illuminate\Support\Facades\DB::table('capital_adjustments as ca')
            ->leftJoin('accounts as a', 'ca.account_id', '=', 'a.id')
            ->select('ca.*', 'a.account_name as account_name');

        if ($capQuick === 'today') {
            $capQuery->whereDate('ca.created_at', now()->toDateString());
        } elseif ($capQuick === 'yesterday') {
            $capQuery->whereDate('ca.created_at', now()->subDay()->toDateString());
        } elseif ($capQuick === 'month') {
            $capQuery->whereMonth('ca.created_at', now()->month)->whereYear('ca.created_at', now()->year);
        } elseif ($capFrom) {
            $capQuery->whereDate('ca.created_at', '>=', $capFrom)->whereDate('ca.created_at', '<=', ($capTo ?: $capFrom));
        }

        $capital_adjustments = $capQuery->orderBy('ca.id', 'desc')->limit(300)->get();
        $cap_total_increase  = $capital_adjustments->where('status', 'active')->where('type', 'increase')->sum('amount');
        $cap_total_decrease  = $capital_adjustments->where('status', 'active')->where('type', 'decrease')->sum('amount');

        return view('financial_ops', compact(
            'accounts', 'transactions', 'quick', 'customFrom', 'customTo',
            'total_income', 'total_expense', 'total_transfer', 'cancelled_count', 'radar_logs',
            'capital_adjustments', 'cap_total_increase', 'cap_total_decrease',
            'capQuick', 'capFrom', 'capTo'
        ));
    }

    /**
     * تعديل رأس المال يدوياً (إضافة/صرف) — يؤثر على رصيد الخزنة (السيولة) فقط
     * ويُسجَّل في جدول capital_adjustments دون المرور على المصروفات/الديون/المستحقات.
     */
    public function storeCapitalAdjustment(Request $request)
    {
        $sessionUser = session('auth_user');
        if (!$sessionUser || ($sessionUser->role ?? '') !== 'admin') {
            return redirect()->route('financial.index', ['tab' => 'capital'])->with('error', 'غير مصرح — تعديل رأس المال متاح للأدمن فقط.');
        }

        $request->validate([
            'account_id' => 'required|integer|exists:accounts,id',
            'type'       => 'required|in:increase,decrease',
            'amount'     => 'required|numeric|min:0.01',
            'reason'     => 'required|string|max:500',
        ], [
            'account_id.required' => 'اختر الخزنة.',
            'type.required'       => 'اختر نوع العملية (إضافة أو صرف).',
            'amount.min'          => 'المبلغ يجب أن يكون أكبر من صفر.',
            'reason.required'     => 'اكتب سبب التعديل.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                if (!$account) throw new \Exception('الخزنة غير موجودة.');

                $amount = round((float) $request->amount, 2);
                $type   = $request->type;

                if ($type === 'decrease' && (float) $account->balance < $amount) {
                    throw new \Exception('رصيد الخزنة (' . number_format($account->balance, 2) . ' ج) لا يكفي للصرف.');
                }

                $newBalance = $type === 'increase'
                    ? (float) $account->balance + $amount
                    : (float) $account->balance - $amount;

                DB::table('accounts')->where('id', $account->id)->update([
                    'balance'    => $newBalance,
                    'updated_at' => now(),
                ]);

                $user = session('auth_user');
                DB::table('capital_adjustments')->insert([
                    'account_id'     => $account->id,
                    'type'           => $type,
                    'amount'         => $amount,
                    'reason'         => trim($request->reason),
                    'status'         => 'active',
                    'created_by'     => $user?->id,
                    'created_by_name'=> $user?->name ?? 'النظام',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                $verb = $type === 'increase' ? '➕ إضافة لرأس المال' : '➖ صرف من رأس المال';
                $this->logActivity('create', 'system',
                    "{$verb}: " . number_format($amount, 2) . " ج | الخزنة: {$account->account_name} | السبب: " . trim($request->reason)
                );
            });

            return redirect()->route('financial.index', ['tab' => 'capital'])->with('success', 'تم تسجيل تعديل رأس المال بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route('financial.index', ['tab' => 'capital'])->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء تعديل رأس المال (يعكس الأثر على رصيد الخزنة) — بكلمة مرور الأدمن.
     */
    public function cancelCapitalAdjustment(Request $request)
    {
        $sessionUser = session('auth_user');
        if (!$sessionUser || ($sessionUser->role ?? '') !== 'admin') {
            return response()->json(['error' => 'غير مصرح — لازم تكون أدمن.'], 403);
        }
        $password = (string) $request->input('admin_pin', '');
        if ($password === '') {
            return response()->json(['error' => 'أدخل كلمة مرور الأدمن.'], 403);
        }
        $dbUser = DB::table('users')->where('id', $sessionUser->id)->first();
        if (!$dbUser || !\Illuminate\Support\Facades\Hash::check($password, $dbUser->password)) {
            return response()->json(['error' => 'كلمة مرور الأدمن غير صحيحة.'], 403);
        }

        $id = (int) $request->input('adj_id');

        try {
            DB::transaction(function () use ($id) {
                $adj = DB::table('capital_adjustments')->where('id', $id)->lockForUpdate()->first();
                if (!$adj) throw new \Exception('العملية غير موجودة.');
                if ($adj->status !== 'active') throw new \Exception('العملية ملغاة بالفعل.');

                $account = DB::table('accounts')->where('id', $adj->account_id)->lockForUpdate()->first();
                if ($account) {
                    // عكس الأثر: لو كانت إضافة نخصم، ولو كانت صرف نضيف
                    $newBalance = $adj->type === 'increase'
                        ? (float) $account->balance - (float) $adj->amount
                        : (float) $account->balance + (float) $adj->amount;

                    DB::table('accounts')->where('id', $account->id)->update([
                        'balance'    => $newBalance,
                        'updated_at' => now(),
                    ]);
                }

                DB::table('capital_adjustments')->where('id', $id)->update([
                    'status'     => 'cancelled',
                    'updated_at' => now(),
                ]);

                $this->logActivity('cancel', 'system',
                    "🚫 إلغاء تعديل رأس المال #{$id} (" . ($adj->type === 'increase' ? 'إضافة' : 'صرف') . " " . number_format($adj->amount, 2) . " ج)"
                );
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * يحدد إن كانت الحركة المالية "يدوية" (إيداع/صرف اتعمل من شاشة العمليات المالية)
     * وبالتالي مسموح بإلغائها من هنا. الحركات المرتبطة بعمليات النظام (بيع/قسط/مورد/بنزينة)
     * لا يُسمح بإلغائها من هنا — تُلغى من شاشتها الأصلية أو سجل العمليات.
     */
    private function isManualCancellable($tx): bool
    {
        if (($tx->status ?? '') !== 'active') return false;
        if (!in_array($tx->type, ['income', 'settlement', 'expense', 'general_expense', 'salary_expense'])) return false;

        $refType = $tx->ref_type ?? null;

        // حركة بدون ربط: نتأكد إنها مش حركة نظامية عبر كلمات البيان
        if (is_null($refType) || $refType === '') {
            $systemKw = ['قسط', 'مخزن', 'بيع', 'مرتجع', 'إهلاك', 'دين', 'بنزين', 'عمول', 'مورد', 'سداد', 'وقود', 'محطة', 'استقطاع', 'تركيب', 'خصم'];
            foreach ($systemKw as $kw) {
                if (mb_strpos((string)($tx->notes ?? ''), $kw) !== false) return false;
            }
            return true;
        }

        // صرف عهدة يدوي → الـ installment المرتبط فئته 'عهد ومصروفات'
        if ($refType === 'installment') {
            $inst = DB::table('installments')->where('id', $tx->ref_id)->first();
            return $inst && ($inst->category === 'عهد ومصروفات');
        }

        // تسوية/إيداع يدوي بدائن → company_debt (بيتعمل فقط من storeFinancialOp)
        if ($refType === 'company_debt') {
            return true;
        }

        // سداد قسط أو دين على الشركة — قابل للإلغاء من سجل العمليات
        if ($refType === 'installment_payment' || $refType === 'company_debt_payment') {
            return true;
        }

        return false;
    }

    /**
     * إلغاء حركة مالية يدوية (إيداع/صرف) بباسورد الأدمن — يعكس الأثر على الخزنة.
     */
    public function cancelFinancialOp(Request $request)
    {
        // ✅ تحقق صلاحية الأدمن + كلمة المرور
        $sessionUser = session('auth_user');
        if (!$sessionUser || ($sessionUser->role ?? '') !== 'admin') {
            return response()->json(['error' => 'غير مصرح — لازم تكون أدمن.'], 403);
        }
        $password = (string) $request->input('admin_pin', '');
        if ($password === '') {
            return response()->json(['error' => 'أدخل كلمة مرور الأدمن.'], 403);
        }
        $dbUser = DB::table('users')->where('id', $sessionUser->id)->first();
        if (!$dbUser || !\Illuminate\Support\Facades\Hash::check($password, $dbUser->password)) {
            return response()->json(['error' => 'كلمة مرور الأدمن غير صحيحة.'], 403);
        }

        $id = (int) $request->input('tx_id');

        try {
            DB::transaction(function () use ($id) {
                $tx = DB::table('financial_transactions')->where('id', $id)->lockForUpdate()->first();
                if (!$tx) throw new \Exception('الحركة غير موجودة.');
                if ($tx->status !== 'active') throw new \Exception('الحركة ملغاة بالفعل.');

                if (!$this->isManualCancellable($tx)) {
                    throw new \Exception('العملية دي مرتبطة بعملية نظام (بيع/قسط/مورد/بنزينة) أو تحويل — الغِها من شاشتها الأصلية أو سجل العمليات.');
                }

                $refType = $tx->ref_type ?? null;

                // فحص السداد على الـ ref المرتبط قبل الإلغاء
                if ($refType === 'installment') {
                    $hasPayments = DB::table('installment_payments')->where('installment_id', $tx->ref_id)->exists();
                    if ($hasPayments) throw new \Exception('في سداد مسجّل على العهدة دي — الغِ السداد أولاً.');
                } elseif ($refType === 'company_debt') {
                    $debt = DB::table('company_debts')->where('id', $tx->ref_id)->first();
                    if ($debt && (float)$debt->paid_amount > 0) throw new \Exception('في سداد على الدين/التسوية دي — الغِ السداد أولاً.');
                }

                // 💰 عكس الأثر على الخزنة
                if (in_array($tx->type, ['income', 'settlement'])) {
                    // إيداع → اخصم من الخزنة المودع فيها
                    if ($tx->to_account_id) {
                        $acc = DB::table('accounts')->where('id', $tx->to_account_id)->lockForUpdate()->first();
                        if ($acc && $acc->balance < $tx->amount) {
                            throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من مبلغ الإيداع المطلوب عكسه. أودِع المبلغ أولاً.");
                        }
                        DB::table('accounts')->where('id', $tx->to_account_id)->decrement('balance', $tx->amount);
                    }
                } else {
                    // صرف → ارجّع المبلغ للخزنة المسحوب منها
                    if ($tx->from_account_id) {
                        DB::table('accounts')->where('id', $tx->from_account_id)->increment('balance', $tx->amount);
                    }
                }

                // عكس الـ ref المرتبط (عهدة / تسوية / سداد)
                if ($refType === 'installment') {
                    DB::table('installments')->where('id', $tx->ref_id)->delete();
                } elseif ($refType === 'company_debt') {
                    DB::table('company_debts')->where('id', $tx->ref_id)->delete();
                } elseif ($refType === 'installment_payment') {
                    $payment = DB::table('installment_payments')->where('id', $tx->ref_id)->lockForUpdate()->first();
                    if ($payment) {
                        \App\Services\InstallmentFinanceService::restoreInstallmentAfterPaymentReversal($payment->installment_id, $payment);
                        DB::table('installment_payments')->where('id', $tx->ref_id)->delete();
                    }
                } elseif ($refType === 'company_debt_payment') {
                    $debt = DB::table('company_debts')->where('id', $tx->ref_id)->lockForUpdate()->first();
                    if ($debt) {
                        // الخصم المكتسب المربوط بنفس سطر السداد يرجع كمان للدين
                        $discLines = DB::table('financial_transactions')
                            ->where('ref_type', 'company_debt_payment_discount')
                            ->where('ref_id', $id)
                            ->where('status', 'active')
                            ->get();
                        $discTotal = (float) $discLines->sum('amount');
                        $restore   = (float) $tx->amount + $discTotal;   // كاش + خصم = الدين كامل يرجع

                        DB::table('company_debts')->where('id', $tx->ref_id)->update([
                            'paid_amount'       => max(0, (float)$debt->paid_amount - $restore),
                            'remaining_balance' => (float)$debt->remaining_balance + $restore,
                            'updated_at'        => now(),
                        ]);

                        // علّم سطور الخصم المكتسب المربوطة ملغاة (تختفي من تقرير الخصومات المكتسبة)
                        if ($discLines->isNotEmpty()) {
                            DB::table('financial_transactions')
                                ->where('ref_type', 'company_debt_payment_discount')
                                ->where('ref_id', $id)
                                ->update([
                                    'status'        => 'cancelled',
                                    'cancelled_at'  => now(),
                                    'cancel_reason' => 'إلغاء السداد المرتبط',
                                    'updated_at'    => now(),
                                ]);
                        }
                    }
                }

                // علّم الحركة ملغاة (تفضل في السجل)
                DB::table('financial_transactions')->where('id', $id)->update([
                    'status'        => 'cancelled',
                    'cancelled_at'  => now(),
                    'cancel_reason' => 'إلغاء يدوي من شاشة العمليات المالية',
                    'updated_at'    => now(),
                ]);

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('cancel', 'finance',
                        "🚫 إلغاء حركة مالية #{$id} | " . ($tx->notes ?? '') . " | مبلغ: " . number_format($tx->amount, 0) . " ج"
                    );
                }
            });

            return response()->json(['success' => true, 'message' => '✅ تم إلغاء الحركة وعكس أثرها على رصيد الخزنة.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

public function storeFinancialOp(Request $request)
    {
        $type   = $request->op_type;
        $amount = floatval($request->amount);
        $notes  = $request->notes;

        $lockKey = 'fin_op_' . md5(auth()->id() . $type . $amount . $request->from_account_id . $request->to_account_id);
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return back()->with('error', '⚠️ العملية قيد التنفيذ بالفعل! الرجاء الانتظار.');
        }

        try {
            DB::transaction(function () use ($request, $type, $amount, $notes) {
                $existing = DB::table('financial_transactions')
                    ->where('type',            $type)
                    ->where('amount',          $amount)
                    ->where('from_account_id', $request->from_account_id)
                    ->where('to_account_id',   $request->to_account_id)
                    ->where('status',          'active')
                    ->where('created_at',      '>=', now()->subSeconds(10))
                    ->exists();

                if ($existing) throw new \Exception('⚠️ تم رصد محاولة تكرار! انتظر ثوانٍ.');

                if ($type === 'expense') {
                    // 🛡️ التحقق من الرصيد المتاح قبل السحب
                    $account = DB::table('accounts')->where('id', $request->from_account_id)->lockForUpdate()->first();
                    if (!$account || $account->balance < $amount) {
                        throw new \Exception('عفواً، الرصيد المتاح في الخزنة لا يكفي لإتمام عملية الصرف!');
                    }

                    DB::table('accounts')
                        ->where('id', $request->from_account_id)
                        ->decrement('balance', $amount);

                    $txId = DB::table('financial_transactions')->insertGetId([
                        'type'            => 'general_expense',
                        'amount'          => $amount,
                        'from_account_id' => $request->from_account_id,
                        'notes'           => $notes,
                        'person_name'     => $request->person_name ?? null,
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);

                    if (!empty($request->person_name)) {
                        $instId = DB::table('installments')->insertGetId([
                            'customer_name'        => $request->person_name,
                            'customer_phone'       => $request->person_phone ?? null,
                            'product_name'         => '💼 عهدة: ' . ($notes ?: 'صرف عهدة'),
                            'cash_price'           => $amount,
                            'down_payment'         => 0,
                            'remaining_after_down' => $amount,
                            'installment_months'   => 0,
                            'interest_rate'        => 0,
                            'total_after_interest' => $amount,
                            'monthly_installment'  => $amount,
                            'due_day'              => date('d'),
                            'remaining_balance'    => $amount,
                            'category'             => 'عهد ومصروفات',
                            // 🔒 لازم نحدد sale_type غير 'inventory' (الافتراضي في الداتا بيز)
                            // وإلا العهدة بتظهر في المخزن والمبيعات والتقارير كأنها منتج
                            'sale_type'            => 'financial',
                            'start_date'           => now()->toDateString(),
                            'created_at'           => now(),
                        ]);
                        DB::table('financial_transactions')
                            ->where('id', $txId)
                            ->update(['ref_id' => $instId, 'ref_type' => 'installment']);
                    }

                    // ✅ direction = 'out' (خروج) → عمولة تُطبّق
                    self::applyAccountCommission(
                        $request->from_account_id,
                        $amount,
                        'صرف عهد/مصروف: ' . $notes,
                        'out'    // ← خروج صريح
                    );
                } elseif ($type === 'settlement') {
                    // ── حالة تسوية/إيراد → ❌ لا عمولة مطلقاً ──
                    DB::table('accounts')
                        ->where('id', $request->to_account_id)
                        ->increment('balance', $amount);

                    $txId = DB::table('financial_transactions')->insertGetId([
                        'type'          => 'settlement',
                        'amount'        => $amount,
                        'to_account_id' => $request->to_account_id,
                        'notes'         => $notes,
                        'person_name'   => $request->creditor_name ?? null,
                        'status'        => 'active',
                        'created_at'    => now(),
                    ]);

                    if (!empty($request->creditor_name)) {
                        $debtId = DB::table('company_debts')->insertGetId([
                            'creditor_name'     => $request->creditor_name,
                            // 💡 عمود reason في company_debts هو NOT NULL من غير default،
                            // فلو المستخدم ما كتبش ملاحظة ($notes = null) لازم نحط سبب افتراضي
                            // وإلا الإيداع بيفشل على الهوست (strict mode) بخطأ 1048.
                            'reason'            => $notes ?: ('إيداع نقدي — دين مستحق للمودع: ' . $request->creditor_name),
                            'total_amount'      => $amount,
                            'paid_amount'       => 0,
                            'remaining_balance' => $amount,
                            'created_at'        => now(),
                        ]);
                        DB::table('financial_transactions')
                            ->where('id', $txId)
                            ->update(['ref_id' => $debtId, 'ref_type' => 'company_debt']);
                    }

                    // ❌ إيراد داخل → لا عمولة (لا تستدعي applyAccountCommission هنا إطلاقاً)
                } elseif ($type === 'transfer') {
                    // ── حالة تحويل داخلي → عمولة على الحساب المُحوّل منه فقط ──
                    if ($request->from_account_id == $request->to_account_id) {
                        throw new \Exception('لا يمكن التحويل لنفس الخزنة!');
                    }

                    // 🛡️ التحقق من الرصيد المتاح قبل التحويل
                    $account = DB::table('accounts')->where('id', $request->from_account_id)->lockForUpdate()->first();
                    if (!$account || $account->balance < $amount) {
                        throw new \Exception('عفواً، الرصيد المتاح في الخزنة لا يكفي لإتمام عملية التحويل!');
                    }

                    DB::table('accounts')
                        ->where('id', $request->from_account_id)
                        ->decrement('balance', $amount);

                    DB::table('accounts')
                        ->where('id', $request->to_account_id)
                        ->increment('balance', $amount);

                    DB::table('financial_transactions')->insert([
                        'type'            => 'transfer',
                        'amount'          => $amount,
                        'from_account_id' => $request->from_account_id,
                        'to_account_id'   => $request->to_account_id,
                        'notes'           => $notes,
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);

                    $toAccName = DB::table('accounts')
                        ->where('id', $request->to_account_id)
                        ->value('account_name') ?? '—';

                    // ✅ عمولة على الخروج من الحساب المُحوّل منه (direction='out')
                    // ❌ لا عمولة على الحساب المُحوّل إليه (direction='in' → 0 تلقائياً)
                    self::applyAccountCommission(
                        $request->from_account_id,
                        $amount,
                        'تحويل داخلي إلى: ' . $toAccName,
                        'out'    // ← خروج من هذا الحساب
                    );
                }

                // 🔔 تسجيل نشاط
                $typeLabels = ['expense' => '💸 مصروف/عهدة', 'settlement' => '💰 إيداع/تحصيل', 'transfer' => '🔁 تحويل داخلي'];
                $typeLabel  = $typeLabels[$type] ?? $type;
                $this->logActivity('create', 'finance',
                    "{$typeLabel} | مبلغ: " . number_format($amount, 2) . " ج | بيان: {$notes}"
                );
            });

            return back()->with('success', 'تم تنفيذ العملية وتحديث الأرصدة بنجاح.');
        } catch (\Exception $e) {
            // 🔄 إضافة withInput للحفاظ على الداتا في الفورم
            return back()->withInput()->with('error', $e->getMessage())->withInput();
        }
    }
    public function archive(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $transactions = DB::table('financial_transactions')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('archive', compact('transactions', 'dateFrom', 'dateTo'));
    }


    public function reversePayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. جلب بيانات الدفعة
                $payment = DB::table('installment_payments')->where('id', $request->payment_id)->first();
                if (!$payment) {
                    throw new \Exception('هذه الدفعة غير موجودة أو تم حذفها مسبقاً.');
                }

                // 💡 السر هنا: استنتاج اسم عمود الخزنة أوتوماتيكياً مهما كان مسماه في الداتابيز
                $accountId = $payment->account_id ?? $payment->method_id ?? $payment->payment_method_id ?? null;

                if (!$accountId) {
                    throw new \Exception('لم يتم العثور على الخزنة المرتبطة بهذه الدفعة لإرجاع المبلغ منها.');
                }

                // 2. جلب بيانات العقد
                $inst = DB::table('installments')->where('id', $payment->installment_id)->first();

                // 3. التحقق من وجود رصيد في الخزنة لرد المبلغ
                $acc = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->first();
                if (!$acc || $acc->balance < $payment->amount_paid) {
                    throw new \Exception("لا يوجد رصيد كافٍ في الخزنة لسحب المبلغ المردود ({$payment->amount_paid} ج).");
                }

                // 4. خصم الفلوس من الخزنة (عكس القيد)
                DB::table('accounts')->where('id', $accountId)->decrement('balance', $payment->amount_paid);

                // 5. إرجاع المديونية للعميل في العقد
                DB::table('installments')->where('id', $inst->id)->increment('remaining_balance', $payment->amount_paid);

                // 6. تسجيل حركة الخصم المالي في الخزنة
                DB::table('financial_transactions')->insert([
                    'type' => 'expense',
                    'amount' => $payment->amount_paid,
                    'from_account_id' => $accountId,
                    'notes' => "🔄 إلغاء دفعة خطأ ورجوع الدين للعميل: " . ($inst->customer_name ?? 'غير معروف'),
                    'status' => 'active',
                    'created_at' => now(),
                ]);

                // 7. حذف الدفعة نهائياً من سجل الدفعات
                DB::table('installment_payments')->where('id', $request->payment_id)->delete();
            });

            return back()->with('success', '✅ تم حذف الدفعة، وخصمها من الخزنة، وإرجاع المديونية للعميل بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }
    // ✅ دالة إلغاء وعكس سداد قسط (Reverse Payment)
    public function reverseInstallmentPayment(Request $request)
    {
        $request->validate(['payment_id' => 'required|exists:installment_payments,id']);

        try {
            DB::transaction(function () use ($request) {
                // 1. جلب الدفعة
                $payment = DB::table('installment_payments')->where('id', $request->payment_id)->first();
                $inst = DB::table('installments')->where('id', $payment->installment_id)->first();
                
                if (!$payment || !$inst) throw new \Exception("الدفعة أو العقد غير موجود.");

                // 2. التحقق من وجود رصيد في الخزنة لرد المبلغ
                $acc = DB::table('accounts')->where('id', $payment->payment_method_id)->first();
                if (!$acc || $acc->balance < $payment->amount_paid) {
                    throw new \Exception("لا يوجد رصيد كافٍ في الخزنة (سحب منها المبلغ المردود).");
                }

                // 3. خصم الفلوس من الخزنة (عكس القيد)
                DB::table('accounts')->where('id', $payment->payment_method_id)->decrement('balance', $payment->amount_paid);

                InstallmentFinanceService::restoreInstallmentAfterPaymentReversal($inst->id, $payment);

                if ($payment->amount_paid > 0 && $payment->payment_method_id) {
                    DB::table('financial_transactions')->insert([
                        'type' => 'expense',
                        'amount' => $payment->amount_paid,
                        'from_account_id' => $payment->payment_method_id,
                        'notes' => "🔄 إلغاء دفعة خطأ ورجوع الدين للعميل: {$inst->customer_name}",
                        'created_at' => now(),
                    ]);
                }

                DB::table('installment_payments')->where('id', $request->payment_id)->delete();
            });

            return back()->with('success', '✅ تم التراجع عن الدفعة بنجاح ورجوع المديونية للعميل.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════
    // بنود المصروفات (Expense Categories)
    // ══════════════════════════════════════════════════════

    public function storeExpenseCategory(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:expense_categories,name',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'اسم البند مطلوب.',
            'name.unique'   => 'هذا البند موجود بالفعل.',
        ]);

        DB::table('expense_categories')->insert([
            'name'        => trim($request->name),
            'description' => trim($request->description ?? ''),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'تم إضافة بند المصروف "' . $request->name . '" بنجاح.');
    }

    public function updateExpenseCategory(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:expense_categories,name,' . $id,
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'اسم البند مطلوب.',
            'name.unique'   => 'هذا الاسم موجود بالفعل في بند آخر.',
        ]);

        DB::table('expense_categories')->where('id', $id)->update([
            'name'        => trim($request->name),
            'description' => trim($request->description ?? ''),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'تم تعديل البند بنجاح.');
    }

    public function destroyExpenseCategory($id)
    {
        $cat = DB::table('expense_categories')->where('id', $id)->first();
        if (!$cat) return back()->withInput()->with('error', 'البند غير موجود.');

        DB::table('expense_categories')->where('id', $id)->delete();

        return back()->with('success', 'تم حذف بند "' . $cat->name . '" بنجاح.');
    }

    /**
     * سداد جزئي للجهة — يأخذ مبلغ ويوزعه على ديون الجهة من الأقدم للأحدث (FIFO).
     * يسجل FT لكل دين تأثر، يحدّث paid_amount/remaining_balance، ويخصم المبلغ من الخزنة دفعة واحدة.
     */
    public function payCompanyDebtPartial(Request $request)
    {
        $request->validate([
            'creditor_name'   => 'required|string',
            'account_id'      => 'required|integer',
            'amount'          => 'required|numeric|min:0',
            'earned_discount' => 'nullable|numeric|min:0',
        ]);

        $lockKey = 'pay_debt_partial_' . md5(auth()->id() . $request->creditor_name . $request->amount);
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return back()->with('error', '⚠️ العملية قيد التنفيذ بالفعل! الرجاء الانتظار.');
        }

        try {
            DB::transaction(function () use ($request) {
                $creditor = $request->creditor_name;
                $cash     = floatval($request->amount);                 // المدفوع نقداً
                $disc     = floatval($request->earned_discount ?? 0);   // الخصم المكتسب
                $total    = $cash + $disc;                              // إجمالي ما يُخصم من الدين

                if ($total <= 0) {
                    throw new \Exception('من فضلك أدخل مبلغ سداد أو خصم مكتسب (واحد منهم على الأقل).');
                }

                $debts = DB::table('company_debts')
                    ->where('creditor_name', $creditor)
                    ->where('remaining_balance', '>', 0)
                    ->orderBy('created_at', 'asc')   // FIFO: الأقدم أولاً
                    ->lockForUpdate()
                    ->get();

                if ($debts->isEmpty()) {
                    throw new \Exception('لا توجد ديون معلقة لهذه الجهة.');
                }

                $totalRemaining = $debts->sum('remaining_balance');
                if ($total > $totalRemaining + 0.01) {
                    throw new \Exception('المدفوع + الخصم (' . number_format($total, 2) . ' ج) أكبر من إجمالي المتبقي على الجهة (' . number_format($totalRemaining, 2) . ' ج).');
                }

                // فحص رصيد الخزنة على الكاش فقط (الخصم لا يخرج من الخزنة)
                if ($cash > 0) {
                    $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                    if (!$account || $account->balance < $cash) {
                        throw new \Exception('عذراً، رصيد الخزنة المتاح (' . number_format($account->balance ?? 0, 2) . ' ج) لا يكفي لسداد هذا المبلغ.');
                    }
                }

                // وزّع (الكاش + الخصم) على الديون من الأقدم للأحدث.
                // داخل كل دين: الكاش أولاً ثم الخصم — عشان سطور المصروفات تسجّل الكاش فقط.
                $remaining = $total;
                $cashLeft  = $cash;
                foreach ($debts as $debt) {
                    if ($remaining <= 0) break;
                    $deduct   = min($remaining, (float) $debt->remaining_balance);
                    $cashPart = min($cashLeft, $deduct);   // جزء الكاش من هذا الخصم
                    $discPart = $deduct - $cashPart;        // جزء الخصم المكتسب من هذا الدين

                    DB::table('company_debts')->where('id', $debt->id)->update([
                        'paid_amount'       => (float) $debt->paid_amount + $deduct,   // يشمل الكاش + الخصم (الدين اتسوّى بالكامل بهذا القدر)
                        'remaining_balance' => (float) $debt->remaining_balance - $deduct,
                        'updated_at'        => now(),
                    ]);

                    $expId = null;
                    if ($cashPart > 0) {
                        $expId = DB::table('financial_transactions')->insertGetId([
                            'type'            => 'expense',
                            'amount'          => $cashPart,
                            'from_account_id' => $request->account_id,
                            'notes'           => 'سداد جزئي — دين لـ: ' . $creditor,
                            'person_name'     => $creditor,
                            'ref_id'          => $debt->id,
                            'ref_type'        => 'company_debt_payment',
                            'status'          => 'active',
                            'created_at'      => now(),
                        ]);
                    }

                    // سجّل الخصم المكتسب لهذا الدين مربوطاً بسطر الكاش — عشان يرجع مع الدين عند حذف العملية
                    if ($discPart > 0) {
                        DB::table('financial_transactions')->insert([
                            'type'        => 'earned_discount',
                            'subtype'     => 'earned_discount',
                            'amount'      => $discPart,
                            'notes'       => 'خصم مكتسب من: ' . $creditor,
                            'person_name' => $creditor,
                            'ref_id'      => $expId ?? $debt->id,
                            'ref_type'    => $expId ? 'company_debt_payment_discount' : 'earned_discount',
                            'status'      => 'active',
                            'created_at'  => now(),
                        ]);
                    }

                    $cashLeft  -= $cashPart;
                    $remaining -= $deduct;
                }

                // خصم الكاش من الخزنة (الخصم لا يمسّ أي خزنة)
                if ($cash > 0) {
                    DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $cash);
                    self::applyAccountCommission($request->account_id, $cash, 'سداد جزئي دين مورد: ' . $creditor, 'out');
                }

                // ملاحظة: الخصم المكتسب اتسجّل داخل اللوب لكل دين على حدة (مربوط بسطر الكاش)
                // عشان لما تحذف العملية الدين يرجع كامل (كاش + خصم).

                $logMsg = '💵 سداد جزئي لدين | دائن: ' . $creditor . ' | مبلغ: ' . number_format($cash, 2) . ' ج';
                if ($disc > 0) $logMsg .= ' | خصم مكتسب: ' . number_format($disc, 2) . ' ج';
                $this->logActivity('payment', 'finance', $logMsg);
            });

            return back()->with('success', '✅ تم توزيع المبلغ على ديون الجهة بنجاح (من الأقدم للأحدث).');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function payCompanyDebtBulk(Request $request)
    {
        $request->validate([
            'creditor_name'   => 'required|string',
            'account_id'      => 'required|integer',
            'earned_discount' => 'nullable|numeric|min:0',
        ]);

        $lockKey = 'pay_debt_bulk_' . md5(auth()->id() . $request->creditor_name);
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return back()->with('error', '⚠️ العملية قيد التنفيذ بالفعل! الرجاء الانتظار.');
        }

        try {
            DB::transaction(function () use ($request) {
                $creditor = $request->creditor_name;
                $disc     = floatval($request->earned_discount ?? 0);   // الخصم المكتسب

                $debts = DB::table('company_debts')
                    ->where('creditor_name', $creditor)
                    ->where('remaining_balance', '>', 0)
                    ->orderBy('created_at', 'asc')   // FIFO عشان توزيع الكاش/الخصم
                    ->lockForUpdate()
                    ->get();

                if ($debts->isEmpty()) {
                    throw new \Exception('لا توجد ديون معلقة لهذه الجهة.');
                }

                $totalAmount = $debts->sum('remaining_balance');   // إجمالي المتبقي (هيتقفل بالكامل)

                if ($disc > $totalAmount + 0.01) {
                    throw new \Exception('الخصم المكتسب (' . number_format($disc, 2) . ' ج) أكبر من إجمالي المتبقي على الجهة (' . number_format($totalAmount, 2) . ' ج).');
                }

                $cash = $totalAmount - $disc;   // الكاش المطلوب فعلاً = المتبقي ناقص الخصم

                // فحص رصيد الخزنة على الكاش فقط
                if ($cash > 0) {
                    $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                    if (!$account || $account->balance < $cash) {
                        throw new \Exception('عذراً، رصيد الخزنة المتاح (' . number_format($account->balance ?? 0, 2) . ' ج) لا يكفي لسداد مديونية الجهة بعد الخصم (' . number_format($cash, 2) . ' ج).');
                    }
                }

                // اقفل كل الديون بالكامل، ووزّع الكاش عليها (الأقدم أولاً) لتسجيل سطور المصروفات بالكاش فقط
                $cashLeft = $cash;
                foreach ($debts as $debt) {
                    $rem      = floatval($debt->remaining_balance);
                    $cashPart = min($cashLeft, $rem);
                    $discPart = $rem - $cashPart;   // جزء الخصم المكتسب من هذا الدين

                    DB::table('company_debts')->where('id', $debt->id)->update([
                        'paid_amount'       => $debt->paid_amount + $rem,   // اتسوّى بالكامل (كاش + خصم)
                        'remaining_balance' => 0,
                        'updated_at'        => now(),
                    ]);

                    $expId = null;
                    if ($cashPart > 0) {
                        $expId = DB::table('financial_transactions')->insertGetId([
                            'type'            => 'expense',
                            'amount'          => $cashPart,
                            'from_account_id' => $request->account_id,
                            'notes'           => 'سداد مجمع — دين لـ: ' . $creditor,
                            'person_name'     => $creditor,
                            'ref_id'          => $debt->id,
                            'ref_type'        => 'company_debt_payment',
                            'status'          => 'active',
                            'created_at'      => now(),
                        ]);
                    }

                    // سجّل الخصم المكتسب لهذا الدين مربوطاً بسطر الكاش — عشان يرجع مع الدين عند حذف العملية
                    if ($discPart > 0) {
                        DB::table('financial_transactions')->insert([
                            'type'        => 'earned_discount',
                            'subtype'     => 'earned_discount',
                            'amount'      => $discPart,
                            'notes'       => 'خصم مكتسب من: ' . $creditor,
                            'person_name' => $creditor,
                            'ref_id'      => $expId ?? $debt->id,
                            'ref_type'    => $expId ? 'company_debt_payment_discount' : 'earned_discount',
                            'status'      => 'active',
                            'created_at'  => now(),
                        ]);
                    }
                    $cashLeft -= $cashPart;
                }

                // خصم الكاش من الخزنة + العمولة على الكاش فقط
                if ($cash > 0) {
                    DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $cash);
                    self::applyAccountCommission($request->account_id, $cash, 'سداد مجمع دين مورد: ' . $creditor, 'out');
                }

                // ملاحظة: الخصم المكتسب اتسجّل داخل اللوب لكل دين على حدة (مربوط بسطر الكاش)
                // عشان لما تحذف العملية الدين يرجع كامل (كاش + خصم).

                $logMsg = '💵 سداد مجمع لدين | دائن: ' . $creditor . ' | مبلغ: ' . number_format($cash, 2) . ' ج';
                if ($disc > 0) $logMsg .= ' | خصم مكتسب: ' . number_format($disc, 2) . ' ج';
                $this->logActivity('payment', 'finance', $logMsg);
            });

            return back()->with('success', 'تم سداد جميع ديون الجهة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}