<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcController extends SystemController
{
    public function index()
    {
        $clients = DB::table('ac_clients')->get();
        $floors = DB::table('ac_floors')->get();
        $classes = DB::table('ac_classes')->get();
        $inventoryItems = DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0)->get();
        $accounts = DB::table('accounts')->where('category', '!=', 'project_sector')->get();

        $normalize = function($text) {
            if (!$text) return '';
            return str_replace(['ة', 'ى', 'أ', 'إ', 'آ'], ['ه', 'ي', 'ا', 'ا', 'ا'], mb_strtolower(trim($text)));
        };

        $acItems = $inventoryItems->filter(function($i) use ($normalize) { 
            $cat = $normalize($i->category);
            return str_contains($cat, 'تكييف') || str_contains($cat, 'صيانه') || str_contains($cat, 'قطع غيار');
        });
        
        $maintenanceItems = $inventoryItems->filter(function($i) use ($normalize) {
            $cat = $normalize($i->category);
            return $cat === 'صيانه' || $cat === 'صيانه تكييفات' || str_contains($cat, 'صيانه');
        });
        
        $acServices = DB::table('ac_services')->get();
        $expenseCategories = DB::table('ac_expense_categories')->get();

        $recentExpenses = DB::table('ac_expenses')
            ->leftJoin('ac_clients', 'ac_expenses.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_expense_categories', 'ac_expenses.ac_expense_category_id', '=', 'ac_expense_categories.id')
            ->select('ac_expenses.*', 'ac_clients.name as client_name', 'ac_expense_categories.name as category_name')
            ->orderBy('ac_expenses.id', 'desc')
            ->get();

        $expensesByCategory = $recentExpenses->groupBy('category_name')->map(function($exps) {
            return collect($exps)->sum('amount');
        })->sortDesc()->toArray();
        $technicians = DB::table('ac_technicians')->orderBy('name')->get();

        return view('ac', compact('clients', 'floors', 'classes', 'inventoryItems', 'accounts', 'acItems', 'maintenanceItems', 'acServices', 'expenseCategories', 'recentExpenses', 'expensesByCategory', 'technicians'));
    }

    public function storeClientAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $id = DB::table('ac_clients')->insertGetId([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['id' => $id, 'name' => $request->name]);
    }

    public function clientProfileAjax(Request $request, $id)
    {
        $client = DB::table('ac_clients')->where('id', $id)->first();
        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client not found'], 404);
        }

        $floorsCount = DB::table('ac_floors')->where('ac_client_id', $id)->count();
        $classesCount = DB::table('ac_classes')->where('ac_client_id', $id)->count();

        $opsQuery = DB::table('ac_operations')->where('ac_client_id', $id);
        $expQuery = DB::table('ac_expenses')->where('ac_client_id', $id);

        if ($request->filled('start_date')) {
            $opsQuery->whereDate('date', '>=', $request->start_date);
            $expQuery->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $opsQuery->whereDate('date', '<=', $request->end_date);
            $expQuery->whereDate('date', '<=', $request->end_date);
        }

        $ops = $opsQuery->get();
        $salesOps = $ops->where('type', 'sale');
        $maintOps = $ops->where('type', 'maintenance');

        $expensesTotal = $expQuery->sum('amount');
        $discountsTotal = $ops->sum('discount_amount');

        $salesCount = $salesOps->count();
        $salesRevenue = $salesOps->sum('total_amount');
        $salesCost = $salesOps->sum('cost_amount');
        $salesProfit = $salesOps->sum('profit_amount');

        $maintCount = $maintOps->count();
        $maintRevenue = $maintOps->sum('total_amount');
        $maintCost = $maintOps->sum('cost_amount');
        $maintProfit = $maintOps->sum('profit_amount');

        $netProfit = ($salesProfit + $maintProfit) - $discountsTotal - $expensesTotal;
        $totalRevenue = $salesRevenue + $maintRevenue;

        return response()->json([
            'success' => true,
            'data' => [
                'client_name' => $client->name,
                'floors_count' => $floorsCount,
                'classes_count' => $classesCount,
                
                'sales_count' => $salesCount,
                'sales_revenue' => $salesRevenue,
                'sales_cost' => $salesCost,
                'sales_profit' => $salesProfit,

                'maint_count' => $maintCount,
                'maint_revenue' => $maintRevenue,
                'maint_cost' => $maintCost,
                'maint_profit' => $maintProfit,

                'total_discounts' => $discountsTotal,
                'total_expenses' => $expensesTotal,
                'total_revenue' => $totalRevenue,
                'net_profit' => $netProfit,
            ]
        ]);
    }

    public function recentInvoicesAjax()
    {
        $ops = DB::table('ac_operations')
            ->leftJoin('ac_clients', 'ac_operations.ac_client_id', '=', 'ac_clients.id')
            ->select('ac_operations.id', 'ac_operations.type', 'ac_operations.total_amount', 'ac_operations.created_at', 'ac_operations.status', 'ac_clients.name as client_name')
            ->orderBy('ac_operations.id', 'desc')
            ->take(5)
            ->get();
            
        $ops->transform(function($op) {
            $op->time = \Carbon\Carbon::parse($op->created_at)->diffForHumans();
            return $op;
        });

        return response()->json($ops);
    }

    public function addSetting(Request $request)
    {
        if ($request->type === 'service') {
            DB::table('ac_services')->insert([
                'name' => $request->name,
                'selling_price' => floatval($request->selling_price),
                'cost_price' => floatval($request->cost_price),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return back()->with('success', 'تم إضافة الخدمة بنجاح.');
        }

        $table = 'ac_' . $request->type . 's'; // clients, floors, classes
        if ($request->type === 'class') $table = 'ac_classes';
        if ($request->type === 'expense_category') $table = 'ac_expense_categories';
        
        $names = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->name))));

        foreach ($names as $name) {
            $data = [
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (in_array($request->type, ['floor', 'class']) && $request->has('ac_client_id')) {
                $data['ac_client_id'] = $request->ac_client_id;
            }
            if ($request->type === 'class' && $request->has('ac_floor_id')) {
                $data['ac_floor_id'] = $request->ac_floor_id;
            }
            DB::table($table)->insert($data);
        }

        return back()->with('success', 'تم إضافة الإعداد بنجاح.');
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:service',
            'name' => 'required|string',
            'selling_price' => 'required|numeric',
            'cost_price' => 'required|numeric',
        ]);

        if ($request->type === 'service') {
            DB::table('ac_services')->where('id', $request->id)->update([
                'name' => $request->name,
                'selling_price' => floatval($request->selling_price),
                'cost_price' => floatval($request->cost_price),
                'updated_at' => now()
            ]);
        }

        return back()->with('success', 'تم التعديل بنجاح.');
    }

    public function addSettingAjax(Request $request)
    {
        $request->validate([
            'type' => 'required|in:floor,class',
            'name' => 'required|string',
            'ac_client_id' => 'required|integer',
        ]);
        $table = $request->type === 'class' ? 'ac_classes' : 'ac_floors';
        
        $names = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->name))));
        $insertedItems = [];

        foreach ($names as $name) {
            $data = [
                'name' => $name,
                'ac_client_id' => $request->ac_client_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($request->type === 'class' && $request->has('ac_floor_id')) {
                $data['ac_floor_id'] = $request->ac_floor_id;
            }
            $id = DB::table($table)->insertGetId($data);
            $insertedItems[] = ['id' => $id, 'name' => $name];
        }

        return response()->json(['items' => $insertedItems, 'type' => $request->type]);
    }

    public function deleteSetting(Request $request)
    {
        if ($request->type === 'service') {
            DB::table('ac_services')->where('id', $request->id)->delete();
            return back()->with('success', 'تم حذف الخدمة بنجاح.');
        }
        
        $table = 'ac_' . $request->type . 's';
        if ($request->type === 'class') $table = 'ac_classes';
        if ($request->type === 'expense_category') $table = 'ac_expense_categories';

        DB::table($table)->where('id', $request->id)->delete();
        return back()->with('success', 'تم حذف الإعداد بنجاح.');
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'ac_client_id' => 'required|integer|exists:ac_clients,id',
            'ac_expense_category_id' => 'required|integer|exists:ac_expense_categories,id',
            'account_id' => 'required|integer|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $expenseId = DB::table('ac_expenses')->insertGetId([
                'ac_client_id' => $request->ac_client_id,
                'ac_expense_category_id' => $request->ac_expense_category_id,
                'amount' => $request->amount,
                'notes' => $request->notes,
                'date' => $request->date,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Deduct from wallet
            DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $request->amount);

            // Record transaction
            $client = DB::table('ac_clients')->where('id', $request->ac_client_id)->first();
            $category = DB::table('ac_expense_categories')->where('id', $request->ac_expense_category_id)->first();
            
            DB::table('financial_transactions')->insert([
                'type' => 'expense',
                'amount' => $request->amount,
                'from_account_id' => $request->account_id,
                'ref_type' => 'ac_expense',
                'ref_id' => $expenseId,
                'notes' => 'مصروف تكييف (' . $category->name . ') - ' . $client->name . ($request->notes ? ' - ' . $request->notes : ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'تم تسجيل المصروف وخصم المبلغ من المحفظة بنجاح.']);
            }
            return back()->with('success', 'تم تسجيل المصروف وخصم المبلغ بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
            }
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function storeOperation(Request $request)
    {
        DB::beginTransaction();
        try {
            $client = DB::table('ac_clients')->where('id', $request->ac_client_id)->first();
            if(!$client) throw new \Exception("العميل غير موجود");

            $totalAmount = 0;
            $costAmount = 0;
            $profitAmount = 0;
            $discountAmount = floatval($request->input('discount_amount', 0));
            $techDebtAmount = 0;

            $items = json_decode($request->items, true);
            $inventoryItemsToEncode = [];
            $allItemNames = [];
            $locationParts = [];
            $firstOpId = null;

            // Group items by context (floor/class/type)
            $groups = [];
            if ($items && is_array($items) && count($items) > 0) {
                foreach ($items as $item) {
                    $ctx = $item['ctx'] ?? [];
                    $floorId = $ctx['floor_id'] ?? $request->ac_floor_id ?? '';
                    $classId = $ctx['class_id'] ?? $request->ac_class_id ?? '';
                    $type = $ctx['type'] ?? $request->type ?? 'maintenance';
                    $floorName = $ctx['floor_name'] ?? '-';
                    $className = $ctx['class_name'] ?? '-';
                    $multiFloorsText = $ctx['multi_floors_text'] ?? $request->multi_floors_text ?? '';
                    $multiClassesText = $ctx['multi_classes_text'] ?? $request->multi_classes_text ?? '';

                    $groupKey = ($floorName ?: '-') . '|' . ($className ?: '-') . '|' . $type;

                    if (!isset($groups[$groupKey])) {
                        $groups[$groupKey] = [
                            'floor_id' => $floorId,
                            'class_id' => $classId,
                            'type' => $type,
                            'floor_name' => $floorName,
                            'class_name' => $className,
                            'multi_floors_text' => $multiFloorsText,
                            'multi_classes_text' => $multiClassesText,
                            'items' => [],
                        ];
                    }
                    $groups[$groupKey]['items'][] = $item;
                }
            }

            // If no groups (empty items), create one default group
            if (empty($groups)) {
                $groups['default'] = [
                    'floor_id' => $request->ac_floor_id,
                    'class_id' => $request->ac_class_id,
                    'type' => $request->type ?? 'maintenance',
                    'floor_name' => '-',
                    'class_name' => '-',
                    'multi_floors_text' => $request->multi_floors_text,
                    'multi_classes_text' => $request->multi_classes_text,
                    'items' => [],
                ];
            }

            // Process each group as a separate ac_operation
            $allOpIds = [];
            foreach ($groups as $groupKey => $group) {
                $opId = DB::table('ac_operations')->insertGetId([
                    'ac_client_id' => $request->ac_client_id,
                    'ac_floor_id' => $group['floor_id'] ?: null,
                    'multi_floors_text' => $group['multi_floors_text'],
                    'ac_class_id' => $group['class_id'] ?: null,
                    'multi_classes_text' => $group['multi_classes_text'],
                    'type' => $group['type'],
                    'maintenance_type_name' => $request->maintenance_type_name,
                    'total_amount' => 0,
                    'cost_amount' => 0,
                    'profit_amount' => 0,
                    'discount_amount' => 0,
                    'date' => now()->toDateString(),
                    'created_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $allOpIds[] = $opId;
                if (!$firstOpId) $firstOpId = $opId;

                $groupTotal = 0;
                $groupCost = 0;
                $groupProfit = 0;

                foreach ($group['items'] as $item) {
                    $isManual = isset($item['is_manual']) && $item['is_manual'];
                    $quantity = floatval($item['quantity']);
                    $sellingPrice = floatval($item['price'] ?? $item['selling_price']);

                    if ($isManual) {
                        $costPrice = floatval($item['cost_price'] ?? 0);
                        $itemName = $item['name'] ?? 'صيانة';
                        $itemId = null;
                        $techDebtAmount += ($costPrice * $quantity);
                    } else {
                        $inventoryItem = DB::table('sales')->where('id', $item['id'])->lockForUpdate()->first();
                        if (!$inventoryItem) throw new \Exception("الصنف غير موجود بالمخزن");
                        if ($inventoryItem->remaining_quantity < $quantity) {
                            throw new \Exception("الكمية غير كافية للصنف: " . $inventoryItem->product_name);
                        }
                        $costPrice = floatval($inventoryItem->purchase_price);
                        $itemName = $inventoryItem->product_name;
                        $itemId = $item['id'];
                        DB::table('sales')->where('id', $item['id'])->decrement('remaining_quantity', $quantity);
                        $inventoryItemsToEncode[] = [
                            'sale_id' => $item['id'],
                            'product_name' => $itemName,
                            'qty' => $quantity,
                            'purchase_price' => $costPrice,
                            'selling_price' => $sellingPrice,
                        ];
                    }

                    $itemTotal = $quantity * $sellingPrice;
                    $itemCost = $quantity * $costPrice;
                    $itemProfit = $itemTotal - $itemCost;

                    $groupTotal += $itemTotal;
                    $groupCost += $itemCost;
                    $groupProfit += $itemProfit;

                    if (!$isManual) {
                        $allItemNames[] = $itemName;
                    }

                    // Add location details to item name for invoice display
                    $itemFloor = $group['multi_floors_text'] ?: ($group['floor_id'] ? DB::table('ac_floors')->where('id', $group['floor_id'])->value('name') : ($group['floor_name'] != '-' ? $group['floor_name'] : ''));
                    $itemClass = $group['multi_classes_text'] ?: ($group['class_id'] ? DB::table('ac_classes')->where('id', $group['class_id'])->value('name') : ($group['class_name'] != '-' ? $group['class_name'] : ''));
                    $itemLocationSuffix = '';
                    if ($itemFloor || $itemClass) {
                        $parts = [];
                        if ($itemFloor) $parts[] = "الدور: $itemFloor";
                        if ($itemClass) $parts[] = "الفصل: $itemClass";
                        $itemLocationSuffix = ' (' . implode(' - ', $parts) . ')';
                    }
                    if (str_contains($itemName, '(الدور:') || str_contains($itemName, '(الفصل:')) {
                        $itemLocationSuffix = '';
                    }

                    DB::table('ac_operation_items')->insert([
                        'ac_operation_id' => $opId,
                        'item_id' => $itemId,
                        'item_name' => $itemName . $itemLocationSuffix,
                        'quantity' => $quantity,
                        'unit_price' => $sellingPrice,
                        'total_price' => $itemTotal,
                        'cost_price' => $itemCost,
                        'profit' => $itemProfit,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $totalAmount += $groupTotal;
                $costAmount += $groupCost;
                $profitAmount += $groupProfit;

                DB::table('ac_operations')->where('id', $opId)->update([
                    'total_amount' => $groupTotal,
                    'cost_amount' => $groupCost,
                    'profit_amount' => $groupProfit,
                ]);

                // Collect location info for installment details
                $fn = $group['multi_floors_text'] ?: ($group['floor_id'] ? DB::table('ac_floors')->where('id', $group['floor_id'])->value('name') : ($group['floor_name'] != '-' ? $group['floor_name'] : 'بدون دور'));
                $cn = $group['multi_classes_text'] ?: ($group['class_id'] ? DB::table('ac_classes')->where('id', $group['class_id'])->value('name') : ($group['class_name'] != '-' ? $group['class_name'] : 'بدون فصل'));
                $locationParts[] = "الدور: $fn - الفصل: $cn";
            }

            // Apply discount to the grand total
            $totalAmount -= $discountAmount;
            $profitAmount -= $discountAmount;
            if ($totalAmount < 0) $totalAmount = 0;

            // Apply discount to the first operation
            if ($discountAmount > 0 && $firstOpId) {
                DB::table('ac_operations')->where('id', $firstOpId)->update([
                    'discount_amount' => $discountAmount,
                ]);
            }

            $locationDetails = ' (' . implode(' | ', array_unique($locationParts)) . ')';
            $productNames = !empty($allItemNames) ? implode(' + ', $allItemNames) : 'مبيعات/صيانة تكييفات';
            $productNames .= $locationDetails;

            $paymentMethod = $request->input('payment_method', 'cash');
            $paidAmount = $totalAmount;
            
            if ($paymentMethod === 'later') {
                $paidAmount = 0;
            } elseif ($paymentMethod === 'partial') {
                $paidAmount = floatval($request->input('paid_amount', 0));
                if ($paidAmount < 0) $paidAmount = 0;
                if ($paidAmount > $totalAmount) $paidAmount = $totalAmount;
            }

            $installmentStatus = ($paidAmount >= $totalAmount) ? 'paid' : 'active';
            $remainingAmount = $totalAmount - $paidAmount;

            // Determine overall type
            $hasInventory = !empty($inventoryItemsToEncode);
            $hasSale = collect($groups)->contains(fn($g) => $g['type'] === 'sale');

            $installmentId = null;
            // Installments tracking (single entry for whole invoice)
            if ($hasSale || $hasInventory || !empty($items)) {
                $installmentId = DB::table('installments')->insertGetId([
                    'sale_type'           => $hasInventory ? 'inventory' : 'direct',
                    'customer_name'       => $client->name,
                    'product_name'        => $productNames,
                    'category'            => 'مبيعات/صيانة تكييفات',
                    'start_date'          => now()->toDateString(),
                    'cash_price'          => $totalAmount,
                    'down_payment'        => $paidAmount,
                    'remaining_after_down'=> $remainingAmount,
                    'installment_months'  => 0,
                    'total_after_interest'=> $totalAmount,
                    'monthly_installment' => $remainingAmount,
                    'remaining_balance'   => $remainingAmount,
                    'due_day'             => 1,
                    'status'              => $installmentStatus,
                    'profit'              => $profitAmount,
                    'inventory_items'     => !empty($inventoryItemsToEncode) ? json_encode($inventoryItemsToEncode, JSON_UNESCAPED_UNICODE) : null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                if (!empty($allOpIds)) {
                    DB::table('ac_operations')->whereIn('id', $allOpIds)->update(['installment_id' => $installmentId]);
                }
            }

            // Technician Debt (Company Debt)
            if ($techDebtAmount > 0 && !empty($request->tech_name)) {
                $techName = $request->tech_name;
                
                $reason = "أجر خدمات/صيانة لعملية: " . $client->name . $locationDetails;
                
                DB::table('company_debts')->insert([
                    'creditor_name'     => $techName,
                    'reason'            => Str::limit($reason, 255),
                    'total_amount'      => $techDebtAmount,
                    'paid_amount'       => 0,
                    'remaining_balance' => $techDebtAmount,
                    'source_type'       => 'ac_invoice',
                    'source_id'         => $installmentId,
                    'created_at'        => now(),
                ]);
            }

            if ($paidAmount > 0) {
                DB::table('accounts')->where('id', $request->deposit_account_id)->increment('balance', $paidAmount);
                $notePrefix = $hasSale ? 'مبيعات وتركيب تكييفات - ' : 'صيانة تكييفات - ';
                $paymentMethodNote = $installmentStatus == 'paid' ? ' (كاش)' : ' (مقدم/جزئي)';
                $notes = $notePrefix . $client->name . $paymentMethodNote;
                DB::table('financial_transactions')->insert([
                    'type' => 'income',
                    'amount' => $paidAmount,
                    'to_account_id' => $request->deposit_account_id,
                    'notes' => $notes,
                    'ref_type' => 'ac_invoice',
                    'ref_id' => $installmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($discountAmount > 0) {
                DB::table('financial_transactions')->insert([
                    'type' => 'discount',
                    'amount' => $discountAmount,
                    'notes' => 'خصم تكييفات للعميل: ' . $client->name,
                    'ref_type' => 'ac_invoice',
                    'ref_id' => $installmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // If this is an edit of a previous invoice, update the old invoice's notes
            if ($request->has('supersedes') && $request->supersedes) {
                DB::table('installments')->where('id', $request->supersedes)->update([
                    'notes' => 'تم التعديل واستبدالها بالفاتورة الجديدة رقم #' . $installmentId
                ]);
            }

            DB::commit();
            $allIdsString = implode(',', $allOpIds);
            return response()->json(['success' => true, 'id' => $allIdsString, 'message' => 'تم حفظ العملية بنجاح!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function reportsAjax(Request $request)
    {
        $search = $request->search;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $query = DB::table('ac_operations')
            ->leftJoin('ac_clients', 'ac_operations.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_floors', 'ac_operations.ac_floor_id', '=', 'ac_floors.id')
            ->leftJoin('ac_classes', 'ac_operations.ac_class_id', '=', 'ac_classes.id')
            ->leftJoin('company_debts', function($join) {
                $join->on('ac_operations.installment_id', '=', 'company_debts.source_id')
                     ->where('company_debts.source_type', '=', 'ac_invoice');
            })
            ->select('ac_operations.*', 'ac_clients.name as client_name', 'ac_floors.name as floor_name', 'ac_classes.name as class_name', 'company_debts.creditor_name as tech_name');

        if ($start_date) $query->whereDate('ac_operations.date', '>=', $start_date);
        if ($end_date) $query->whereDate('ac_operations.date', '<=', $end_date);
        
        if ($request->has('client_id') && $request->client_id) {
            $query->where('ac_operations.ac_client_id', $request->client_id);
        }
        
        if ($request->has('floor_id') && $request->floor_id) {
            $query->where(function($q) use ($request) {
                $q->where('ac_operations.ac_floor_id', $request->floor_id)
                  ->orWhere('ac_operations.multi_floors_text', 'like', '%' . DB::table('ac_floors')->where('id', $request->floor_id)->value('name') . '%');
            });
        }
        
        if ($request->has('class_id') && $request->class_id) {
            $query->where(function($q) use ($request) {
                $q->where('ac_operations.ac_class_id', $request->class_id)
                  ->orWhere('ac_operations.multi_classes_text', 'like', '%' . DB::table('ac_classes')->where('id', $request->class_id)->value('name') . '%');
            });
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ac_clients.name', 'like', "%{$search}%")
                  ->orWhere('ac_classes.name', 'like', "%{$search}%");
            });
        }

        $allOperations = $query->orderBy('date', 'desc')->get();
        if ($request->view !== 'logs') {
            $operations = $allOperations->where('status', '!=', 'cancelled');
        } else {
            $operations = $allOperations;
        }
        $opIds = $operations->pluck('id')->toArray();
        
        $itemsData = DB::table('ac_operation_items')
            ->leftJoin('sales', 'ac_operation_items.item_id', '=', 'sales.id')
            ->whereIn('ac_operation_items.ac_operation_id', $opIds)
            ->select('ac_operation_items.ac_operation_id', 'ac_operation_items.item_name', 'sales.product_name', 'ac_operation_items.quantity')
            ->get();
            
        $itemsGrouped = $itemsData->groupBy('ac_operation_id');
        
        foreach ($operations as $op) {
            $opItems = $itemsGrouped->get($op->id, collect());
            $names = $opItems->map(function($i) {
                $name = $i->product_name ?: $i->item_name;
                return $name . ($i->quantity > 1 ? " ({$i->quantity})" : "");
            })->implode('، ');
            $op->items_text = $names;
        }

        $clients = $operations->groupBy('client_name');

        $reports = [];
        foreach ($clients as $clientName => $ops) {
            $salesOps = $ops->where('type', 'sale');
            $maintOps = $ops->where('type', 'maintenance');

            $reports[] = [
                'client_name' => $clientName,
                'total_deals' => $ops->sum('total_amount'),
                'total_cost' => $ops->sum('cost_amount'),
                'sales_count' => $salesOps->count(),
                'maint_count' => $maintOps->count(),
                'sales_total' => $salesOps->sum('total_amount'),
                'sales_profit' => $salesOps->sum('profit_amount'),
                'maint_total' => $maintOps->sum('total_amount'),
                'maint_profit' => $maintOps->sum('profit_amount'),
                'total_discounts' => $ops->sum('discount_amount'),
                'most_active_class' => $ops->whereNotNull('class_name')->groupBy('class_name')->map->count()->sortDesc()->keys()->first() ?? 'لا يوجد',
                'operations' => $ops
            ];
        }

        $reports = collect($reports)->sortByDesc('total_deals')->values()->all();

        $topClients = collect($reports)->take(10)->map(function($r) {
            return [
                'name' => $r['client_name'],
                'count' => $r['sales_count'] + $r['maint_count'],
                'revenue' => $r['total_deals'],
                'cost' => $r['total_cost'],
                'discount' => $r['total_discounts'],
                'profit' => $r['sales_profit'] + $r['maint_profit'] - $r['total_discounts']
            ];
        })->toArray();

        $globalStats = [
            'ops_count'   => $operations->count(),
            'sales_count' => $operations->where('type', 'sale')->count(),
            'maint_count' => $operations->where('type', 'maintenance')->count(),
            'sales_total' => $operations->where('type', 'sale')->sum('total_amount'),
            'sales_profit' => $operations->where('type', 'sale')->sum('profit_amount'),
            'maint_total' => $operations->where('type', 'maintenance')->sum('total_amount'),
            'maint_profit' => $operations->where('type', 'maintenance')->sum('profit_amount'),
            'discounts' => $operations->sum('discount_amount'),
            'net_profit' => $operations->sum('profit_amount') - $operations->sum('discount_amount'),
        ];

        $dailyTrend = [];
        if ($start_date && $end_date) {
            $period = \Carbon\Carbon::parse($start_date);
            $end = \Carbon\Carbon::parse($end_date);
            while ($period->lte($end)) {
                $dayStr = $period->toDateString();
                $dayOps = $operations->where('date', $dayStr);
                $dailyTrend[] = [
                    'label'   => $period->format('m-d'),
                    'revenue' => (float) $dayOps->sum('total_amount'),
                    'profit'  => (float) $dayOps->sum('profit_amount') - (float) $dayOps->sum('discount_amount'),
                ];
                $period->addDay();
                if (count($dailyTrend) > 60) break;
            }
        }

        // Expenses Data
        $expensesQuery = DB::table('ac_expenses')
            ->leftJoin('ac_clients', 'ac_expenses.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_expense_categories', 'ac_expenses.ac_expense_category_id', '=', 'ac_expense_categories.id')
            ->select('ac_expenses.*', 'ac_clients.name as client_name', 'ac_expense_categories.name as category_name');
        
        if ($start_date) $expensesQuery->whereDate('ac_expenses.date', '>=', $start_date);
        if ($end_date) $expensesQuery->whereDate('ac_expenses.date', '<=', $end_date);
        if ($request->has('client_id') && $request->client_id) {
            $expensesQuery->where('ac_expenses.ac_client_id', $request->client_id);
        }
        
        $expenses = $expensesQuery->get();
        
        $globalStats['total_expenses'] = $expenses->sum('amount');
        $globalStats['net_profit'] -= $globalStats['total_expenses']; // Adjust net profit
        
        $expensesByClientName = $expenses->groupBy('client_name')->map(function($exps) {
            return $exps->sum('amount');
        })->toArray();

        foreach ($topClients as &$c) {
            $clientExp = $expensesByClientName[$c['name']] ?? 0;
            $c['expenses'] = $clientExp;
            $c['profit'] -= $clientExp;
        }
        unset($c);
        
        $expensesByCategory = $expenses->groupBy('category_name')->map(function($exps) {
            return $exps->sum('amount');
        })->sortDesc()->toArray();
        
        $topExpenseClients = $expenses->groupBy('client_name')->map(function($exps) {
            return $exps->sum('amount');
        })->sortDesc()->take(5)->toArray();

        if ($request->view === 'logs' || $request->view === 'logs_print') {
            $logGroups = $operations->groupBy(function($op) {
                $client = $op->client_name ?? 'بدون عميل';
                $floor = $op->multi_floors_text ?: ($op->floor_name ?? 'بدون دور');
                $class = $op->multi_classes_text ?: ($op->class_name ?? 'بدون فصل');
                return $client . ' ➖ ' . $floor . ' ➖ ' . $class;
            });
            
            $logsData = [];
            foreach ($logGroups as $groupName => $ops) {
                $logsData[] = [
                    'client_name' => $groupName,
                    'operations' => $ops
                ];
            }
            $reports = $logsData;
            $highlight_terms = [];
            if ($request->search) $highlight_terms[] = $request->search;
            if ($request->has('floor_id') && $request->floor_id) {
                $highlight_terms[] = DB::table('ac_floors')->where('id', $request->floor_id)->value('name');
            }
            if ($request->has('class_id') && $request->class_id) {
                $highlight_terms[] = DB::table('ac_classes')->where('id', $request->class_id)->value('name');
            }
            $highlight_terms = array_filter($highlight_terms);
            
            if ($request->view === 'logs_print') {
                return view('ac_logs_print_wrapper', compact('reports', 'highlight_terms'));
            }
            return view('ac_logs_partial', compact('reports', 'highlight_terms'));
        }

        return view('ac_reports_partial', compact('reports', 'globalStats', 'topClients', 'dailyTrend', 'expensesByCategory', 'topExpenseClients', 'expenses'));
    }

    public function printInvoice($id)
    {
        $ids = explode(',', $id);
        
        $operations = DB::table('ac_operations')
            ->leftJoin('ac_clients', 'ac_operations.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_floors', 'ac_operations.ac_floor_id', '=', 'ac_floors.id')
            ->leftJoin('ac_classes', 'ac_operations.ac_class_id', '=', 'ac_classes.id')
            ->whereIn('ac_operations.id', $ids)
            ->select('ac_operations.*', 'ac_clients.name as client_name', 'ac_floors.name as floor_name', 'ac_classes.name as class_name')
            ->get();

        if ($operations->isEmpty()) return abort(404);

        $firstOp = $operations->first();
        
        // Aggregate if multiple
        if ($operations->count() > 1) {
            $firstOp->multi_floors_text = 'متعدد (انظر بنود الفاتورة)';
            $firstOp->multi_classes_text = 'متعدد (انظر بنود الفاتورة)';
            $firstOp->type = 'متعدد';
            $firstOp->maintenance_type_name = '';
            
            $firstOp->total_amount = $operations->sum('total_amount');
            $firstOp->discount_amount = $operations->sum('discount_amount');
            // Just use the first operation ID to show on top
            $firstOp->id = $ids[0] . (count($ids)>1 ? ' (مجمع)' : '');
        }

        $items = DB::table('ac_operation_items')
            ->leftJoin('sales', 'ac_operation_items.item_id', '=', 'sales.id')
            ->leftJoin('ac_operations', 'ac_operation_items.ac_operation_id', '=', 'ac_operations.id')
            ->leftJoin('ac_floors', 'ac_operations.ac_floor_id', '=', 'ac_floors.id')
            ->leftJoin('ac_classes', 'ac_operations.ac_class_id', '=', 'ac_classes.id')
            ->whereIn('ac_operation_items.ac_operation_id', $ids)
            ->select('ac_operation_items.*', 'sales.product_name', 'ac_operations.multi_floors_text', 'ac_operations.multi_classes_text', 'ac_floors.name as op_floor_name', 'ac_classes.name as op_class_name')
            ->get();

        $operation = $firstOp;
        $invoiceStatus = DB::table('installments')->where('id', $operation->installment_id)->value('status');
        return view('ac_invoice', compact('operation', 'items', 'invoiceStatus'));
    }

    public function getInvoices(Request $request)
    {
        $query = DB::table('installments')->where('category', 'مبيعات/صيانة تكييفات');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('id', $request->search)
                  ->orWhere('product_name', 'like', '%' . $request->search . '%');
            });
        }

        $invoices = $query->orderBy('id', 'desc')->paginate(20);

        // Fetch associated op_ids for each invoice to fix print button (Fix N+1 query)
        $invoiceIds = collect($invoices->items())->pluck('id')->toArray();
        if (!empty($invoiceIds)) {
            $ops = DB::table('ac_operations')
                ->whereIn('installment_id', $invoiceIds)
                ->select('id', 'installment_id')
                ->get()
                ->groupBy('installment_id');

            foreach ($invoices as $inv) {
                $inv->op_ids = $ops->has($inv->id) ? $ops[$inv->id]->pluck('id')->implode(',') : '';
            }
        }

        return response()->json([
            'html' => view('partials.ac_invoices_table', compact('invoices'))->render()
        ]);
    }

    public function destroyInvoice(Request $request, $id)
    {
        try {
            // Verify PIN if called from Operations Log
            if ($request->has('pin')) {
                if (!\Illuminate\Support\Facades\Hash::check($request->pin, auth()->user()->password) && session('auth_pin') != $request->pin) {
                    throw new \Exception("كلمة المرور غير صحيحة");
                }
            }

            DB::beginTransaction();

            $installment = DB::table('installments')->where('id', $id)->where('category', 'مبيعات/صيانة تكييفات')->first();
            if (!$installment) throw new \Exception("الفاتورة غير موجودة");

            // 1. Revert Inventory
            if ($installment->inventory_items) {
                $invItems = json_decode($installment->inventory_items, true);
                if (is_array($invItems)) {
                    foreach ($invItems as $invItem) {
                        DB::table('sales')->where('id', $invItem['sale_id'])->increment('remaining_quantity', $invItem['qty']);
                    }
                }
            }

            // 2. Revert Accounts (Financial Transactions)
            $fts = DB::table('financial_transactions')->where('ref_type', 'ac_invoice')->where('ref_id', $id)->get();
            foreach ($fts as $ft) {
                if ($ft->type === 'income' && $ft->to_account_id) {
                    DB::table('accounts')->where('id', $ft->to_account_id)->decrement('balance', $ft->amount);
                }
                DB::table('financial_transactions')->where('id', $ft->id)->delete();
            }

            // 3. Delete Company Debts (Technician Wages)
            DB::table('company_debts')->where('source_type', 'ac_invoice')->where('source_id', $id)->delete();

            // 4. Update AC Operations status to cancelled
            $opIds = DB::table('ac_operations')->where('installment_id', $id)->pluck('id');
            if ($opIds->count() > 0) {
                DB::table('ac_operations')->whereIn('id', $opIds)->update(['status' => 'cancelled']);
            }

            // 5. Mark Installment as Cancelled instead of deleting it (for Operations Log)
            // If it's an edit, we can just say 'cancelled' to show the history.
            $isEdit = request()->query('is_edit') == '1';
            $actionWord = $isEdit ? 'مُعدّلة' : 'ملغاة';
            
            DB::table('installments')->where('id', $id)->update([
                'status' => 'cancelled',
                'remaining_balance' => 0,
                'remaining_after_down' => 0,
                'cancelled_via_oplog_at' => now(),
                'notes' => 'فاتورة تكييفات ' . $actionWord . ' بتاريخ ' . now()->format('Y-m-d H:i:s')
            ]);
            
            // Add to system audit logs just in case
            \App\Services\AuditService::log(
                $isEdit ? 'update' : 'delete',
                'ac_invoices',
                "تم " . ($isEdit ? 'تعديل' : 'حذف') . " فاتورة تكييفات رقم $id",
                $installment,
                null,
                'installment',
                $id
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم حذف الفاتورة واسترجاع كافة الحركات بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getInvoiceDetails($id)
    {
        // For Edit Invoice feature:
        // Due to the complex nature, we return the info needed, 
        // the frontend can load it into the cart and call destroyInvoice, then let the user save it as a new one.
        // Actually this is highly requested, let's return all operations and items linked to this invoice.
        
        $installment = DB::table('installments')->where('id', $id)->where('category', 'مبيعات/صيانة تكييفات')->first();
        if (!$installment) return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة']);

        $operations = DB::table('ac_operations')->where('installment_id', $id)->get();
        if ($operations->isEmpty()) return response()->json(['success' => false, 'message' => 'تفاصيل العمليات غير متوفرة (قد تكون فاتورة قديمة)']);
        
        $firstOp = $operations->first();

        // Load items for each operation
        $cart = [];
        foreach ($operations as $op) {
            $items = DB::table('ac_operation_items')->where('ac_operation_id', $op->id)->get();
            $floorName = $op->multi_floors_text ?: ($op->ac_floor_id ? DB::table('ac_floors')->where('id', $op->ac_floor_id)->value('name') : '-');
            $className = $op->multi_classes_text ?: ($op->ac_class_id ? DB::table('ac_classes')->where('id', $op->ac_class_id)->value('name') : '-');
            
            $ctx = [
                'floor_id' => $op->ac_floor_id,
                'floor_name' => $floorName,
                'class_id' => $op->ac_class_id,
                'class_name' => $className,
                'multi_floors_text' => $op->multi_floors_text,
                'multi_classes_text' => $op->multi_classes_text,
                'type' => $op->type
            ];
            
            foreach ($items as $item) {
                // Determine raw name by removing location suffix
                $rawName = preg_replace('/\s*\(الدور:.*الفصل:.*\)$/', '', $item->item_name);
                
                $cart[] = [
                    'id' => $item->item_id ?: 'manual_' . rand(1000, 9999),
                    'name' => $rawName,
                    'selling_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'cost_price' => $item->cost_price,
                    'is_manual' => empty($item->item_id),
                    'ctx' => $ctx
                ];
            }
        }
        
        // Try to guess technician from company_debts
        $debt = DB::table('company_debts')->where('source_type', 'ac_invoice')->where('source_id', $id)->first();
        $techName = '';
        if ($debt) {
            $techParts = explode(' - ', $debt->creditor_name);
            $techName = $techParts[0] ?? '';
        }

        return response()->json([
            'success' => true,
            'client_id' => $firstOp->ac_client_id,
            'cart' => $cart,
            'discount' => $firstOp->discount_amount, // The first op holds the discount
            'tech_name' => $techName
        ]);
    }
}
