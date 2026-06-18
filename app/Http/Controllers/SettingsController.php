<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SettingsController extends SystemController
{
    public function settings()
    {
        $payment_methods = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $companies = DB::table('transport_companies')->get();
        $stations  = DB::table('gas_stations')->get();
        $items     = DB::table('items')->get();
        $suppliers = DB::table('suppliers')->get();
        $users     = DB::table('users')->orderBy('role')->orderBy('name')->get();

        // الإعدادات العامة للنظام
        \App\Services\SystemSetting::clearCache();
        $system_settings = \App\Services\SystemSetting::all();
        $system_settings_meta = collect(\App\Services\SystemSetting::grouped()['general'] ?? [])->keyBy('key');

        return view('settings', compact(
            'payment_methods', 'companies', 'stations', 'items', 'suppliers', 'users',
            'system_settings', 'system_settings_meta'
        ));
    }

    /**
     * تحديث قيم الإعدادات العامة.
     */
    public function updateSystemSettings(Request $request)
    {
        $allowed = [
            'fuel_profit_rate', 'default_interest_rate', 'max_installment_months',
            'min_down_payment_pct', 'invoice_header_text', 'invoice_footer_text',
            'low_balance_threshold', 'theme_color',
        ];
        foreach ($allowed as $key) {
            if ($request->has($key)) {
                \App\Services\SystemSetting::set($key, (string) $request->input($key));
            }
        }
        return back()->with('success', '✅ تم حفظ إعدادات النظام.');
    }

    public function storeSupplier(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        DB::table('suppliers')->insert([
            'name'       => trim($request->name),
            'created_at' => now(),
        ]);
        return back()->with('success', 'تم إضافة المورد بنجاح.');
    }

    public function destroySupplier($id)
    {
        DB::table('suppliers')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف المورد بنجاح.');
    }

    public function storeCompany(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        DB::table('transport_companies')->insert([
            'company_name' => trim($request->name),
            'balance'      => 0,
            'created_at'   => now(),
        ]);
        return back()->with('success', 'تم إضافة الشركة / المقاول بنجاح.');
    }

    public function destroyCompany($id)
    {
        DB::table('transport_companies')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف الشركة بنجاح.');
    }

    public function storeStation(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        DB::table('gas_stations')->insert([
            'station_name' => trim($request->name),
            'balance'      => 0,
            'created_at'   => now(),
        ]);
        return back()->with('success', 'تم إضافة المحطة بنجاح.');
    }

    public function destroyStation($id)
    {
        try {
            $hasTransactions = DB::table('fuel_transactions')
                ->where('station_id', $id)
                ->exists();

            if ($hasTransactions) {
                return back()->withInput()->with('error', 'لا يمكن حذف المحطة لوجود عمليات مرتبطة بها.');
            }

            DB::table('gas_stations')->where('id', $id)->delete();
            return back()->with('success', 'تم حذف المحطة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدث خطأ أثناء الحذف.');
        }
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'item_name'      => 'required|string|max:255',
            'original_price' => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
        ]);

        DB::table('items')->insert([
            'item_name'      => $request->item_name,
            'original_price' => floatval($request->original_price),
            'discount_price' => floatval($request->selling_price),
            'selling_price'  => floatval($request->selling_price),
        ]);

        return back()->with('success', 'تم إضافة الصنف والأسعار بنجاح.');
    }

    public function destroyItem($id)
    {
        DB::table('items')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف الصنف بنجاح.');
    }

    public function storePaymentMethod(Request $request)
    {
        DB::table('accounts')->insert([
            'account_name' => $request->account_name,
            'category'     => $request->category,
            'balance'      => floatval($request->initial_balance),
            'status'       => 'active',
            'created_at'   => now(),
        ]);
        return back()->with('success', 'تم إضافة المحفظة/الخزنة بنجاح.');
    }

    public function renameAccount(Request $request, $id = null)
    {
        try {
            $accountId = $id ?? $request->account_id ?? $request->id;
            $newName   = $request->account_name ?? $request->name ?? $request->new_name;

            if (!$accountId || empty(trim($newName))) {
                throw new \Exception('البيانات غير مكتملة، يرجى التأكد من كتابة الاسم الجديد.');
            }

            $oldAccount = DB::table('accounts')->where('id', $accountId)->first();
            if (!$oldAccount) {
                throw new \Exception('هذه المحفظة غير موجودة في النظام.');
            }

            DB::table('accounts')->where('id', $accountId)->update([
                'account_name' => trim($newName),
            ]);

            $this->logActivity('update', 'system', "✏️ تم تغيير اسم المحفظة من ({$oldAccount->account_name}) إلى ({$newName})");

            return back()->with('success', '✅ تم تعديل اسم المحفظة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyAccount(Request $request, $id)
    {
        $sessionUser = session('auth_user');
        if (!$sessionUser || ($sessionUser->role ?? '') !== 'admin') {
            return back()->with('error', 'هذه العملية للمدير فقط.');
        }

        $pin = (string) $request->input('admin_pin', '');
        if ($pin === '') {
            return back()->with('error', 'يجب إدخال كلمة المرور لتأكيد الحذف.');
        }

        $dbUser = DB::table('users')->where('id', $sessionUser->id)->first();
        if (!$dbUser || !Hash::check($pin, $dbUser->password)) {
            return back()->with('error', 'كلمة المرور غير صحيحة.');
        }

        $account = DB::table('accounts')->where('id', $id)->first();
        if (!$account) {
            return back()->with('error', 'الخزنة/المحفظة غير موجودة.');
        }

        if ((float) $account->balance != 0) {
            return back()->with('error', "لا يمكن حذف هذه الخزنة — الرصيد يجب أن يكون صفراً (الرصيد الحالي: {$account->balance} ج).");
        }

        $this->logActivity('delete', 'system',
            "🗑️ تم حذف الخزنة: ({$account->account_name}) — رصيد وقت الحذف: {$account->balance} ج"
        );

        DB::table('accounts')->where('id', $id)->delete();

        return back()->with('success', "✅ تم حذف الخزنة ({$account->account_name}) بنجاح.");
    }

    public function updateCommissionSettings(Request $request)
    {
        DB::table('accounts')->where('id', $request->account_id)->update([
            'has_commission'   => $request->has_commission ? 1 : 0,
            'commission_type'  => $request->commission_type,
            'commission_value' => $request->commission_value ?? 0,
            'max_commission'   => $request->max_commission ?? 0,
            'min_commission'   => $request->min_commission ?? 0,
            'flat_fee_below'   => $request->flat_fee_below ?? 0,
            'flat_fee_amount'  => $request->flat_fee_amount ?? 0,
            'updated_at'       => now(),
        ]);

        return back()->with('success', 'تم تحديث إعدادات العمولة للوسيلة بنجاح.');
    }

    public function storeDeduction(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        DB::table('fuel_deductions')->insert([
            'name'       => $request->name,
            'percentage' => floatval($request->percentage),
            'created_at' => now(),
        ]);

        return back()->with('success', 'تم إضافة بند الاستقطاع الجديد بنجاح للسيستم.');
    }

    public function destroyDeduction($id)
    {
        DB::table('fuel_deductions')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف بند الاستقطاع بنجاح من الحسابات.');
    }
}
