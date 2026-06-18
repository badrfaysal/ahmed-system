<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * بوابة العميل - Public-facing بدون nav bar.
 * العميل يدخل برقم موبايله ويشوف:
 *  - أقساطه المفتوحة
 *  - تاريخ مدفوعاته
 *  - تواريخ استحقاقاته
 *  - فواتيره بالتفصيل
 * بدون دفع إلكتروني.
 */
class CustomerPortalController extends Controller
{
    public function showLogin()
    {
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:30',
        ], [
            'phone.required' => 'رقم الموبايل مطلوب.',
        ]);

        $phone = $this->normalizePhone($request->phone);

        // Rate limit: 5 محاولات / دقيقة لكل IP
        $key = 'portal_login:' . $request->ip();
        $attempts = (int) cache()->get($key, 0);
        if ($attempts >= 5) {
            return back()->withErrors(['phone' => 'محاولات كتيرة. حاول بعد دقيقة.'])->withInput();
        }
        cache()->put($key, $attempts + 1, 60);

        $customer = $this->findCustomerByPhone($phone);

        if (!$customer) {
            return back()->withErrors(['phone' => 'الرقم ده مش موجود عندنا. تأكد من كتابته صح أو اتصل بالشركة.'])->withInput();
        }

        session([
            'portal_customer' => [
                'name'     => $customer->name,
                'phone'    => $customer->phone,
                'login_at' => now()->toDateTimeString(),
                'ip'       => $request->ip(),
            ],
        ]);

        \App\Services\AuditService::log('portal_login', 'portal', "دخول عميل من البوابة: {$customer->name} ({$phone})");

        return redirect()->route('portal.dashboard');
    }

    public function dashboard()
    {
        $cust = session('portal_customer');
        if (!$cust) return redirect()->route('portal.login');

        $name  = $cust['name'];
        $phone = $cust['phone'];

        $contracts = DB::table('installments')
            ->where('customer_name', $name)
            ->where(function($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
            ->orderByDesc('created_at')
            ->get();

        $contractIds = $contracts->pluck('id')->toArray();
        $payments = empty($contractIds) ? collect() : DB::table('installment_payments')
            ->whereIn('installment_id', $contractIds)
            ->orderByDesc('payment_date')
            ->get()
            ->groupBy('installment_id');

        // المدفوع = الإجمالي - المتبقي (متسق مع الـ DB)
        $totalAfterInterest = (float) $contracts->sum('total_after_interest');
        $totalRemaining     = (float) $contracts->sum('remaining_balance');

        $stats = [
            'total_contracts'  => $contracts->count(),
            'open_contracts'   => $contracts->where('remaining_balance', '>', 0)->count(),
            'closed_contracts' => $contracts->where('remaining_balance', '<=', 0)->count(),
            'total_remaining'  => $totalRemaining,
            'total_paid'       => max(0, $totalAfterInterest - $totalRemaining),
            'next_due'         => $this->getNextDueDate($contracts),
        ];

        return view('portal.dashboard', compact('cust', 'contracts', 'payments', 'stats'));
    }

    public function contractDetails($id)
    {
        $cust = session('portal_customer');
        if (!$cust) return redirect()->route('portal.login');

        $contract = DB::table('installments')->where('id', $id)->first();
        if (!$contract || $contract->customer_name !== $cust['name']) {
            abort(403, 'لا تملك صلاحية عرض هذا العقد.');
        }

        $payments = DB::table('installment_payments')
            ->where('installment_id', $id)
            ->orderBy('payment_date')
            ->get();

        return view('portal.contract', compact('cust', 'contract', 'payments'));
    }

    public function logout()
    {
        session()->forget('portal_customer');
        return redirect()->route('portal.login')->with('msg', 'تم تسجيل الخروج بنجاح.');
    }

    // ════════════════════════════════════════
    // Helpers
    // ════════════════════════════════════════

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '2') && strlen($digits) === 12) {
            $digits = substr($digits, 1);
        }
        return $digits;
    }

    private function findCustomerByPhone(string $phone): ?object
    {
        // البحث في جدول customers أولاً
        $c = DB::table('customers')->where('phone', 'LIKE', "%{$phone}")->first();
        if ($c) return (object) ['name' => $c->name, 'phone' => $c->phone];

        // البحث في installments (مع استبعاد البنزينة)
        $i = DB::table('installments')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', 'بدون')
            ->where('customer_phone', 'NOT LIKE', 'GAS-%')
            ->where('customer_phone', 'LIKE', "%{$phone}")
            ->select('customer_name as name', 'customer_phone as phone')
            ->first();

        return $i ? (object) (array) $i : null;
    }

    private function getNextDueDate($contracts): ?string
    {
        $open = $contracts->where('remaining_balance', '>', 0)->where('installment_months', '>', 0);
        if ($open->isEmpty()) return null;

        // نجيب آخر دفعة لكل عقد عشان نعرف هل قسط الشهر ده اتسدد
        $contractIds = $open->pluck('id')->toArray();
        $lastPayments = \Illuminate\Support\Facades\DB::table('installment_payments')
            ->whereIn('installment_id', $contractIds)
            ->selectRaw('installment_id, MAX(payment_date) as last_pay')
            ->groupBy('installment_id')
            ->pluck('last_pay', 'installment_id');

        $today = now()->startOfDay();
        $dates = $open->map(function($c) use ($today, $lastPayments) {
            $day = (int) min(max(1, $c->due_day ?? 1), 28);
            $thisMonth = \Carbon\Carbon::create($today->year, $today->month, $day)->startOfDay();

            $lastPay = $lastPayments[$c->id] ?? null;
            $paidThisCycle = false;
            if ($lastPay) {
                try {
                    $lp = \Carbon\Carbon::parse($lastPay);
                    // دفعة آخر 28 يوم = سدد قسط الدورة الحالية
                    if ($lp->diffInDays($today) <= 28) $paidThisCycle = true;
                } catch (\Throwable $e) {}
            }

            if ($paidThisCycle) {
                return $thisMonth->copy()->addMonth();
            }
            return $thisMonth;
        });

        return $dates->min()->format('Y-m-d');
    }
}
