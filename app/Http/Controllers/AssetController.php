<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetController extends SystemController
{
    /**
     * 💡 دالة ذكية تعمل بصمت لإهلاك الأصول (شهرياً أو سنوياً) بشكل آلي 
     */

    public function manualDepreciate(Request $request)
    {
        $request->validate([
            'asset_id'          => 'required|exists:assets,id',
            'amount'            => 'required|numeric|min:0.01',
            'notes'             => 'required|string|max:255',
            'depreciation_date' => 'required|date', // ✅ إلزامي لأن الـ form يطلبه دائماً
        ]);

        try {
            DB::transaction(function () use ($request) {
                $asset = DB::table('assets')->where('id', $request->asset_id)->lockForUpdate()->first();
                if (!$asset) throw new \Exception('الأصل غير موجود.');

                $amount = floatval($request->amount);
                if ($amount > $asset->current_value) {
                    throw new \Exception('قيمة الإهلاك لا يمكن أن تكون أكبر من القيمة الحالية للأصل!');
                }

                // ✅ التاريخ إلزامي الآن — يُستخدم مباشرة بدون fallback
                $depDate = $request->depreciation_date . ' ' . date('H:i:s');

                DB::table('assets')->where('id', $asset->id)->update([
                    'current_value' => $asset->current_value - $amount,
                    'updated_at'    => now(),
                ]);

                DB::table('financial_transactions')->insert([
                    'type'       => 'general_expense',
                    'amount'     => $amount,
                    'notes'      => "إهلاك أصل ثابت (يدوي): {$asset->name} | {$request->notes}",
                    'status'     => 'active',
                    'created_at' => $depDate, // 🚀 حفظ العملية بالتاريخ المحدد
                ]);

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity('update', 'assets', "📉 إهلاك يدوي لأصل: {$asset->name} بقيمة " . number_format($amount, 2) . " ج");
                }
            });

            return back()->with('success', '✅ تم تسجيل الإهلاك بنجاح وتحديث قيمة الأصل.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
    
    private function runAutoDepreciation()
    {
        $assets = DB::table('assets')
            ->where('status', 'active')
            ->where('current_value', '>', 0)
            ->where('auto_depreciate_percent', '>', 0)
            ->get();

        foreach ($assets as $asset) {
            $lastDate = $asset->last_depreciation_date ? Carbon::parse($asset->last_depreciation_date) : Carbon::parse($asset->created_at);
            $interval = $asset->auto_depreciate_interval ?? 'month';
            
            $passed = 0;
            if ($interval === 'year') {
                $passed = $lastDate->diffInYears(now());
            } else {
                $passed = $lastDate->diffInMonths(now());
            }

            if ($passed >= 1) {
                // حساب قيمة الإهلاك بناءً على المدة الفائتة
                $depAmount = ($asset->purchase_price * $asset->auto_depreciate_percent) / 100;
                $totalDepAmountToApply = $depAmount * $passed;

                if ($totalDepAmountToApply > 0) {
                    if ($asset->current_value - $totalDepAmountToApply < 0) {
                        $totalDepAmountToApply = $asset->current_value; // لا يمكن أن يهلك بالسالب
                    }

                    // 💡 تم إصلاح مسار دالة رأس المال
                    $capitalBefore = \App\Http\Controllers\SystemController::getTotalCapital();
                    DB::table('assets')->where('id', $asset->id)->decrement('current_value', $totalDepAmountToApply);
                    $capitalAfter = \App\Http\Controllers\SystemController::getTotalCapital();

                    $intervalLabel = $interval === 'year' ? 'سنة' : 'شهر';

                DB::table('financial_transactions')->insert([
                        'type'            => 'general_expense',
                        'amount'          => $totalDepAmountToApply,
                        'balance_before'  => $capitalBefore,
                        'balance_after'   => $capitalAfter,
                        // 💡 تم تعديل النص ليحتوي على كلمة "إهلاك أصل ثابت" عشان يسمّع في بند الإهلاكات في الخزنة
                        'notes'           => 'إهلاك أصل ثابت (آلي) لمدة ' . $passed . ' ' . $intervalLabel . ' للأصل: ' . $asset->name . ' بنسبة ' . $asset->auto_depreciate_percent . '%',
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);

                    $newDate = $interval === 'year' ? $lastDate->copy()->addYears($passed) : $lastDate->copy()->addMonths($passed);
                    DB::table('assets')->where('id', $asset->id)->update(['last_depreciation_date' => $newDate]);
                }
            }
        }
    }

    public function assets()
    {
        // تشغيل نظام الإهلاك التلقائي المخفي قبل جلب الداتا
        $this->runAutoDepreciation();

        $accounts   = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $all_assets = DB::table('assets')->orderBy('id', 'desc')->get();

        $assets          = $all_assets->where('status', 'active')->where('current_value', '>', 0);
        $sold_assets     = $all_assets->where('status', 'sold');
        $scrapped_assets = $all_assets->where('status', 'active')->where('current_value', '<=', 0);

        $total_purchase_value = $assets->sum('purchase_price');
        $total_current_value  = $assets->sum('current_value');
        $total_depreciation   = $total_purchase_value - $total_current_value;

        // ⚡ كان N+1: استعلام لكل أصل (N استعلامات × LIKE full-scan).
        // الحل: استعلام واحد بـ subtype index + توزيع في الذاكرة.
        $depreciationRows = DB::table('financial_transactions')
            ->where('type', 'general_expense')
            ->where('subtype', 'depreciation')
            ->orderBy('created_at', 'asc')
            ->get(['amount', 'notes', 'created_at']);

        foreach ($all_assets as $asset) {
            $needle = $asset->name;
            $asset->depreciation_history = $depreciationRows->filter(
                fn($r) => $needle !== '' && mb_strpos($r->notes ?? '', $needle) !== false
            )->values();
        }

        return view('assets', compact(
            'assets', 'sold_assets', 'scrapped_assets', 'accounts',
            'total_purchase_value', 'total_current_value', 'total_depreciation'
        ));
    }

  public function storeAsset(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'purchase_price' => 'required|numeric|min:1',
            'account_id'     => 'nullable|integer',
            'auto_depreciate_percent' => 'nullable|numeric|min:0|max:100',
            'auto_depreciate_interval'=> 'nullable|in:month,year',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $capitalBefore = \App\Http\Controllers\SystemController::getTotalCapital();
                
                if ($request->account_id) {
                    $acc = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                    if (!$acc || $acc->balance < $request->purchase_price) {
                        throw new \Exception('رصيد الخزنة غير كافٍ لشراء الأصل.');
                    }
                    DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $request->purchase_price);
                }

                $autoDep = floatval($request->auto_depreciate_percent ?? 0);
                $interval = $request->auto_depreciate_interval ?? 'month';

                // 💡 تم إزالة حقل category الذي كان يسبب المشكلة
                DB::table('assets')->insert([
                    'name'           => $request->name,
                    'purchase_price' => $request->purchase_price,
                    'current_value'  => $request->purchase_price,
                    'auto_depreciate_percent'  => $autoDep,
                    'auto_depreciate_interval' => $interval,
                    'last_depreciation_date'   => now(),
                    'status'         => 'active',
                    'notes'          => $request->notes,
                    'created_at'     => $request->purchase_date ? \Carbon\Carbon::parse($request->purchase_date) : now(),
                ]);

                $capitalAfter = \App\Http\Controllers\SystemController::getTotalCapital(); 

                if ($request->account_id) {
                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $request->purchase_price,
                        'from_account_id' => $request->account_id,
                        'balance_before'  => $capitalBefore,
                        'balance_after'   => $capitalAfter,
                        'notes'           => 'شراء أصل ثابت: ' . $request->name,
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                }
            });
            return back()->with('success', 'تم تسجيل الأصل وتفعيل نظام إهلاكه بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateAsset(Request $request)
    {
        $request->validate([
            'asset_id'       => 'required|integer',
            'name'           => 'required|string',
            'current_value'  => 'required|numeric|min:0',
            'auto_depreciate_percent' => 'nullable|numeric|min:0|max:100',
            'auto_depreciate_interval'=> 'nullable|in:month,year',
        ]);

        DB::table('assets')->where('id', $request->asset_id)->update([
            'name'           => $request->name,
            'current_value'  => $request->current_value,
            'notes'          => $request->notes,
            'auto_depreciate_percent'  => floatval($request->auto_depreciate_percent ?? 0),
            'auto_depreciate_interval' => $request->auto_depreciate_interval ?? 'month',
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'تم تحديث بيانات الأصل وإعدادات الإهلاك بنجاح.');
    }

    public function depreciateAsset(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer',
            'amount'   => 'required|numeric|min:0.01',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $asset = DB::table('assets')->where('id', $request->asset_id)->lockForUpdate()->first();
                if (!$asset || $asset->current_value < $request->amount) {
                    throw new \Exception('قيمة الإهلاك أكبر من القيمة الحالية للأصل.');
                }

                $capitalBefore = \App\Http\Controllers\SystemController::getTotalCapital();
                DB::table('assets')->where('id', $asset->id)->decrement('current_value', $request->amount);
                $capitalAfter = \App\Http\Controllers\SystemController::getTotalCapital();

                DB::table('financial_transactions')->insert([
                    'type'           => 'general_expense',
                    'amount'         => $request->amount,
                    'balance_before' => $capitalBefore,
                    'balance_after'  => $capitalAfter,
                    'notes'          => 'إهلاك أصل ثابت يدوياً (' . $asset->name . ')' . ($request->notes ? ' - ' . $request->notes : ''),
                    'ref_type'       => 'asset_depreciation',
                    'ref_id'         => $asset->id,
                    'status'         => 'active',
                    'created_at'     => now(),
                ]);
            });
            return back()->with('success', 'تم تسجيل إهلاك الأصل يدوياً بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function sellAsset(Request $request)
    {
        $request->validate([
            'asset_id'   => 'required|integer',
            'sell_price' => 'required|numeric|min:0',
            'account_id' => 'required|integer',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $asset = DB::table('assets')->where('id', $request->asset_id)->lockForUpdate()->first();
                if (!$asset) throw new \Exception('الأصل غير موجود.');

                $sellPrice = floatval($request->sell_price);
                $accId     = $request->account_id;
                $currVal   = floatval($asset->current_value);

                $profit = max(0, $sellPrice - $currVal);
                $loss   = max(0, $currVal - $sellPrice);
                $returnToSafe = min($sellPrice, $currVal);

                $capitalBefore = \App\Http\Controllers\SystemController::getTotalCapital();

                DB::table('assets')->where('id', $asset->id)->update([
                    'status'         => 'sold',
                    'original_value' => $currVal,
                    'selling_price'  => $sellPrice,
                    'current_value'  => 0,
                    'updated_at'     => now()
                ]);

                DB::table('accounts')->where('id', $accId)->increment('balance', $sellPrice);
                
                $capitalAfter = \App\Http\Controllers\SystemController::getTotalCapital();

                // 💡 تسجيل عملية بيع الأصل في ريكورد واحد شامل (إجمالي المبلغ المحصل للخزنة + حفظ القيمة الدفترية وصافي الربح/الخسارة)
                $netDiff = round($sellPrice - $currVal, 2); // موجب = ربح، سالب = خسارة، صفر = بنفس القيمة
                if ($profit > 0) {
                    $txType    = 'income';
                    $txSubtype = 'asset_sale_gain';
                    $txNotes   = 'بيع أصل ثابت: ' . $asset->name
                               . ' (سعر البيع: ' . number_format($sellPrice, 0) . ' ج'
                               . ' | القيمة الدفترية: ' . number_format($currVal, 0) . ' ج'
                               . ' | الربح: +' . number_format($profit, 0) . ' ج)';
                } elseif ($loss > 0) {
                    $txType    = 'settlement';
                    $txSubtype = 'asset_sale_loss';
                    $txNotes   = 'بيع أصل ثابت (بخسارة): ' . $asset->name
                               . ' (سعر البيع: ' . number_format($sellPrice, 0) . ' ج'
                               . ' | القيمة الدفترية: ' . number_format($currVal, 0) . ' ج'
                               . ' | الخسارة: -' . number_format($loss, 0) . ' ج)';
                } else {
                    $txType    = 'settlement';
                    $txSubtype = 'asset_sale';
                    $txNotes   = 'بيع أصل ثابت (بنفس القيمة الدفترية): ' . $asset->name
                               . ' (' . number_format($sellPrice, 0) . ' ج)';
                }

                DB::table('financial_transactions')->insert([
                    'type'           => $txType,
                    'subtype'        => $txSubtype,
                    'amount'         => $sellPrice,
                    'to_account_id'  => $accId,
                    'balance_before' => $capitalBefore,
                    'balance_after'  => $capitalAfter,
                    'total_before'   => $currVal,
                    'total_after'    => $netDiff,
                    'notes'          => $txNotes,
                    'ref_type'       => 'asset_sale',
                    'ref_id'         => $asset->id,
                    'status'         => 'active',
                    'created_at'     => now(),
                ]);
            });

            return back()->with('success', 'تم بيع الأصل وتوثيق الأرباح/الخسائر بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyAsset(Request $request)
    {
        DB::table('assets')->where('id', $request->asset_id)->delete();
        return back()->with('success', 'تم حذف الأصل نهائياً من السجلات.');
    }

    /**
     * عكس أثر بيع الأصل الثابت أو إهلاكه اليدوي عند إلغاء/حذف الحركة المالية من سجل العمليات
     */
    public static function reverseAssetTransactionIfApplicable(object $tx, string $cancelReason = 'إلغاء بيع الأصل المرتبط'): void
    {
        $refType = $tx->ref_type ?? '';
        $notes   = trim((string) ($tx->notes ?? ''));

        // 1. حالة إلغاء إهلاك يدوي لأصل ثابت
        if ($refType === 'asset_depreciation' || str_starts_with($notes, 'إهلاك أصل ثابت يدوياً (')) {
            $asset = null;
            if (!empty($tx->ref_id)) {
                $asset = DB::table('assets')->where('id', $tx->ref_id)->lockForUpdate()->first();
            }
            if (!$asset && preg_match('/^إهلاك أصل ثابت يدوياً \((.+?)\)/u', $notes, $m)) {
                $asset = DB::table('assets')->where('name', trim($m[1]))->orderBy('id', 'desc')->lockForUpdate()->first();
            }
            if ($asset) {
                DB::table('assets')->where('id', $asset->id)->increment('current_value', (float) $tx->amount);
            }
            return;
        }

        // 2. حالة إلغاء عملية بيع أصل ثابت (استرداد القيمة أو الربح أو الخسارة)
        $salePrefixes = [
            'استرداد قيمة أصل مباع: ',
            'أرباح بيع أصل ثابت: ',
            'استرداد جزء من أصل مباع (بخسارة): ',
            'خسارة بيع أصل ثابت: ',
            'استرداد قيمة أصل مباع (بنفس القيمة الدفترية): ',
        ];

        $matchedAssetName = null;
        foreach ($salePrefixes as $prefix) {
            if (str_starts_with($notes, $prefix)) {
                $matchedAssetName = trim(mb_substr($notes, mb_strlen($prefix)));
                break;
            }
        }

        if ($refType !== 'asset_sale' && $matchedAssetName === null) {
            return;
        }

        $asset = null;
        if (!empty($tx->ref_id)) {
            $asset = DB::table('assets')->where('id', $tx->ref_id)->lockForUpdate()->first();
        }
        if (!$asset && $matchedAssetName !== null) {
            $asset = DB::table('assets')
                ->where('name', $matchedAssetName)
                ->orderByRaw("CASE WHEN status = 'sold' THEN 0 ELSE 1 END")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();
        }

        // جلب الحركات الشقيقة المرتبطة بنفس عملية بيع الأصل (سواء نشطة لإلغائها أو ملغاة لحساب القيمة الدفترية)
        $siblingsQuery = DB::table('financial_transactions')->where('id', '!=', $tx->id);
        if ($asset && $refType === 'asset_sale' && !empty($tx->ref_id)) {
            $siblingsQuery->where('ref_type', 'asset_sale')->where('ref_id', $asset->id);
        } elseif ($matchedAssetName !== null) {
            $siblingsQuery->where(function ($q) use ($salePrefixes, $matchedAssetName) {
                foreach ($salePrefixes as $pfx) {
                    $q->orWhere('notes', $pfx . $matchedAssetName);
                }
            });
            if (!empty($tx->created_at)) {
                $ts = strtotime($tx->created_at);
                $siblingsQuery->whereBetween('created_at', [date('Y-m-d H:i:s', $ts - 120), date('Y-m-d H:i:s', $ts + 120)]);
            }
        } else {
            return;
        }

        $siblings = $siblingsQuery->lockForUpdate()->get();

        // عكس أثر أي حركة شقيقة ما زالت نشطة (مثل حركة استرداد الأصل عند حذف حركة الربح أو العكس)
        foreach ($siblings as $sib) {
            if ($sib->status === 'active') {
                if ($sib->to_account_id && in_array($sib->type, ['income', 'settlement'])) {
                    DB::table('accounts')->where('id', $sib->to_account_id)->decrement('balance', (float) $sib->amount);
                }
                DB::table('financial_transactions')->where('id', $sib->id)->update([
                    'status'        => 'cancelled',
                    'cancelled_at'  => now(),
                    'cancel_reason' => $cancelReason,
                    'updated_at'    => now(),
                ]);
            }
        }

        // إعادة الأصل المباع إلى حالته النشطة وقيمته الدفترية قبل البيع
        if ($asset && $asset->status === 'sold') {
            $restoredValue = (float) ($tx->total_before ?? 0);
            if ($restoredValue <= 0) {
                $restoredValue = (float) ($asset->original_value ?? 0);
            }
            if ($restoredValue <= 0) {
                $allSaleTxs = collect([$tx])->merge($siblings);
                $settlementSum = (float) $allSaleTxs->where('type', 'settlement')->sum('amount');
                $lossSum       = (float) $allSaleTxs->where('type', 'general_expense')->sum('amount');
                $restoredValue = $settlementSum + $lossSum;
            }
            if ($restoredValue <= 0) {
                $restoredValue = (float) ($asset->purchase_price ?? 0);
            }

            DB::table('assets')->where('id', $asset->id)->update([
                'status'        => 'active',
                'current_value' => $restoredValue,
                'selling_price' => 0,
                'updated_at'    => now(),
            ]);
        }
    }
}