<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * حسابات موحّدة للأقساط والخزنة والتقارير — مصدر واحد للحقيقة.
 */
class InstallmentFinanceService
{
    public static function totalContractValue(object $inst): float
    {
        return max(0, (float) ($inst->total_after_interest ?? 0));
    }

    public static function financedPrincipal(object $inst): float
    {
        return max(0, self::totalContractValue($inst) - (float) ($inst->down_payment ?? 0));
    }

    public static function paymentsSum(object $inst, $payments = null): float
    {
        if ($payments !== null) {
            return (float) collect($payments)->sum('amount_paid');
        }
        return (float) DB::table('installment_payments')
            ->where('installment_id', $inst->id)
            ->sum('amount_paid');
    }

    public static function totalPaidByCustomer(object $inst, $payments = null): float
    {
        return (float) ($inst->down_payment ?? 0) + self::paymentsSum($inst, $payments);
    }

    public static function paymentProgressPercent(object $inst, $payments = null): int
    {
        $total = self::totalContractValue($inst);
        if ($total <= 0) {
            return 100;
        }
        return (int) min(100, round((self::totalPaidByCustomer($inst, $payments) / $total) * 100));
    }

    public static function contractProfit(object $inst): float
    {
        return max(0, (float) ($inst->profit ?? 0));
    }

    public static function uncollectedProfitShare(object $inst): float
    {
        $remaining = (float) ($inst->remaining_balance ?? 0);
        if ($remaining <= 0) {
            return 0;
        }
        $profit = self::contractProfit($inst);
        if ($profit <= 0) {
            return 0;
        }
        $financed = self::financedPrincipal($inst);
        if ($financed <= 0) {
            return 0;
        }
        return $profit * ($remaining / $financed);
    }

    public static function isWrittenOff(object $inst): bool
    {
        return ($inst->close_reason ?? '') === 'written_off';
    }

    public static function isPaidInFull(object $inst): bool
    {
        if ((float) ($inst->remaining_balance ?? 0) > 0) {
            return false;
        }
        return ! self::isWrittenOff($inst);
    }

    public static function isClosed(object $inst): bool
    {
        return (float) ($inst->remaining_balance ?? 0) <= 0;
    }

    public static function totalCollectedAmount($installmentIds = null): float
    {
        $downQuery = DB::table('installments');
        $payQuery  = DB::table('installment_payments');

        if ($installmentIds !== null) {
            $ids = collect($installmentIds)->filter()->values()->all();
            if (empty($ids)) {
                return 0;
            }
            $downQuery->whereIn('id', $ids);
            $payQuery->whereIn('installment_id', $ids);
        }

        return (float) $downQuery->sum('down_payment') + (float) $payQuery->sum('amount_paid');
    }

  public static function treasurySummary(): array
    {
        // 1. حساب السيولة النقدية الحالية في جميع الخزائن والمحافظ النشطة
        $liquidity = (float) \Illuminate\Support\Facades\DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->sum('balance');

        // 2. حساب قيمة قسم المشاريع
        $projectsValue = (float) \Illuminate\Support\Facades\DB::table('accounts')
            ->where('category', 'project_sector')
            ->sum('balance');

        // 3. حساب قيمة بضاعة المخزن الحالية (بناءً على سعر الشراء المتبقي)
        // ⚡ تجميع داخل DB بدلاً من تحميل كل الـ rows للذاكرة
        $inventoryAssets = (float) \Illuminate\Support\Facades\DB::table('sales')
            ->where('inventory_status', 'to_inventory')
            ->where('remaining_quantity', '>', 0)
            ->selectRaw('COALESCE(SUM(remaining_quantity * purchase_price), 0) as total')
            ->value('total');

        // 4. حساب الأصول الثابتة
        $fixedAssets = (float) \Illuminate\Support\Facades\DB::table('assets')
            ->where('status', 'active')
            ->sum('current_value');

        // 5. حساب مديونيات منظومة الأقساط (الأقساط المتبقية بالخارج التي لم تُحصل بعد)
        $installmentsSystemDebts = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        // 6. مستحقات لنا بالسوق (مبيعات مباشرة وآجلة بدون البنزينة)
        $otherDebtsForUs = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', 0)
            ->where('remaining_balance', '>', 0)
            ->where(function ($q) {
                $q->where('category', '!=', 'بنزينة')->orWhereNull('category');
            })
            ->sum('remaining_balance');

        $totalDebtsForUs = $installmentsSystemDebts + $otherDebtsForUs;

        // 7. الديون والالتزامات على الشركة للموردين
        $totalDebtsOnUs = (float) \Illuminate\Support\Facades\DB::table('company_debts')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        // 8. مستحقات منظومة اقساط المقاولات
        $contractingInstallmentsDebts = (float) \Illuminate\Support\Facades\DB::table('sy2_installment_contracts')
            ->where('remaining_balance', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->sum('remaining_balance');

        // ====================================================
        // 📈 حساب الإيرادات والأرباح التفصيلية المربوطة بحالة العقد
        // ====================================================

        // 1. أرباح التقسيط (الفوائد فقط من العقود النشطة وغير المفسوخة)
        // 🔒 استبعاد البنزينة بـ category AND sale_type (دفاع مزدوج)
        $profitInstallments = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('category', '!=', 'بنزينة')->orWhereNull('category');
            })
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
            })
            ->selectRaw('COALESCE(SUM(total_after_interest - cash_price), 0) as total')
            ->value('total');

        // 2. أرباح المبيعات المباشرة (البيع المباشر كاش + فرق سعر منتجات البيع المباشر المباعة بالتقسيط)
        $directCashProfit = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', 0)
            ->where('category', 'مبيعات مباشرة')
            ->where('status', '!=', 'cancelled')
            ->sum('profit');

        // فرق سعر منتجات البيع المباشر المباعة بالتقسيط (مش من المخزن)
        $directInstallmentProductProfit = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('category', '!=', 'بنزينة')->orWhereNull('category');
            })
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
            })
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'inventory')->orWhereNull('sale_type');
            })
            ->where(function ($q) {
                $q->where('category', '!=', 'مبيعات مخزن')->orWhereNull('category');
            })
            ->selectRaw('COALESCE(SUM(cash_price - purchase_cost), 0) as total')
            ->value('total');

        $profitDirectProducts = $directCashProfit + $directInstallmentProductProfit;

        // 3. أرباح المخزن النظيفة — يجب أن لا تشمل البنزينة
        // (أ) أرباح البيع الكاش من المخزن
        $inventoryCashProfit = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', 0)
            ->where(function ($q) {
                $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory');
            })
            ->where('category', '!=', 'بنزينة')
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
            })
            ->where('status', '!=', 'cancelled')
            ->sum('profit');

        // (ب) فرق سعر منتجات المخزن المباعة بالتقسيط
        $inventoryInstallmentProductProfit = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory');
            })
            ->where('category', '!=', 'بنزينة')
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
            })
            ->selectRaw('COALESCE(SUM(cash_price - purchase_cost), 0) as total')
            ->value('total');

        $profitInventory = $inventoryCashProfit + $inventoryInstallmentProductProfit;

        // 4. أرباح قطاع الخدمات (من جدول installments بـ category='خدمات')
        $profitServices = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('category', 'خدمات')
            ->where('status', '!=', 'cancelled')
            ->sum('profit');

        // 5. أرباح البنزينة وبيع الأصول والإيرادات الأخرى
        $profitGas = (float) \Illuminate\Support\Facades\DB::table('fuel_transactions')->whereNull('superseded_by')->sum('ahmed_profit');

        // ⚡ استعلام واحد بـ GROUP BY بدل 10 استعلامات منفصلة بـ LIKE.
        $fb = self::financialBreakdown();
        $profitAssetSales        = $fb['profitAssetSales'];
        $additionalIncomes       = $fb['additionalIncomes'];
        $totalCommissions        = $fb['totalCommissions'];
        $lossesDepreciation      = $fb['lossesDepreciation'];
        $lossesReturns           = $fb['lossesReturns'];
        $lossesBadDebts          = $fb['lossesBadDebts'];
        $lossesAssetSales        = $fb['lossesAssetSales'];
        $lossesInventoryShortage = $fb['lossesInventoryShortage'];
        $lossesDiscounts         = $fb['lossesDiscounts'];
        $expensesSalaries        = $fb['expensesSalaries'];
        $expensesGeneral         = $fb['expensesGeneral'];

        $totalGrossRevenue = $profitInstallments + $profitInventory + $profitDirectProducts
                             + $profitServices + $profitGas + $profitAssetSales;

        $totalDeductions = $expensesGeneral + $expensesSalaries + $totalCommissions + $lossesDepreciation 
                          + $lossesReturns + $lossesDiscounts + $lossesBadDebts + $lossesAssetSales + $lossesInventoryShortage;

        // ====================================================
        // 💼 صافي الأرباح الرأسمالية والتوزيعات
        // ====================================================
        $netBookProfit = $totalGrossRevenue - $totalDeductions;

        // ⚡ تجميع داخل DB بدلاً من تحميل آلاف الـ installments للذاكرة
        $uncollectedProfit = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('installment_months', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->where('remaining_balance', '>', 0)
            ->where('total_after_interest', '>', 0)
            ->selectRaw('COALESCE(SUM(remaining_balance * profit / total_after_interest), 0) as total')
            ->value('total');

        $realCollectedProfit = $netBookProfit - $uncollectedProfit;

        $totalDistributedProfits = (float) \Illuminate\Support\Facades\DB::table('financial_transactions')
            ->where('type', 'withdraw_partner')
            ->where('status', 'active')
            ->sum('amount');

        $remainingCompanyProfit = $realCollectedProfit - $totalDistributedProfits;

        // 💡 مستحقات البنزينة (ما تدين به شركات النقل لنا من عمليات الوقود) — تدخل ضمن "ديون بالسوق".
        // المعادلة بتطرح ديون البنزينة على الشركة (محطات + استقطاعات) ضمن totalDebtsOnUs، فلازم
        // نضيف الطرف المقابل (المستحقات) عشان ربح العملية يظهر في رأس المال فوراً بدل ما يستنى
        // تحصيل المستحقات وتسديد كل الديون.
        $gasReceivables = (float) \Illuminate\Support\Facades\DB::table('installments')
            ->where('category', 'بنزينة')
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        // حساب رأس المال الفعلي للشركة
        $capital = $liquidity + $projectsValue + $inventoryAssets + $fixedAssets + $totalDebtsForUs + $gasReceivables - $totalDebtsOnUs + $totalCommissions;

        return compact(
            'liquidity', 'projectsValue', 'inventoryAssets', 'fixedAssets', 'installmentsSystemDebts',
            'otherDebtsForUs', 'totalDebtsForUs', 'gasReceivables', 'totalDebtsOnUs', 'capital', 'profitInstallments',
            'profitInventory', 'profitDirectProducts', 'profitServices', 'profitGas', 'profitAssetSales',
            'additionalIncomes', 'totalGrossRevenue', 'expensesGeneral', 'totalCommissions', 'lossesDepreciation',
            'lossesBadDebts', 'lossesReturns', 'lossesDiscounts', 'lossesAssetSales', 'expensesSalaries', 
            'lossesInventoryShortage', 'totalDeductions', 'contractingInstallmentsDebts',
            'netBookProfit', 'uncollectedProfit', 'realCollectedProfit', 'totalDistributedProfits', 'remainingCompanyProfit'
        );
    }
    /**
     * ⚡ تجميع كل بنود الأرباح/المصروفات بـ subtype في query واحدة (GROUP BY).
     * يستبدل ~10 استعلامات منفصلة كانت بتعمل full-scan بسبب LIKE '%...%'.
     *
     * @param  \Carbon\Carbon|null $from  فلتر تاريخ من (شامل)
     * @param  \Carbon\Carbon|null $to    فلتر تاريخ إلى (شامل)
     * @return array{
     *   profitAssetSales:float, additionalIncomes:float, totalCommissions:float,
     *   lossesDepreciation:float, lossesReturns:float, lossesBadDebts:float,
     *   lossesAssetSales:float, lossesInventoryShortage:float,
     *   lossesDiscounts:float, expensesSalaries:float, expensesGeneral:float
     * }
     */
    public static function financialBreakdown($from = null, $to = null): array
    {
        $q = DB::table('financial_transactions')->where('status', 'active');
        if ($from && $to) {
            $q->whereBetween('created_at', [$from, $to]);
        }

        $rows = $q->selectRaw('type, COALESCE(subtype, \'\') as st, person_name, SUM(amount) as total')
                  ->groupBy('type', 'st', 'person_name')
                  ->get();

        $bySubtype = ['income' => [], 'general_expense' => [], 'salary_expense' => [], 'discount' => []];
        $generalTotal = 0.0;
        $generalClassified = 0.0;
        foreach ($rows as $r) {
            $t  = $r->type;
            $st = $r->st ?: 'plain';
            $bySubtype[$t][$st] = ($bySubtype[$t][$st] ?? 0) + (float) $r->total;
            if ($t === 'general_expense' && $r->person_name === null) {
                $generalTotal += (float) $r->total;
                if ($st !== 'plain') {
                    $generalClassified += (float) $r->total;
                }
            }
        }
        $g = fn(string $t, string $st) => (float) ($bySubtype[$t][$st] ?? 0);

        return [
            'profitAssetSales'        => $g('income', 'asset_sale_gain'),
            'additionalIncomes'       => $g('income', 'return_income'),
            'totalCommissions'        => $g('general_expense', 'commission_auto'),
            'lossesDepreciation'      => $g('general_expense', 'depreciation'),
            'lossesReturns'           => $g('general_expense', 'return_loss'),
            'lossesBadDebts'          => $g('general_expense', 'bad_debt'),
            'lossesAssetSales'        => $g('general_expense', 'asset_sale_loss'),
            'lossesInventoryShortage' => $g('general_expense', 'inventory_loss'),
            'lossesDiscounts'         => array_sum($bySubtype['discount'] ?? []),
            'expensesSalaries'        => array_sum($bySubtype['salary_expense'] ?? []),
            'expensesGeneral'         => max(0, $generalTotal - $generalClassified),
        ];
    }

    public static function paymentReversalAmount(object $payment): float
    {
        $cash = (float) ($payment->amount_paid ?? 0);
        $disc = 0;
        if (Schema::hasColumn('installment_payments', 'discount_applied')) {
            $disc = (float) ($payment->discount_applied ?? 0);
        }
        return $cash + $disc;
    }

    public static function restoreInstallmentAfterPaymentReversal(int $installmentId, object $payment): void
    {
        $restore = self::paymentReversalAmount($payment);
        if ($restore <= 0) {
            return;
        }
        DB::table('installments')->where('id', $installmentId)->increment('remaining_balance', $restore);
        $disc = Schema::hasColumn('installment_payments', 'discount_applied')
            ? (float) ($payment->discount_applied ?? 0) : 0;
        if ($disc > 0) {
            DB::table('installments')->where('id', $installmentId)->increment('profit', $disc);
        }
        if (Schema::hasColumn('installments', 'close_reason')) {
            DB::table('installments')->where('id', $installmentId)->update(['close_reason' => null]);
        }
    }
}