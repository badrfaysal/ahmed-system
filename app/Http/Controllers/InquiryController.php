<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InquiryController extends SystemController
{
    /**
     * عرض شاشة استفسارات العملاء.
     */
    public function index(Request $request)
    {
        $search    = trim((string) $request->input('search', ''));
        $period    = $request->input('period', 'all');
        $fromDate  = $request->input('from_date');
        $toDate    = $request->input('to_date');

        [$start, $end] = $this->resolvePeriod($period, $fromDate, $toDate);

        $base = DB::table('customer_inquiries');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                  ->orWhere('inquiry', 'LIKE', "%{$search}%")
                  ->orWhere('product_type', 'LIKE', "%{$search}%");
            });
        }

        if ($start && $end) {
            $base->whereBetween('created_at', [$start, $end]);
        }

        $pending = (clone $base)->where('is_contacted', 0)
            ->orderByDesc('created_at')->get();

        $done = (clone $base)->where('is_contacted', 1)
            ->orderByDesc('contacted_at')->get();

        // إحصائيات على نطاق الفلتر الحالي
        $scope = DB::table('customer_inquiries');
        if ($start && $end) {
            $scope->whereBetween('created_at', [$start, $end]);
        }
        $stats = [
            'total'   => (clone $scope)->count(),
            'pending' => (clone $scope)->where('is_contacted', 0)->count(),
            'done'    => (clone $scope)->where('is_contacted', 1)->count(),
            'today'   => DB::table('customer_inquiries')->whereDate('created_at', today())->count(),
        ];

        return view('inquiries', compact(
            'pending', 'done', 'stats',
            'search', 'period', 'fromDate', 'toDate'
        ));
    }

    /**
     * تسجيل استفسار جديد.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'inquiry'        => 'required|string|max:2000',
            'product_type'   => 'nullable|string|max:100',
        ]);

        $user = session('auth_user');

        DB::table('customer_inquiries')->insert([
            'customer_name'     => $request->customer_name,
            'customer_phone'    => $request->customer_phone,
            'inquiry'           => $request->inquiry,
            'product_type'      => $request->product_type,
            'is_contacted'      => $request->has('is_contacted') ? 1 : 0,
            'contacted_at'      => $request->has('is_contacted') ? now() : null,
            'contacted_by'      => $request->has('is_contacted') ? ($user->id ?? null) : null,
            'contacted_by_name' => $request->has('is_contacted') ? ($user->name ?? null) : null,
            'created_by'        => $user->id ?? null,
            'created_by_name'   => $user->name ?? 'غير معروف',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return back()->with('success', 'تم تسجيل استفسار العميل بنجاح.');
    }

    /**
     * تأكيد التواصل / إرجاع لـ "لم يتم".
     */
    public function toggleContact(Request $request, $id)
    {
        $row = DB::table('customer_inquiries')->where('id', $id)->first();
        if (!$row) {
            return back()->withInput()->with('error', 'الاستفسار غير موجود.');
        }

        $user = session('auth_user');

        if ($row->is_contacted) {
            DB::table('customer_inquiries')->where('id', $id)->update([
                'is_contacted'      => 0,
                'contacted_at'      => null,
                'contacted_by'      => null,
                'contacted_by_name' => null,
                'updated_at'        => now(),
            ]);
            return back()->with('success', 'تم إرجاع الاستفسار إلى قائمة الانتظار.');
        }

        DB::table('customer_inquiries')->where('id', $id)->update([
            'is_contacted'      => 1,
            'contacted_at'      => now(),
            'contacted_by'      => $user->id ?? null,
            'contacted_by_name' => $user->name ?? 'غير معروف',
            'contact_notes'     => $request->input('contact_notes'),
            'updated_at'        => now(),
        ]);

        return back()->with('success', 'تم تأكيد التواصل مع العميل.');
    }

    /**
     * تحديث بيانات استفسار.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'inquiry'        => 'required|string|max:2000',
            'product_type'   => 'nullable|string|max:100',
            'contact_notes'  => 'nullable|string|max:2000',
        ]);

        DB::table('customer_inquiries')->where('id', $id)->update([
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'inquiry'        => $request->inquiry,
            'product_type'   => $request->product_type,
            'contact_notes'  => $request->contact_notes,
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'تم تحديث بيانات الاستفسار.');
    }

    /**
     * حذف استفسار.
     */
    public function destroy($id)
    {
        DB::table('customer_inquiries')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف الاستفسار.');
    }

    private function resolvePeriod(string $period, ?string $from, ?string $to): array
    {
        switch ($period) {
            case 'today':      return [now()->startOfDay(), now()->endOfDay()];
            case 'yesterday':  return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];
            case 'week':       return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
            case 'month':      return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
            case 'this_month': return [now()->startOfMonth(), now()->endOfMonth()];
            case 'custom':
                if ($from && $to) {
                    return [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()];
                }
                return [null, null];
            case 'all':
            default:
                return [null, null];
        }
    }
}
