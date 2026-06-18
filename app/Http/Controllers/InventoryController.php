<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends SystemController
{
    public function inventory(\Illuminate\Http\Request $request)
    {
        $search     = $request->input('search', '');
        $category   = $request->input('category', '');
        $supplier   = $request->input('supplier', '');
        $lowStock   = $request->input('low_stock', false); 
        
        $suppliers  = \Illuminate\Support\Facades\DB::table('suppliers')->get();
        $accounts   = \Illuminate\Support\Facades\DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $dbCategories = \Illuminate\Support\Facades\DB::table('sales')->select('category')->distinct()->whereNotNull('category')->pluck('category')->toArray();
        $categories = array_unique(array_merge(['ثلاجات', 'غسالات', 'شاشات وتلفزيونات', 'تكييفات'], $dbCategories));

        // 💡 تجميع كل الموردين
        $dbSuppliers = $suppliers->pluck('name')->toArray();
        $salesSuppliers = \Illuminate\Support\Facades\DB::table('sales')
            ->whereNotNull('supplier_name')
            ->where('supplier_name', 'not like', 'فسخ عقد%')        // استبعاد باتشات فسخ العقود (مش موردين)
            ->where(function($q) {
                $q->where('category', '!=', 'مرتجعات عملاء')->orWhereNull('category');
            })
            ->distinct()->pluck('supplier_name')->toArray();
        $allSuppliersList = array_unique(array_merge($dbSuppliers, $salesSuppliers));
        sort($allSuppliersList);

        $all_inventory_items = \Illuminate\Support\Facades\DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0)->get();
        $total_items      = $all_inventory_items->count();
        $total_cost_value = $all_inventory_items->sum(fn($i) => floatval($i->purchase_price) * floatval($i->remaining_quantity));
        $total_sell_value = $all_inventory_items->sum(fn($i) => floatval($i->selling_price) * floatval($i->remaining_quantity));
        $potential_profit = $total_sell_value - $total_cost_value;

        $baseQuery = \Illuminate\Support\Facades\DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0);
        if ($search) { $baseQuery->where(function($q) use ($search) { $q->where('product_name', 'like', "%{$search}%")->orWhere('supplier_name', 'like', "%{$search}%"); }); }
        if ($category) $baseQuery->where('category', $category);
        if ($supplier) $baseQuery->where('supplier_name', $supplier);
        if ($lowStock) $baseQuery->where('remaining_quantity', '<', 5);

        $main_store_items = (clone $baseQuery)->where('store_id', 1)->orderBy('id', 'desc')->get();
        $sub_store_items  = (clone $baseQuery)->where('store_id', 2)->orderBy('id', 'desc')->get();
        $low_stock_count = \Illuminate\Support\Facades\DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0)->where('remaining_quantity', '<', 5)->count();

        $sales_log = \Illuminate\Support\Facades\DB::table('installments')
            ->leftJoin('installment_expenses', 'installments.id', '=', 'installment_expenses.installment_id')
            ->where('installments.sale_type', 'inventory')
            ->where('installments.category', '!=', 'بنزينة')
            ->where(function($q) {
                // 🔒 ضمان عدم ظهور أي عملية بنزينة في سجل مبيعات المخزن
                $q->where('installments.sale_type', '!=', 'fuel')->orWhereNull('installments.sale_type');
            })
            ->select('installments.*', 'installment_expenses.transport_cost', 'installment_expenses.installation_cost', 'installment_expenses.materials_cost')
            ->orderBy('installments.created_at', 'desc')
            ->limit(150)
            ->get();
            
        // 🔒 مرتجعات العملاء فقط — استبعاد مرتجعات الموردين (بـ category وبـ notes للتوافق مع البيانات القديمة)
        $returns_log = \Illuminate\Support\Facades\DB::table('sale_returns')
            ->where(function($q) {
                $q->where('category', '!=', 'مرتجعات موردين')->orWhereNull('category');
            })
            ->where(function($q) {
                $q->where('notes', 'not like', '%مرتجع للمورد%')->orWhereNull('notes');
            })
            ->where(function($q) {
                $q->where('product_name', 'not like', 'مرتجع مورد:%')->orWhereNull('product_name');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        // 💡 مرتجعات الموردين بتفاصيل كاملة من جدول sale_returns (الصنف، المورد، التكلفة، الاسترداد، الخسارة)
        $supplier_returns = \Illuminate\Support\Facades\DB::table('sale_returns as sr')
            ->leftJoin('sales as s', 'sr.sale_id', '=', 's.id')
            ->leftJoin('accounts as a', 'sr.refund_account_id', '=', 'a.id')
            ->where('sr.category', 'مرتجعات موردين')
            ->orderBy('sr.created_at', 'desc')
            ->select(
                'sr.id',
                'sr.created_at',
                'sr.notes',
                'sr.quantity_returned as quantity',
                'sr.purchase_price',
                'sr.return_price',
                'sr.total_refunded',
                'sr.loss_amount',
                \Illuminate\Support\Facades\DB::raw('COALESCE(s.product_name, sr.product_name) as product_name'),
                \Illuminate\Support\Facades\DB::raw("COALESCE(NULLIF(s.supplier_name, ''), 'غير محدد') as supplier_name"),
                's.category as product_category',
                \Illuminate\Support\Facades\DB::raw("COALESCE(a.account_name, '—') as refund_account")
            )
            ->get();
        $history_log = \Illuminate\Support\Facades\DB::table('activity_logs')->where('module', 'inventory')->orderBy('id', 'desc')->limit(200)->get();

        $inventoryItems = \Illuminate\Support\Facades\DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0)->get();
// 💡 التعديل الجذري هنا: جلب العملاء بأرقام هواتفهم لمنع التعديل عليهم في الواجهة
       // 💡 التعديل الجذري: جلب العملاء بأرقام هواتفهم لمنع التعديل عليهم في الواجهة
        $uniqueCustomers = \Illuminate\Support\Facades\DB::table('installments')
            ->select('customer_name', 'customer_phone')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '-')
            ->orderByDesc('created_at')
            ->get()
            ->merge(
                \Illuminate\Support\Facades\DB::table('customers')
                    ->select('name as customer_name', 'phone as customer_phone')
                    ->whereNotNull('phone')
                    ->get()
            )
            ->unique('customer_phone')
            ->values();
        $purchasesRaw = \Illuminate\Support\Facades\DB::table('sales')
            ->where('supplier_name', 'not like', 'فسخ عقد%')        // استبعاد باتشات فسخ العقود من سجل مشتريات الموردين
            ->where(function($q) {
                $q->where('category', '!=', 'مرتجعات عملاء')->orWhereNull('category');
            })
            ->select('supplier_name', 'product_name', 'quantity', 'purchase_price', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $supplierPurchases = $purchasesRaw->groupBy(function($item) {
            return $item->supplier_name ?: 'بدون مورد';
        })->map(function($items, $supplier) {
            $total_cost = $items->sum(function($i) { return $i->quantity * $i->purchase_price; });
            return [
                'supplier' => $supplier,
                'total_batches' => $items->count(),
                'total_items' => $items->sum('quantity'),
                'total_cost' => $total_cost,
                'details' => $items
            ];
        })->values();

// جلب قائمة العملاء المسجلين بالاسم والرقم لشاشة المخزن
// 🚀 التعديل الصحيح: جلب البيانات من جدول العملاء الموحد في النظام
$customersList = \Illuminate\Support\Facades\DB::table('customers')
    ->select('name as customer_name', 'phone as phone')
    ->whereNotNull('phone')
    ->where('phone', '!=', '-')
    ->distinct()
    ->get();

        // ── بيانات تاب التقارير ──
        // مشتريات مفصّلة لكل باتش
        $purchasesRawReport = \Illuminate\Support\Facades\DB::table('sales')
            ->select('id', 'category', 'supplier_name', 'purchase_price', 'quantity',
                     \Illuminate\Support\Facades\DB::raw('COALESCE(purchase_date, DATE(created_at)) as purchase_date'))
            ->orderBy(\Illuminate\Support\Facades\DB::raw('COALESCE(purchase_date, created_at)'))
            ->get()
            ->map(fn($r) => [
                'date'       => $r->purchase_date,
                'category'   => $r->category ?? 'عام',
                'supplier'   => $r->supplier_name ?? 'غير محدد',
                'qty'        => (float)$r->quantity,
                'total_cost' => round((float)$r->quantity * (float)$r->purchase_price, 2),
            ])->values()->toArray();

        // مبيعات مفصّلة بالفئة (نربط installments بـ sales عبر inventory_items JSON)
        $inventoryItemsLookup = \Illuminate\Support\Facades\DB::table('sales')
            ->select('id', 'category', 'supplier_name', 'purchase_price', 'selling_price')
            ->get()
            ->keyBy('id');

        $salesInstallments = \Illuminate\Support\Facades\DB::table('installments')
            ->where('sale_type', 'inventory')
            ->where('category', '!=', 'بنزينة')
            ->whereNotNull('inventory_items')
            ->select('id', 'created_at', 'inventory_items')
            ->orderBy('created_at')
            ->get();

        $salesRawReport = [];
        foreach ($salesInstallments as $inst) {
            $items = json_decode($inst->inventory_items, true) ?? [];
            $instDate = \Carbon\Carbon::parse($inst->created_at)->format('Y-m-d');
            foreach ($items as $item) {
                $saleId = $item['sale_id'] ?? null;
                if (!$saleId) continue;
                $inv = $inventoryItemsLookup->get((int)$saleId);
                if (!$inv) continue;
                $qty       = (float)($item['qty'] ?? 1);
                $sellPrice = (float)($inv->selling_price ?? 0);
                $costPrice = (float)($inv->purchase_price ?? 0);
                $salesRawReport[] = [
                    'date'       => $instDate,
                    'category'   => $inv->category ?? 'عام',
                    'supplier'   => $inv->supplier_name ?? 'غير محدد',
                    'qty'        => $qty,
                    'sell_value' => round($qty * $sellPrice, 2),
                    'cost_value' => round($qty * $costPrice, 2),
                    'profit'     => round($qty * ($sellPrice - $costPrice), 2),
                ];
            }
        }

        return view('inventory', compact(
            'main_store_items', 'sub_store_items', 'sales_log', 'returns_log', 'supplier_returns', 'history_log',
            'accounts', 'categories', 'search', 'category', 'supplier', 'lowStock', 'low_stock_count',
            'total_items', 'total_cost_value', 'total_sell_value', 'potential_profit',
            'uniqueCustomers', 'suppliers', 'inventoryItems', 'allSuppliersList', 'supplierPurchases', 'customersList',
            'purchasesRawReport', 'salesRawReport'
        ));
    }

    public function inventoryStore(\Illuminate\Http\Request $request)
    {
        try {
           $request->validate([
                'payment_type'       => 'required|in:cash,ajel,partial',
                'withdrawal_account' => 'required_if:payment_type,cash,partial',
                'paid_amount'        => 'nullable|required_if:payment_type,partial|numeric|min:0',
                'product_name'       => 'required|array',
                'product_name.*'     => 'required|string',
                'quantity.*'         => 'required|numeric|min:1',
            ]);

            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $paymentType = $request->payment_type; 
                $account_id  = $request->withdrawal_account;
                $paidNow     = floatval($request->paid_amount ?? 0);
                $totalCost   = 0;
                $itemsCount  = 0;
                $supplierCosts = []; 
                $supplierItems = []; 
                $radarDetails  = []; 

                $names      = $request->product_name;
                $purchases  = $request->purchase_price;
                $sellings   = $request->selling_price;
                $quantities = $request->quantity;
                $suppliers  = $request->supplier_name;
                $categories = $request->category;

                for ($i = 0; $i < count($names); $i++) {
                    $baseName = trim($names[$i] ?? '');
                    if (empty($baseName)) continue;
                    $qty = floatval($quantities[$i] ?? 0);
                    $pPrice = floatval($purchases[$i] ?? 0);
                    $sPrice = floatval($sellings[$i] ?? 0);
                    $sup = trim($suppliers[$i] ?? 'عام');
                    if ($qty <= 0) continue;

                    $itemCost = $qty * $pPrice;
                    $totalCost += $itemCost;
                    $supplierCosts[$sup] = ($supplierCosts[$sup] ?? 0) + $itemCost;
                    $supplierItems[$sup][] = "({$qty} × {$baseName})"; 
                    $itemsCount++;

                    $cleanName = str_replace([' (سعر جديد)', ' (سعر قديم)'], '', $baseName);
                    $finalName = $cleanName;
                    $existing = \Illuminate\Support\Facades\DB::table('sales')->where('product_name', 'like', "%{$cleanName}%")->orderBy('id', 'desc')->first();
                    
                    if ($existing && $pPrice > floatval($existing->purchase_price)) {
                        $finalName = $cleanName . ' (سعر جديد)';
                        \Illuminate\Support\Facades\DB::table('sales')->where('product_name', 'like', "%{$cleanName}%")->where('id', '!=', $existing->id)->update(['product_name' => $cleanName . ' (سعر قديم)']);
                    }

                    \Illuminate\Support\Facades\DB::table('sales')->insert([
                        'store_id' => $request->store_id ?? 1, 'product_name' => $finalName, 'category' => $categories[$i] ?? 'عام',
                        'supplier_name' => $sup, 'purchase_price' => $pPrice, 'selling_price' => $sPrice,
                        'quantity' => $qty, 'remaining_quantity' => $qty, 'inventory_status' => 'to_inventory',
                        'purchase_date' => now()->toDateString(), 'created_at' => now(),
                    ]);

                    $radarDetails[] = "▪️ {$qty} × {$finalName} | المورد: {$sup} | الإجمالي: " . number_format($itemCost, 0) . " ج";
                }

                if ($itemsCount == 0) throw new \Exception('يرجى إدخال بيانات الأصناف بشكل صحيح.');

                if ($paymentType === 'cash') $paidNow = $totalCost;

                if ($paidNow > 0 && $account_id) {
                    $acc = \Illuminate\Support\Facades\DB::table('accounts')->where('id', $account_id)->lockForUpdate()->first();
                    if (!$acc || $acc->balance < $paidNow) throw new \Exception("رصيد الخزنة لا يكفي.");

                    \Illuminate\Support\Facades\DB::table('accounts')->where('id', $account_id)->decrement('balance', $paidNow);
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type' => 'expense', 'amount' => $paidNow, 'from_account_id' => $account_id,
                        'balance_before' => self::getTotalCapital(), 'balance_after' => self::getTotalCapital(),
                        'notes' => "🛒 توريد مخزن: مدفوعات لفاتورة قيمتها (" . number_format($totalCost, 0) . " ج)", 'status' => 'active', 'created_at' => now(),
                    ]);

                    if (method_exists($this, 'applyAccountCommission')) {
                        self::applyAccountCommission($account_id, $paidNow, "سداد فاتورة توريد مخزن", 'out');
                    }
                }

                $remainingDebt = max($totalCost - $paidNow, 0);
                if ($remainingDebt > 0) {
                    foreach ($supplierCosts as $supName => $cost) {
                        $debtShare = ($cost / $totalCost) * $remainingDebt; 
                        $itemsStr = implode(' + ', $supplierItems[$supName]); 
                        
                        \Illuminate\Support\Facades\DB::table('company_debts')->insert([
                            'creditor_name' => $supName, 
                            'reason' => "مشتريات بضاعة | الأصناف: {$itemsStr}", 
                            'total_amount' => $debtShare, 
                            'paid_amount' => 0, 
                            'remaining_balance' => $debtShare, 
                            'category' => 'مورد', 
                            'created_at' => now(),
                        ]);
                    }
                }

                if (method_exists($this, 'logActivity')) {
                    $detailsStr = implode("\n", $radarDetails);
                    $this->logActivity('create', 'inventory', "📦 توريد بضاعة للمخزن (إجمالي " . number_format($totalCost, 0) . " ج)\nالتفاصيل:\n{$detailsStr}");
                }
            });
            
            return back()->with('success', '✅ تم التوريد وتسجيل الحسابات بنجاح!');

        } catch (\Exception $e) { 
            return back()->withInput()->with('error', $e->getMessage())->withInput()->with('open_modal', 'buyStockModal'); 
        }
    }

    public function updateInventory(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'sale_id'        => 'required|integer|exists:sales,id',
                'product_name'   => 'required|string|max:255',
                'category'       => 'required|string|max:255',
                'supplier_name'  => 'required|string|max:255',
                'selling_price'  => 'required|numeric|min:0',
            ]);

            // 🔒 سعر الشراء مش بيتعدل من هنا — تعديله من سجل العمليات فقط
            // (لأنه بيترتب عليه تسوية ديون المورد / الخزنة تلقائياً)
            \Illuminate\Support\Facades\DB::table('sales')->where('id', $request->sale_id)->update([
                'product_name'   => trim($request->product_name),
                'category'       => trim($request->category),
                'supplier_name'  => trim($request->supplier_name),
                'selling_price'  => floatval($request->selling_price),
            ]);
            
            if (method_exists($this, 'logActivity')) {
                $this->logActivity('update', 'inventory', "📝 تعديل بيانات صنف في المخزن: ({$request->product_name})");
            }

            return back()->with('success', '✅ تم التعديل بنجاح.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('open_modal', 'editStockModal');
        } catch (\Exception $e) { 
            return back()->withInput()->with('error', $e->getMessage())->withInput()->with('open_modal', 'editStockModal'); 
        }
    }

   public function sellFromInventory(\Illuminate\Http\Request $request)
    {
        try {
          $request->validate([
                'payment_type'         => 'required|in:cash,ajel,partial',
                'deposit_account'      => 'required_if:payment_type,cash,partial',
                'paid_amount'          => 'nullable|required_if:payment_type,partial|numeric|min:0',
                'sale_id'              => 'required|array',
                'sale_id.*'            => 'required|integer',
                'expense_account'      => 'nullable|integer|exists:accounts,id',
                'commission_amount'    => 'nullable|numeric|min:0',
            ]);

            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $paymentType      = $request->payment_type; 
                $depositAccId     = $request->deposit_account;
                $expenseAccId     = $request->expense_account;
                $discount         = floatval($request->discount_amount ?? 0);
                $customerName     = trim($request->customer_name ?? '');
                $customerPhone    = trim($request->customer_phone ?? '');

                // ✅ تحقق Server-Side: لو الرقم مسجل مسبقاً، نفرض الاسم المسجل بغض النظر عما أرسله الفورم
                if (!empty($customerPhone) && $customerPhone !== '-') {
                    $registeredCustomer = \Illuminate\Support\Facades\DB::table('customers')
                        ->where('phone', $customerPhone)
                        ->first();

                    if ($registeredCustomer) {
                        $customerName = $registeredCustomer->name;
                    }
                }

                $saleIds = $request->sale_id; 
                $qtys    = $request->sell_quantity; 
                $prices  = $request->selling_price;
                
                $transCosts = $request->transport_cost;
                $instCosts  = $request->installation_cost;
                $othCosts   = $request->materials_cost;

                $totalCommission = floatval($request->commission_amount ?? 0);

                $totalInvoiceBeforeDisc = 0; $totalCost = 0; $productNames = [];
                $totalTrans = 0; $totalInst = 0; $totalOth = 0;
                $inventoryItems = []; // 🔗 snapshot للأصناف المباعة - لإرجاعها عند إلغاء البيعة

                for ($i = 0; $i < count($saleIds); $i++) {
                    $sId = $saleIds[$i]; $qty = floatval($qtys[$i] ?? 0); $price = floatval($prices[$i] ?? 0);
                    if ($qty <= 0) continue;

                    $item = \Illuminate\Support\Facades\DB::table('sales')->where('id', $sId)->lockForUpdate()->first();
                    if (!$item || $item->remaining_quantity < $qty) {
                        throw new \Exception("الكمية المطلوبة من ({$item->product_name}) غير متوفرة. المتاح: {$item->remaining_quantity}");
                    }

                    $tCost = floatval($transCosts[$i] ?? 0);
                    $iCost = floatval($instCosts[$i] ?? 0);
                    $oCost = floatval($othCosts[$i] ?? 0);
                    $rowExtras = $tCost + $iCost + $oCost;

                    $totalTrans += $tCost;
                    $totalInst  += $iCost;
                    $totalOth   += $oCost;

                    $totalInvoiceBeforeDisc += ($qty * $price) + $rowExtras;
                    $totalCost += ($qty * $item->purchase_price);
                    $productNames[] = $item->product_name . " (x{$qty})";

                    $inventoryItems[] = [
                        'sale_id'      => (int) $sId,
                        'qty'          => (float) $qty,
                        'product_name' => $item->product_name,
                    ];

                    \Illuminate\Support\Facades\DB::table('sales')->where('id', $sId)->update([
                        'remaining_quantity' => $item->remaining_quantity - $qty,
                        'inventory_status' => ($item->remaining_quantity - $qty) <= 0 ? 'sold_from_inventory' : 'to_inventory'
                    ]);
                }

                $totalInvoice = max($totalInvoiceBeforeDisc - $discount, 0);
                // 💡 بنود التركيب (نقل/تركيب/خامات) مصاريف تمريرية: العميل يدفعها والشركة تصرفها من خزنة المصاريف،
                //    لذلك تُستبعد تماماً من الربح. الربح = (قيمة بيع الأجهزة − الخصم) − تكلفة الأجهزة.
                $totalExtras = $totalTrans + $totalInst + $totalOth;
                $profit = max(0, ($totalInvoice - $totalExtras)) - $totalCost;
                $combinedNames = implode(' + ', $productNames);

                $paidNow = 0;
                $remaining = 0;

                if ($paymentType === 'cash') {
                    $paidNow = $totalInvoice;
                    $remaining = 0;
                } elseif ($paymentType === 'ajel') {
                    $paidNow = 0;
                    $remaining = $totalInvoice;
                } elseif ($paymentType === 'partial') {
                    $paidNow = floatval($request->paid_amount);
                    if ($paidNow >= $totalInvoice) {
                        throw new \Exception("المبلغ المدفوع يجب أن يكون أقل من إجمالي الفاتورة في حالة الدفع الجزئي.");
                    }
                    $remaining = $totalInvoice - $paidNow;
                }

                if ($paidNow > 0 && $depositAccId) {
                    \Illuminate\Support\Facades\DB::table('accounts')->where('id', $depositAccId)->increment('balance', $paidNow);
                    $notePrefix = $paymentType === 'cash' ? 'بيع نقدي' : 'مقدم دفع جزئي';
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type' => 'income', 'amount' => $paidNow, 'to_account_id' => $depositAccId,
                        'balance_before' => self::getTotalCapital(), 'balance_after' => self::getTotalCapital(),
                        'notes' => "💰 {$notePrefix}: {$combinedNames}", 'status' => 'active', 'created_at' => now()
                    ]);
                }

                if ($discount > 0) {
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type' => 'discount', 'amount' => $discount,
                        'notes' => "✂️ خصم فاتورة مبيعات", 'status' => 'active', 'created_at' => now()
                    ]);
                }

                $totalACExpenses = $totalTrans + $totalInst + $totalOth;
                if ($totalACExpenses > 0) {
                    // ❄️ صرف مصاريف التكييف (نقل + تركيب + خامات) إجباري من خزنة محددة
                    if (!$expenseAccId) {
                        throw new \Exception("⚠️ يجب اختيار خزنة لصرف مصاريف التكييف (نقل + تركيب + خامات) قيمتها " . number_format($totalACExpenses, 0) . " ج.م.");
                    }
                    $expAcc = \Illuminate\Support\Facades\DB::table('accounts')->where('id', $expenseAccId)->lockForUpdate()->first();
                    if (!$expAcc || $expAcc->balance < $totalACExpenses) {
                        $available = $expAcc ? number_format($expAcc->balance, 0) : '0';
                        throw new \Exception("⚠️ رصيد الخزنة المختارة لا يكفي لصرف مصاريف التكييف. المطلوب: " . number_format($totalACExpenses, 0) . " ج.م، المتاح: {$available} ج.م.");
                    }
                    \Illuminate\Support\Facades\DB::table('accounts')->where('id', $expenseAccId)->decrement('balance', $totalACExpenses);
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type'            => 'general_expense',
                        'amount'          => $totalACExpenses,
                        'from_account_id' => $expenseAccId,
                        'balance_before'  => self::getTotalCapital(),
                        'balance_after'   => self::getTotalCapital(),
                        'notes'           => "[بنود التكييفات] نقل: " . number_format($totalTrans, 0) . " ج | تركيب: " . number_format($totalInst, 0) . " ج | خامات: " . number_format($totalOth, 0) . " ج | الصنف: {$combinedNames}",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                }

                $instId = \Illuminate\Support\Facades\DB::table('installments')->insertGetId([
                    'customer_name'        => $customerName ?: 'عميل نقدي',
                    'customer_phone'       => $customerPhone,
                    'product_name'         => "فاتورة مبيعات: " . \Illuminate\Support\Str::limit($combinedNames, 200),
                    'sale_type'            => 'inventory',
                    'purchase_cost'        => $totalCost,
                    'quantity'             => 1,
                    'cash_price'           => $totalInvoiceBeforeDisc,
                    'discount'             => $discount,
                    'down_payment'         => $paidNow,
                    'remaining_after_down' => $remaining,
                    'installment_months'   => 0,
                    'total_after_interest' => $totalInvoice,
                    'monthly_installment'  => $remaining,
                    'remaining_balance'    => $remaining,
                    'profit'               => $profit,
                    'category'             => 'مبيعات مخزن',
                    'start_date'           => now()->toDateString(),
                    'created_at'           => now(),
                    'inventory_items'      => json_encode($inventoryItems, JSON_UNESCAPED_UNICODE),
                ]);

                if ($totalTrans > 0 || $totalInst > 0 || $totalOth > 0) {
                    \Illuminate\Support\Facades\DB::table('installment_expenses')->insert([
                        'installment_id'    => $instId,
                        'transport_cost'    => $totalTrans,
                        'installation_cost' => $totalInst,
                        'materials_cost'        => $totalOth,
                        'created_at'        => now(),
                    ]);
                }

                // 💡 توحيد مسمى العمولات لتكون "عمولات البيع" وتسجيلها في العمليات المالية
                if ($totalCommission > 0) {
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type'            => 'commission',
                        'amount'          => $totalCommission,
                        'balance_before'  => self::getTotalCapital(),
                        'balance_after'   => self::getTotalCapital(),
                        'notes'           => "عمولات البيع عن مبيعات مخزن: {$combinedNames}",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                    
                    \Illuminate\Support\Facades\DB::table('company_debts')->insert([
                        'creditor_name'     => 'عمولات البيع',
                        'reason'            => "عمولات البيع | {$combinedNames}",
                        'total_amount'      => $totalCommission,
                        'paid_amount'       => 0,
                        'remaining_balance' => $totalCommission,
                        'category'          => 'عمولات',
                        'created_at'        => now(),
                    ]);
                }

                if (!empty($customerPhone)) {
                    $customerExists = \Illuminate\Support\Facades\DB::table('customers')->where('phone', $customerPhone)->exists();
                    if (!$customerExists && !empty($customerName)) {
                        \Illuminate\Support\Facades\DB::table('customers')->insert([
                            'name'       => $customerName,
                            'phone'      => $customerPhone,
                            'address'    => 'غير مسجل',
                            'created_at' => now(),
                        ]);
                    }
                }
            });
            return back()->with('success', '✅ تم إذن الصرف بنجاح.');
            
        } catch (\Exception $e) { 
            return back()->withInput()->with('error', $e->getMessage())->withInput()->with('open_modal', 'sellModal'); 
        }
    }

    public function storeReturn(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $saleId      = $request->sale_id;
                $qtyReturned = floatval($request->quantity_returned);
                $returnPrice = floatval($request->return_price);
                $accountId   = $request->refund_account ?: null;
                $notes       = $request->return_notes ?? '';

                $item = DB::table('sales')->where('id', $saleId)->first();
                if (!$item) throw new \Exception('الصنف غير موجود.');

                $totalRefunded = $returnPrice * $qtyReturned; 
                $originalCost  = $item->purchase_price * $qtyReturned; 
                $netDiff       = $originalCost - $totalRefunded; 
                
                $lossAmount    = $netDiff < 0 ? abs($netDiff) : 0;
                $profitAmount  = $netDiff > 0 ? $netDiff : 0;

                DB::table('sale_returns')->insert([
                    'sale_id' => $saleId, 'product_name' => $item->product_name, 'category' => $item->category,
                    'quantity_returned' => $qtyReturned, 'purchase_price' => $item->purchase_price, 'return_price' => $returnPrice,
                    'total_refunded' => $totalRefunded, 'loss_amount' => $lossAmount, 'refund_account_id' => $accountId,
                    'notes' => $notes, 'created_at' => now(),
                ]);

                DB::table('sales')->where('id', $saleId)->update([
                    'remaining_quantity' => $item->remaining_quantity + $qtyReturned,
                    'inventory_status'   => 'to_inventory',
                ]);

                if ($totalRefunded > 0 && $accountId) {
                    DB::table('accounts')->where('id', $accountId)->decrement('balance', $totalRefunded);
                    DB::table('financial_transactions')->insert([
                        'type' => 'expense', 'amount' => $totalRefunded, 'from_account_id' => $accountId, 
                        'notes' => "↩️ استرداد نقدي لعميل نظير مرتجع: {$item->product_name}", 'status' => 'active', 'created_at' => now(),
                    ]);
                }
            });
            return back()->with('success', 'تم تسجيل مرتجع العميل وتسوية الأرباح والخسائر بنجاح.');
        } catch (\Exception $e) { return back()->withInput()->with('error', $e->getMessage()); }
    }

    public function restockInventory(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $item = DB::table('sales')->where('id', $request->sale_id)->first();
                if (!$item) throw new \Exception('الصنف غير موجود.');

                $addQty    = floatval($request->quantity);
                $newPrice  = floatval($request->purchase_price ?? $item->purchase_price);
                $totalCost = $addQty * $newPrice;
                $accountId = $request->account_id;

                DB::table('sales')->where('id', $request->sale_id)->update([
                    'remaining_quantity' => $item->remaining_quantity + $addQty,
                    'purchase_price'     => $newPrice,
                ]);

                if ($totalCost > 0 && $accountId) {
                    DB::table('accounts')->where('id', $accountId)->decrement('balance', $totalCost);
                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $totalCost,
                        'from_account_id' => $accountId,
                        'notes'           => "إعادة تخزين: {$item->product_name} | كمية: {$addQty}",
                        'created_at'      => now(),
                    ]);
                    
                    if(method_exists($this, 'applyAccountCommission')) {
                        self::applyAccountCommission($accountId, $totalCost, "إعادة تخزين صنف: {$item->product_name}", 'out');
                    }
                }
            });

            return back()->with('success', 'تم إعادة التخزين وتحديث الأرصدة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    public function returnToSupplier(\Illuminate\Http\Request $request)
    {
        try {
            $qty         = floatval($request->quantity_returned ?? 0);
            $refundPrice = floatval($request->refund_price ?? 0);
            $totalRefund = $qty * $refundPrice;

            $rules = [
                'sale_id'           => 'required|integer',
                'quantity_returned' => 'required|numeric|min:1',
                'refund_price'      => 'required|numeric|min:0',
            ];
            if ($totalRefund > 0) {
                $rules['payment_type']   = 'required|in:cash,ajel,partial';
                $rules['refund_account'] = 'required_if:payment_type,cash,partial';
                $rules['paid_amount']    = 'nullable|required_if:payment_type,partial|numeric|min:0';
            }
            $request->validate($rules);

            \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
                $item = \Illuminate\Support\Facades\DB::table('sales')->where('id', $request->sale_id)->lockForUpdate()->first();
                if (!$item) throw new \Exception('الصنف غير موجود.');

                $qtyReturned = floatval($request->quantity_returned);
                $refundPrice = floatval($request->refund_price);
                $totalRefund = $qtyReturned * $refundPrice;
                $paymentType = $totalRefund > 0 ? $request->payment_type : 'ajel';
                $accountId   = $request->refund_account;
                $paidNow     = floatval($request->paid_amount ?? 0);

                if ($qtyReturned <= 0 || $qtyReturned > $item->remaining_quantity) {
                    throw new \Exception("الكمية غير صالحة. المتاح للإرجاع: {$item->remaining_quantity}");
                }

                $totalRefund  = $qtyReturned * $refundPrice;
                $originalCost = $item->purchase_price * $qtyReturned;
                $netDiff      = $totalRefund - $originalCost; 
                
                $lossAmount   = $netDiff < 0 ? abs($netDiff) : 0;
                $profitAmount = $netDiff > 0 ? $netDiff : 0;

                if ($paymentType === 'cash') $paidNow = $totalRefund;

                \Illuminate\Support\Facades\DB::table('sales')->where('id', $item->id)->update([
                    'remaining_quantity' => $item->remaining_quantity - $qtyReturned
                ]);

                if ($paidNow > 0 && $accountId) {
                    \Illuminate\Support\Facades\DB::table('accounts')->where('id', $accountId)->increment('balance', $paidNow);
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type' => 'income', 'amount' => $paidNow, 'to_account_id' => $accountId,
                        'notes' => "استرداد نقدي نظير مرتجع لمورد: {$item->product_name}", 'status' => 'active', 'created_at' => now()
                    ]);
                }

                $remainingDebt = max($totalRefund - $paidNow, 0);
                if ($remainingDebt > 0) {
                    $ourDebtsToSupplier = \Illuminate\Support\Facades\DB::table('company_debts')
                        ->where('creditor_name', $item->supplier_name)
                        ->where('remaining_balance', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($ourDebtsToSupplier as $debt) {
                        if ($remainingDebt <= 0) break;

                        $debtRem = floatval($debt->remaining_balance);
                        if ($remainingDebt >= $debtRem) {
                            \Illuminate\Support\Facades\DB::table('company_debts')->where('id', $debt->id)->update([
                                'paid_amount'       => \Illuminate\Support\Facades\DB::raw("paid_amount + {$debtRem}"),
                                'remaining_balance' => 0,
                                'updated_at'        => now()
                            ]);
                            $remainingDebt -= $debtRem;
                        } else {
                            \Illuminate\Support\Facades\DB::table('company_debts')->where('id', $debt->id)->update([
                                'paid_amount'       => \Illuminate\Support\Facades\DB::raw("paid_amount + {$remainingDebt}"),
                                'remaining_balance' => \Illuminate\Support\Facades\DB::raw("remaining_balance - {$remainingDebt}"),
                                'updated_at'        => now()
                            ]);
                            $remainingDebt = 0;
                        }
                    }

                    if ($remainingDebt > 0) {
                        \Illuminate\Support\Facades\DB::table('installments')->insert([
                            'customer_name'        => $item->supplier_name,
                            'customer_phone'       => '-', 
                            'customer_address'     => 'مورد',
                            'product_name'         => "مستحقات مرتجع مورد: {$item->product_name}",
                            'sale_type'            => 'inventory',
                            'purchase_cost'        => 0,
                            'quantity'             => $qtyReturned,
                            'cash_price'           => $totalRefund,
                            'discount'             => 0,
                            'down_payment'         => $paidNow,
                            'remaining_after_down' => $remainingDebt,
                            'installment_months'   => 0, 
                            'interest_rate'        => 0,
                            'total_after_interest' => $totalRefund,
                            'monthly_installment'  => $remainingDebt,
                            'due_day'              => date('d'),
                            'remaining_balance'    => $remainingDebt,
                            'profit'               => 0,
                            'category'             => 'مرتجعات موردين',
                            'start_date'           => now()->toDateString(),
                            'created_at'           => now(),
                        ]);
                    }
                }

                \Illuminate\Support\Facades\DB::table('sale_returns')->insert([
                    'sale_id' => $item->id, 'product_name' => "مرتجع مورد: " . $item->product_name, 'category' => 'مرتجعات موردين',
                    'quantity_returned' => $qtyReturned, 'purchase_price' => $item->purchase_price, 'return_price' => $refundPrice,
                    'total_refunded' => $totalRefund, 'loss_amount' => $lossAmount, 'refund_account_id' => $accountId,
                    'notes' => 'مرتجع للمورد', 'created_at' => now()
                ]);

                // 💸 تسجيل الخسارة في financial_transactions عشان تطلع في صفحة الماليات تحت "خسائر المرتجعات"
                if ($lossAmount > 0) {
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type'       => 'general_expense',
                        'amount'     => $lossAmount,
                        'notes'      => "📉 خسارة فرق سعر مرتجع للمورد: {$item->product_name} (المورد: {$item->supplier_name})",
                        'status'     => 'active',
                        'created_at' => now(),
                    ]);
                }

                \Illuminate\Support\Facades\DB::table('inventory_movements')->insert(['sale_id' => $item->id, 'type' => 'supplier_return', 'quantity' => $qtyReturned, 'notes' => "مرتجع للمورد ({$item->supplier_name})", 'created_at' => now()]);
            });
            return back()->with('success', '✅ تم ترجيع البضاعة وتسوية الحسابات بنجاح.');
        } catch (\Exception $e) { 
            return back()->withInput()->with('error', $e->getMessage())->withInput()->with('open_modal', 'supplierReturnModal'); 
        }
    }

    public function deleteInventory(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'sale_id'            => 'required|integer',
                'delete_reason'      => 'required|in:mistake,damage',
                'refund_amount'      => 'nullable|numeric|min:0',
                'refund_account'     => 'nullable|integer',
                'cancel_debt_amount' => 'nullable|numeric|min:0',
            ]);

            \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
                $item = \Illuminate\Support\Facades\DB::table('sales')->where('id', $request->sale_id)->first();
                if (!$item) throw new \Exception('الصنف غير موجود.');

                $totalCost = $item->quantity * $item->purchase_price;

                if ($request->delete_reason === 'mistake') {
                    $refundAmt = floatval($request->refund_amount);
                    $refundAcc = $request->refund_account;
                    
                    if ($refundAmt > 0) {
                        if (!$refundAcc) throw new \Exception('يرجى اختيار الخزنة لاسترداد المبلغ النقدي.');
                        \Illuminate\Support\Facades\DB::table('accounts')->where('id', $refundAcc)->increment('balance', $refundAmt);
                        \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                            'type' => 'income', 'amount' => $refundAmt, 'to_account_id' => $refundAcc,
                            'notes' => "استرداد نقدي لإلغاء دفعة مخزون خطأ: {$item->product_name}", 
                            'status' => 'active', 'created_at' => now()
                        ]);
                    }

                    $cancelDebtAmt = floatval($request->cancel_debt_amount);
                    if ($cancelDebtAmt > 0) {
                        $ourDebts = \Illuminate\Support\Facades\DB::table('company_debts')
                            ->where('creditor_name', $item->supplier_name)
                            ->where('remaining_balance', '>', 0)
                            ->orderBy('created_at', 'desc') 
                            ->get();
                        
                        $remainingToCancel = $cancelDebtAmt;
                        foreach ($ourDebts as $debt) {
                            if ($remainingToCancel <= 0) break;
                            $debtRem = floatval($debt->remaining_balance);
                            
                            if ($remainingToCancel >= $debtRem) {
                                \Illuminate\Support\Facades\DB::table('company_debts')->where('id', $debt->id)->update([
                                    'total_amount'      => \Illuminate\Support\Facades\DB::raw("total_amount - {$debtRem}"),
                                    'remaining_balance' => 0,
                                    'updated_at'        => now()
                                ]);
                                $remainingToCancel -= $debtRem;
                            } else {
                                \Illuminate\Support\Facades\DB::table('company_debts')->where('id', $debt->id)->update([
                                    'total_amount'      => \Illuminate\Support\Facades\DB::raw("total_amount - {$remainingToCancel}"),
                                    'remaining_balance' => \Illuminate\Support\Facades\DB::raw("remaining_balance - {$remainingToCancel}"),
                                    'updated_at'        => now()
                                ]);
                                $remainingToCancel = 0;
                            }
                        }
                    }
                } elseif ($request->delete_reason === 'damage') {
                    if ($totalCost > 0) {
                        \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                            'type' => 'general_expense', 'amount' => $totalCost,
                            'notes' => "📉 إهلاك أصل ثابت: بضاعة تالفة/هالك من المخزن: {$item->product_name} (الكمية: {$item->quantity})", 
                            'status' => 'active', 'created_at' => now()
                        ]);
                    }
                }
                \Illuminate\Support\Facades\DB::table('sales')->where('id', $item->id)->delete();
            });

            return back()->with('success', '✅ تم تنفيذ عملية الحذف وتسوية الحسابات بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage())->withInput()->with('open_modal', 'deleteStockModal');
        }
    }

    // ⚖️ دالة تسوية الجرد (زيادة أو نقصان) مع التأثير المحاسبي
    public function adjustStock(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|in:increase,decrease',
            'qty'  => 'required|integer|min:1'
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
                $item = \Illuminate\Support\Facades\DB::table('sales')->where('id', $request->id)->first();
                if (!$item) throw new \Exception('المنتج غير موجود.');

                $qty = $request->qty;
                $cost = $item->purchase_price * $qty;

                if ($request->type === 'increase') {
                    // زيادة: رفع الكمية (زيادة أصول بدون نقدية)
                    \Illuminate\Support\Facades\DB::table('sales')->where('id', $item->id)->increment('remaining_quantity', $qty);
                    
                    if (method_exists(app('App\Http\Controllers\SystemController'), 'logActivity')) {
                        app('App\Http\Controllers\SystemController')->logActivity('inventory', 'inventory', "📈 تسوية بالزيادة: إضافة ({$qty}) قطعة لصنف: {$item->product_name}");
                    }
                } else {
                    // نقصان: خصم الكمية وتسجيلها كـ (خسارة/مصروف)
                    if ($item->remaining_quantity < $qty) {
                        throw new \Exception('لا يمكن خصم كمية أكبر من المتاح في المخزن!');
                    }
                    
                    \Illuminate\Support\Facades\DB::table('sales')->where('id', $item->id)->decrement('remaining_quantity', $qty);
                    
                    // تسجيل الخسارة في المصروفات
                    \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                        'type'       => 'general_expense',
                        'amount'     => $cost,
                        'status'     => 'active',
                        'notes'      => "📉 خسارة تسوية جرد (عجز/هالك) لصنف: {$item->product_name} بكمية {$qty}",
                        'created_at' => now()
                    ]);

                    if (method_exists(app('App\Http\Controllers\SystemController'), 'logActivity')) {
                        app('App\Http\Controllers\SystemController')->logActivity('inventory', 'inventory', "📉 تسوية بالعجز: خصم ({$qty}) قطعة من صنف: {$item->product_name}");
                    }
                }
            });

            return back()->with('success', '✅ تم تنفيذ التسوية وتحديث الحسابات والأرصدة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}