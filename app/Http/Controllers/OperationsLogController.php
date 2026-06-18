<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\AuditService;

class OperationsLogController extends SystemController
{

    /**
     * شاشة سجل العمليات الموحدة (قراءة فقط).
     * تجمع العمليات من 4 مصادر:
     *  - fuel_transactions       (بنزينة)
     *  - sales                   (توريد مخزن)
     *  - inventory_movements     (إهلاك/هالك/نقل/مرتجعات)
     *  - installments (cash)     (بيع مباشر/خدمات/مبيعات مخزن — استثناء التقسيط والبنزينة)
     */
    public function index(Request $request)
    {
        $type     = $request->input('type', 'all');
        $period   = $request->input('period', 'today');
        $search   = trim((string) $request->input('search', ''));
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        [$start, $end] = $this->resolvePeriod($period, $fromDate, $toDate);

        $rows = collect();

        // 1) البنزينة
        if (in_array($type, ['all', 'fuel'])) {
            $q = DB::table('fuel_transactions as f')
                ->leftJoin('transport_companies as tc', 'f.company_id', '=', 'tc.id')
                ->leftJoin('gas_stations as gs', 'f.station_id', '=', 'gs.id')
                ->select(
                    'f.id', 'f.liters', 'f.cash_advance', 'f.fuel_type',
                    'f.total_on_company', 'f.total_to_station', 'f.ahmed_profit',
                    'f.driver_name', 'f.created_at',
                    'f.supersedes', 'f.superseded_by', 'f.superseded_at',
                    DB::raw('tc.company_name as company_name'),
                    DB::raw('gs.station_name as station_name')
                );
            if ($start && $end) $q->whereBetween('f.created_at', [$start, $end]);
            if ($search !== '') {
                $q->where(function ($x) use ($search) {
                    $x->where('tc.company_name', 'LIKE', "%{$search}%")
                      ->orWhere('gs.station_name', 'LIKE', "%{$search}%")
                      ->orWhere('f.driver_name', 'LIKE', "%{$search}%")
                      ->orWhere('f.fuel_type', 'LIKE', "%{$search}%");
                });
            }
            foreach ($q->orderByDesc('f.created_at')->limit(500)->get() as $r) {
                $rows->push([
                    'type'        => 'fuel',
                    'type_label'  => 'بنزينة',
                    'icon'        => 'fa-gas-pump',
                    'color'       => 'warning',
                    'id'          => $r->id,
                    'date'        => $r->created_at,
                    'title'       => "محطة {$r->station_name} — شركة {$r->company_name}",
                    'description' => "السائق: {$r->driver_name} | {$r->fuel_type} | لتر: " . rtrim(rtrim(number_format((float)$r->liters,2,'.',''),'0'),'.') . " | عهدة: " . number_format((float)$r->cash_advance,0) . " ج",
                    'amount'      => $r->total_on_company,
                    'profit'      => $r->ahmed_profit,
                    'editable'    => empty($r->superseded_by),
                    'editor'      => 'fuel',
                    'is_edit'     => !empty($r->supersedes),
                    'supersedes'  => $r->supersedes,
                    'superseded_by' => $r->superseded_by,
                    'superseded_at' => $r->superseded_at,
                    'is_cancelled'  => false,
                ]);
            }
        }

        // 2) توريد المخزن (sales) — فقط ما أضيف للمخزن فعلاً، وليس البيع المباشر
        if (in_array($type, ['all', 'inventory_purchase'])) {
            $q = DB::table('sales')->where('inventory_status', 'to_inventory');
            if ($start && $end) $q->whereBetween('created_at', [$start, $end]);
            if ($search !== '') {
                $q->where(function ($x) use ($search) {
                    $x->where('product_name', 'LIKE', "%{$search}%")
                      ->orWhere('supplier_name', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
                });
            }
            foreach ($q->orderByDesc('created_at')->limit(500)->get() as $r) {
                $rows->push([
                    'type'        => 'inventory_purchase',
                    'type_label'  => 'توريد مخزن',
                    'icon'        => 'fa-truck-ramp-box',
                    'color'       => 'primary',
                    'id'          => $r->id,
                    'date'        => $r->created_at,
                    'title'       => "{$r->product_name} — مورد: {$r->supplier_name}",
                    'description' => "الفئة: {$r->category} | الكمية: {$r->quantity} | شراء: " . number_format((float)$r->purchase_price,0) . " | بيع: " . number_format((float)$r->selling_price,0),
                    'amount'      => (float)$r->purchase_price * (float)$r->quantity,
                    'profit'      => null,
                    'editable'    => empty($r->last_edited_at),
                    'editor'      => 'inventory_purchase',
                    'is_edit'     => false,
                    'supersedes'  => null,
                    'superseded_by'=> null,
                    'superseded_at'=> $r->last_edited_at ?? null,
                    'is_cancelled'=> false,
                ]);
            }
        }

        // 3) حركات المخزن (إهلاك/هالك/نقل/مرتجعات)
        if (in_array($type, ['all', 'inventory_movement'])) {
            $q = DB::table('inventory_movements as m')
                ->leftJoin('sales as s', 'm.sale_id', '=', 's.id')
                ->select('m.*', DB::raw('s.product_name as product_name'), DB::raw('s.supplier_name as supplier_name'));
            if ($start && $end) $q->whereBetween('m.created_at', [$start, $end]);
            if ($search !== '') {
                $q->where(function ($x) use ($search) {
                    $x->where('s.product_name', 'LIKE', "%{$search}%")
                      ->orWhere('m.notes', 'LIKE', "%{$search}%")
                      ->orWhere('m.type', 'LIKE', "%{$search}%");
                });
            }
            foreach ($q->orderByDesc('m.created_at')->limit(500)->get() as $r) {
                $typeLabel = match($r->type) {
                    'damage', 'damaged', 'هالك' => 'هالك/إهلاك',
                    'supplier_return' => 'مرتجع لمورد',
                    'customer_return' => 'مرتجع من عميل',
                    'transfer' => 'نقل بين المخازن',
                    'adjustment' => 'تسوية مخزن',
                    default => $r->type,
                };
                $rows->push([
                    'type'        => 'inventory_movement',
                    'type_label'  => $typeLabel,
                    'icon'        => 'fa-arrows-up-down',
                    'color'       => 'danger',
                    'id'          => $r->id,
                    'date'        => $r->created_at,
                    'title'       => $r->product_name ?? '—',
                    'description' => "النوع: {$typeLabel} | الكمية: {$r->quantity} | " . ($r->notes ?? ''),
                    'amount'      => null,
                    'profit'      => null,
                    'editable'    => true,
                    'editor'      => 'inventory_movement',
                    'is_edit'     => false,
                    'supersedes'  => null,
                    'superseded_by'=> null,
                    'superseded_at'=> null,
                    'is_cancelled'=> false,
                ]);
            }
        }

        // 4) عمليات البيع (cash من installments — استثناء التقسيط والبنزينة، يشمل الملغاة عشان نظهرها)
        if (in_array($type, ['all', 'sale_cash'])) {
            $q = DB::table('installments')
                ->where('installment_months', 0)
                ->where(function ($x) {
                    $x->where('category', '!=', 'بنزينة')->orWhereNull('category');
                })
                ->where(function ($x) {
                    $x->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
                });
            if ($start && $end) $q->whereBetween('created_at', [$start, $end]);
            if ($search !== '') {
                $q->where(function ($x) use ($search) {
                    $x->where('customer_name', 'LIKE', "%{$search}%")
                      ->orWhere('product_name', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
                });
            }
            foreach ($q->orderByDesc('created_at')->limit(500)->get() as $r) {
                $editor = match(true) {
                    $r->category === 'خدمات'              => 'service',
                    $r->category === 'مبيعات مخزن' || $r->sale_type === 'inventory' => 'sale_inventory',
                    default                               => 'sale_direct',
                };
                $typeLabel = match($editor) {
                    'service'        => 'خدمة',
                    'sale_inventory' => 'بيع من المخزن',
                    default          => 'بيع مباشر',
                };
                $color = match($editor) {
                    'service'        => 'info',
                    'sale_inventory' => 'success',
                    default          => 'success',
                };
                $isCancelled = ($r->status === 'cancelled') || !empty($r->cancelled_via_oplog_at);
                $rows->push([
                    'type'        => 'sale_cash',
                    'type_label'  => $typeLabel,
                    'icon'        => 'fa-cash-register',
                    'color'       => $color,
                    'id'          => $r->id,
                    'date'        => $r->created_at,
                    'title'       => "{$r->product_name} — عميل: {$r->customer_name}",
                    'description' => "السعر: " . number_format((float)$r->cash_price,0) . " | الربح: " . number_format((float)$r->profit,0),
                    'amount'      => $r->cash_price,
                    'profit'      => $r->profit,
                    'editable'    => !$isCancelled,
                    'editor'      => $editor,
                    'is_edit'     => false,
                    'supersedes'  => null,
                    'superseded_by'=> null,
                    'superseded_at'=> $r->cancelled_via_oplog_at ?? null,
                    'is_cancelled'=> $isCancelled,
                ]);
            }
        }

        // 5) المصروفات (financial_transactions type=general_expense / salary_expense / expense — مع استثناء عهد الوقود و الإيرادات المرتبطة)
        if (in_array($type, ['all', 'expense'])) {
            $q = DB::table('financial_transactions as ft')
                ->leftJoin('accounts as a', 'ft.from_account_id', '=', 'a.id')
                ->whereIn('ft.type', ['general_expense', 'salary_expense', 'expense'])
                ->where('ft.status', 'active')
                ->where(function ($x) {
                    $x->whereNull('ft.ref_type')->orWhere('ft.ref_type', '!=', 'fuel_transaction');
                })
                ->select('ft.*', DB::raw('a.account_name as account_name'));
            if ($start && $end) $q->whereBetween('ft.created_at', [$start, $end]);
            if ($search !== '') {
                $q->where(function ($x) use ($search) {
                    $x->where('ft.notes', 'LIKE', "%{$search}%")
                      ->orWhere('a.account_name', 'LIKE', "%{$search}%");
                });
            }
            foreach ($q->orderByDesc('ft.created_at')->limit(500)->get() as $r) {
                $category = 'مصروفات تشغيلية';
                $actualNote = $r->notes ?? '';
                if ($r->type === 'salary_expense') $category = 'رواتب وأجور';
                elseif ($r->type === 'discount')   $category = 'خصومات للعملاء';
                elseif (preg_match('/^\[(.*?)\]\s*(.*)/u', $actualNote, $m)) {
                    $category = $m[1];
                    $actualNote = $m[2];
                }
                $rows->push([
                    'type'        => 'expense',
                    'type_label'  => 'مصروف',
                    'icon'        => 'fa-receipt',
                    'color'       => 'danger',
                    'id'          => $r->id,
                    'date'        => $r->created_at,
                    'title'       => "{$category} — " . ($actualNote ?: 'بدون بيان'),
                    'description' => "خزنة: " . ($r->account_name ?? 'غير محدد'),
                    'amount'      => $r->amount,
                    'profit'      => null,
                    'editable'    => true,
                    'editor'      => 'expense',
                    'is_edit'     => false,
                    'supersedes'  => null,
                    'superseded_by'=> null,
                    'superseded_at'=> null,
                    'is_cancelled'=> false,
                ]);
            }
        }

        // ترتيب موحد بالتاريخ
        $operations = $rows->sortByDesc('date')->values();

        // إحصائيات سريعة
        $stats = [
            'total' => $operations->count(),
            'fuel'  => $operations->where('type', 'fuel')->count(),
            'inv'   => $operations->whereIn('type', ['inventory_purchase', 'inventory_movement'])->count(),
            'sales' => $operations->where('type', 'sale_cash')->count(),
        ];

        return view('operations_log', compact(
            'operations', 'stats', 'type', 'period', 'search', 'fromDate', 'toDate'
        ));
    }

    /**
     * يعرض بيانات عملية للتعديل (JSON).
     */
    public function show(Request $request, string $editor, int $id)
    {
        if (!$this->verifyAdminPin($request)) {
            return response()->json(['error' => 'الرمز السري غير صحيح'], 403);
        }

        switch ($editor) {
            case 'fuel':                return $this->showFuel($id);
            case 'inventory_purchase':  return $this->showInventoryPurchase($id);
            case 'sale_inventory':
            case 'sale_direct':
            case 'service':             return $this->showSaleCash($id, $editor);
            case 'sale_return':         return $this->showSaleReturn($id);
            case 'expense':             return $this->showExpense($id);
            default: return response()->json(['error' => "نوع عملية غير مدعوم بعد للتعديل: {$editor}."], 400);
        }
    }

    /**
     * يحذف عملية بالكامل (للبنزينة والمصروفات).
     */
    public function destroy(Request $request, string $editor, int $id)
    {
        if (!$this->verifyAdminPin($request)) {
            return back()->with('error', 'الرمز السري غير صحيح.');
        }

        try {
            switch ($editor) {
                case 'service':
                    return back()->with('error', '⛔ عمليات الخدمات لا يمكن حذفها — لأنها مرتبطة بمعادلة رأس المال. لو في خطأ في العملية تواصل مع المسؤول.');
                case 'fuel':    return $this->destroyFuel($id);
                case 'expense': return $this->destroyExpense($id);
                default:
                    return back()->with('error', "حذف عمليات [{$editor}] غير متاح.");
            }
        } catch (\Throwable $e) {
            return back()->with('error', '❌ فشل الحذف: ' . $e->getMessage());
        }
    }

    /**
     * يحدّث عملية بإعادة بناء الأثر بالكامل (reverse القديم + redo الجديد).
     */
    public function update(Request $request, string $editor, int $id)
    {
        $wantsJson = $request->expectsJson() || $request->ajax() || $editor === 'sale_return';

        if (!$this->verifyAdminPin($request)) {
            if ($wantsJson) {
                return response()->json(['error' => 'الرمز السري غير صحيح.'], 403);
            }
            return back()->with('error', 'الرمز السري غير صحيح.');
        }

        try {
            switch ($editor) {
                case 'fuel':
                    return $this->updateFuel($request, $id);
                case 'inventory_purchase':
                    return $this->updateInventoryPurchase($request, $id);
                case 'service':
                case 'sale_inventory':
                case 'sale_direct':
                    return $this->cancelSaleCash($request, $id);
                case 'sale_return':
                    return $this->returnSaleCash($request, $id);
                case 'expense':
                    return $this->updateExpense($request, $id);
                default:
                    if ($wantsJson) {
                        return response()->json(['error' => "تعديل عمليات [{$editor}] لسه مش متاح."], 400);
                    }
                    return back()->with('error', "تعديل عمليات [{$editor}] لسه مش متاح.");
            }
        } catch (\Throwable $e) {
            if ($wantsJson) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', '❌ فشل التعديل: ' . $e->getMessage());
        }
    }

    // ───────────────────────────────────────────────────────────────────────────
    // البنزينة
    // ───────────────────────────────────────────────────────────────────────────

    private function showFuel(int $id)
    {
        $tx = DB::table('fuel_transactions')->where('id', $id)->first();
        if (!$tx) return response()->json(['error' => 'عملية غير موجودة'], 404);

        // العثور على الـ financial_transaction المرتبطة بسحب العهدة (لو موجود)
        $advanceFt = DB::table('financial_transactions')
            ->where('ref_type', 'fuel_transaction')
            ->where('ref_id', $id)
            ->where('type', 'expense')
            ->first();

        $advanceSource = 'none';
        $accountId = null;
        if ($advanceFt) {
            $advanceSource = 'safe';
            $accountId = $advanceFt->from_account_id;
        } elseif ($tx->cash_advance > 0) {
            // عهدة من المحطة
            $advanceSource = 'station';
        }

        $companies = DB::table('transport_companies')->get();
        $stations  = DB::table('gas_stations')->get();
        $items     = DB::table('items')->get();
        $accounts  = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        $hasPaidDebts = DB::table('company_debts')
            ->where('source_type', 'fuel_transaction')
            ->where('source_id', $id)
            ->where('paid_amount', '>', 0)
            ->exists();

        if (!$hasPaidDebts) {
            $oldInst = DB::table('installments')
                ->where('source_type', 'fuel_transaction')
                ->where('source_id', $id)
                ->first();
            if ($oldInst) {
                $hasPaidDebts = DB::table('installment_payments')
                    ->where('installment_id', $oldInst->id)
                    ->exists();
            }
        }

        return response()->json([
            'tx'             => $tx,
            'advance_source' => $advanceSource,
            'account_id'     => $accountId,
            'companies'      => $companies,
            'stations'       => $stations,
            'items'          => $items,
            'accounts'       => $accounts,
            'has_paid_debts' => $hasPaidDebts,
        ]);
    }

    private function updateFuel(Request $request, int $id)
    {
        $request->validate([
            'company_id'      => 'required|integer',
            'station_id'      => 'required|integer',
            'driver_car'      => 'nullable|string|max:120',
            'item_id'         => 'required',                 // 0 = بدون وقود
            'quantity'        => 'nullable|numeric|min:0',
            'advance_source'  => 'required|in:none,safe,station',
            'cash_advance'    => 'nullable|numeric|min:0',
            'account_id'      => 'required_if:advance_source,safe',
            'manual_discount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {
            $old = DB::table('fuel_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$old) throw new \Exception('العملية غير موجودة.');

            // منع التعديل المتكرر
            if (!empty($old->superseded_by)) {
                throw new \Exception("هذه العملية اتعدلت قبل كده — راجع التعديل في عملية #{$old->superseded_by}.");
            }

            // 📝 احفظ snapshot
            $user = session('auth_user');
            $revisionId = DB::table('operation_revisions')->insertGetId([
                'source_type'    => 'fuel_transaction',
                'source_id'      => $id,
                'action'         => 'edit',
                'snapshot_before'=> json_encode((array)$old, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => 'تعديل عملية بنزينة من سجل العمليات',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // ═══════════════════════════════════════════════════
            // (1) عكس الأثر القديم بالكامل
            // ═══════════════════════════════════════════════════

            // أ) إرجاع العهدة المسحوبة من الخزنة (لو كان عندنا financial_transaction expense مربوطة)
            $oldExpense = DB::table('financial_transactions')
                ->where('ref_type', 'fuel_transaction')->where('ref_id', $id)
                ->where('type', 'expense')->first();
            if ($oldExpense) {
                DB::table('accounts')->where('id', $oldExpense->from_account_id)->increment('balance', $oldExpense->amount);
                DB::table('financial_transactions')->where('id', $oldExpense->id)->delete();
            }

            // ب) حذف الديون المرتبطة (الوقود + الاستقطاعات)
            // التحقق إن المستحقات لسه مش مدفوعة (لو فيها سداد، نمنع التعديل)
            $hasPaidDebts = DB::table('company_debts')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)
                ->where('paid_amount', '>', 0)->exists();
            if ($hasPaidDebts) {
                throw new \Exception('لا يمكن التعديل: في مستحقات مرتبطة بهذه العملية تم سدادها جزئياً أو كلياً. الغِ السداد أولاً.');
            }
            DB::table('company_debts')->where('source_type', 'fuel_transaction')->where('source_id', $id)->delete();

            // ج) حذف الـ installment (دين شركة النقل)
            $oldInst = DB::table('installments')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)->first();
            if ($oldInst) {
                $hasPayments = DB::table('installment_payments')->where('installment_id', $oldInst->id)->exists();
                if ($hasPayments) {
                    throw new \Exception('لا يمكن التعديل: شركة النقل سددت جزء من المستحقات. الغِ السداد أولاً.');
                }
                DB::table('installments')->where('id', $oldInst->id)->delete();
            }

            // د) نسيب fuel_transaction الأصلية (للأرشيف) - بنعلمها بـ superseded_by بعد إنشاء الجديدة

            // ═══════════════════════════════════════════════════
            // (2) إعادة الإدخال بالقيم الجديدة
            // ═══════════════════════════════════════════════════

            $company = DB::table('transport_companies')->where('id', $request->company_id)->first();
            $station = DB::table('gas_stations')->where('id', $request->station_id)->first();
            if (!$company || !$station) throw new \Exception('الشركة أو المحطة غير موجودة.');

            $companyName = $company->company_name ?? $company->name;
            $stationName = $station->station_name ?? $station->name;
            $driverCar   = $request->driver_car ?: 'غير محدد';
            $itemName = 'وقود فقط (بدون عهدة)';
            $dbPrice = 0;

            if ($request->item_id != "0") {
                $item = DB::table('items')->where('id', $request->item_id)->first();
                if (!$item) throw new \Exception('الصنف غير موجود.');
                $itemName = $item->item_name ?? $item->name;
                $dbPrice  = floatval($item->original_price ?? $item->price ?? 0);
                if ($dbPrice == 0) $dbPrice = (float) \App\Services\SystemSetting::get('default_fuel_price', 20);
            }

            $advanceSource  = $request->advance_source;
            $quantity       = ($request->item_id == "0") ? 0 : floatval($request->quantity ?? 0);
            $advance        = ($advanceSource === 'none') ? 0 : floatval($request->cash_advance ?? 0);
            $fuelDebt       = $quantity * $dbPrice;
            $manualDiscount = floatval($request->manual_discount ?? 0);

            if ($fuelDebt <= 0 && $advance <= 0) {
                throw new \Exception('⚠️ لا يمكن حفظ عملية فارغة — أدخل كمية وقود أو عهدة على الأقل.');
            }

            $profitRate = (float) \App\Services\SystemSetting::get('fuel_profit_rate', 0.01);

            if ($advanceSource === 'none') {
                $grossProfit    = $fuelDebt * $profitRate;
                $totalToStation = $fuelDebt;
            } elseif ($advanceSource === 'station') {
                $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                $totalToStation = $fuelDebt + $advance;
            } else { // safe
                $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                $totalToStation = $fuelDebt;
            }

            $totalOnCompany      = $fuelDebt + $advance + $grossProfit;
            $profitAfterDiscount = $grossProfit - $manualDiscount;

            $deductions = DB::table('fuel_deductions')->get();
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

            // أنشئ fuel_transaction جديد مع ربط الـ supersedes
            $newFuelTxId = DB::table('fuel_transactions')->insertGetId([
                'company_id' => $company->id, 'station_id' => $station->id, 'driver_name' => $driverCar, 'car_number' => '-',
                'fuel_type' => $itemName, 'liters' => $quantity, 'cash_advance' => $advance, 'market_price_at_time' => $dbPrice,
                'discount_price_at_time' => $dbPrice, 'total_to_station' => $totalToStation, 'total_on_company' => $totalOnCompany,
                'ahmed_profit' => $netProfit, 'created_at' => now(),
                'supersedes' => $id,
            ]);

            // علِّم الأصلية كـ "تم تعديلها"
            DB::table('fuel_transactions')->where('id', $id)->update([
                'superseded_by' => $newFuelTxId,
                'superseded_at' => now(),
            ]);

            // حدّث revision بـ new_source_id
            DB::table('operation_revisions')->where('id', $revisionId)->update([
                'new_source_id' => $newFuelTxId,
                'updated_at'    => now(),
            ]);

            // سحب العهدة من الخزنة (لو safe)
            if ($advanceSource === 'safe' && $advance > 0) {
                $accId = $request->account_id;
                $account = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                if (!$account || $account->balance < $advance) throw new \Exception('رصيد الخزنة لا يكفي لصرف العهدة.');

                DB::table('accounts')->where('id', $accId)->decrement('balance', $advance);
                DB::table('financial_transactions')->insert([
                    'type' => 'expense', 'amount' => $advance, 'from_account_id' => $accId,
                    'notes' => "صرف عهدة نقدية لـ ({$driverCar}) | شركة {$companyName}",
                    'status' => 'active', 'ref_type' => 'fuel_transaction', 'ref_id' => $newFuelTxId,
                    'created_at' => now(),
                ]);
            }

            // الاستقطاعات
            foreach ($deductionRows as $row) {
                DB::table('company_debts')->insert([
                    'creditor_name'     => $row['name'],
                    'reason'            => "استقطاع من بنزينة ({$stationName}) | السائق/السيارة: {$driverCar} | شركة النقل: {$companyName}",
                    'total_amount'      => $row['amount'], 'paid_amount' => 0, 'remaining_balance' => $row['amount'],
                    'category'          => 'استقطاعات',
                    'source_type'       => 'fuel_transaction', 'source_id' => $newFuelTxId,
                    'created_at'        => now(),
                ]);
            }

            // دين المحطة
            if ($totalToStation > 0) {
                DB::table('company_debts')->insert([
                    'creditor_name'     => trim($stationName),
                    'reason'            => "السائق/السيارة: {$driverCar} | شركة النقل: {$companyName} | الكمية: {$quantity} لتر | عهدة: {$advance} ج",
                    'total_amount'      => $totalToStation, 'paid_amount' => 0, 'remaining_balance' => $totalToStation,
                    'category'          => 'وقود',
                    'source_type'       => 'fuel_transaction', 'source_id' => $newFuelTxId,
                    'created_at'        => now(),
                ]);
            }

            // دين شركة النقل
            DB::table('installments')->insert([
                'customer_name' => $companyName, 'customer_phone' => 'بدون',
                'product_name'  => "بنزينة ({$stationName}) - {$itemName} | {$driverCar}",
                'cash_price' => ($fuelDebt + $advance + $grossProfit), 'discount' => 0, 'down_payment' => 0,
                'remaining_after_down' => $totalOnCompany, 'installment_months' => 0, 'interest_rate' => 0,
                'total_after_interest' => $totalOnCompany, 'monthly_installment' => $totalOnCompany, 'due_day' => date('d'),
                'remaining_balance' => $totalOnCompany, 'profit' => $netProfit,
                'category' => 'بنزينة', 'sale_type' => 'fuel',
                'start_date' => now()->toDateString(), 'created_at' => now(),
                'fuel_liters' => $quantity, 'cash_custody' => $advance,
                'source_type' => 'fuel_transaction', 'source_id' => $newFuelTxId,
            ]);

            // Audit
            AuditService::warning(
                'update', 'gas_station',
                "تم تعديل عملية بنزينة #{$id} (إعادة بناء كامل) — لتر: {$quantity} | عهدة: {$advance} | محطة: {$stationName}",
                (array) $old,
                ['fuel_tx_id' => $newFuelTxId, 'liters' => $quantity, 'advance' => $advance],
                'fuel_transaction', $id
            );
        });

        return redirect()->route('operations.index')->with('success', '✅ تم تعديل عملية البنزينة بنجاح وتسوية كل القيود المرتبطة.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // توريد المخزن (sales row)
    // ───────────────────────────────────────────────────────────────────────────

    private function showInventoryPurchase(int $id)
    {
        $row = DB::table('sales')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'الصنف غير موجود'], 404);

        $soldQty = (float)$row->quantity - (float)$row->remaining_quantity;
        $suppliers = DB::table('suppliers')->get();
        $accounts  = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        return response()->json([
            'row'        => $row,
            'sold_qty'   => $soldQty,
            'suppliers'  => $suppliers,
            'accounts'   => $accounts,
        ]);
    }

    private function updateInventoryPurchase(Request $request, int $id)
    {
        $request->validate([
            'product_name'    => 'required|string|max:255',
            'category'        => 'required|string|max:255',
            'supplier_name'   => 'required|string|max:255',
            'purchase_price'  => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'quantity'        => 'required|numeric|min:1',
            'settle_method'   => 'required|in:supplier_debt,account',  // كيف نسوّي فرق التكلفة
            'settle_account'  => 'required_if:settle_method,account',
        ]);

        DB::transaction(function () use ($request, $id) {
            $row = DB::table('sales')->where('id', $id)->lockForUpdate()->first();
            if (!$row) throw new \Exception('الصنف غير موجود.');

            // منع التعديل المتكرر
            if (!empty($row->last_edited_at)) {
                throw new \Exception('هذا الصنف اتعدل قبل كده — مينفعش يتعدل تاني.');
            }

            // 📝 snapshot قبل التعديل
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'    => 'sales',
                'source_id'      => $id,
                'action'         => 'edit',
                'snapshot_before'=> json_encode((array)$row, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => 'تعديل توريد مخزن من سجل العمليات',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $oldQty    = (float)$row->quantity;
            $oldPrice  = (float)$row->purchase_price;
            $oldSold   = $oldQty - (float)$row->remaining_quantity;
            $oldCost   = $oldQty * $oldPrice;

            $newQty    = floatval($request->quantity);
            $newPrice  = floatval($request->purchase_price);
            $newSPrice = floatval($request->selling_price);
            $newSup    = trim($request->supplier_name);
            $newName   = trim($request->product_name);
            $newCat    = trim($request->category);
            $newCost   = $newQty * $newPrice;

            if ($newQty < $oldSold) {
                throw new \Exception("الكمية الجديدة ({$newQty}) أقل من المباع منها فعلاً ({$oldSold}). ارجع المبيعات أولاً.");
            }

            $newRemaining = $newQty - $oldSold;
            $diff = $newCost - $oldCost; // موجب: نحتاج زيادة، سالب: نخفّض

            // (1) تعديل سطر المخزن
            DB::table('sales')->where('id', $id)->update([
                'product_name'       => $newName,
                'category'           => $newCat,
                'supplier_name'      => $newSup,
                'purchase_price'     => $newPrice,
                'selling_price'      => $newSPrice,
                'quantity'           => $newQty,
                'remaining_quantity' => $newRemaining,
                'last_edited_at'     => now(),
                'updated_at'         => now(),
            ]);

            // (2) تسوية الفرق المالي
            if (abs($diff) > 0.01) {
                if ($request->settle_method === 'supplier_debt') {
                    // نعدل دين المورد. لو زاد الكوست → نضيف دين، لو نقص → نخصم من دين قائم
                    if ($diff > 0) {
                        DB::table('company_debts')->insert([
                            'creditor_name'     => $newSup,
                            'reason'            => "تعديل توريد: {$newName} — زيادة تكلفة بـ " . number_format($diff, 0) . " ج",
                            'total_amount'      => $diff,
                            'paid_amount'       => 0,
                            'remaining_balance' => $diff,
                            'category'          => 'مورد',
                            'created_at'        => now(),
                        ]);
                    } else {
                        $reduce = abs($diff);
                        $debts = DB::table('company_debts')
                            ->where('creditor_name', $newSup)
                            ->where('remaining_balance', '>', 0)
                            ->orderByDesc('created_at')->get();
                        foreach ($debts as $d) {
                            if ($reduce <= 0) break;
                            $rem = (float)$d->remaining_balance;
                            $take = min($rem, $reduce);
                            DB::table('company_debts')->where('id', $d->id)->update([
                                'total_amount'      => DB::raw("total_amount - {$take}"),
                                'remaining_balance' => DB::raw("remaining_balance - {$take}"),
                                'updated_at'        => now(),
                            ]);
                            $reduce -= $take;
                        }
                        if ($reduce > 0) {
                            throw new \Exception("الدين المتاح للمورد ({$newSup}) أقل من قيمة التخفيض المطلوبة. اختر تسوية بالخزنة أو راجع الديون.");
                        }
                    }
                } else { // account
                    $accId = $request->settle_account;
                    $acc = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                    if (!$acc) throw new \Exception('الخزنة غير موجودة.');

                    if ($diff > 0) {
                        if ($acc->balance < $diff) throw new \Exception('رصيد الخزنة لا يكفي للزيادة.');
                        DB::table('accounts')->where('id', $accId)->decrement('balance', $diff);
                        DB::table('financial_transactions')->insert([
                            'type' => 'expense', 'amount' => $diff, 'from_account_id' => $accId,
                            'notes' => "تسوية تعديل توريد: {$newName} — فرق زيادة", 'status' => 'active', 'created_at' => now(),
                        ]);
                    } else {
                        $back = abs($diff);
                        DB::table('accounts')->where('id', $accId)->increment('balance', $back);
                        DB::table('financial_transactions')->insert([
                            'type' => 'income', 'amount' => $back, 'to_account_id' => $accId,
                            'notes' => "تسوية تعديل توريد: {$newName} — استرداد فرق", 'status' => 'active', 'created_at' => now(),
                        ]);
                    }
                }
            }

            AuditService::warning(
                'update', 'inventory',
                "تعديل توريد مخزن #{$id} — كمية: {$oldQty}→{$newQty} | شراء: {$oldPrice}→{$newPrice} | فرق: " . number_format($diff,0),
                (array) $row,
                $request->only(['product_name','category','supplier_name','purchase_price','selling_price','quantity','settle_method','settle_account']),
                'sales', $id
            );
        });

        return redirect()->route('operations.index')->with('success', '✅ تم تعديل التوريد وتسوية الفرق المالي.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // إلغاء بيعة نقدية (sale_inventory / sale_direct / service)
    // ───────────────────────────────────────────────────────────────────────────

    private function showSaleCash(int $id, string $editor)
    {
        $row = DB::table('installments')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'البيعة غير موجودة'], 404);

        // البحث عن الـ income FT المرتبطة (paid_amount = down_payment) في نافذة ±5 ثوان
        $relatedIncomeFt = null;
        if ((float)$row->down_payment > 0) {
            $createdAt = Carbon::parse($row->created_at);
            $relatedIncomeFt = DB::table('financial_transactions')
                ->where('type', 'income')
                ->where('amount', $row->down_payment)
                ->where('status', 'active')
                ->whereBetween('created_at', [$createdAt->copy()->subSeconds(5), $createdAt->copy()->addSeconds(5)])
                ->whereNotNull('to_account_id')
                ->orderByDesc('id')->first();
        }

        return response()->json([
            'row'          => $row,
            'editor'       => $editor,
            'income_ft'    => $relatedIncomeFt,
            'has_payments' => DB::table('installment_payments')->where('installment_id', $id)->exists(),
        ]);
    }

    private function cancelSaleCash(Request $request, int $id)
    {
        $returnedItems = [];
        $unmatchedItems = false;

        DB::transaction(function () use ($id, &$returnedItems, &$unmatchedItems) {
            $inst = DB::table('installments')->where('id', $id)->lockForUpdate()->first();
            if (!$inst) throw new \Exception('البيعة غير موجودة.');

            $createdAt = Carbon::parse($inst->created_at);
            $windowStart = $createdAt->copy()->subSeconds(5);
            $windowEnd   = $createdAt->copy()->addSeconds(5);
            $paid        = (float)$inst->down_payment;

            // ══════════════════════════════════════════════════════════
            // الحالة الوحيدة للمنع: في سداد فعلي على المستحقات/المديونيات
            // ══════════════════════════════════════════════════════════

            // (أ) دفعات إضافية بعد المقدم: إجمالي installment_payments > المقدم
            $totalPaid = (float) DB::table('installment_payments')
                ->where('installment_id', $id)->sum('amount_paid');
            if ($totalPaid > $paid + 0.01) {
                throw new \Exception(
                    'في دفعات إضافية مسجلة على البيعة دي (إجمالي مدفوع: '
                    . number_format($totalPaid, 0)
                    . ' ج، المقدم الأصلي: '
                    . number_format($paid, 0)
                    . ' ج). الغِ الدفعات الإضافية أولاً من شاشة الأقساط/المبيعات.'
                );
            }

            // (ب) سداد على ديون المورد/العمولات المرتبطة بالبيعة
            $supplierDebtsPaid = DB::table('company_debts')
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->where(function ($q) {
                    $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                })
                ->where('paid_amount', '>', 0)
                ->exists();
            if ($supplierDebtsPaid) {
                throw new \Exception(
                    'في تسديد فعلي على ديون مورد أو عمولات مرتبطة بالبيعة دي. الغِ التسديد أولاً من شاشة الديون/المستحقات.'
                );
            }

            // 📝 snapshot قبل الحذف (للأرشيف)
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'    => 'installments',
                'source_id'      => $id,
                'action'         => 'delete',
                'snapshot_before'=> json_encode((array)$inst, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => 'حذف بيعة كامل من سجل العمليات (كأنها لم تحدث)',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // (1) إرجاع البضاعة للمخزن من snapshot الأصناف (لو مخزون)
            if (($inst->sale_type === 'inventory' || $inst->category === 'مبيعات مخزن') && !empty($inst->inventory_items)) {
                $items = json_decode($inst->inventory_items, true);
                if (is_array($items)) {
                    foreach ($items as $it) {
                        $saleId = (int)($it['sale_id'] ?? 0);
                        $qty    = (float)($it['qty'] ?? 0);
                        if ($saleId <= 0 || $qty <= 0) continue;
                        $exists = DB::table('sales')->where('id', $saleId)->first();
                        if (!$exists) {
                            $unmatchedItems = true;
                            continue;
                        }
                        DB::table('sales')->where('id', $saleId)->update([
                            'remaining_quantity' => DB::raw("remaining_quantity + {$qty}"),
                            'inventory_status'   => 'to_inventory',
                            'updated_at'         => now(),
                        ]);
                        $returnedItems[] = "{$qty}× {$it['product_name']}";
                    }
                }
            } elseif ($inst->sale_type === 'inventory' || $inst->category === 'مبيعات مخزن') {
                // بيعة قديمة قبل ربط الـ items - مش هنقدر نطابق
                $unmatchedItems = true;
            }

            // (2) إرجاع المقدم من الخزنة وحذف الـ income FT (مش بس تعليمها cancelled)
            if ($paid > 0) {
                $ft = DB::table('financial_transactions')
                    ->where('type', 'income')
                    ->where('amount', $paid)
                    ->where('status', 'active')
                    ->whereNotNull('to_account_id')
                    ->whereBetween('created_at', [$windowStart, $windowEnd])
                    ->orderByDesc('id')->first();

                if ($ft) {
                    $acc = DB::table('accounts')->where('id', $ft->to_account_id)->lockForUpdate()->first();
                    if ($acc && $acc->balance < $paid) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المقدم المطلوب إرجاعه. أودِع المبلغ أولاً أو ألغِ السحوبات اللاحقة.");
                    }
                    DB::table('accounts')->where('id', $ft->to_account_id)->decrement('balance', $paid);
                    DB::table('financial_transactions')->where('id', $ft->id)->delete();
                }
            }

            // (3) حذف الـ FT المرتبطة (خصومات + مصروفات + عمولات) + إرجاع الأرصدة لو expense
            $ftWindow = DB::table('financial_transactions')
                ->whereIn('type', ['discount', 'general_expense', 'commission', 'expense'])
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->get();
            foreach ($ftWindow as $ft) {
                if (in_array($ft->type, ['general_expense', 'expense']) && $ft->from_account_id) {
                    DB::table('accounts')->where('id', $ft->from_account_id)->increment('balance', $ft->amount);
                }
                DB::table('financial_transactions')->where('id', $ft->id)->delete();
            }

            // (4) حذف ديون المورد/العمولات المرتبطة (مش بنزينة، ومافيش paid_amount لأننا منعنا قبل كده)
            DB::table('company_debts')
                ->where(function ($q) {
                    $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                })
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->delete();

            // (5) حذف installment_expenses لو موجود
            if (Schema::hasTable('installment_expenses')) {
                DB::table('installment_expenses')->where('installment_id', $id)->delete();
            }

            // (6) حذف الـ installment_payments (المقدم نفسه)
            DB::table('installment_payments')->where('installment_id', $id)->delete();

            // (7) حذف سطر sales اللي اتعمل وقت البيع المباشر/الخدمات (inventory_status='sold')
            if ($inst->sale_type === 'direct' || $inst->category === 'خدمات' || $inst->category === 'مبيعات مباشرة') {
                DB::table('sales')
                    ->where('inventory_status', 'sold')
                    ->where('product_name', $inst->product_name)
                    ->whereBetween('created_at', [$windowStart, $windowEnd])
                    ->delete();
            }

            // (8) حذف البيعة نفسها — كأنها لم تحدث
            DB::table('installments')->where('id', $id)->delete();

            AuditService::warning(
                'delete', 'sales',
                "حذف بيعة #{$id} ({$inst->category}) — العميل: {$inst->customer_name} | المبلغ: " . number_format((float)$inst->cash_price, 0),
                (array) $inst, null, 'installments', $id
            );
        });

        $msg = '✅ تم حذف البيعة بالكامل وإرجاع كل القيود المرتبطة (كأنها لم تحدث).';
        if (!empty($returnedItems)) {
            $msg .= ' البضاعة رجعت للمخزن: ' . implode(' + ', $returnedItems);
        }
        if ($unmatchedItems) {
            $msg .= ' ⚠️ في أصناف ما اتربطتش بالمخزن (بيعة قديمة) — راجع وأضف الكميات يدوياً.';
        }
        return redirect()->route('operations.index')->with('success', $msg);
    }

    // ───────────────────────────────────────────────────────────────────────────
    // مرتجع بيعة (يرد كل الفلوس + يرجع البضاعة للمخزن + يحذف كل المستحقات)
    // ───────────────────────────────────────────────────────────────────────────

    private function showSaleReturn(int $id)
    {
        $row = DB::table('installments')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'البيعة غير موجودة'], 404);

        $createdAt   = Carbon::parse($row->created_at);
        $windowStart = $createdAt->copy()->subSeconds(5);
        $windowEnd   = $createdAt->copy()->addSeconds(5);

        // إجمالي اللي العميل دفعه = installment_payments (المقدم بيتسجل ضمنها وقت إنشاء البيعة)
        $paymentsSum = (float) DB::table('installment_payments')
            ->where('installment_id', $id)->sum('amount_paid');
        $downPayment = (float) $row->down_payment;
        $hasDownPaymentRow = DB::table('installment_payments')
            ->where('installment_id', $id)
            ->where('amount_paid', $downPayment)
            ->whereBetween('payment_date', [$windowStart, $windowEnd])
            ->exists();
        $totalPaid = $hasDownPaymentRow ? $paymentsSum : ($paymentsSum + $downPayment);

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        $hasPaidSupplierDebts = DB::table('company_debts')
            ->whereBetween('created_at', [$windowStart, $windowEnd])
            ->where(function ($q) {
                $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
            })
            ->where('paid_amount', '>', 0)
            ->exists();

        return response()->json([
            'row'                     => $row,
            'total_paid'              => $totalPaid,
            'accounts'                => $accounts,
            'has_paid_supplier_debts' => $hasPaidSupplierDebts,
        ]);
    }

    private function returnSaleCash(Request $request, int $id)
    {
        $returnedItems = [];
        $unmatchedItems = false;
        $totalRefund = 0;

        DB::transaction(function () use ($request, $id, &$returnedItems, &$unmatchedItems, &$totalRefund) {
            $inst = DB::table('installments')->where('id', $id)->lockForUpdate()->first();
            if (!$inst) throw new \Exception('البيعة غير موجودة.');

            $createdAt   = Carbon::parse($inst->created_at);
            $windowStart = $createdAt->copy()->subSeconds(5);
            $windowEnd   = $createdAt->copy()->addSeconds(5);

            // ══════ المنع الوحيد: في تسديد فعلي على ديون مورد مرتبطة ══════
            $supplierDebtsPaid = DB::table('company_debts')
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->where(function ($q) {
                    $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                })
                ->where('paid_amount', '>', 0)
                ->exists();
            if ($supplierDebtsPaid) {
                throw new \Exception('في تسديد فعلي على ديون مورد مرتبطة بالبيعة دي. الغِ التسديد أولاً ثم اعمل المرتجع.');
            }

            // ══════ حساب إجمالي اللي العميل دفعه ══════
            $paymentsSum = (float) DB::table('installment_payments')
                ->where('installment_id', $id)->sum('amount_paid');
            $downPayment = (float) $inst->down_payment;
            $hasDownPaymentRow = DB::table('installment_payments')
                ->where('installment_id', $id)
                ->where('amount_paid', $downPayment)
                ->whereBetween('payment_date', [$windowStart, $windowEnd])
                ->exists();
            $totalRefund = $hasDownPaymentRow ? $paymentsSum : ($paymentsSum + $downPayment);

            // 📝 snapshot
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'    => 'installments',
                'source_id'      => $id,
                'action'         => 'return',
                'snapshot_before'=> json_encode((array)$inst, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => "مرتجع بيعة من سجل العمليات — رد " . number_format($totalRefund, 0) . " ج للعميل",
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // (1) رد المبلغ من الخزنة
            if ($totalRefund > 0) {
                $accId = (int) $request->refund_account_id;
                if (!$accId) throw new \Exception('لازم تختار خزنة الصرف لرد المبلغ للعميل.');
                $acc = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                if (!$acc) throw new \Exception('الخزنة المختارة غير موجودة.');
                if ($acc->balance < $totalRefund) {
                    throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب رده (" . number_format($totalRefund, 0) . " ج).");
                }
                DB::table('accounts')->where('id', $accId)->decrement('balance', $totalRefund);
                DB::table('financial_transactions')->insert([
                    'type'            => 'expense',
                    'amount'          => $totalRefund,
                    'from_account_id' => $accId,
                    'notes'           => "مرتجع بيعة #{$id} — رد فلوس للعميل: {$inst->customer_name}",
                    'status'          => 'active',
                    'created_at'      => now(),
                ]);

                // حذف الـ income الأصلي المرتبط بالمقدم
                if ($downPayment > 0) {
                    $origIncome = DB::table('financial_transactions')
                        ->where('type', 'income')
                        ->where('amount', $downPayment)
                        ->where('status', 'active')
                        ->whereNotNull('to_account_id')
                        ->whereBetween('created_at', [$windowStart, $windowEnd])
                        ->orderByDesc('id')->first();
                    if ($origIncome) {
                        $origAcc = DB::table('accounts')->where('id', $origIncome->to_account_id)->lockForUpdate()->first();
                        if ($origAcc && $origAcc->balance >= $downPayment) {
                            DB::table('accounts')->where('id', $origIncome->to_account_id)->decrement('balance', $downPayment);
                        }
                        DB::table('financial_transactions')->where('id', $origIncome->id)->delete();
                    }
                }
            }

            // (2) إرجاع البضاعة للمخزن
            $qty = floatval($inst->quantity ?? 1);
            $purchaseCost = floatval($inst->purchase_cost ?? 0);
            $unitPurchase = $qty > 0 ? $purchaseCost / $qty : $purchaseCost;
            $unitSelling  = $qty > 0 ? floatval($inst->cash_price) / $qty : floatval($inst->cash_price);

            if (($inst->sale_type === 'inventory' || $inst->category === 'مبيعات مخزن') && !empty($inst->inventory_items)) {
                $items = json_decode($inst->inventory_items, true);
                if (is_array($items)) {
                    foreach ($items as $it) {
                        $saleId = (int)($it['sale_id'] ?? 0);
                        $itQty  = (float)($it['qty'] ?? 0);
                        if ($saleId <= 0 || $itQty <= 0) continue;
                        $exists = DB::table('sales')->where('id', $saleId)->first();
                        if (!$exists) {
                            $unmatchedItems = true;
                            continue;
                        }
                        DB::table('sales')->where('id', $saleId)->update([
                            'remaining_quantity' => DB::raw("remaining_quantity + {$itQty}"),
                            'inventory_status'   => 'to_inventory',
                            'updated_at'         => now(),
                        ]);
                        DB::table('inventory_movements')->insert([
                            'sale_id'    => $saleId,
                            'type'       => 'customer_return',
                            'quantity'   => $itQty,
                            'notes'      => "مرتجع من العميل: {$inst->customer_name} — بيعة #{$id}",
                            'created_at' => now(),
                        ]);
                        $returnedItems[] = "{$itQty}× " . ($it['product_name'] ?? $inst->product_name);
                    }
                }
            } else {
                // بيعة مباشر / خدمة / بيعة قديمة من المخزن من غير ربط — ننشئ batch جديد
                $newSaleId = DB::table('sales')->insertGetId([
                    'store_id'           => 1,
                    'product_name'       => $inst->product_name,
                    'category'           => 'مرتجعات عملاء',
                    'supplier_name'      => 'مرتجع من العميل: ' . $inst->customer_name,
                    'purchase_price'     => $unitPurchase,
                    'selling_price'      => $unitSelling,
                    'quantity'           => $qty,
                    'remaining_quantity' => $qty,
                    'inventory_status'   => 'to_inventory',
                    'purchase_date'      => now()->toDateString(),
                    'created_at'         => now(),
                ]);
                DB::table('inventory_movements')->insert([
                    'sale_id'    => $newSaleId,
                    'type'       => 'customer_return',
                    'quantity'   => $qty,
                    'notes'      => "مرتجع من العميل: {$inst->customer_name} — بيعة #{$id}",
                    'created_at' => now(),
                ]);
                $returnedItems[] = "{$qty}× {$inst->product_name}";
            }

            // (3) تسجيل المرتجع في sale_returns (تاب "مرتجعات العملاء")
            if (Schema::hasTable('sale_returns')) {
                DB::table('sale_returns')->insert([
                    'sale_id'           => 0,
                    'product_name'      => $inst->product_name,
                    'category'          => 'مرتجعات عملاء',
                    'quantity_returned' => $qty,
                    'purchase_price'    => $unitPurchase,
                    'return_price'      => $totalRefund,
                    'total_refunded'    => $totalRefund,
                    'loss_amount'       => 0,
                    'refund_account_id' => $request->refund_account_id,
                    'notes'             => "مرتجع بيعة العميل: {$inst->customer_name}",
                    'created_at'        => now(),
                ]);
            }

            // (4) حذف الـ FTs المرتبطة (خصومات/عمولات/مصاريف) + إرجاع الأرصدة
            $ftWindow = DB::table('financial_transactions')
                ->whereIn('type', ['discount', 'general_expense', 'commission', 'expense'])
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->get();
            foreach ($ftWindow as $ft) {
                if (in_array($ft->type, ['general_expense', 'expense']) && $ft->from_account_id) {
                    DB::table('accounts')->where('id', $ft->from_account_id)->increment('balance', $ft->amount);
                }
                DB::table('financial_transactions')->where('id', $ft->id)->delete();
            }

            // (5) حذف العمولات/الخصومات بس (مش ديون المورد!)
            // 🚨 البضاعة رجعت للمخزن أو لسه عندنا → الدين على المورد فاضل
            DB::table('company_debts')
                ->where(function ($q) {
                    $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                })
                ->whereNotIn('category', ['مورد', 'موردين']) // ⬅️ استثناء ديون الموردين
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->delete();

            // (6) حذف installment_expenses
            if (Schema::hasTable('installment_expenses')) {
                DB::table('installment_expenses')->where('installment_id', $id)->delete();
            }

            // (7) حذف installment_payments
            DB::table('installment_payments')->where('installment_id', $id)->delete();

            // (8) حذف sales row للبيع المباشر/الخدمات (inventory_status='sold')
            if ($inst->sale_type === 'direct' || $inst->category === 'خدمات' || $inst->category === 'مبيعات مباشرة') {
                DB::table('sales')
                    ->where('inventory_status', 'sold')
                    ->where('product_name', $inst->product_name)
                    ->whereBetween('created_at', [$windowStart, $windowEnd])
                    ->delete();
            }

            // (9) حذف البيعة نفسها (تمسح كل المستحقات والمديونيات)
            DB::table('installments')->where('id', $id)->delete();

            AuditService::warning(
                'return', 'sales',
                "مرتجع بيعة #{$id} ({$inst->category}) — العميل: {$inst->customer_name} | المردود: " . number_format($totalRefund, 0),
                (array) $inst,
                ['refund_amount' => $totalRefund, 'refund_account_id' => $request->refund_account_id],
                'installments', $id
            );
        });

        $msg = '✅ تم تسجيل المرتجع ورد ' . number_format($totalRefund, 0) . ' ج للعميل من الخزنة.';
        if (!empty($returnedItems)) {
            $msg .= ' البضاعة رجعت للمخزن: ' . implode(' + ', $returnedItems);
        }
        if ($unmatchedItems) {
            $msg .= ' ⚠️ في أصناف ما اتربطتش بالمخزن (بيعة قديمة) — راجع وأضف الكميات يدوياً.';
        }
        return response()->json(['success' => true, 'message' => $msg]);
    }

    // ───────────────────────────────────────────────────────────────────────────
    // أدوات مساعدة
    // ───────────────────────────────────────────────────────────────────────────

    /**
     * يتحقق من باسورد الـ admin الحالي (المستخدم المسجّل دخوله) — لا يوجد رمز ثابت.
     */
    private function verifyAdminPin(Request $request): bool
    {
        $sessionUser = session('auth_user');
        if (!$sessionUser || ($sessionUser->role ?? '') !== 'admin') return false;

        $password = (string) $request->input('admin_pin', '');
        if ($password === '') return false;

        $dbUser = DB::table('users')->where('id', $sessionUser->id)->first();
        if (!$dbUser) return false;

        return Hash::check($password, $dbUser->password);
    }

    // ───────────────────────────────────────────────────────────────────────────
    // المصروفات (financial_transactions)
    // ───────────────────────────────────────────────────────────────────────────

    private function showExpense(int $id)
    {
        $row = DB::table('financial_transactions')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'المصروف غير موجود'], 404);

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        $manualCategories = DB::table('expense_categories')->orderBy('name')->pluck('name')->toArray();
        $dynamicCategories = DB::table('financial_transactions')
            ->where('type', 'general_expense')
            ->where('notes', 'like', '[%')
            ->where('notes', 'like', '%]%')
            ->selectRaw('DISTINCT SUBSTRING(notes, 2, LOCATE(\']\', notes) - 2) as cat')
            ->pluck('cat')->filter()->all();

        $allCategories = array_values(array_unique(array_merge(
            ['رواتب وأجور', 'خصومات للعملاء', 'عمولات المحافظ'],
            $manualCategories,
            $dynamicCategories
        )));

        $category = 'مصروفات تشغيلية';
        $actualNote = $row->notes ?? '';
        if ($row->type === 'salary_expense') $category = 'رواتب وأجور';
        elseif ($row->type === 'discount')   $category = 'خصومات للعملاء';
        elseif (preg_match('/^\[(.*?)\]\s*(.*)/u', $actualNote, $m)) {
            $category = $m[1];
            $actualNote = $m[2];
        }

        return response()->json([
            'row'           => $row,
            'category'      => $category,
            'actual_note'   => $actualNote,
            'accounts'      => $accounts,
            'categories'    => $allCategories,
        ]);
    }

    private function updateExpense(Request $request, int $id)
    {
        $request->validate([
            'category'   => 'required|string|max:100',
            'notes'      => 'nullable|string|max:500',
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|integer',
        ]);

        DB::transaction(function () use ($request, $id) {
            $ft = DB::table('financial_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$ft) throw new \Exception('المصروف غير موجود.');
            if ($ft->status !== 'active') throw new \Exception('المصروف ملغي بالفعل — مينفعش يتعدل.');
            if (!in_array($ft->type, ['general_expense', 'salary_expense', 'expense'])) {
                throw new \Exception('نوع الحركة غير مدعوم للتعديل من هنا.');
            }

            $oldAmount  = (float) $ft->amount;
            $oldAccount = $ft->from_account_id;
            $newAmount  = (float) $request->amount;
            $newAccount = (int) $request->account_id;
            $newCategory= trim($request->category);
            $newNotes   = trim((string) $request->notes);

            // 1) إرجاع المبلغ القديم للخزنة القديمة
            if ($oldAccount) {
                DB::table('accounts')->where('id', $oldAccount)->increment('balance', $oldAmount);
            }

            // 2) خصم المبلغ الجديد من الخزنة الجديدة
            $acc = DB::table('accounts')->where('id', $newAccount)->lockForUpdate()->first();
            if (!$acc) throw new \Exception('الخزنة الجديدة غير موجودة.');
            if ($acc->balance < $newAmount) {
                throw new \Exception("رصيد الخزنة ({$acc->account_name}) لا يكفي بعد الخصم. الرصيد الحالي بعد الإرجاع: " . number_format($acc->balance, 2) . " ج");
            }
            DB::table('accounts')->where('id', $newAccount)->decrement('balance', $newAmount);

            // 3) تحديد الـ type والـ notes الجديدة بناءً على التصنيف
            $newType = 'general_expense';
            $finalNotes = "[{$newCategory}] {$newNotes}";
            if ($newCategory === 'رواتب وأجور') {
                $newType = 'salary_expense';
                $finalNotes = $newNotes ?: 'راتب';
            } elseif ($newCategory === 'خصومات للعملاء') {
                $newType = 'discount';
                $finalNotes = $newNotes ?: 'خصم للعميل';
            }

            DB::table('financial_transactions')->where('id', $id)->update([
                'type'            => $newType,
                'amount'          => $newAmount,
                'from_account_id' => $newAccount,
                'notes'           => $finalNotes,
                'updated_at'      => now(),
            ]);
        });

        return redirect()->route('operations.index')->with('success', '✅ تم تعديل المصروف بنجاح.');
    }

    private function destroyExpense(int $id)
    {
        DB::transaction(function () use ($id) {
            $ft = DB::table('financial_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$ft) throw new \Exception('المصروف غير موجود.');
            if ($ft->status !== 'active') throw new \Exception('المصروف ملغي بالفعل.');
            if (!in_array($ft->type, ['general_expense', 'salary_expense', 'expense', 'discount'])) {
                throw new \Exception('نوع الحركة غير مدعوم للحذف من هنا.');
            }

            // إرجاع المبلغ للخزنة
            if ($ft->from_account_id) {
                DB::table('accounts')->where('id', $ft->from_account_id)->increment('balance', $ft->amount);
            }

            // حذف نهائي للسطر
            DB::table('financial_transactions')->where('id', $id)->delete();
        });

        return redirect()->route('operations.index')->with('success', '✅ تم حذف المصروف وإرجاع المبلغ للخزنة.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // حذف عملية بنزينة بالكامل
    // ───────────────────────────────────────────────────────────────────────────

    private function destroyFuel(int $id)
    {
        DB::transaction(function () use ($id) {
            $tx = DB::table('fuel_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$tx) throw new \Exception('عملية البنزينة غير موجودة.');
            if (!empty($tx->superseded_by)) {
                throw new \Exception("هذه العملية اتعدلت قبل كده — احذف العملية المعدّلة #{$tx->superseded_by} بدلاً منها.");
            }

            // امنع لو فيها مدفوعات/سداد
            $hasPaidDebts = DB::table('company_debts')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)
                ->where('paid_amount', '>', 0)->exists();
            if ($hasPaidDebts) {
                throw new \Exception('لا يمكن الحذف: في مستحقات مرتبطة بهذه العملية تم سدادها جزئياً أو كلياً. الغِ السداد أولاً.');
            }

            $oldInst = DB::table('installments')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)->first();
            if ($oldInst) {
                $hasPayments = DB::table('installment_payments')->where('installment_id', $oldInst->id)->exists();
                if ($hasPayments) {
                    throw new \Exception('لا يمكن الحذف: شركة النقل سددت جزء من المستحقات. الغِ السداد أولاً.');
                }
            }

            // 1) إرجاع العهدة للخزنة (لو كان عندنا financial_transaction expense مربوطة)
            $oldExpense = DB::table('financial_transactions')
                ->where('ref_type', 'fuel_transaction')->where('ref_id', $id)
                ->where('type', 'expense')->first();
            if ($oldExpense) {
                if ($oldExpense->from_account_id) {
                    DB::table('accounts')->where('id', $oldExpense->from_account_id)->increment('balance', $oldExpense->amount);
                }
                DB::table('financial_transactions')->where('id', $oldExpense->id)->delete();
            }

            // 2) حذف الديون المرتبطة (وقود + استقطاعات)
            DB::table('company_debts')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)
                ->delete();

            // 3) حذف installment شركة النقل
            if ($oldInst) {
                DB::table('installments')->where('id', $oldInst->id)->delete();
            }

            // 4) حذف الـ fuel_transaction نفسه
            DB::table('fuel_transactions')->where('id', $id)->delete();

            // Audit
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'    => 'fuel_transaction',
                'source_id'      => $id,
                'action'         => 'delete',
                'snapshot_before'=> json_encode((array)$tx, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => 'حذف عملية بنزينة كاملة من سجل العمليات',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            AuditService::warning(
                'delete', 'gas_station',
                "تم حذف عملية بنزينة #{$id} بالكامل (مع كل القيود المرتبطة)",
                (array) $tx,
                null,
                'fuel_transaction', $id
            );
        });

        return redirect()->route('operations.index')->with('success', '✅ تم حذف عملية البنزينة بالكامل مع كل القيود المرتبطة.');
    }

    private function resolvePeriod(string $period, ?string $from, ?string $to): array
    {
        switch ($period) {
            case 'today':     return [now()->startOfDay(), now()->endOfDay()];
            case 'yesterday': return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];
            case 'week':      return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
            case 'month':     return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
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
