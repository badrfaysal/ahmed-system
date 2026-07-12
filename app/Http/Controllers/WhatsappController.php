<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WhatsappController extends SystemController
{
    public function whatsappCenter(Request $request)
    {
        $filter   = $request->input('filter', 'all');
        $dueDay   = $request->input('due_day');
        $search   = trim((string) $request->input('search', ''));
        $sortBy   = $request->input('sort_by', 'balance_desc');

        $today = now()->startOfDay();

        // ═══════════════════════════════════════════════════════════════
        // (1) استخراج العملاء من installments — مع استبعاد:
        //     - البنزينة (sale_type=fuel أو category=بنزينة)
        //     - الموردين (customer_address='مورد' أو category=مورد أو مرتجعات موردين)
        //     - أي بيانات بدون تليفون
        // ═══════════════════════════════════════════════════════════════
        $rawInstallments = DB::table('installments')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '-')
            ->where('customer_phone', '!=', 'بدون')
            ->where('customer_phone', '!=', '')
            ->where(function ($q) {
                $q->where('category', '!=', 'بنزينة')->orWhereNull('category');
            })
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
            })
            ->where(function ($q) {
                $q->where('customer_address', '!=', 'مورد')->orWhereNull('customer_address');
            })
            ->where(function ($q) {
                $q->where('category', '!=', 'مورد')
                  ->where('category', '!=', 'مرتجعات موردين')
                  ->orWhereNull('category');
            })
            ->select(
                'id', 'customer_name', 'customer_phone', 'remaining_balance',
                'installment_months', 'due_day', 'start_date', 'created_at',
                'cash_price', 'total_after_interest', 'monthly_installment',
                'status', 'category', 'sale_type'
            )
            ->get();

        // ═══════════════════════════════════════════════════════════════
        // (2) جلب آخر دفعة لكل installment لمعرفة "هل متأخر"
        // ═══════════════════════════════════════════════════════════════
        $lastPayments = DB::table('installment_payments')
            ->select('installment_id', DB::raw('MAX(payment_date) as last_pay_date'))
            ->groupBy('installment_id')
            ->pluck('last_pay_date', 'installment_id');

        // ═══════════════════════════════════════════════════════════════
        // (3) دمج البيانات على مستوى العميل (phone)
        // ═══════════════════════════════════════════════════════════════
        $grouped = $rawInstallments->groupBy('customer_phone')->map(function ($group) use ($lastPayments, $today) {
            $first = $group->first();

            $totalRemaining   = (float) $group->sum('remaining_balance');
            $totalOriginal    = (float) $group->sum('total_after_interest');
            $monthlyTotal     = (float) $group->where('installment_months', '>', 0)
                                              ->where('remaining_balance', '>', 0)
                                              ->sum('monthly_installment');
            $hasInstallments  = $group->contains(fn($r) => (int)$r->installment_months > 0 && (float)$r->remaining_balance > 0);
            $contractsCount   = $group->count();
            $activeContracts  = $group->where('remaining_balance', '>', 0)->count();
            $dueDays          = $group->where('installment_months', '>', 0)
                                      ->where('remaining_balance', '>', 0)
                                      ->pluck('due_day')->unique()->filter()->values()->all();

            // تحقق إن العميل متعثر:
            //   عميل متعثر = عليه قسط شهري + آخر دفعة كانت قبل أكتر من 30 يوم (أو ما سددش أصلاً وفات وقت قسط)
            $isDefaulter = false;
            $oldestUnpaid = null;
            foreach ($group as $inst) {
                if ((int)$inst->installment_months <= 0 || (float)$inst->remaining_balance <= 0) continue;
                $dueDay = (int)($inst->due_day ?? 1);
                $lastPay = $lastPayments[$inst->id] ?? null;
                $checkDate = $lastPay
                    ? Carbon::parse($lastPay)
                    : Carbon::parse($inst->start_date ?? $inst->created_at);

                // لو فات شهر كامل من آخر دفعة → متعثر
                if ($checkDate->copy()->addMonth()->lt($today)) {
                    $isDefaulter = true;
                    if (!$oldestUnpaid || $checkDate->lt(Carbon::parse($oldestUnpaid))) {
                        $oldestUnpaid = $checkDate->toDateString();
                    }
                }
            }

            $daysLate = 0;
            if ($oldestUnpaid) {
                $daysLate = Carbon::parse($oldestUnpaid)->diffInDays($today);
            }

            $status = match (true) {
                $totalRemaining <= 0           => 'completed',
                $isDefaulter                   => 'defaulter',
                $hasInstallments               => 'on_installments',
                default                        => 'has_balance',
            };

            return (object) [
                'name'            => $first->customer_name,
                'phone'           => $first->customer_phone,
                'total_remaining' => $totalRemaining,
                'total_original'  => $totalOriginal,
                'monthly_total'   => $monthlyTotal,
                'contracts'       => $contractsCount,
                'active'          => $activeContracts,
                'due_days'        => $dueDays,
                'status'          => $status,
                'days_late'       => $daysLate,
                'last_payment'    => $oldestUnpaid,
                'has_installments'=> $hasInstallments,
            ];
        });

        // ═══════════════════════════════════════════════════════════════
        // (4) ضم عملاء من جدول customers اللي مالهمش حركة في installments
        // ═══════════════════════════════════════════════════════════════
        $extraCustomers = DB::table('customers')
            ->whereNotNull('phone')->where('phone', '!=', '-')->where('phone', '!=', '')
            ->get();

        foreach ($extraCustomers as $cust) {
            if ($grouped->has($cust->phone)) continue;
            $grouped->put($cust->phone, (object) [
                'name'            => $cust->name,
                'phone'           => $cust->phone,
                'total_remaining' => 0,
                'total_original'  => 0,
                'monthly_total'   => 0,
                'contracts'       => 0,
                'active'          => 0,
                'due_days'        => [],
                'status'          => 'no_transactions',
                'days_late'       => 0,
                'last_payment'    => null,
                'has_installments'=> false,
            ]);
        }

        $customers = $grouped->values();

        // ═══════════════════════════════════════════════════════════════
        // (5) تطبيق الفلاتر
        // ═══════════════════════════════════════════════════════════════
        $filteredCustomers = $customers->filter(function ($c) use ($filter, $dueDay, $today) {
            switch ($filter) {
                case 'defaulters':       return $c->status === 'defaulter';
                case 'on_installments':  return $c->has_installments && $c->status !== 'defaulter';
                case 'has_balance':      return $c->total_remaining > 0;
                case 'completed':        return $c->status === 'completed';
                case 'no_transactions':  return $c->status === 'no_transactions';
                case 'due_day':
                    if (!$dueDay) return false;
                    return in_array((int)$dueDay, array_map('intval', $c->due_days));
                case 'due_this_week':
                    $weekDays = [];
                    for ($i = 0; $i < 7; $i++) $weekDays[] = (int) $today->copy()->addDays($i)->day;
                    return !empty(array_intersect($weekDays, array_map('intval', $c->due_days)));
                case 'late_over_30':
                    return $c->days_late >= 30;
                case 'late_over_60':
                    return $c->days_late >= 60;
                case 'all':
                default:
                    return true;
            }
        });

        // (6) بحث بالاسم أو التليفون — مع توحيد الحروف العربية المتشابهة (أ/ا، ة/ه، ى/ي)
        if ($search !== '') {
            $normSearch = $this->normalizeArabicTerm($search);
            $filteredCustomers = $filteredCustomers->filter(function ($c) use ($normSearch) {
                return str_contains($this->normalizeArabicTerm($c->name ?? ''), $normSearch)
                    || str_contains($c->phone ?? '', $normSearch);
            });
        }

        // (7) ترتيب
        $filteredCustomers = match ($sortBy) {
            'balance_asc'  => $filteredCustomers->sortBy('total_remaining'),
            'name_asc'     => $filteredCustomers->sortBy('name'),
            'days_late_desc' => $filteredCustomers->sortByDesc('days_late'),
            'monthly_desc' => $filteredCustomers->sortByDesc('monthly_total'),
            'balance_desc' => $filteredCustomers->sortByDesc('total_remaining'),
            default        => $filteredCustomers->sortByDesc('total_remaining'),
        };

        $filteredCustomers = $filteredCustomers->values();

        // إحصائيات لأعلى الصفحة
        $stats = [
            'total'           => $customers->count(),
            'defaulters'      => $customers->where('status', 'defaulter')->count(),
            'on_installments' => $customers->filter(fn($c) => $c->has_installments && $c->status !== 'defaulter')->count(),
            'completed'       => $customers->where('status', 'completed')->count(),
            'total_due'       => $customers->sum('total_remaining'),
            'showing'         => $filteredCustomers->count(),
        ];

        return view('whatsapp', [
            'customers' => $filteredCustomers,
            'filter'    => $filter,
            'dueDay'    => $dueDay,
            'search'    => $search,
            'sortBy'    => $sortBy,
            'stats'     => $stats,
        ]);
    }
}
