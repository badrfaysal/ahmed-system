<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends SystemController
{
    public function storeSales(Request $request)
    {
        try {
            $request->validate([
                'product_name'   => 'required|string|max:255',
                'purchase_price' => 'required|numeric|min:0',
                'selling_price'  => 'required|numeric|min:0',
            ]);

            DB::transaction(function () use ($request) {
                $isService = true;
                $qty       = 1;
                $pPrice    = floatval($request->purchase_price);
                $sPrice    = floatval($request->selling_price);

                $totalCost    = $qty * $pPrice;
                $totalRevenue = $qty * $sPrice;
                $discount     = floatval($request->discount_amount ?? 0);
                $finalRevenue = max($totalRevenue - $discount, 0);

                if ($discount > 0) {
                    DB::table('financial_transactions')->insert([
                        'type'       => 'discount',
                        'amount'     => $discount,
                        'balance_before' => self::getTotalCapital(),
                        'balance_after'  => self::getTotalCapital(),
                        'notes'      => 'خصم للعميل في بيع مباشر: ' . $request->product_name,
                        'status'     => 'active',
                        'created_at' => now(),
                    ]);
                }

                $supPayType = $request->supplier_pay_type;
                $supPaid    = 0;

                if ($supPayType === 'cash') {
                    $supPaid = $totalCost;
                } elseif ($supPayType === 'partial') {
                    $supPaid = floatval($request->supplier_paid_amount);
                }

                if ($supPaid > $totalCost) {
                    throw new \Exception('المبلغ المدفوع للمورد أكبر من التكلفة الإجمالية!');
                }

                if ($supPaid > 0) {
                    $withAcc = DB::table('accounts')->where('id', $request->withdrawal_account)->lockForUpdate()->first();
                    if (!$withAcc || $withAcc->balance < $supPaid) {
                        throw new \Exception('عذراً، رصيد الخزنة المتاح لا يكفي لسداد التكلفة للمورد!');
                    }

                    DB::table('accounts')->where('id', $request->withdrawal_account)->decrement('balance', $supPaid);

                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $supPaid,
                        'from_account_id' => $request->withdrawal_account,
                        'balance_before'  => self::getTotalCapital(),
                        'balance_after'   => self::getTotalCapital(),
                        'notes'           => 'سداد تكلفة خدمة: ' . $request->product_name,
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);

                    if(method_exists($this, 'applyAccountCommission')) {
                        self::applyAccountCommission($request->withdrawal_account, $supPaid, 'سداد مورد (بيع مباشر)', 'out');
                    }
                }

                $supRemaining = $totalCost - $supPaid;
                if ($supRemaining > 0) {
                    $creditorName = trim($request->technician_name ?? 'صنايعي عام');

                    DB::table('company_debts')->insert([
                        'creditor_name'     => $creditorName,
                        'reason'            => 'تكلفة خدمة مباعة | ' . $request->product_name,
                        'total_amount'      => $totalCost,
                        'paid_amount'       => $supPaid,
                        'remaining_balance' => $supRemaining,
                        'category'          => 'استقطاعات',
                        'created_at'        => now(),
                    ]);
                }

                $custPaid = floatval($request->paid_amount ?? 0);
                if ($custPaid > $finalRevenue) {
                    throw new \Exception('المبلغ المحصل من العميل أكبر من المطلوب!');
                }

                if ($custPaid > 0) {
                    $depAccId = $request->deposit_account;
                    DB::table('accounts')->where('id', $depAccId)->increment('balance', $custPaid);

                    DB::table('financial_transactions')->insert([
                        'type'          => 'income',
                        'amount'        => $custPaid,
                        'to_account_id' => $depAccId,
                        'balance_before'=> self::getTotalCapital(),
                        'balance_after' => self::getTotalCapital(),
                        'notes'         => 'مبيعات مباشرة: ' . $request->product_name,
                        'status'        => 'active',
                        'created_at'    => now(),
                    ]);

                    if(method_exists($this, 'applyAccountCommission')) {
                        self::applyAccountCommission($depAccId, $custPaid, 'تحصيل بيع مباشر', 'in');
                    }
                }

                $profit = $finalRevenue - $totalCost;
                $commVal = floatval($request->commission_amount ?? 0);
                if ($request->deduct_from_profit) {
                    $profit -= $commVal;
                }
                $custRemaining = $finalRevenue - $custPaid;

                $supplierName = trim($request->technician_name ?? 'صنايعي عام');
                DB::table('sales')->insert([
                    'store_id'           => 1,
                    'product_name'       => $request->product_name,
                    'category'           => 'خدمات',
                    'supplier_name'      => $supplierName,
                    'purchase_price'     => $pPrice,
                    'selling_price'      => $sPrice,
                    'quantity'           => $qty,
                    'remaining_quantity' => 0,
                    'inventory_status'   => 'sold',
                    'purchase_date'      => now()->toDateString(),
                    'created_at'         => now(),
                ]);

                // 💡 لو الرقم لعميل مسجل، نتجاهل أي محاولة تعديل اسم من النموذج ونثبّت الاسم المسجل
                $finalCustomerName = trim($request->customer_name ?? 'عميل نقدي');
                $phoneRaw = trim($request->customer_phone ?? '');
                if (!empty($phoneRaw)) {
                    $existing = DB::table('customers')->where('phone', $phoneRaw)->first()
                              ?? DB::table('installments')->where('customer_phone', $phoneRaw)->whereNotNull('customer_name')->orderByDesc('id')->first();
                    if ($existing) {
                        $finalCustomerName = trim($existing->name ?? $existing->customer_name);
                    }
                }

                $instId = DB::table('installments')->insertGetId([
                    'customer_name'        => $finalCustomerName,
                    'customer_phone'       => $phoneRaw,
                    'product_name'         => $request->product_name,
                    'category'             => 'خدمات',
                    'sale_type'            => 'direct',
                    'purchase_cost'        => $totalCost,
                    'quantity'             => $qty,
                    'cash_price'           => $totalRevenue,
                    'discount'             => $discount,
                    'down_payment'         => $custPaid,
                    'remaining_after_down' => $custRemaining,
                    'installment_months'   => 0,
                    'total_after_interest' => $finalRevenue,
                    'monthly_installment'  => $custRemaining,
                    'due_day'              => date('d'),
                    'remaining_balance'    => $custRemaining,
                    'profit'               => $profit,
                    'notes'                => trim($request->customer_notes ?? '') ?: null,
                    'start_date'           => now()->toDateString(),
                    'created_at'           => now(),
                ]);

                if ($custPaid > 0) {
                    DB::table('installment_payments')->insert([
                        'installment_id'    => $instId,
                        'amount_paid'       => $custPaid,
                        'payment_method_id' => $request->deposit_account,
                        'payment_date'      => now(),
                    ]);
                }

                // 💡 توحيد مسمى العمولات لتكون "عمولات البيع" وتسجيلها في العمليات المالية
                if ($commVal > 0) {
                    DB::table('company_debts')->insert([
                        'creditor_name'     => 'عمولات البيع',
                        'reason'            => "عمولات البيع عن مبيعات مباشر: {$request->product_name}",
                        'total_amount'      => $commVal,
                        'paid_amount'       => 0,
                        'remaining_balance' => $commVal,
                        'category'          => 'عمولات',
                        'created_at'        => now(),
                    ]);

                    DB::table('financial_transactions')->insert([
                        'type'            => 'commission',
                        'amount'          => $commVal,
                        'balance_before'  => self::getTotalCapital(),
                        'balance_after'   => self::getTotalCapital(),
                        'notes'           => "عمولات البيع عن مبيعات مباشر: {$request->product_name}",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                }

                if (!empty($phoneRaw)) {
                    $customerExists = DB::table('customers')->where('phone', $phoneRaw)->exists();
                    if (!$customerExists) {
                        DB::table('customers')->insert([
                            'name'       => $finalCustomerName ?: 'بدون اسم',
                            'phone'      => $phoneRaw,
                            'created_at' => now(),
                        ]);
                    }
                }
            });

            return back()->with('success', '✅ تم تسجيل البيع وتحديث الأرصدة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage())->withInput();
        }
    }

    public function sales()
    {
        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        // ⚡ دمج الموردين من كل المصادر: جدول الموردين + اسم المورد في المبيعات + الدائنين في الديون
        $suppliersFromTable = DB::table('suppliers')->whereNotNull('name')->pluck('name');
        $suppliersFromSales = DB::table('sales')->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->where('supplier_name', 'not like', 'فسخ عقد%')        // استبعاد باتشات فسخ العقود
            ->where(function($q) {
                $q->where('category', '!=', 'مرتجعات عملاء')->orWhereNull('category');
            })
            ->distinct()->pluck('supplier_name');
        $suppliersFromDebts = DB::table('company_debts')->whereNotNull('creditor_name')
            ->where('creditor_name', '!=', '')
            ->whereIn('category', ['مورد', 'موردين'])
            ->distinct()->pluck('creditor_name');

        $suppliers = $suppliersFromTable
            ->merge($suppliersFromSales)
            ->merge($suppliersFromDebts)
            ->map(fn($n) => trim($n))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn($n) => (object)['name' => $n]);

        $uniqueCustomers = DB::table('installments')
            ->select('customer_name', 'customer_phone')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '-')
            ->orderByDesc('created_at')
            ->get()
            ->unique('customer_phone')
            ->values();

        return view('sales', compact('accounts', 'suppliers', 'uniqueCustomers'));
    }
}