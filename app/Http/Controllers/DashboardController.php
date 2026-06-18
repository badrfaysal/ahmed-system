<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InstallmentFinanceService;

class DashboardController extends SystemController
{
    public function index(Request $request)
    {
        $user = session('auth_user');

        // ════════ KPIs مالية أساسية ════════
        // رأس المال يأتي مكتملاً من InstallmentFinanceService (يشمل مستحقات البنزينة ضمن "ديون بالسوق")
        $summary = InstallmentFinanceService::treasurySummary();

        // ════════ فلتر الفترة الزمنية ════════
        $filter   = $request->input('filter', 'today');
        $fromDate = $request->input('from_date', '');
        $toDate   = $request->input('to_date', '');

        [$startDate, $endDate, $filterLabel] = $this->resolveDateRange($filter, $fromDate, $toDate);

        // ════════ مبيعات الفترة ════════
        $periodSalesCount = DB::table('installments')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where(function($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
            ->count();

        $periodSalesValue = (float) DB::table('installments')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where(function($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
            ->sum('total_after_interest');

        // ════════ صافي الربح للفترة (نفس منطق صفحة الماليات) ════════
        $periodProfit = $this->computeNetProfit($startDate, $endDate);

        // ════════ مصاريف التكييفات الإضافية (نقل/تركيب/خامات) — مجمّعة من المخزن + التقسيط ════════
        $invExtras = DB::table('installment_expenses as ie')
            ->join('installments as i', 'i.id', '=', 'ie.installment_id')
            ->where('i.sale_type', 'inventory')
            ->whereBetween('i.created_at', [$startDate, $endDate])
            ->selectRaw('COALESCE(SUM(ie.transport_cost),0) as t,
                         COALESCE(SUM(ie.installation_cost),0) as i,
                         COALESCE(SUM(ie.materials_cost),0) as m')
            ->first();
        $instExtras = DB::table('batch_expenses')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('COALESCE(SUM(transport_cost),0) as t,
                         COALESCE(SUM(installation_cost),0) as i,
                         COALESCE(SUM(materials_cost),0) as m')
            ->first();

        $acExtras = (object)[
            'transport'    => (float) $invExtras->t + (float) $instExtras->t,
            'installation' => (float) $invExtras->i + (float) $instExtras->i,
            'materials'    => (float) $invExtras->m + (float) $instExtras->m,
        ];
        $acExtras->total = $acExtras->transport + $acExtras->installation + $acExtras->materials;

        // ════════ أقساط مستحقة اليوم — استبعاد اللي دفع قسط الشهر ده ════════
        $todayDay = (int) now()->day;
        $dueToday = DB::table('installments as i')
            ->where('i.installment_months', '>', 0)
            ->where('i.remaining_balance', '>', 0)
            ->where('i.due_day', $todayDay)
            ->where('i.status', '!=', 'cancelled')
            ->whereRaw('NOT EXISTS (
                SELECT 1 FROM installment_payments p
                WHERE p.installment_id = i.id
                  AND p.payment_date >= DATE_SUB(CURDATE(), INTERVAL 28 DAY)
            )')
            ->count();

        // ════════ منطق متأخر ════════
        $overdueInstallments = DB::table('installments as i')
            ->where('i.installment_months', '>', 0)
            ->where('i.remaining_balance', '>', 0)
            ->where('i.status', '!=', 'cancelled')
            ->leftJoin('installment_payments as ip', 'ip.installment_id', '=', 'i.id')
            ->select('i.id', 'i.customer_name', 'i.product_name', 'i.remaining_balance',
                     'i.monthly_installment', 'i.due_day', 'i.created_at as contract_date',
                     DB::raw('MAX(ip.payment_date) as last_payment'))
            ->groupBy('i.id', 'i.customer_name', 'i.product_name', 'i.remaining_balance',
                     'i.monthly_installment', 'i.due_day', 'i.created_at')
            ->get()
            ->filter(function($r) {
                $reference = $r->last_payment ?: $r->contract_date;
                if (!$reference) return false;
                try {
                    return \Carbon\Carbon::parse($reference)->diffInDays(now()) > 35;
                } catch (\Throwable $e) {
                    return false;
                }
            })
            ->values();

        $overdueCount = $overdueInstallments->count();
        $overdueTotal = (float) $overdueInstallments->sum('remaining_balance');

        // ════════ الاستفسارات المعلقة ════════
        $pendingInquiries = DB::table('customer_inquiries')->where('is_contacted', 0)->count();
        $pendingInquiriesList = DB::table('customer_inquiries')
            ->where('is_contacted', 0)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ════════ مخزون قارب على النفاد ════════
        $lowStock = DB::table('sales')
            ->where('inventory_status', 'to_inventory')
            ->where('remaining_quantity', '>', 0)
            ->where('remaining_quantity', '<=', 3)
            ->orderBy('remaining_quantity')
            ->limit(10)
            ->get();

        // ════════ آخر العمليات المالية ════════
        $recentTransactions = DB::table('financial_transactions')
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get();

        // ════════ Chart: مبيعات وأرباح آخر 14 يوم ════════
        $salesChart   = [];
        $profitsChart = [];
        for ($i = 13; $i >= 0; $i--) {
            $day      = now()->subDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd   = $day->copy()->endOfDay();

            $salesValue = (float) DB::table('installments')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->where(function($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
                ->sum('total_after_interest');

            $salesChart[] = [
                'label' => $day->format('m/d'),
                'value' => round($salesValue, 2),
            ];

            $profitsChart[] = [
                'label' => $day->format('m/d'),
                'value' => round($this->computeNetProfit($dayStart, $dayEnd), 2),
            ];
        }

        // ════════ أقساط قريبة من الاستحقاق ════════
        $upcomingDue = DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('remaining_balance', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('due_day', [$todayDay, min(28, $todayDay + 7)])
            ->orderBy('due_day')
            ->select('id', 'customer_name', 'product_name', 'monthly_installment', 'due_day')
            ->limit(8)
            ->get();

        // ════════ أعلى موظف أداءً اليوم ════════
        $topUserToday = DB::table('financial_transactions')
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereNotNull('person_name')
            ->select('person_name', DB::raw('SUM(amount) as total'))
            ->groupBy('person_name')
            ->orderByDesc('total')
            ->first();

        return view('dashboard', compact(
            'user', 'summary',
            'periodSalesCount', 'periodSalesValue', 'periodProfit', 'filterLabel',
            'filter', 'fromDate', 'toDate',
            'dueToday', 'overdueCount', 'overdueTotal', 'overdueInstallments',
            'pendingInquiries', 'pendingInquiriesList',
            'lowStock', 'recentTransactions',
            'salesChart', 'profitsChart',
            'upcomingDue', 'topUserToday', 'acExtras'
        ));
    }

    // ══════════════════════════════════════════════════════
    // تحديد الفترة الزمنية حسب الفلتر
    // ══════════════════════════════════════════════════════
    private function resolveDateRange(string $filter, string $fromDate, string $toDate): array
    {
        switch ($filter) {
            case 'yesterday':
                return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), 'أمس'];
            case 'week':
                return [now()->startOfWeek(\Carbon\Carbon::SATURDAY), now()->endOfWeek(\Carbon\Carbon::FRIDAY), 'هذا الأسبوع'];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth(), 'هذا الشهر'];
            case 'custom':
                if ($fromDate && $toDate) {
                    $s = \Carbon\Carbon::parse($fromDate)->startOfDay();
                    $e = \Carbon\Carbon::parse($toDate)->endOfDay();
                    $label = $fromDate === $toDate ? 'يوم ' . $fromDate : "من {$fromDate} إلى {$toDate}";
                    return [$s, $e, $label];
                }
                return [now()->startOfDay(), now()->endOfDay(), 'اليوم'];
            case 'today':
            default:
                return [now()->startOfDay(), now()->endOfDay(), 'اليوم'];
        }
    }

    // ══════════════════════════════════════════════════════
    // حساب صافي الربح لفترة (مطابق لمنطق صفحة الماليات)
    // ══════════════════════════════════════════════════════
    private function computeNetProfit($startDate, $endDate): float
    {
        $apply = function($query) use ($startDate, $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        };

        // ═══ الإيرادات ═══
        // 🔒 كل استعلامات الـ installments بتستبعد البنزينة (category + sale_type) لمنع الخلط
        $excludeFuel = function($q) {
            $q->where(function($w) {
                $w->where('category', '!=', 'بنزينة')->orWhereNull('category');
            })->where(function($w) {
                $w->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
            });
        };

        $profitInstallments = (float) $apply(DB::table('installments'))
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where($excludeFuel)
            ->selectRaw('COALESCE(SUM(total_after_interest - cash_price), 0) as total')
            ->value('total');

        // أرباح المخزن: كاش + فرق سعر منتجات المخزن المباعة بالتقسيط
        $inventoryCashProfit = (float) $apply(DB::table('installments'))
            ->where('installment_months', 0)
            ->where(function ($q) { $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory'); })
            ->where($excludeFuel)
            ->where('status', '!=', 'cancelled')
            ->sum('profit');

        $inventoryInstallmentProductProfit = (float) $apply(DB::table('installments'))
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where($excludeFuel)
            ->where(function ($q) { $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory'); })
            ->selectRaw('COALESCE(SUM(cash_price - purchase_cost), 0) as total')
            ->value('total');

        $profitInventory = $inventoryCashProfit + $inventoryInstallmentProductProfit;

        // أرباح المبيعات المباشرة: كاش + فرق سعر منتجات البيع المباشر المباعة بالتقسيط (مش من المخزن)
        $directCashProfit = (float) $apply(DB::table('installments'))
            ->where('installment_months', 0)
            ->where('category', 'مبيعات مباشرة')
            ->where('status', '!=', 'cancelled')
            ->sum('profit');

        $directInstallmentProductProfit = (float) $apply(DB::table('installments'))
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where($excludeFuel)
            ->where(function ($q) { $q->where('sale_type', '!=', 'inventory')->orWhereNull('sale_type'); })
            ->where(function ($q) { $q->where('category', '!=', 'مبيعات مخزن')->orWhereNull('category'); })
            ->selectRaw('COALESCE(SUM(cash_price - purchase_cost), 0) as total')
            ->value('total');

        $profitDirectProducts = $directCashProfit + $directInstallmentProductProfit;

        $profitServices = (float) $apply(DB::table('installments'))
            ->where('category', 'خدمات')
            ->where('status', '!=', 'cancelled')
            ->sum('profit');

        $profitGas = (float) $apply(DB::table('fuel_transactions'))->sum('ahmed_profit');

        // ⚡ استعلام واحد بـ GROUP BY بدل 10 استعلامات منفصلة بـ LIKE.
        $fb = \App\Services\InstallmentFinanceService::financialBreakdown($startDate, $endDate);

        $totalGrossRevenue = $profitInstallments + $profitInventory + $profitDirectProducts
                           + $profitServices + $profitGas + $fb['profitAssetSales'];

        $totalDeductions = $fb['expensesGeneral'] + $fb['expensesSalaries'] + $fb['totalCommissions']
                         + $fb['lossesDepreciation'] + $fb['lossesReturns'] + $fb['lossesDiscounts']
                         + $fb['lossesBadDebts'] + $fb['lossesAssetSales'] + $fb['lossesInventoryShortage'];

        return $totalGrossRevenue - $totalDeductions;
    }
}
