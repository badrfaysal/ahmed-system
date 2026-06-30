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
            $this->applyArabicSearch($customersQuery, ['name', 'phone'], $search);
        }
        if ($cityFilter) {
            $this->applyArabicSearch($customersQuery, ['address'], $cityFilter);
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

    // تعديل بيانات عميل موجود (الاسم / التليفون / العنوان)
    public function updateCustomer(Request $request)
    {
        $request->validate([
            'original_name' => 'required|string|max:255',
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'required|string|max:255',
        ], [], [
            'name'    => 'اسم العميل',
            'address' => 'العنوان',
        ]);

        $originalName = trim($request->original_name);
        $newName      = trim($request->name);
        $phone        = trim($request->phone ?? '');
        $address      = trim($request->address);

        // منع الدمج بالغلط: لو غيّرت الاسم لاسم عميل تاني موجود بالفعل
        if ($newName !== $originalName) {
            $clash = DB::table('customers')->where('name', $newName)->exists()
                  || DB::table('installments')->where('customer_name', $newName)->exists();
            if ($clash) {
                return back()->with('error', "❌ الاسم \"{$newName}\" مستخدم لعميل آخر بالفعل. اختر اسماً مختلفاً.");
            }
        }

        try {
            DB::transaction(function () use ($originalName, $newName, $phone, $address) {
                // 1) جدول customers: لو العميل موجود نعدّله، لو لأ (عميل من الأقساط فقط) ننشئه
                $existing = DB::table('customers')->where('name', $originalName)->first();
                if ($existing) {
                    DB::table('customers')->where('id', $existing->id)->update([
                        'name'       => $newName,
                        'phone'      => $phone,
                        'address'    => $address,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('customers')->insert([
                        'name'       => $newName,
                        'phone'      => $phone,
                        'address'    => $address,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 2) ربط السجل التاريخي: نحدّث الأقساط المرتبطة بالعميل (الأرشيف بيتجمّع بالاسم)
                $instUpdate = ['customer_phone' => $phone, 'updated_at' => now()];
                if ($newName !== $originalName) {
                    $instUpdate['customer_name'] = $newName;
                }
                DB::table('installments')->where('customer_name', $originalName)->update($instUpdate);

                // 3) لو الاسم اتغيّر نحدّث استفسارات العميل كمان عشان يفضل مربوط
                if ($newName !== $originalName) {
                    DB::table('customer_inquiries')->where('customer_name', $originalName)->update([
                        'customer_name'  => $newName,
                        'customer_phone' => $phone,
                        'updated_at'     => now(),
                    ]);
                }
            });

            $this->logActivity('update', 'customer', "✏️ تم تعديل بيانات العميل: {$originalName}" . ($newName !== $originalName ? " → {$newName}" : ''));

            return back()->with('success', "✅ تم تحديث بيانات العميل ({$newName}) بنجاح.");
        } catch (\Throwable $e) {
            return back()->with('error', '❌ فشل تحديث البيانات: ' . $e->getMessage());
        }
    }

    /**
     * توليد كود بوابة عشوائي فريد للعميل — يستخدمه للدخول للبورتال.
     * لو العميل موجود في installments بس مش في customers → ينشئ سجل جديد.
     */
    public function generatePortalCode(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
        ]);

        $name  = trim($request->customer_name);
        $phone = trim($request->customer_phone ?? '');

        try {
            $code = $this->generateUniquePortalCode();

            // ابحث في customers بالاسم
            $customer = DB::table('customers')->where('name', $name)->first();

            if ($customer) {
                DB::table('customers')->where('id', $customer->id)->update([
                    'portal_code' => $code,
                    'phone'       => $customer->phone ?: $phone,
                    'updated_at'  => now(),
                ]);
            } else {
                // اقرأ من installments عشان نملأ العنوان لو موجود
                $address = 'لم يحدد بعد';
                DB::table('customers')->insert([
                    'name'        => $name,
                    'phone'       => $phone,
                    'address'     => $address,
                    'portal_code' => $code,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            $this->logActivity('create', 'portal_code', "🔑 تم توليد كود بوابة للعميل: {$name} | الكود: {$code}");

            return back()->with('success', "✅ تم توليد كود بوابة العميل ({$name}): {$code}");
        } catch (\Throwable $e) {
            return back()->with('error', '❌ فشل توليد الكود: ' . $e->getMessage());
        }
    }

    /**
     * توليد كود فريد لا يتكرر — 8 خانات (أحرف كبيرة + أرقام)، يحذف الأحرف المتشابهة (0/O/1/I).
     */
    private function generateUniquePortalCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            // تنسيق: XXXX-XXXX
            $formatted = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            if (!DB::table('customers')->where('portal_code', $formatted)->exists()) {
                return $formatted;
            }
        }
        throw new \Exception('تعذر توليد كود فريد بعد عدة محاولات.');
    }
}
