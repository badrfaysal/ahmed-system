<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GasStationController extends SystemController
{
    public function gasStation()
    {
        $companies       = DB::table('transport_companies')->get();
        $stations        = DB::table('gas_stations')->get();
        $items           = DB::table('items')->get();
        $payment_methods = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $deductions      = DB::table('fuel_deductions')->get();

        // 💡 المستحقات اللي لينا من كل شركة نقل (ديون البنزينة على الشركة) — مفتاحها اسم الشركة
        $companyDues = DB::table('installments')
            ->where('category', 'بنزينة')
            ->where('remaining_balance', '>', 0)
            ->groupBy('customer_name')
            ->selectRaw('customer_name, SUM(remaining_balance) as total')
            ->pluck('total', 'customer_name');

        // 💡 الديون اللي علينا لكل محطة وقود (ثمن الوقود المستحق للمحطة) — مفتاحها اسم المحطة
        $stationDebts = DB::table('company_debts')
            ->where('category', 'وقود')
            ->where('remaining_balance', '>', 0)
            ->groupBy('creditor_name')
            ->selectRaw('creditor_name, SUM(remaining_balance) as total')
            ->pluck('total', 'creditor_name');

        return view('gas_station', compact('companies', 'stations', 'items', 'payment_methods', 'deductions', 'companyDues', 'stationDebts'));
    }

public function storeGasStation(Request $request)
    {
        $request->validate([
            'company_id'      => 'required|exists:transport_companies,id',
            'station_id'      => 'required|exists:gas_stations,id',
            'advance_source'  => 'required|in:none,safe,station',
            'account_id'      => 'required_if:advance_source,safe',
        ], [
            'account_id.required_if' => '⚠️ يجب اختيار الخزنة لصرف العهدة النقدية.',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $company = \Illuminate\Support\Facades\DB::table('transport_companies')->where('id', $request->company_id)->first();
                $station = \Illuminate\Support\Facades\DB::table('gas_stations')->where('id', $request->station_id)->first();
                if (!$company || !$station) throw new \Exception('الشركة أو المحطة غير موجودة.');

                $companyName = $company->company_name ?? $company->name;
                $stationName = $station->station_name ?? $station->name;
                $driverCar   = $request->driver_car ?? 'غير محدد';
                $itemName = 'وقود فقط (بدون عهدة)';
                $dbPrice = 0;

                if ($request->item_id != "0") {
                    $item = \Illuminate\Support\Facades\DB::table('items')->where('id', $request->item_id)->first();
                    if (!$item) throw new \Exception('الصنف غير موجود.');
                    $itemName = $item->item_name ?? $item->name;
                    $dbPrice  = floatval($item->original_price ?? $item->price ?? 0);
                    if ($dbPrice == 0) $dbPrice = (float) \App\Services\SystemSetting::get('default_fuel_price', 20);
                }

                $advanceSource = $request->advance_source; // 'none' | 'safe' | 'station'
                $quantity = ($request->item_id == "0") ? 0 : floatval($request->quantity ?? 0);
                // 💡 لو "بدون عهدة" نتجاهل أي قيمة دخلت في خانة المبلغ
                $advance  = ($advanceSource === 'none') ? 0 : floatval($request->cash_advance ?? 0);
                $fuelDebt = $quantity * $dbPrice;

                // لازم يكون فيه وقود أو عهدة على الأقل
                if ($fuelDebt <= 0 && $advance <= 0) {
                    throw new \Exception('⚠️ لا يمكن تسجيل عملية فارغة — أدخل كمية وقود أو عهدة نقدية على الأقل.');
                }

                // 💡 استدعاء الخصم اليدوي (مصروفات) لخصمه من إجمالي الأرباح
                $manualDiscount = floatval($request->manual_discount ?? 0);

                $profitRate = (float) \App\Services\SystemSetting::get('fuel_profit_rate', 0.01);

                // 💡 اللوجيك المحاسبي بناءً على جهة الصرف
                if ($advanceSource === 'none') {
                    // لا توجد عهدة — العمولة على الوقود فقط، والمحطة تستحق ثمن الوقود
                    $grossProfit    = $fuelDebt * $profitRate;
                    $totalToStation = $fuelDebt;
                } elseif ($advanceSource === 'station') {
                    // العهدة من المحطة — العمولة على (الوقود + العهدة)، المحطة تستحق الاثنين
                    $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                    $totalToStation = $fuelDebt + $advance;
                } else {
                    // العهدة من خزنة الشركة — العمولة على الإجمالي، المحطة تستحق الوقود فقط
                    $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                    $totalToStation = $fuelDebt;

                    // سحب العهدة من الخزنة
                    if ($advance > 0) {
                        $accId = $request->account_id;
                        $account = \Illuminate\Support\Facades\DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                        if (!$account || $account->balance < $advance) throw new \Exception("رصيد الخزنة لا يكفي لصرف العهدة.");

                        \Illuminate\Support\Facades\DB::table('accounts')->where('id', $accId)->decrement('balance', $advance);
                        \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                            'type' => 'expense', 'amount' => $advance, 'from_account_id' => $accId,
                            'notes' => "صرف عهدة نقدية لـ ({$driverCar}) | شركة {$companyName}", 'status' => 'active', 'created_at' => now(),
                        ]);
                    }
                }

                $totalOnCompany = $fuelDebt + $advance + $grossProfit;

                // 💡 تطبيق المصروفات (الخصم اليدوي) للحصول على الأرباح الفعلية
                $profitAfterDiscount = $grossProfit - $manualDiscount;

                // 💡 حساب الاستقطاعات (نحسبها أولاً قبل أي insert)
                $deductions = \Illuminate\Support\Facades\DB::table('fuel_deductions')->get();
                $totalDeductionsAmount = 0;
                $deductionRows = [];
                foreach ($deductions as $ded) {
                    $dedVal = $profitAfterDiscount * ($ded->percentage / 100);
                    $totalDeductionsAmount += $dedVal;
                    if ($dedVal > 0) {
                        $deductionRows[] = ['name' => trim($ded->name), 'amount' => $dedVal];
                    }
                }

                $netProfit = $profitAfterDiscount - $totalDeductionsAmount;

                // 1) إنشاء fuel_transaction أولاً للحصول على ID المصدر
                $fuelTxId = \Illuminate\Support\Facades\DB::table('fuel_transactions')->insertGetId([
                    'company_id' => $company->id, 'station_id' => $station->id, 'driver_name' => $driverCar, 'car_number' => '-',
                    'fuel_type' => $itemName, 'liters' => $quantity, 'cash_advance' => $advance, 'market_price_at_time' => $dbPrice,
                    'discount_price_at_time' => $dbPrice, 'total_to_station' => $totalToStation, 'total_on_company' => $totalOnCompany,
                    'ahmed_profit' => $netProfit, 'created_at' => now(),
                ]);

                // 2) ربط أي financial_transaction اتسجلت قبل (سحب العهدة) بالـ fuel_tx
                if ($advanceSource === 'safe' && $advance > 0) {
                    \Illuminate\Support\Facades\DB::table('financial_transactions')
                        ->where('from_account_id', $request->account_id)
                        ->where('amount', $advance)
                        ->where('type', 'expense')
                        ->whereNull('ref_id')
                        ->where('created_at', '>=', now()->subSeconds(5))
                        ->orderByDesc('id')->limit(1)
                        ->update(['ref_type' => 'fuel_transaction', 'ref_id' => $fuelTxId]);
                }

                // 3) إدخال الاستقطاعات في company_debts مع الربط
                foreach ($deductionRows as $row) {
                    \Illuminate\Support\Facades\DB::table('company_debts')->insert([
                        'creditor_name'     => $row['name'],
                        'reason'            => "استقطاع من بنزينة ({$stationName}) | السائق/السيارة: {$driverCar} | شركة النقل: {$companyName}",
                        'total_amount'      => $row['amount'],
                        'paid_amount'       => 0,
                        'remaining_balance' => $row['amount'],
                        'category'          => 'استقطاعات',
                        'source_type'       => 'fuel_transaction',
                        'source_id'         => $fuelTxId,
                        'created_at'        => now(),
                    ]);
                }

                // 4) دين المحطة (الوقود) مربوط بالـ fuel_tx
                if ($totalToStation > 0) {
                    $reasonDetails = "السائق/السيارة: {$driverCar} | " .
                                     "شركة النقل: {$companyName} | " .
                                     "الكمية: {$quantity} لتر | " .
                                     "عهدة نقدية: {$advance} ج | " .
                                     "صافي الربح المُحقق: {$netProfit} ج";

                    \Illuminate\Support\Facades\DB::table('company_debts')->insert([
                        'creditor_name'     => trim($stationName),
                        'reason'            => $reasonDetails,
                        'total_amount'      => $totalToStation,
                        'paid_amount'       => 0,
                        'remaining_balance' => $totalToStation,
                        'category'          => 'وقود',
                        'source_type'       => 'fuel_transaction',
                        'source_id'         => $fuelTxId,
                        'created_at'        => now(),
                    ]);
                }

                // 5) دين شركة النقل في installments
                \Illuminate\Support\Facades\DB::table('installments')->insert([
                    'customer_name' => $companyName, 'customer_phone' => 'بدون', 'product_name' => "بنزينة ({$stationName}) - {$itemName} | {$driverCar}",
                    'cash_price' => ($fuelDebt + $advance + $grossProfit), 'discount' => 0, 'down_payment' => 0,
                    'remaining_after_down' => $totalOnCompany, 'installment_months' => 0, 'interest_rate' => 0, 'total_after_interest' => $totalOnCompany,
                    'monthly_installment' => $totalOnCompany, 'due_day' => date('d'), 'remaining_balance' => $totalOnCompany,
                    'profit' => $netProfit, 'category' => 'بنزينة',
                    'sale_type' => 'fuel',
                    'start_date' => now()->toDateString(), 'created_at' => now(),
                    'fuel_liters'  => $quantity,
                    'cash_custody' => $advance,
                    'source_type'  => 'fuel_transaction',
                    'source_id'    => $fuelTxId,
                ]);

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity(
                        'create',
                        'gas_station',
                        "⛽ تم تسجيل عملية بنزينة لمحطة ({$stationName}) | الكمية: {$quantity} لتر | عهدة: {$advance} ج"
                    );
                }

            });
            return back()->with('success', '✅ تم تسجيل عملية البنزينة بنجاح، وربطها بالديون والاستقطاعات!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}