<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends SystemController
{
   // عرض أرشيف العملاء الشامل (محدث لدمج عملاء المبيعات تلقائياً)
   public function customersArchive(Request $request)
    {
        $search = $request->input('search');
        $cityFilter = $request->input('city');

        // الدمج الذكي للعملاء مع حساب الديون التاريخية والحالية والأرباح
        $customersQuery = DB::table(DB::raw("(
            SELECT name, phone, address, created_at FROM customers
            UNION
            SELECT customer_name as name, customer_phone as phone, 'لم يحدد بعد' as address, MIN(created_at) as created_at 
            FROM installments 
            WHERE customer_name IS NOT NULL AND customer_name != '' AND customer_name NOT IN (SELECT name FROM customers WHERE name IS NOT NULL)
            GROUP BY customer_name, customer_phone
        ) as customers"))
        ->select([
            'customers.*',
            DB::raw('(SELECT COUNT(*) FROM installments WHERE installments.customer_name = customers.name) as total_purchases'),
            DB::raw('(SELECT COALESCE(SUM(remaining_balance), 0) FROM installments WHERE installments.customer_name = customers.name) as total_current_debts'),
            DB::raw('(SELECT COALESCE(SUM(total_after_interest), 0) FROM installments WHERE installments.customer_name = customers.name) as total_historical_debts'),
            DB::raw('(SELECT COALESCE(SUM(profit), 0) FROM installments WHERE installments.customer_name = customers.name) as total_profit'),
        ]);

        if ($search) {
            $customersQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        if ($cityFilter) {
            $customersQuery->where('address', 'LIKE', "%{$cityFilter}%");
        }

        $customers = $customersQuery->get();

        // 💡 استخراج بيانات كروت الـ VIP من القائمة المجمعة
        $topPurchaser  = $customers->sortByDesc('total_purchases')->first();
        $topDebtor     = $customers->sortByDesc('total_historical_debts')->first();
        $topProfitable = $customers->sortByDesc('total_profit')->first();

        $totalCustomersCount = $customers->count();
        $grandTotalDebts = DB::table('installments')->sum('remaining_balance');

        $topLocations = DB::table('customers')
            ->select('address', DB::raw('count(*) as total'))
            ->whereNotNull('address')
            ->where('address', '!=', 'لم يحدد بعد')
            ->groupBy('address')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 💡 تمرير كروت الـ VIP للواجهة
        return view('customers_archive', compact(
            'customers', 'totalCustomersCount', 'grandTotalDebts', 
            'topLocations', 'search', 'cityFilter', 
            'topPurchaser', 'topDebtor', 'topProfitable'
        ));
    }

    // حفظ عميل جديد
    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255', // العنوان إجباري لتحليل البيانات الجغرافية
        ]);

        try {
            DB::table('customers')->insert([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'تم تسجيل العميل الجديد بنجاح في الأرشيف.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage());
        }
    }
}
