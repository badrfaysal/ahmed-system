<?php

namespace App\Http\Controllers;

use App\Services\InstallmentFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PartnerController extends SystemController
{
    public function partners()
    {
        $partners = DB::table('partners')->get();
        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        $total_partners_capital = $partners->sum('capital');
        $total_liquidity        = $accounts->sum('balance');

        $summary = InstallmentFinanceService::treasurySummary();
        $real_collected_profit = $summary['realCollectedProfit'];
        $total_distributed_so_far = $summary['totalDistributedProfits'];
        $estimated_net_profit = $summary['remainingCompanyProfit'];
        $system_capital = $summary['capital'];

        return view('partners', compact('partners', 'accounts', 'total_partners_capital', 'total_liquidity', 'real_collected_profit', 'total_distributed_so_far', 'estimated_net_profit', 'system_capital'));
    }

    public function distributeProfit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        try {
            $amount = floatval($request->amount);

            DB::transaction(function () use ($amount) {
                $summary = InstallmentFinanceService::treasurySummary();
                $availableToDistribute = $summary['remainingCompanyProfit'];

                if ($amount <= 0) {
                    throw new \Exception('يجب إدخال مبلغ أكبر من صفر.');
                }
                if ($amount > $availableToDistribute + 0.01) {
                    throw new \Exception('المبلغ المطلوب توزيعه أكبر من الأرباح المتاحة للتوزيع حالياً (' . number_format($availableToDistribute, 2) . ' ج).');
                }

                $partners = DB::table('partners')->get();
                $totalCapital = $partners->sum('capital');
                if ($totalCapital <= 0) {
                    throw new \Exception('لا يوجد رأس مال مسجل للشركاء لحساب النسب.');
                }

                foreach ($partners as $partner) {
                    $share = round(($partner->capital / $totalCapital) * $amount, 2);
                    if ($share <= 0) {
                        continue;
                    }

                    DB::table('partners')->where('id', $partner->id)->increment('profit_wallet', $share);

                    DB::table('financial_transactions')->insert([
                        'type'       => 'profit_distribution',
                        'amount'     => $share,
                        'notes'      => '📊 توزيع أرباح للشريك: ' . $partner->name,
                        'status'     => 'active',
                        'created_at' => now(),
                    ]);
                }
            });

            return back()->with('success', 'تم توزيع الأرباح على محافظ الشركاء بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function exitPartner(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'account_id' => 'required|exists:accounts,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $partner = DB::table('partners')->where('id', $request->partner_id)->lockForUpdate()->first();
                if (!$partner) {
                    throw new \Exception('الشريك غير موجود.');
                }

                $capital = floatval($partner->capital);
                $profit  = floatval($partner->profit_wallet ?? 0);
                $total   = $capital + $profit;

                if ($total > 0) {
                    $account = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                    if (!$account || floatval($account->balance) < $total) {
                        throw new \Exception('رصيد الخزنة المختارة لا يكفي لصرف مستحقات التخارج.');
                    }

                    DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $total);
                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $total,
                        'from_account_id' => $request->account_id,
                        'notes'           => 'تصفية وتخارج الشريك: ' . $partner->name . " (رأس مال: {$capital} + أرباح: {$profit})",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                }

                DB::table('partners')->where('id', $partner->id)->delete();
            });

            return back()->with('success', 'تم تخارج الشريك وصرف مستحقاته بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // إضافة شريك جديد (وإيداع رأس ماله في الخزنة)
    public function storePartner(Request $request)
    {
        DB::transaction(function () use ($request) {
            $capital = floatval($request->capital);
            $accId = $request->account_id;

            DB::table('partners')->insert([
                'name' => $request->name,
                'capital' => $capital,
                'created_at' => now(),
            ]);

            if ($capital > 0 && $accId) {
                DB::table('accounts')->where('id', $accId)->increment('balance', $capital);
                DB::table('financial_transactions')->insert([
                    'type' => 'income', 'amount' => $capital, 'to_account_id' => $accId,
                    'notes' => 'إيداع رأس مال شريك جديد: ' . $request->name, 'created_at' => now(),
                ]);
            }
        });
        return back()->with('success', 'تم تسجيل الشريك وإيداع رأس المال بنجاح.');
    }

    // سحب أرباح الشريك كاش (تقل الأرباح ولا تتأثر نسبته)
    public function withdrawProfit(Request $request)
    {
        DB::transaction(function () use ($request) {
            $amount = floatval($request->amount);
            $accId = $request->account_id;
            $partner = DB::table('partners')->where('id', $request->partner_id)->lockForUpdate()->first();

            if ($amount <= 0 || $amount > floatval($partner->profit_wallet ?? 0)) {
                throw new \Exception('المبلغ أكبر من الأرباح المتاحة في محفظة الشريك.');
            }

            DB::table('partners')->where('id', $partner->id)->decrement('profit_wallet', $amount);

            // خصم من الخزنة
            DB::table('accounts')->where('id', $accId)->decrement('balance', $amount);

            // تسجيلها كمسحوبات شركاء (لتقليل صافي الربح المتاح للتوزيع)
            DB::table('financial_transactions')->insert([
                'type' => 'partner_withdrawal', 'amount' => $amount, 'from_account_id' => $accId,
                'notes' => 'سحب أرباح للشريك: ' . $partner->name, 'created_at' => now(),
            ]);
        });
        return back()->with('success', 'تم صرف الأرباح للشريك بنجاح وخصمها من الخزنة.');
    }

    // رسملة الأرباح (تضاف لرأس ماله فتزيد نسبته في الشركة)
    public function reinvestProfit(Request $request)
    {
        DB::transaction(function () use ($request) {
            $amount = floatval($request->amount);
            $partner = DB::table('partners')->where('id', $request->partner_id)->lockForUpdate()->first();

            if ($amount <= 0 || $amount > floatval($partner->profit_wallet ?? 0)) {
                throw new \Exception('المبلغ أكبر من الأرباح المتاحة في محفظة الشريك.');
            }

            DB::table('partners')->where('id', $partner->id)->decrement('profit_wallet', $amount);

            // 1. زيادة رأس مال الشريك
            DB::table('partners')->where('id', $partner->id)->increment('capital', $amount);

            if (Schema::hasColumn('partners', 'last_reinvest_year')) {
                DB::table('partners')->where('id', $partner->id)->update(['last_reinvest_year' => (int) date('Y')]);
            }

            // 2. تسجيلها كمسحوبات أرباح (بدون خصم كاش من الخزنة لأن الفلوس فضلت في الشركة)
            DB::table('financial_transactions')->insert([
                'type' => 'partner_withdrawal', 'amount' => $amount,
                'notes' => 'رسملة أرباح (إضافة لرأس المال) للشريك: ' . $partner->name, 'created_at' => now(),
            ]);
        });
        return back()->with('success', 'تم إضافة الأرباح لرأس مال الشريك بنجاح، وتم إعادة ضبط النسب المئوية للملاك.');
    }
}
