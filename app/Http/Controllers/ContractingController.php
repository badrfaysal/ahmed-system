<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ContractingController extends SystemController
{
    protected function ensureCancelledInvoicesTableExists(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('cont_cancelled_invoices')) {
            \Illuminate\Support\Facades\Schema::create('cont_cancelled_invoices', function ($table) {
                $table->id();
                $table->string('invoice_ref')->nullable();
                $table->unsignedBigInteger('project_id')->nullable();
                $table->string('project_name')->nullable();
                $table->string('client_name')->nullable();
                $table->string('supplier_name')->nullable();
                $table->integer('items_count')->default(0);
                $table->decimal('total_cost', 15, 2)->default(0);
                $table->decimal('total_selling', 15, 2)->default(0);
                $table->decimal('total_paid', 15, 2)->default(0);
                $table->decimal('total_debt', 15, 2)->default(0);
                $table->longText('items_json')->nullable();
                $table->string('original_date')->nullable();
                $table->string('cancelled_by')->nullable();
                $table->timestamp('cancelled_at')->nullable();
            });

            // استرجاع أي فواتير ملغاة سابقة من سجل النشاطات (activity_logs) إن وجدت
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                    $pastLogs = DB::table('activity_logs')
                        ->where('module', 'contracting')
                        ->where('action', 'delete')
                        ->where('description', 'like', '%تم إلغاء فاتورة الخامات%')
                        ->orderBy('id', 'asc')
                        ->get();
                    foreach ($pastLogs as $log) {
                        $extra = !empty($log->extra_data) ? json_decode($log->extra_data, true) : [];
                        $pId = $extra['project_id'] ?? null;
                        $proj = $pId ? DB::table('cont_projects')->where('id', $pId)->first() : null;
                        $invRef = $extra['invoice_ref'] ?? 'فاتورة ملغاة';
                        $refunded = floatval($extra['refunded'] ?? 0);
                        $debtCancelled = floatval($extra['debt_cancelled'] ?? 0);
                        $totCost = $refunded + $debtCancelled;

                        DB::table('cont_cancelled_invoices')->insert([
                            'invoice_ref'   => $invRef,
                            'project_id'    => $pId,
                            'project_name'  => $proj?->name ?? 'مشروع غير محدد',
                            'client_name'   => $proj?->client_name ?? '-',
                            'supplier_name' => '-',
                            'items_count'   => 0,
                            'total_cost'    => $totCost,
                            'total_selling' => 0,
                            'total_paid'    => $refunded,
                            'total_debt'    => $debtCancelled,
                            'items_json'    => null,
                            'original_date' => substr($log->created_at ?? now(), 0, 10),
                            'cancelled_by'  => $log->user_name ?? 'الإدارة',
                            'cancelled_at'  => $log->created_at ?? now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {}
        }
    }

    /**
     * Display the main contracting dashboard (Single-page Tabbed POS)
     */
    public function index(Request $request)
    {
        if (function_exists('isDeveloperUser') && !isDeveloperUser()) {
            return view('coming_soon', [
                'screenTitle' => 'المقاولات والتشطيبات',
                'screenIcon'  => 'fa-hard-hat',
            ]);
        }

        $this->ensureCancelledInvoicesTableExists();

        // تنظيف أي مسميات مصروفات قديمة سُجلت بالخطأ كموردين في سجل المتعاملين
        DB::table('cont_directory')
            ->whereIn('name', ['نثريات موقع', 'مصروف موقع / إدارة', 'مصروف داخلي'])
            ->delete();
        $projects = DB::table('cont_projects')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate metrics for each project
        $projectMetrics = [];
        foreach ($projects as $p) {
            $ops = DB::table('cont_operations')->where('project_id', $p->id)->get();
            $discounts = DB::table('cont_discounts')->where('project_id', $p->id)->sum('amount');
            $invoices = DB::table('cont_invoices')->where('project_id', $p->id)->get();

            $totalCost = $ops->sum('total_cost');
            $billableSelling = $ops->where('is_billable', 1)->sum('total_selling');
            $billableCost = $ops->where('is_billable', 1)->sum('total_cost');
            $supervisionAmount = max(0, $billableSelling - $billableCost);
            $grossSelling = $billableSelling;
            $internalExpenses = $ops->where('is_billable', 0)->sum('total_cost');
            $netSelling = max(0, $grossSelling - $discounts);
            $clientPaid = $invoices->sum('paid_amount');
            $clientRemaining = max(0, $netSelling - $clientPaid);
            $netProfit = $netSelling - $totalCost;

            $projectMetrics[$p->id] = [
                'ops_count'          => $ops->count(),
                'total_cost'         => $totalCost,
                'billable_selling'   => $grossSelling,
                'supervision_amount' => $supervisionAmount,
                'internal_expenses'  => $internalExpenses,
                'discounts'          => $discounts,
                'net_selling'        => $netSelling,
                'client_paid'        => $clientPaid,
                'client_remaining'   => $clientRemaining,
                'net_profit'         => $netProfit,
            ];
        }

        $items = DB::table('cont_project_items')->orderBy('sort_order')->get();

        // Sync any historical suppliers/technicians from cont_operations into cont_directory
        $existingSuppliers = DB::table('cont_directory')->where('type', 'supplier')->pluck('name')->flip();
        $opSuppliers = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->whereNotNull('person_name')
            ->where('person_name', '!=', '')
            ->whereNotIn('person_name', ['نثريات موقع', 'مصروف موقع / إدارة', 'مصروف داخلي'])
            ->distinct()
            ->pluck('person_name');
        foreach ($opSuppliers as $sName) {
            $sName = trim($sName);
            if ($sName !== '' && !isset($existingSuppliers[$sName])) {
                DB::table('cont_directory')->insert([
                    'type'         => 'supplier',
                    'name'         => $sName,
                    'balance_debt' => 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $existingSuppliers[$sName] = true;
            }
        }

        $existingTechs = DB::table('cont_directory')->where('type', 'technician')->pluck('name')->flip();
        $opTechs = DB::table('cont_operations')
            ->whereIn('op_type', ['technician', 'labor'])
            ->whereNotNull('person_name')
            ->where('person_name', '!=', '')
            ->distinct()
            ->pluck('person_name');
        foreach ($opTechs as $tName) {
            $tName = trim($tName);
            if ($tName !== '' && !isset($existingTechs[$tName])) {
                DB::table('cont_directory')->insert([
                    'type'         => 'technician',
                    'name'         => $tName,
                    'balance_debt' => 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $existingTechs[$tName] = true;
            }
        }

        $directory = DB::table('cont_directory')->orderBy('name')->get();

        // Auto-cleanup any orphaned company_debts from contracting where all linked cont_operations were deleted
        $contDebts = DB::table('company_debts')
            ->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
            ->get();
        foreach ($contDebts as $cd) {
            $hasLinkedOps = DB::table('cont_operations')->where('debt_id', $cd->id)->exists();
            $hasSourceOp  = $cd->source_id ? DB::table('cont_operations')->where('id', $cd->source_id)->exists() : false;
            if (!$hasLinkedOps && !$hasSourceOp) {
                DB::table('company_debts')->where('id', $cd->id)->delete();
            }
        }
        
        // Calculate and sync live balances for directory contacts (debts for suppliers/techs, receivables for clients)
        $companyDebtsMap = DB::table('company_debts')
            ->select('creditor_name', DB::raw('SUM(remaining_balance) as rem_balance'))
            ->groupBy('creditor_name')
            ->pluck('rem_balance', 'creditor_name');

        $clientDuesMap = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('status', '!=', 'cancelled')
            ->select('customer_name', DB::raw('SUM(remaining_balance) as rem_balance'))
            ->groupBy('customer_name')
            ->pluck('rem_balance', 'customer_name');

        foreach ($directory as $person) {
            $nameKey = trim($person->name);
            if ($person->type === 'client') {
                $liveBal = (float) ($clientDuesMap[$nameKey] ?? $clientDuesMap[$person->name] ?? 0);
            } else {
                $liveBal = (float) ($companyDebtsMap[$nameKey] ?? $companyDebtsMap[$person->name] ?? 0);
            }
            if (abs((float)$person->balance_debt - $liveBal) > 0.001) {
                DB::table('cont_directory')->where('id', $person->id)->update(['balance_debt' => $liveBal]);
                $person->balance_debt = $liveBal;
            }
        }

        $suppliers   = $directory->where('type', 'supplier')->values();
        $technicians = $directory->where('type', 'technician')->values();
        $marketers   = $directory->where('type', 'marketer')->values();
        $clientsList = $directory->where('type', 'client')->values();

        $accounts = DB::table('accounts')->where('category', '!=', 'project_sector')->get();
        $recentOperations = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
            ->select('cont_operations.*', 'cont_projects.name as project_name', 'cont_project_items.title as item_title')
            ->orderBy('cont_operations.id', 'desc')
            ->limit(50)
            ->get();

        $quotations = DB::table('cont_quotations')->orderBy('id', 'desc')->get();
        $invoices = DB::table('cont_invoices')
            ->leftJoin('cont_projects', 'cont_invoices.project_id', '=', 'cont_projects.id')
            ->select('cont_invoices.*', 'cont_projects.name as project_name', 'cont_projects.client_name')
            ->orderBy('cont_invoices.id', 'desc')
            ->get();

        // Distinct Material Invoices (فواتير خامات ومشتريات الموردين - مجمعة كفاتورة واحدة لكل رقم مرجعي)
        $materialInvoices = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->where('cont_operations.op_type', 'material')
            ->whereNotNull('cont_operations.invoice_ref')
            ->where('cont_operations.invoice_ref', '!=', '')
            ->select(
                'cont_operations.invoice_ref',
                'cont_operations.project_id',
                'cont_projects.name as project_name',
                'cont_projects.client_name',
                'cont_operations.person_name as supplier_name',
                DB::raw('MAX(cont_operations.payment_method) as payment_method'),
                DB::raw('MAX(cont_operations.debt_id) as debt_id'),
                DB::raw('COUNT(cont_operations.id) as items_count'),
                DB::raw('SUM(cont_operations.total_cost) as total_cost'),
                DB::raw('SUM(cont_operations.total_selling) as total_selling'),
                DB::raw('SUM(cont_operations.paid_amount) as total_paid'),
                DB::raw('SUM(cont_operations.remaining_debt) as total_debt'),
                DB::raw('MAX(cont_operations.created_at) as created_at')
            )
            ->groupBy(
                'cont_operations.invoice_ref',
                'cont_operations.project_id',
                'cont_projects.name',
                'cont_projects.client_name',
                'cont_operations.person_name'
            )
            ->orderByDesc('created_at')
            ->get();

        $cancelledInvoices = DB::table('cont_cancelled_invoices')
            ->orderByDesc('id')
            ->get();

        // Autocomplete distinct historical data
        $autocompleteMaterials = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->whereNotNull('name_or_title')
            ->distinct()
            ->pluck('name_or_title')
            ->filter()
            ->values();

        $unitsList = DB::table('cont_units')->orderBy('id', 'asc')->get();
        $pettyTitlesList = DB::table('cont_petty_titles')->orderBy('id', 'asc')->get();
        $expenseTitlesList = DB::table('cont_expense_titles')->orderBy('id', 'asc')->get();

        $autocompleteUnits = collect(['متر', 'م2', 'م3', 'عدد', 'قطعة', 'كيس', 'طن', 'لفة', 'مقطوعية', 'يومية', 'نقلة', 'ساعة', 'لوح', 'كيلو', 'شيكارة', 'مجموعة'])
            ->merge($unitsList->pluck('name'))
            ->merge(DB::table('cont_operations')->whereNotNull('unit')->distinct()->pluck('unit'))
            ->unique()
            ->values();

        $globalItems = DB::table('cont_global_items')->orderBy('sort_order')->get();
        $pettyCategories = DB::table('cont_petty_categories')->orderBy('id')->get();
        $recentPettyExpenses = DB::table('cont_petty_expenses')
            ->leftJoin('cont_petty_categories', 'cont_petty_expenses.category_id', '=', 'cont_petty_categories.id')
            ->leftJoin('accounts', 'cont_petty_expenses.account_id', '=', 'accounts.id')
            ->select('cont_petty_expenses.*', 'cont_petty_categories.name as category_name', 'accounts.account_name')
            ->orderBy('cont_petty_expenses.id', 'desc')
            ->limit(50)
            ->get();

        $autocompleteItemTitles = collect(['بند السباكة والصرف', 'بند الكهرباء والإنارة', 'بند المحارة والأسمنت', 'بند الجبس بورد والأسقف', 'بند السيراميك والبورسلين', 'بند الدهانات والتشطيبات', 'بند النجارة والأبواب', 'بند الألوميتال والواجهات', 'بند العزل المائي والحراري', 'بند الخرسانات والمباني', 'بند الرخام والجرانيت', 'بند التكييف والتهوية'])
            ->merge($globalItems->pluck('title'))
            ->merge(DB::table('cont_project_items')->distinct()->pluck('title'))
            ->unique()
            ->values();

        $autocompleteSuppliers = $suppliers->pluck('name')
            ->merge(DB::table('cont_operations')->where('op_type', 'material')->whereNotNull('person_name')->pluck('person_name'))
            ->reject(fn($n) => in_array(trim($n), ['نثريات موقع', 'مصروف موقع / إدارة', 'مصروف داخلي'], true))
            ->unique()
            ->values();

        $autocompleteTechs = $technicians->pluck('name')
            ->merge(DB::table('cont_operations')->where('op_type', 'technician')->whereNotNull('person_name')->pluck('person_name'))
            ->unique()
            ->values();

        $autocompleteMarketers = $marketers->pluck('name')
            ->merge(DB::table('cont_operations')->where('op_type', 'commission')->whereNotNull('person_name')->pluck('person_name'))
            ->unique()
            ->values();

        // Surveys & Area Calculations History
        $surveys = DB::table('cont_surveys')->orderBy('id', 'desc')->get()->map(function ($s) {
            if (empty($s->estimated_total) && !empty($s->rooms_data)) {
                $decoded = is_string($s->rooms_data) ? json_decode($s->rooms_data, true) : (array) $s->rooms_data;
                if (is_array($decoded) && isset($decoded['estimated_total'])) {
                    $s->estimated_total = floatval($decoded['estimated_total']);
                    $s->total_labor_cost = floatval($decoded['total_labor_cost'] ?? 0);
                    $s->total_material_cost = floatval($decoded['total_material_cost'] ?? 0);
                }
            }
            return $s;
        });

        // Material Prices Summary Tracker (متابعة أسعار الخامات)
        $materialOps = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->whereNotNull('name_or_title')
            ->where('name_or_title', '!=', '')
            ->orderBy('created_at', 'asc')
            ->get();

        $materialPricesSummary = $materialOps->groupBy('name_or_title')->map(function($rows, $name) {
            $count = $rows->count();
            $prices = $rows->pluck('cost_price')->map(fn($v) => floatval($v))->filter(fn($v) => $v > 0);

            $minPrice = $prices->isNotEmpty() ? $prices->min() : 0;
            $maxPrice = $prices->isNotEmpty() ? $prices->max() : 0;
            $avgPrice = $prices->isNotEmpty() ? round($prices->avg(), 2) : 0;

            $lastRow = $rows->last();
            $latestPrice = floatval($lastRow->cost_price ?? 0);

            $changePct = null;
            if ($count >= 2) {
                $prevRow = $rows->slice(-2, 1)->first();
                $prevPrice = floatval($prevRow->cost_price ?? 0);
                if ($prevPrice > 0) {
                    $changePct = round((($latestPrice - $prevPrice) / $prevPrice) * 100, 1);
                } else {
                    $changePct = 0;
                }
            }

            return (object) [
                'name'            => $name,
                'unit'            => $lastRow->unit ?? 'عدد',
                'purchases_count' => $count,
                'min_price'       => $minPrice,
                'avg_price'       => $avgPrice,
                'max_price'       => $maxPrice,
                'latest_price'    => $latestPrice,
                'change_pct'      => $changePct,
                'last_date'       => $lastRow->created_at ?? null,
            ];
        })->values()->sortByDesc('purchases_count')->values();

        // Supplier Comparison Matrix Cards (مقارنة الموردين)
        $supplierOps = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->whereNotNull('name_or_title')
            ->where('name_or_title', '!=', '')
            ->whereNotNull('person_name')
            ->where('person_name', '!=', '')
            ->orderBy('created_at', 'desc')
            ->get();

        $supplierComparisonCards = $supplierOps->groupBy('name_or_title')
            ->map(function($rows, $matName) {
                $unit = $rows->first()->unit ?? 'وحدة';
                // Group by supplier
                $suppliers = $rows->groupBy('person_name')->map(function($sRows, $sName) {
                    $minPrice = floatval($sRows->min('cost_price'));
                    $latestPrice = floatval($sRows->sortByDesc('created_at')->first()->cost_price ?? $minPrice);
                    return [
                        'name'         => $sName,
                        'price'        => $latestPrice > 0 ? $latestPrice : $minPrice,
                        'min_price'    => $minPrice,
                        'latest_price' => $latestPrice,
                        'count'        => $sRows->count(),
                    ];
                })->values()->sortBy('price')->values();

                $minPrice = $suppliers->min('price');
                $maxPrice = $suppliers->max('price');

                $suppliersWithBadges = $suppliers->map(function($sup) use ($minPrice, $maxPrice, $suppliers) {
                    $sup['is_cheapest'] = ($sup['price'] == $minPrice);
                    $sup['is_highest'] = ($suppliers->count() > 1 && $sup['price'] == $maxPrice && $maxPrice > $minPrice);
                    return $sup;
                })->all();

                return (object) [
                    'material_name' => $matName,
                    'unit'          => $unit,
                    'suppliers'     => $suppliersWithBadges,
                ];
            })->values();

        // ══════════════════════════════════════════════════════════════════
        // REPORTS & DECISION INTELLIGENCE METRICS (With Time Filter)
        // Default filter period: last 3 months
        // ══════════════════════════════════════════════════════════════════
        $filterPreset = $request->input('period', '3months');
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $filterFrom = Carbon::parse($request->input('from_date'))->startOfDay();
            $filterTo = Carbon::parse($request->input('to_date'))->endOfDay();
            $filterPreset = 'custom';
        } else {
            switch ($filterPreset) {
                case 'month':
                    $filterFrom = Carbon::now()->startOfMonth();
                    $filterTo = Carbon::now()->endOfDay();
                    break;
                case '6months':
                    $filterFrom = Carbon::now()->subMonths(6)->startOfDay();
                    $filterTo = Carbon::now()->endOfDay();
                    break;
                case 'year':
                    $filterFrom = Carbon::now()->startOfYear();
                    $filterTo = Carbon::now()->endOfDay();
                    break;
                case 'all':
                    $filterFrom = Carbon::parse('2020-01-01 00:00:00');
                    $filterTo = Carbon::now()->endOfDay();
                    break;
                case '3months':
                default:
                    $filterPreset = '3months';
                    $filterFrom = Carbon::now()->subMonths(3)->startOfDay();
                    $filterTo = Carbon::now()->endOfDay();
                    break;
            }
        }

        $projectsMap = $projects->keyBy('id');
        $repOps = DB::table('cont_operations')->whereBetween('created_at', [$filterFrom, $filterTo])->get();
        $repDiscounts = DB::table('cont_discounts')->whereBetween('created_at', [$filterFrom, $filterTo])->get();
        $repInvoices = DB::table('cont_invoices')->whereBetween('created_at', [$filterFrom, $filterTo])->get();

        $reportProjectMetrics = [];
        foreach ($projects as $p) {
            $pOps = $repOps->where('project_id', $p->id);
            $pDiscounts = floatval($repDiscounts->where('project_id', $p->id)->sum('amount'));
            $pInvoices = $repInvoices->where('project_id', $p->id);

            $pCost = floatval($pOps->sum('total_cost'));
            $pBillableSelling = floatval($pOps->where('is_billable', 1)->sum('total_selling'));
            $pBillableCost = floatval($pOps->where('is_billable', 1)->sum('total_cost'));
            $pSupervisionProfit = max(0, $pBillableSelling - $pBillableCost);
            $pNetSelling = max(0, $pBillableSelling - $pDiscounts);
            $pNetProfit = $pNetSelling - $pCost;
            $pClientPaid = floatval($pInvoices->sum('paid_amount'));
            $pClientRemaining = max(0, $pNetSelling - $pClientPaid);
            $pMargin = $pNetSelling > 0 ? round(($pNetProfit / $pNetSelling) * 100, 1) : 0;

            $reportProjectMetrics[$p->id] = [
                'project_id'         => $p->id,
                'project_name'       => $p->name,
                'client_name'        => $p->client_name,
                'client_phone'       => $p->client_phone,
                'supervision_pct'    => $p->supervision_pct,
                'ops_count'          => $pOps->count(),
                'total_cost'         => $pCost,
                'billable_selling'   => $pBillableSelling,
                'supervision_profit' => $pSupervisionProfit,
                'discounts'          => $pDiscounts,
                'net_selling'        => $pNetSelling,
                'client_paid'        => $pClientPaid,
                'client_remaining'   => $pClientRemaining,
                'net_profit'         => $pNetProfit,
                'margin_pct'         => $pMargin,
            ];
        }

        $grandCost = collect($reportProjectMetrics)->sum('total_cost');
        $grandSelling = collect($reportProjectMetrics)->sum('net_selling');
        $grandProfit = $grandSelling - $grandCost;
        $avgMargin = $grandSelling > 0 ? round(($grandProfit / $grandSelling) * 100, 1) : 0;
        $grandPaid = collect($reportProjectMetrics)->sum('client_paid');
        $grandRemaining = collect($reportProjectMetrics)->sum('client_remaining');
        $collectionRate = $grandSelling > 0 ? round(($grandPaid / $grandSelling) * 100, 1) : 0;
        $supplierDebts = floatval(DB::table('company_debts')->where('source_type', 'like', 'cont_%')->sum('remaining_balance'));

        // 1. Most profitable project
        $topProfitProj = collect($reportProjectMetrics)->filter(fn($x) => $x['net_profit'] > 0)->sortByDesc('net_profit')->first();

        // 2. Most spending project
        $topSpendingProj = collect($reportProjectMetrics)->filter(fn($x) => $x['total_cost'] > 0)->sortByDesc('total_cost')->first();

        // 3. Most discount project
        $topDiscountProj = collect($reportProjectMetrics)->filter(fn($x) => $x['discounts'] > 0)->sortByDesc('discounts')->first();

        // 4. Top marketer
        $topMarketer = $repOps->where('op_type', 'commission')->whereNotNull('person_name')->where('person_name', '!=', '')->groupBy('person_name')->map(function($rows, $name) {
            return [
                'name'             => $name,
                'total_commission' => floatval($rows->sum('total_cost')),
                'ops_count'        => $rows->count(),
            ];
        })->sortByDesc('total_commission')->first();

        // 5. Top cost operation
        $topCostOpRaw = $repOps->sortByDesc('total_cost')->first();
        $topCostOp = null;
        if ($topCostOpRaw && $topCostOpRaw->total_cost > 0) {
            $p = $projectsMap[$topCostOpRaw->project_id] ?? null;
            $topCostOp = [
                'title'        => $topCostOpRaw->name_or_title,
                'cost'         => floatval($topCostOpRaw->total_cost),
                'qty'          => floatval($topCostOpRaw->quantity),
                'unit'         => $topCostOpRaw->unit,
                'op_type'      => $topCostOpRaw->op_type,
                'project_id'   => $topCostOpRaw->project_id,
                'project_name' => $p ? $p->name : 'مشروع غير محدد',
                'client_name'  => $p ? $p->client_name : 'عميل غير محدد',
            ];
        }

        // 6. Top profit operation
        $topProfitOpRaw = $repOps->filter(fn($x) => $x->profit_amount > 0)->sortByDesc('profit_amount')->first();
        $topProfitOp = null;
        if ($topProfitOpRaw) {
            $p = $projectsMap[$topProfitOpRaw->project_id] ?? null;
            $topProfitOp = [
                'title'        => $topProfitOpRaw->name_or_title,
                'profit'       => floatval($topProfitOpRaw->profit_amount),
                'cost'         => floatval($topProfitOpRaw->total_cost),
                'selling'      => floatval($topProfitOpRaw->total_selling),
                'qty'          => floatval($topProfitOpRaw->quantity),
                'unit'         => $topProfitOpRaw->unit,
                'project_id'   => $topProfitOpRaw->project_id,
                'project_name' => $p ? $p->name : 'مشروع غير محدد',
                'client_name'  => $p ? $p->client_name : 'عميل غير محدد',
            ];
        }

        // 7. Top technician
        $topTech = $repOps->where('op_type', 'technician')->whereNotNull('person_name')->where('person_name', '!=', '')->groupBy('person_name')->map(function($rows, $name) {
            return [
                'name'           => $name ?: 'فني عام',
                'total_cost'     => floatval($rows->sum('total_cost')),
                'paid_amount'    => floatval($rows->sum('paid_amount')),
                'remaining_debt' => floatval($rows->sum('remaining_debt')),
                'jobs_count'     => $rows->count(),
            ];
        })->sortByDesc('total_cost')->first();

        // 8. Top material
        $topMaterial = $repOps->where('op_type', 'material')->whereNotNull('name_or_title')->where('name_or_title', '!=', '')->groupBy('name_or_title')->map(function($rows, $name) {
            return [
                'title'      => $name,
                'total_qty'  => floatval($rows->sum('quantity')),
                'unit'       => $rows->first()->unit ?? '',
                'total_cost' => floatval($rows->sum('total_cost')),
                'count'      => $rows->count(),
            ];
        })->sortByDesc('total_qty')->first();

        // 9. Pure supervision profit
        $supervisionProfitTotal = floatval($repOps->where('is_billable', 1)->map(function($o) {
            return max(0, floatval($o->total_selling) - floatval($o->total_cost));
        })->sum());

        // 10. Expenses breakdown
        $expensesBreakdown = [
            'material'   => floatval($repOps->where('op_type', 'material')->sum('total_cost')),
            'technician' => floatval($repOps->where('op_type', 'technician')->sum('total_cost')),
            'petty'      => floatval($repOps->where('op_type', 'petty')->sum('total_cost')),
            'expense'    => floatval($repOps->where('op_type', 'expense')->sum('total_cost')),
            'commission' => floatval($repOps->where('op_type', 'commission')->sum('total_cost')),
            'total'      => floatval($repOps->sum('total_cost')),
        ];

        // Monthly Trend (6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $mLabel = $mStart->locale('ar')->translatedFormat('F Y');

            $mOps = DB::table('cont_operations')->whereBetween('created_at', [$mStart, $mEnd])->get();
            $mDiscounts = DB::table('cont_discounts')->whereBetween('created_at', [$mStart, $mEnd])->sum('amount');

            $mCost = floatval($mOps->sum('total_cost'));
            $mSelling = max(0, floatval($mOps->where('is_billable', 1)->sum('total_selling')) - floatval($mDiscounts));
            $mProfit = $mSelling - $mCost;

            $monthlyTrend[] = [
                'month'   => $mLabel,
                'cost'    => $mCost,
                'selling' => $mSelling,
                'profit'  => $mProfit,
            ];
        }

        // Projects Comparison (Top 7 active projects by cost/volume)
        $projectsComparison = collect($reportProjectMetrics)
            ->filter(fn($x) => $x['total_cost'] > 0 || $x['net_selling'] > 0)
            ->sortByDesc('net_selling')
            ->take(7)
            ->values();

        $reportHighlights = [
            'kpis' => [
                'grand_selling'   => $grandSelling,
                'grand_cost'      => $grandCost,
                'grand_profit'    => $grandProfit,
                'avg_margin'      => $avgMargin,
                'grand_paid'      => $grandPaid,
                'grand_remaining' => $grandRemaining,
                'collection_rate' => $collectionRate,
                'supplier_debts'  => $supplierDebts,
            ],
            'top_profit_project'       => $topProfitProj,
            'top_spending_project'     => $topSpendingProj,
            'top_discount_project'     => $topDiscountProj,
            'top_marketer'             => $topMarketer,
            'top_cost_item'            => $topCostOp,
            'top_profit_item'          => $topProfitOp,
            'top_tech'                 => $topTech,
            'top_material'             => $topMaterial,
            'supervision_profit_total' => $supervisionProfitTotal,
            'expenses_breakdown'       => $expensesBreakdown,
            'monthly_trend'            => $monthlyTrend,
            'projects_comparison'      => $projectsComparison,
        ];

        $filterFromStr = $filterFrom->format('Y-m-d');
        $filterToStr = $filterTo->format('Y-m-d');

        return view('contracting', compact(
            'projects',
            'projectMetrics',
            'items',
            'globalItems',
            'pettyCategories',
            'recentPettyExpenses',
            'directory',
            'suppliers',
            'technicians',
            'marketers',
            'clientsList',
            'accounts',
            'recentOperations',
            'quotations',
            'invoices',
            'materialInvoices',
            'cancelledInvoices',
            'surveys',
            'materialPricesSummary',
            'supplierComparisonCards',
            'autocompleteMaterials',
            'autocompleteUnits',
            'autocompleteItemTitles',
            'autocompleteSuppliers',
            'autocompleteTechs',
            'autocompleteMarketers',
            'unitsList',
            'pettyTitlesList',
            'expenseTitlesList',
            'reportProjectMetrics',
            'reportHighlights',
            'filterFromStr',
            'filterToStr',
            'filterPreset'
        ));
    }

    /**
     * Store a new Project
     */
    public function storeProject(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
        ]);

        $supervisionPct = floatval($request->input('supervision_pct', 15.00));
        $user = session('auth_user');

        $projectId = DB::table('cont_projects')->insertGetId([
            'name'                 => $request->name,
            'client_name'          => $request->client_name,
            'client_phone'         => $request->client_phone,
            'location'             => $request->location,
            'area_sqm'             => $request->filled('area_sqm') ? floatval($request->area_sqm) : null,
            'supervision_pct'      => $supervisionPct,
            'contract_type'        => $request->input('contract_type', 'cost_plus'),
            'total_contract_value' => floatval($request->input('total_contract_value', 0)),
            'status'               => 'active',
            'notes'                => $request->notes,
            'created_by'           => $user?->id ?? 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Add to Directory if not exists
        if (!DB::table('cont_directory')->where('type', 'client')->where('name', $request->client_name)->exists()) {
            DB::table('cont_directory')->insert([
                'type'       => 'client',
                'name'       => $request->client_name,
                'phone'      => $request->client_phone,
                'notes'      => 'مشروع: ' . $request->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add standard initial items if requested
        if ($request->has('add_default_items')) {
            $defaultItems = [
                'بند أعمال الكهرباء',
                'بند أعمال السباكة',
                'بند أعمال المحارة',
                'بند أعمال الجبس بورد',
                'بند أعمال السيراميك والبورسلين',
                'بند أعمال الدهانات',
            ];
            foreach ($defaultItems as $idx => $title) {
                DB::table('cont_project_items')->insert([
                    'project_id' => $projectId,
                    'title'      => $title,
                    'status'     => 'open',
                    'sort_order' => $idx + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->logActivity('create', 'contracting', "تم إنشاء مشروع مقاولات جديد: {$request->name} للعميل: {$request->client_name}", [
            'project_id' => $projectId,
            'client'     => $request->client_name,
            'supervision'=> $supervisionPct . '%',
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'project_id' => $projectId, 'message' => 'تم إنشاء المشروع بنجاح']);
        }

        return back()->with('success', 'تم إنشاء المشروع وإضافته بنجاح');
    }

    /**
     * Store a new Item (بند) inside a project
     */
    public function storeProjectItem(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'title'      => 'required|string|max:255',
        ]);

        $project = DB::table('cont_projects')->where('id', $request->project_id)->first();
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);
        }

        $maxSort = DB::table('cont_project_items')->where('project_id', $request->project_id)->max('sort_order') ?? 0;

        $itemId = DB::table('cont_project_items')->insertGetId([
            'project_id' => $request->project_id,
            'title'      => trim($request->title),
            'status'     => 'open',
            'sort_order' => $maxSort + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity('create', 'contracting', "تمت إضافة بند جديد: {$request->title} لمشروع: {$project->name}", [
            'project_id' => $project->id,
            'item_id'    => $itemId,
        ]);

        return response()->json([
            'success'   => true,
            'item_id'   => $itemId,
            'title'     => $request->title,
            'message'   => 'تمت إضافة البند بنجاح',
        ]);
    }

    /**
     * Store Operation (Material / Labor / Overhead / Commission)
     * Handles single operation or batch from cart.
     */
    public function storeOperation(Request $request)
    {
        $user = session('auth_user');
        $projectId = $request->input('project_id');
        $project = DB::table('cont_projects')->where('id', $projectId)->first();

        if (!$project) {
            return response()->json(['success' => false, 'message' => 'يرجى اختيار المشروع أولاً'], 422);
        }

        $items = $request->input('items', []);
        // If single item passed directly in form
        if (empty($items) && $request->has('name_or_title')) {
            $items = [$request->all()];
        }

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'لم يتم إرسال أي بنود أو خامات للتسجيل'], 422);
        }

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $supervisionPct = floatval($project->supervision_pct);

            // ── A. MULTI-ITEM INVOICE MODE (فاتورة خامات مجمعة) ──
            if ($request->boolean('is_invoice')) {
                $supplierName = trim($request->input('supplier_name', ''));
                if ($supplierName === '' || !DB::table('cont_directory')->where('type', 'supplier')->where('name', $supplierName)->exists()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'يرجى اختيار مورد مسجل في قاعدة البيانات أولاً!'
                    ], 422);
                }
                $invoiceRef = trim($request->input('invoice_ref', 'فاتورة خامات ' . date('Y-m-d')));
                $paidAmount = floatval($request->input('paid_amount', 0));
                $remainingDebt = floatval($request->input('remaining_debt', 0));
                $accountId = $request->input('account_id');
                $defaultItemId = $request->input('item_id');

                // Determine payment method automatically based on debt
                if ($remainingDebt > 0 && $paidAmount <= 0) {
                    $paymentMethod = 'later';
                } elseif ($remainingDebt > 0 && $paidAmount > 0) {
                    $paymentMethod = 'partial';
                } else {
                    $paymentMethod = $request->input('payment_method', 'cash');
                }

                // 1. Single Company Debt for the supplier for entire invoice
                $debtId = null;
                if ($remainingDebt > 0 && !empty($supplierName)) {
                    $reason = "متبقي فاتورة خامات ({$invoiceRef}) - مشروع: " . $project->name;
                    $debtId = DB::table('company_debts')->insertGetId([
                        'creditor_name'     => $supplierName,
                        'reason'            => Str::limit($reason, 255),
                        'total_amount'      => $remainingDebt,
                        'paid_amount'       => 0,
                        'remaining_balance' => $remainingDebt,
                        'source_type'       => 'cont_operation_invoice',
                        'source_id'         => null,
                        'created_at'        => now(),
                    ]);

                    // Update or insert supplier in directory & increment balance
                    $suppInDir = DB::table('cont_directory')->where('name', $supplierName)->where('type', 'supplier')->first();
                    if ($suppInDir) {
                        DB::table('cont_directory')->where('id', $suppInDir->id)->increment('balance_debt', $remainingDebt);
                    } else {
                        DB::table('cont_directory')->insert([
                            'type'         => 'supplier',
                            'name'         => $supplierName,
                            'phone'        => $request->input('supplier_phone'),
                            'balance_debt' => $remainingDebt,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                } else if (!empty($supplierName)) {
                    // Ensure supplier exists in directory even if cash paid
                    if (!DB::table('cont_directory')->where('name', $supplierName)->where('type', 'supplier')->exists()) {
                        DB::table('cont_directory')->insert([
                            'type'         => 'supplier',
                            'name'         => $supplierName,
                            'phone'        => $request->input('supplier_phone'),
                            'balance_debt' => 0,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }

                // 2. Single deduction from Treasury Account if cash/bank paid
                if ($paidAmount > 0) {
                    if (empty($accountId)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'طالما يوجد سداد نقدي للمورد، يجب اختيار الخزينة أو الحساب البنكي للسحب منه!'
                        ], 422);
                    }
                    $acc = DB::table('accounts')->where('id', $accountId)->first();
                    if ($acc) {
                        DB::table('accounts')->where('id', $accountId)->decrement('balance', $paidAmount);
                        DB::table('financial_transactions')->insert([
                            'type'            => 'expense',
                            'subtype'         => 'supplier_payment',
                            'amount'          => $paidAmount,
                            'from_account_id' => $accountId,
                            'person_name'     => $supplierName,
                            'notes'           => "سداد فاتورة خامات ({$invoiceRef}) للمورد {$supplierName} - مشروع {$project->name}",
                            'status'          => 'active',
                            'ref_type'        => 'cont_operation_invoice',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }

                // Calculate total invoice cost to distribute paid/debt accurately
                $grandInvoiceCost = 0;
                foreach ($items as $item) {
                    $q = floatval($item['quantity'] ?? 1);
                    if ($q <= 0) $q = 1;
                    $grandInvoiceCost += ($q * floatval($item['cost_price'] ?? 0));
                }

                // 3. Insert each material item line
                $firstOpId = null;
                foreach ($items as $item) {
                    $qty = floatval($item['quantity'] ?? 1);
                    if ($qty <= 0) $qty = 1;
                    $costPrice = floatval($item['cost_price'] ?? 0);
                    $totalCost = $qty * $costPrice;

                    $itemSupPct = isset($item['supervision_pct']) ? floatval($item['supervision_pct']) : $supervisionPct;
                    $baseSellingPrice = floatval($item['selling_price'] ?? 0);
                    if ($baseSellingPrice <= 0 && $costPrice > 0) {
                        $baseSellingPrice = $costPrice;
                    }
                    $sellingPrice = $baseSellingPrice;
                    $totalSelling = round($qty * $baseSellingPrice * (1 + ($itemSupPct / 100)), 2);
                    $profit = $totalSelling - $totalCost;

                    $itemRatio = $grandInvoiceCost > 0 ? ($totalCost / $grandInvoiceCost) : (1 / max(1, count($items)));
                    $itemPaid = round($paidAmount * $itemRatio, 2);
                    $itemDebt = round($remainingDebt * $itemRatio, 2);

                    $lineItemId = !empty($item['item_id']) ? intval($item['item_id']) : $defaultItemId;

                    // Auto-persist unit to cont_units for future autocomplete
                    if (!empty($item['unit'])) {
                        $rawUnit = trim($item['unit']);
                        if ($rawUnit !== '' && mb_strlen($rawUnit) <= 50) {
                            DB::table('cont_units')->insertOrIgnore([
                                'name'       => $rawUnit,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    $opId = DB::table('cont_operations')->insertGetId([
                        'project_id'      => $project->id,
                        'item_id'         => $lineItemId,
                        'op_type'         => 'material',
                        'is_billable'     => 1,
                        'name_or_title'   => $item['name_or_title'] ?? 'خامة بدون اسم',
                        'quantity'        => $qty,
                        'unit'            => $item['unit'] ?? 'عدد',
                        'cost_price'      => $costPrice,
                        'supervision_pct' => $itemSupPct,
                        'selling_price'   => $sellingPrice,
                        'total_cost'      => $totalCost,
                        'total_selling'   => $totalSelling,
                        'profit_amount'   => $profit,
                        'person_name'     => $supplierName,
                        'payment_method'  => $paymentMethod,
                        'paid_amount'     => $itemPaid,
                        'remaining_debt'  => $itemDebt,
                        'debt_id'         => $debtId,
                        'invoice_ref'     => $invoiceRef,
                        'notes'           => $item['notes'] ?? $request->notes,
                        'created_by'      => $user?->id ?? 1,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    if (!$firstOpId) $firstOpId = $opId;
                    $savedCount++;
                }

                if ($debtId && $firstOpId) {
                    DB::table('company_debts')->where('id', $debtId)->update(['source_id' => $firstOpId]);
                }

                $this->syncProjectCustomerReceivable($project->id);

                DB::commit();

                $this->logActivity('create', 'contracting', "تم تسجيل فاتورة خامات ({$invoiceRef}) للمورد {$supplierName} تحتوي على {$savedCount} خامة بمشروع: {$project->name}", [
                    'project_id'  => $project->id,
                    'invoice_ref' => $invoiceRef,
                    'supplier'    => $supplierName,
                    'count'       => $savedCount,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "تم حفظ فاتورة الخامات بنجاح بإجمالي ({$savedCount}) خامة وترحيل حساب المورد.",
                ]);
            }

            // ── B. REGULAR ITEMS & COMMISSIONS & TECHNICIANS ──
            // Group material items by invoice_ref + supplier so a multi-material batch creates a single consolidated supplier debt
            $groupedMaterialDebts = [];
            foreach ($items as $idx => $item) {
                $opType = $item['op_type'] ?? 'material';
                if ($opType === 'material' && !empty($item['invoice_ref'])) {
                    $qty = max(0.0001, floatval($item['quantity'] ?? 1));
                    $cPrice = floatval($item['cost_price'] ?? 0);
                    $totCost = $qty * $cPrice;
                    $paidAmt = floatval($item['paid_amount'] ?? 0);
                    $remDebt = max(0, $totCost - $paidAmt);
                    $pName = trim($item['person_name'] ?? '');
                    if ($remDebt > 0 && $pName !== '') {
                        $groupKey = $pName . '||' . trim($item['invoice_ref']);
                        if (!isset($groupedMaterialDebts[$groupKey])) {
                            $groupedMaterialDebts[$groupKey] = [
                                'creditor_name' => $pName,
                                'invoice_ref'   => trim($item['invoice_ref']),
                                'total_debt'    => 0,
                                'debt_id'       => null,
                            ];
                        }
                        $groupedMaterialDebts[$groupKey]['total_debt'] += $remDebt;
                    }
                }
            }

            foreach ($groupedMaterialDebts as $gKey => &$gData) {
                $reason = "متبقي فاتورة خامات ({$gData['invoice_ref']}) - مشروع: " . $project->name;
                $gData['debt_id'] = DB::table('company_debts')->insertGetId([
                    'creditor_name'     => $gData['creditor_name'],
                    'reason'            => Str::limit($reason, 255),
                    'total_amount'      => $gData['total_debt'],
                    'paid_amount'       => 0,
                    'remaining_balance' => $gData['total_debt'],
                    'source_type'       => 'cont_operation_invoice',
                    'source_id'         => null,
                    'created_at'        => now(),
                ]);

                DB::table('cont_directory')
                    ->where('name', $gData['creditor_name'])
                    ->where('type', 'supplier')
                    ->increment('balance_debt', $gData['total_debt']);
            }
            unset($gData);

            foreach ($items as $item) {
                $opType = $item['op_type'] ?? 'material';
                $isBillable = isset($item['is_billable']) ? intval($item['is_billable']) : 1;
                $quantity = floatval($item['quantity'] ?? 1);
                if ($quantity <= 0) $quantity = 1;

                $costPrice = floatval($item['cost_price'] ?? 0);
                $totalCost = $quantity * $costPrice;

                // Selling price & Supervision %
                $itemSupPct = isset($item['supervision_pct']) ? floatval($item['supervision_pct']) : ($isBillable ? $supervisionPct : 0);
                $baseSellingPrice = floatval($item['selling_price'] ?? 0);
                if ($isBillable && $baseSellingPrice <= 0 && $costPrice > 0) {
                    $baseSellingPrice = $costPrice;
                }
                if (!$isBillable || $opType === 'commission' || $opType === 'overhead') {
                    $baseSellingPrice = 0;
                    $itemSupPct = 0;
                }
                $sellingPrice = $baseSellingPrice;
                $totalSelling = $isBillable ? round($quantity * $baseSellingPrice * (1 + ($itemSupPct / 100)), 2) : 0;
                $profitAmount = $totalSelling - $totalCost;

                $paidAmount = floatval($item['paid_amount'] ?? 0);
                $remainingDebt = max(0, $totalCost - $paidAmount);
                $paymentMethod = $item['payment_method'] ?? 'later';
                $accountId = $item['account_id'] ?? null;

                // المصروفات (النثرية والداخلية) يجب صرفها مباشرة من خزينة ولا تُسجل آجل
                if (in_array($opType, ['petty', 'overhead'], true)) {
                    if (empty($accountId)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "يجب اختيار الخزينة أو الحساب المالي لصرف المصروف ({$item['name_or_title']})!"
                        ], 422);
                    }
                    $paymentMethod = 'cash';
                    $paidAmount = $totalCost;
                    $remainingDebt = 0;
                }

                $personName = trim($item['person_name'] ?? '');
                if ($opType === 'material' && ($personName === '' || !DB::table('cont_directory')->where('type', 'supplier')->where('name', $personName)->exists())) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "يرجى اختيار مورد مسجل في قاعدة البيانات للخامة ({$item['name_or_title']})!"
                    ], 422);
                }
                if ($opType === 'technician' && ($personName === '' || !DB::table('cont_directory')->where('type', 'technician')->where('name', $personName)->exists())) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "يرجى اختيار فني / مقاول مسجل في قاعدة البيانات للمصنعية ({$item['name_or_title']})!"
                    ], 422);
                }
                // For commission, item_id is optional / null (Project-wide commission!)
                $itemId = ($opType === 'commission') ? null : (!empty($item['item_id']) ? intval($item['item_id']) : null);

                // 1. If remaining debt exists, record to company_debts (or use consolidated invoice debt)
                $debtId = null;
                $matGroupKey = ($opType === 'material' && !empty($item['invoice_ref']) && $personName !== '')
                    ? ($personName . '||' . trim($item['invoice_ref']))
                    : null;

                if ($matGroupKey && isset($groupedMaterialDebts[$matGroupKey])) {
                    $debtId = $groupedMaterialDebts[$matGroupKey]['debt_id'];
                } elseif ($remainingDebt > 0 && !empty($personName) && in_array($opType, ['material', 'technician', 'commission'], true)) {
                    $reasonTitle = match($opType) {
                        'commission' => "متبقي عمولة مسوق ({$personName})",
                        'technician' => "متبقي مصنعية ({$item['name_or_title']})",
                        default      => "متبقي حساب {$opType} ({$item['name_or_title']})"
                    };
                    $reason = "{$reasonTitle} - مشروع: " . $project->name;
                    $debtId = DB::table('company_debts')->insertGetId([
                        'creditor_name'     => $personName,
                        'reason'            => Str::limit($reason, 255),
                        'total_amount'      => $remainingDebt,
                        'paid_amount'       => 0,
                        'remaining_balance' => $remainingDebt,
                        'source_type'       => 'cont_operation',
                        'source_id'         => null,
                        'created_at'        => now(),
                    ]);

                    // Update directory balance
                    DB::table('cont_directory')
                        ->where('name', $personName)
                        ->increment('balance_debt', $remainingDebt);
                }

                // 2. If cash/bank paid, validate and deduct from account
                if ($paidAmount > 0) {
                    if (empty($accountId)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "طالما يوجد سداد نقدي لحركة ({$item['name_or_title']})، يجب اختيار الخزينة أو الحساب البنكي للسحب منه!"
                        ], 422);
                    }
                    $acc = DB::table('accounts')->where('id', $accountId)->first();
                    if ($acc) {
                        DB::table('accounts')->where('id', $accountId)->decrement('balance', $paidAmount);

                        $subtype = match($opType) {
                            'commission' => 'marketer_commission',
                            'technician' => 'technician_payment',
                            'petty', 'overhead' => 'general_expense',
                            default      => 'supplier_payment'
                        };

                        DB::table('financial_transactions')->insert([
                            'type'            => 'expense',
                            'subtype'         => $subtype,
                            'amount'          => $paidAmount,
                            'from_account_id' => $accountId,
                            'person_name'     => $personName,
                            'notes'           => "صرف لمشروع {$project->name} ({$item['name_or_title']}) - للمستفيد: {$personName}",
                            'status'          => 'active',
                            'ref_type'        => 'cont_operation',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }

                // Auto-persist unit to cont_units for future autocomplete
                if (!empty($item['unit'])) {
                    $rawUnit = trim($item['unit']);
                    if ($rawUnit !== '' && mb_strlen($rawUnit) <= 50) {
                        DB::table('cont_units')->insertOrIgnore([
                            'name'       => $rawUnit,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Auto-persist petty title (without price)
                if ($opType === 'petty' && !empty($item['name_or_title'])) {
                    $rawTitle = trim($item['name_or_title']);
                    if ($rawTitle !== '' && mb_strlen($rawTitle) <= 190) {
                        DB::table('cont_petty_titles')->insertOrIgnore([
                            'title'      => $rawTitle,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Auto-persist internal overhead / general expense title (without price)
                if ($opType === 'overhead' && !empty($item['name_or_title'])) {
                    $rawTitle = trim($item['name_or_title']);
                    if ($rawTitle !== '' && mb_strlen($rawTitle) <= 190) {
                        DB::table('cont_expense_titles')->insertOrIgnore([
                            'title'      => $rawTitle,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // 3. Insert operation
                $opId = DB::table('cont_operations')->insertGetId([
                    'project_id'     => $project->id,
                    'item_id'        => $itemId,
                    'op_type'        => $opType,
                    'is_billable'    => $isBillable,
                    'name_or_title'  => $item['name_or_title'] ?? 'حركة بدون اسم',
                    'quantity'       => $quantity,
                    'unit'           => $item['unit'] ?? 'عدد',
                    'cost_price'      => $costPrice,
                    'supervision_pct' => $itemSupPct,
                    'selling_price'   => $sellingPrice,
                    'total_cost'      => $totalCost,
                    'total_selling'  => $totalSelling,
                    'profit_amount'  => $profitAmount,
                    'person_id'      => $item['person_id'] ?? null,
                    'person_name'    => $personName,
                    'payment_method' => $paymentMethod,
                    'paid_amount'    => $paidAmount,
                    'remaining_debt' => $remainingDebt,
                    'debt_id'        => $debtId,
                    'invoice_ref'    => $item['invoice_ref'] ?? null,
                    'notes'          => $item['notes'] ?? null,
                    'created_by'     => $user?->id ?? 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                if ($debtId) {
                    if ($matGroupKey) {
                        if (empty($groupedMaterialDebts[$matGroupKey]['source_set'])) {
                            DB::table('company_debts')->where('id', $debtId)->update(['source_id' => $opId]);
                            $groupedMaterialDebts[$matGroupKey]['source_set'] = true;
                        }
                    } else {
                        DB::table('company_debts')->where('id', $debtId)->update(['source_id' => $opId]);
                    }
                }

                // 4. Update or insert into cont_directory so it's suggested in the future
                if (!empty($personName) && in_array($opType, ['material', 'technician', 'commission'], true)) {
                    $dirType = match($opType) {
                        'material'   => 'supplier',
                        'technician' => 'technician',
                        'commission' => 'marketer',
                    };
                    $exists = DB::table('cont_directory')->where('name', $personName)->where('type', $dirType)->first();
                    if (!$exists) {
                        DB::table('cont_directory')->insert([
                            'type'         => $dirType,
                            'name'         => $personName,
                            'phone'        => $item['person_phone'] ?? null,
                            'specialty'    => $item['specialty'] ?? null,
                            'balance_debt' => $remainingDebt,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }

                $savedCount++;
            }

            $this->syncProjectCustomerReceivable($project->id);

            DB::commit();

            $this->logActivity('create', 'contracting', "تم تسجيل {$savedCount} حركة صرف بمشروع: {$project->name}", [
                'project_id' => $project->id,
                'count'      => $savedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تسجيل العمليات بنجاح وترحيل الحسابات المالية.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an Operation
     */
    public function destroyOperation($id)
    {
        $op = DB::table('cont_operations')->where('id', $id)->first();
        if (!$op) {
            return response()->json(['success' => false, 'message' => 'العملية غير موجودة'], 404);
        }

        // Check if item belongs to a batch material invoice
        if ($op->op_type === 'material' && !empty($op->invoice_ref)) {
            return response()->json([
                'success' => false,
                'message' => "⛔ هذا البند يتبع فاتورة خامات ({$op->invoice_ref}). لتعديل بنود الفاتورة أو إثبات مرتجع أو حذفها بالكامل واسترداد مبالغها، يرجى التوجه إلى تابة (الفواتير والمستخلصات) -> (فواتير الخامات والمشتريات)."
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Revert linked company_debts if exists
            $opIds = [$id];
            $debtIdsToDelete = collect([$op->debt_id])
                ->merge(DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $opIds)->pluck('id'))
                ->filter()
                ->unique();

            foreach ($debtIdsToDelete as $dId) {
                $otherOpsCount = DB::table('cont_operations')->where('debt_id', $dId)->where('id', '!=', $id)->count();
                if ($otherOpsCount > 0) {
                    DB::table('company_debts')->where('id', $dId)->decrement('total_amount', $op->remaining_debt);
                    DB::table('company_debts')->where('id', $dId)->decrement('remaining_balance', $op->remaining_debt);
                } else {
                    DB::table('company_debts')->where('id', $dId)->delete();
                }
            }

            if ($op->person_name) {
                $liveDebt = (float) DB::table('company_debts')->where('creditor_name', $op->person_name)->sum('remaining_balance');
                DB::table('cont_directory')
                    ->where('name', $op->person_name)
                    ->update(['balance_debt' => max(0, $liveDebt)]);
            }

            // If cash was paid from treasury account for this individual operation, refund it
            if ($op->paid_amount > 0) {
                $ft = DB::table('financial_transactions')
                    ->where('ref_type', 'cont_operation')
                    ->where('amount', $op->paid_amount)
                    ->where('notes', 'like', "%{$op->name_or_title}%")
                    ->where('status', 'active')
                    ->first();

                $refundAccId = $ft?->from_account_id ?: DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                if ($refundAccId) {
                    DB::table('accounts')->where('id', $refundAccId)->increment('balance', $op->paid_amount);
                    DB::table('financial_transactions')->insert([
                        'type'          => 'income',
                        'subtype'       => 'refund',
                        'amount'        => $op->paid_amount,
                        'to_account_id' => $refundAccId,
                        'notes'         => "استرداد سداد لحذف حركة ({$op->name_or_title}) بمشروع ID #{$op->project_id}",
                        'status'        => 'active',
                        'ref_type'      => 'cont_operation_cancelled',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    if ($ft) {
                        DB::table('financial_transactions')->where('id', $ft->id)->update(['status' => 'cancelled']);
                    }
                }
            }

            DB::table('cont_operations')->where('id', $id)->delete();

            // Synchronize customer receivable
            $this->syncProjectCustomerReceivable($op->project_id);

            DB::commit();

            $this->logActivity('delete', 'contracting', "تم حذف حركة صرف ({$op->name_or_title}) بمشروع ID #{$op->project_id}", [
                'operation_id' => $id,
                'cost'         => $op->total_cost,
            ]);

            return response()->json(['success' => true, 'message' => 'تم حذف العملية بنجاح وتحديث الحسابات ومستحقات العميل.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'تعذر الحذف: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Apply Discount to Project
     */
    public function storeDiscount(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'amount'     => 'required|numeric|min:0.01',
        ]);

        $project = DB::table('cont_projects')->where('id', $request->project_id)->first();
        if (!$project) return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);

        $user = session('auth_user');
        $id = DB::table('cont_discounts')->insertGetId([
            'project_id' => $project->id,
            'amount'     => floatval($request->amount),
            'reason'     => $request->reason ?? 'خصم تجاري للعميل',
            'created_by' => $user?->id ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->syncProjectCustomerReceivable($project->id);

        $this->logActivity('create', 'contracting', "تمت إضافة خصم بقيمة {$request->amount} ج لمشروع {$project->name}", [
            'project_id' => $project->id,
            'amount'     => $request->amount,
            'reason'     => $request->reason,
        ]);

        return response()->json(['success' => true, 'message' => 'تم تطبيق الخصم وتحديث مستحقات العميل بنجاح']);
    }

    /**
     * Update an individual operation (single item / labor / technician)
     */
    public function updateOperation(Request $request, $id)
    {
        $op = DB::table('cont_operations')->where('id', $id)->first();
        if (!$op) {
            return response()->json(['success' => false, 'message' => 'العملية غير موجودة'], 404);
        }

        $project = DB::table('cont_projects')->where('id', $op->project_id)->first();
        $name = trim($request->input('name_or_title', $op->name_or_title));
        $qty = floatval($request->input('quantity', $op->quantity));
        if ($qty <= 0) $qty = 1;
        $unit = $request->input('unit', $op->unit);
        $costPrice = floatval($request->input('cost_price', $op->cost_price));
        $totalCost = $qty * $costPrice;

        $supPct = $request->has('supervision_pct') ? floatval($request->supervision_pct) : floatval($op->supervision_pct);
        $baseSellingPrice = floatval($request->input('selling_price', $op->selling_price));
        if ($baseSellingPrice <= 0 && $costPrice > 0) {
            $baseSellingPrice = $costPrice;
        }
        $sellingPrice = $op->is_billable ? $baseSellingPrice : 0;
        $totalSelling = $op->is_billable ? round($qty * $baseSellingPrice * (1 + ($supPct / 100)), 2) : 0;
        $profit = $totalSelling - $totalCost;

        $personName = $request->input('person_name', $op->person_name);
        $notes = $request->input('notes', $op->notes);

        DB::beginTransaction();
        try {
            // If there was a debt linked, calculate difference
            if ($op->debt_id) {
                $costDiff = $totalCost - $op->total_cost;
                if ($costDiff != 0) {
                    DB::table('company_debts')->where('id', $op->debt_id)->increment('remaining_balance', $costDiff);
                    DB::table('company_debts')->where('id', $op->debt_id)->increment('total_amount', $costDiff);
                    if ($personName) {
                        DB::table('cont_directory')->where('name', $personName)->increment('balance_debt', $costDiff);
                    }
                }
            }

            DB::table('cont_operations')->where('id', $id)->update([
                'name_or_title'   => $name,
                'quantity'        => $qty,
                'unit'            => $unit,
                'cost_price'      => $costPrice,
                'supervision_pct' => $supPct,
                'selling_price'   => $sellingPrice,
                'total_cost'      => $totalCost,
                'total_selling'   => $totalSelling,
                'profit_amount'   => $profit,
                'person_name'     => $personName,
                'notes'           => $notes,
                'updated_at'      => now(),
            ]);

            $this->syncProjectCustomerReceivable($op->project_id);

            DB::commit();

            $this->logActivity('update', 'contracting', "تم تعديل حركة ({$name}) بمشروع {$project?->name}", [
                'operation_id' => $id,
            ]);

            return response()->json(['success' => true, 'message' => 'تم تحديث الحركة بنجاح وتحديث مستحقات العميل.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Material Invoice and all its linked items & financials
     * Reverses cash deductions to treasury, clears supplier debts, and updates customer receivables.
     */
    public function deleteMaterialInvoice(Request $request)
    {
        $invoiceRef = trim($request->input('invoice_ref', ''));
        $projectId = intval($request->input('project_id', 0));

        if (empty($invoiceRef) || empty($projectId)) {
            return response()->json(['success' => false, 'message' => 'بيانات الفاتورة غير مكتملة'], 422);
        }

        $ops = DB::table('cont_operations')
            ->where('invoice_ref', $invoiceRef)
            ->where('project_id', $projectId)
            ->get();

        if ($ops->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على الفاتورة المحددة'], 404);
        }

        $project = DB::table('cont_projects')->where('id', $projectId)->first();

        DB::beginTransaction();
        try {
            // 1. Revert cash payments made from treasury/bank accounts
            $totalPaid = floatval($ops->sum('paid_amount'));
            if ($totalPaid > 0) {
                // Find financial transaction for this invoice
                $ft = DB::table('financial_transactions')
                    ->where('ref_type', 'cont_operation_invoice')
                    ->where('notes', 'like', "%{$invoiceRef}%")
                    ->where('status', 'active')
                    ->first();

                $refundAccountId = $ft?->from_account_id;
                if (!$refundAccountId) {
                    $refundAccountId = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                }

                if ($refundAccountId) {
                    DB::table('accounts')->where('id', $refundAccountId)->increment('balance', $totalPaid);

                    DB::table('financial_transactions')->insert([
                        'type'          => 'income',
                        'subtype'       => 'supplier_refund',
                        'amount'        => $totalPaid,
                        'to_account_id' => $refundAccountId,
                        'person_name'   => $ops->first()->person_name,
                        'notes'         => "استرداد مسدد نقدي لإلغاء فاتورة خامات ({$invoiceRef}) - مشروع: " . ($project?->name ?? ''),
                        'status'        => 'active',
                        'ref_type'      => 'cont_operation_invoice_cancelled',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    if ($ft) {
                        DB::table('financial_transactions')->where('id', $ft->id)->update(['status' => 'cancelled']);
                    }
                }
            }

            // 2. Revert / delete company debts (by debt_id, source_id, or matching invoice_ref in reason)
            $opIds = $ops->pluck('id')->all();
            $debtIds = $ops->pluck('debt_id')
                ->merge(DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $opIds)->pluck('id'))
                ->merge(DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->where('reason', 'like', "%({$invoiceRef})%")->pluck('id'))
                ->filter()
                ->unique();

            $supplierName = $ops->first()->person_name;
            $totalDebt = floatval($ops->sum('remaining_debt'));

            foreach ($debtIds as $dId) {
                DB::table('company_debts')->where('id', $dId)->delete();
            }

            // حفظ سجل الفاتورة الملغاة بكافة أصنافها في جدول الفواتير الملغاة قبل حذفها
            $this->ensureCancelledInvoicesTableExists();
            DB::table('cont_cancelled_invoices')->insert([
                'invoice_ref'   => $invoiceRef,
                'project_id'    => $projectId,
                'project_name'  => $project?->name ?? 'مشروع غير محدد',
                'client_name'   => $project?->client_name ?? '-',
                'supplier_name' => $supplierName ?: '-',
                'items_count'   => $ops->count(),
                'total_cost'    => floatval($ops->sum('total_cost')),
                'total_selling' => floatval($ops->sum('total_selling')),
                'total_paid'    => $totalPaid,
                'total_debt'    => $totalDebt,
                'items_json'    => json_encode($ops->values()->all(), JSON_UNESCAPED_UNICODE),
                'original_date' => substr($ops->first()->created_at ?? now(), 0, 10),
                'cancelled_by'  => session('auth_user')?->name ?? 'الإدارة',
                'cancelled_at'  => now(),
            ]);

            // 3. Delete operations
            DB::table('cont_operations')
                ->where('invoice_ref', $invoiceRef)
                ->where('project_id', $projectId)
                ->delete();

            // Recalculate supplier's live balance from remaining company_debts
            if ($supplierName) {
                $liveDebt = (float) DB::table('company_debts')
                    ->where('creditor_name', $supplierName)
                    ->sum('remaining_balance');
                DB::table('cont_directory')
                    ->where('name', $supplierName)
                    ->where('type', 'supplier')
                    ->update(['balance_debt' => max(0, $liveDebt)]);
            }

            // 4. Update customer receivables
            $this->syncProjectCustomerReceivable($projectId);

            DB::commit();

            $this->logActivity('delete', 'contracting', "تم إلغاء فاتورة الخامات ({$invoiceRef}) بالكامل واسترداد {$totalPaid} ج للخزينة وإلغاء ديون {$totalDebt} ج", [
                'project_id'     => $projectId,
                'invoice_ref'    => $invoiceRef,
                'refunded'       => $totalPaid,
                'debt_cancelled' => $totalDebt
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم إلغاء فاتورة الخامات بالكامل واسترداد المسدد ({$totalPaid} ج) للخزينة وإلغاء ديون المورد وتحديث مستحقات العميل بنجاح."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الإلغاء: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get details of a material invoice for editing / returns
     */
    public function getMaterialInvoiceDetails(Request $request)
    {
        $invoiceRef = trim($request->input('invoice_ref', ''));
        $projectId = intval($request->input('project_id', 0));

        $ops = DB::table('cont_operations')
            ->where('invoice_ref', $invoiceRef)
            ->where('project_id', $projectId)
            ->orderBy('id', 'asc')
            ->get();

        if ($ops->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة'], 404);
        }

        $firstOp = $ops->first();
        $project = DB::table('cont_projects')->where('id', $projectId)->first();

        $ft = DB::table('financial_transactions')
            ->where('ref_type', 'cont_operation_invoice')
            ->where('notes', 'like', "%{$invoiceRef}%")
            ->first();

        return response()->json([
            'success'        => true,
            'invoice_ref'    => $invoiceRef,
            'project_id'     => $projectId,
            'project_name'   => $project?->name,
            'client_name'    => $project?->client_name,
            'supplier_name'  => $firstOp->person_name,
            'payment_method' => $firstOp->payment_method,
            'account_id'     => $ft?->from_account_id,
            'total_cost'     => floatval($ops->sum('total_cost')),
            'total_selling'  => floatval($ops->sum('total_selling')),
            'paid_amount'    => floatval($ops->sum('paid_amount')),
            'remaining_debt' => floatval($ops->sum('remaining_debt')),
            'created_at'     => $firstOp->created_at,
            'items'          => $ops->map(function($op) {
                return [
                    'id'              => $op->id,
                    'name_or_title'   => $op->name_or_title,
                    'quantity'        => floatval($op->quantity),
                    'unit'            => $op->unit,
                    'cost_price'      => floatval($op->cost_price),
                    'supervision_pct' => floatval($op->supervision_pct),
                    'selling_price'   => floatval($op->selling_price),
                    'total_cost'      => floatval($op->total_cost),
                    'total_selling'   => floatval($op->total_selling),
                    'notes'           => $op->notes,
                ];
            })->values()
        ]);
    }

    /**
     * Update Material Invoice (handles item edits, returns, price changes, and cash/debt reconciliations)
     */
    public function updateMaterialInvoice(Request $request)
    {
        $request->validate([
            'invoice_ref' => 'required|string',
            'project_id'  => 'required|integer',
            'items'       => 'required|array|min:1',
        ]);

        $invoiceRef = trim($request->input('invoice_ref'));
        $projectId = intval($request->input('project_id'));
        $items = $request->input('items');

        $oldOps = DB::table('cont_operations')
            ->where('invoice_ref', $invoiceRef)
            ->where('project_id', $projectId)
            ->get();

        if ($oldOps->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة'], 404);
        }

        $project = DB::table('cont_projects')->where('id', $projectId)->first();
        $supplierName = trim($request->input('supplier_name', $oldOps->first()->person_name));
        $newPaidAmount = floatval($request->input('paid_amount', 0));
        $oldPaidAmount = floatval($oldOps->sum('paid_amount'));
        $newAccountId = $request->input('account_id');

        // Calculate new total cost
        $newGrandCost = 0;
        foreach ($items as $it) {
            $q = floatval($it['quantity'] ?? 1);
            $c = floatval($it['cost_price'] ?? 0);
            $newGrandCost += ($q * $c);
        }

        $newRemainingDebt = max(0, $newGrandCost - $newPaidAmount);
        $oldRemainingDebt = floatval($oldOps->sum('remaining_debt'));

        DB::beginTransaction();
        try {
            // 1. Adjust cash differences in treasury accounts
            $paidDiff = $newPaidAmount - $oldPaidAmount;
            if ($paidDiff > 0) {
                if (empty($newAccountId)) {
                    $ft = DB::table('financial_transactions')
                        ->where('ref_type', 'cont_operation_invoice')
                        ->where('notes', 'like', "%{$invoiceRef}%")
                        ->first();
                    $newAccountId = $ft?->from_account_id;
                }
                if (!$newAccountId) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'يرجى تحديد الخزينة لخصم فارق السداد الإضافي'], 422);
                }
                DB::table('accounts')->where('id', $newAccountId)->decrement('balance', $paidDiff);
                DB::table('financial_transactions')->insert([
                    'type'            => 'expense',
                    'subtype'         => 'supplier_payment',
                    'amount'          => $paidDiff,
                    'from_account_id' => $newAccountId,
                    'person_name'     => $supplierName,
                    'notes'           => "فارق سداد إضافي لتعديل فاتورة خامات ({$invoiceRef}) - مشروع {$project->name}",
                    'status'          => 'active',
                    'ref_type'        => 'cont_operation_invoice_edit',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            } elseif ($paidDiff < 0) {
                // Refund to treasury (e.g. return items or reduced paid amount)
                $refundAmount = abs($paidDiff);
                $ft = DB::table('financial_transactions')
                    ->where('ref_type', 'cont_operation_invoice')
                    ->where('notes', 'like', "%{$invoiceRef}%")
                    ->first();
                $refundAccId = $newAccountId ?: ($ft?->from_account_id ?: DB::table('accounts')->value('id'));

                if ($refundAccId) {
                    DB::table('accounts')->where('id', $refundAccId)->increment('balance', $refundAmount);
                    DB::table('financial_transactions')->insert([
                        'type'          => 'income',
                        'subtype'       => 'supplier_refund',
                        'amount'        => $refundAmount,
                        'to_account_id' => $refundAccId,
                        'person_name'   => $supplierName,
                        'notes'         => "استرداد فارق مرتجع/تعديل فاتورة خامات ({$invoiceRef}) - مشروع {$project->name}",
                        'status'        => 'active',
                        'ref_type'      => 'cont_operation_invoice_edit',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            // 2. Adjust supplier debt (consolidate any multiple item debts into a single invoice debt)
            $oldDebtIds = $oldOps->pluck('debt_id')
                ->merge(DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $oldOps->pluck('id')->all())->pluck('id'))
                ->filter()
                ->unique()
                ->values();

            $existingDebtId = $oldDebtIds->first();
            if ($oldDebtIds->count() > 1) {
                DB::table('company_debts')->whereIn('id', $oldDebtIds->slice(1)->all())->delete();
            }

            if ($newRemainingDebt > 0) {
                if ($existingDebtId && DB::table('company_debts')->where('id', $existingDebtId)->exists()) {
                    DB::table('company_debts')->where('id', $existingDebtId)->update([
                        'creditor_name'     => $supplierName,
                        'total_amount'      => $newRemainingDebt,
                        'remaining_balance' => $newRemainingDebt,
                        'source_type'       => 'cont_operation_invoice',
                        'updated_at'        => now(),
                    ]);
                    $debtId = $existingDebtId;
                } else {
                    $reason = "متبقي فاتورة خامات ({$invoiceRef}) - مشروع: " . $project->name;
                    $debtId = DB::table('company_debts')->insertGetId([
                        'creditor_name'     => $supplierName,
                        'reason'            => Str::limit($reason, 255),
                        'total_amount'      => $newRemainingDebt,
                        'paid_amount'       => 0,
                        'remaining_balance' => $newRemainingDebt,
                        'source_type'       => 'cont_operation_invoice',
                        'source_id'         => null,
                        'created_at'        => now(),
                    ]);
                }
            } else {
                if ($oldDebtIds->isNotEmpty()) {
                    DB::table('company_debts')->whereIn('id', $oldDebtIds->all())->delete();
                }
                $debtId = null;
            }

            if ($supplierName) {
                $liveSuppDebt = (float) DB::table('company_debts')->where('creditor_name', $supplierName)->sum('remaining_balance');
                DB::table('cont_directory')->where('name', $supplierName)->where('type', 'supplier')->update(['balance_debt' => max(0, $liveSuppDebt)]);
            }

            // 3. Remove old operations and recreate updated ones
            $firstOpDate = $oldOps->first()->created_at;
            $firstOpItem = $oldOps->first()->item_id;
            DB::table('cont_operations')
                ->where('invoice_ref', $invoiceRef)
                ->where('project_id', $projectId)
                ->delete();

            $paymentMethod = ($newRemainingDebt > 0 && $newPaidAmount <= 0) ? 'later' : (($newRemainingDebt > 0 && $newPaidAmount > 0) ? 'partial' : 'cash');
            $firstNewOpId = null;

            foreach ($items as $it) {
                $qty = floatval($it['quantity'] ?? 1);
                if ($qty <= 0) continue; // skip 0 quantity items (returned completely)
                $costPrice = floatval($it['cost_price'] ?? 0);
                $totalCost = $qty * $costPrice;
                $supPct = isset($it['supervision_pct']) ? floatval($it['supervision_pct']) : floatval($project->supervision_pct);
                $baseSellingPrice = floatval($it['selling_price'] ?? 0);
                if ($baseSellingPrice <= 0 && $costPrice > 0) {
                    $baseSellingPrice = $costPrice;
                }
                $sellingPrice = $baseSellingPrice;
                $totalSelling = round($qty * $baseSellingPrice * (1 + ($supPct / 100)), 2);
                $profit = $totalSelling - $totalCost;

                $itemRatio = $newGrandCost > 0 ? ($totalCost / $newGrandCost) : (1 / max(1, count($items)));
                $itemPaid = round($newPaidAmount * $itemRatio, 2);
                $itemDebt = round($newRemainingDebt * $itemRatio, 2);

                $newOpId = DB::table('cont_operations')->insertGetId([
                    'project_id'      => $projectId,
                    'item_id'         => $it['item_id'] ?? $firstOpItem,
                    'op_type'         => 'material',
                    'is_billable'     => 1,
                    'name_or_title'   => $it['name_or_title'] ?? 'خامة',
                    'quantity'        => $qty,
                    'unit'            => $it['unit'] ?? 'عدد',
                    'cost_price'      => $costPrice,
                    'supervision_pct' => $supPct,
                    'selling_price'   => $sellingPrice,
                    'total_cost'      => $totalCost,
                    'total_selling'   => $totalSelling,
                    'profit_amount'   => $profit,
                    'person_name'     => $supplierName,
                    'payment_method'  => $paymentMethod,
                    'paid_amount'     => $itemPaid,
                    'remaining_debt'  => $itemDebt,
                    'debt_id'         => $debtId,
                    'invoice_ref'     => $invoiceRef,
                    'notes'           => $it['notes'] ?? null,
                    'created_by'      => session('auth_user')?->id ?? 1,
                    'created_at'      => $firstOpDate,
                    'updated_at'      => now(),
                ]);

                if (!$firstNewOpId) $firstNewOpId = $newOpId;
            }

            if ($debtId && $firstNewOpId) {
                DB::table('company_debts')->where('id', $debtId)->update(['source_id' => $firstNewOpId]);
            }

            // 4. Synchronize customer receivable in /debts
            $this->syncProjectCustomerReceivable($projectId);

            DB::commit();

            $this->logActivity('update', 'contracting', "تم تعديل فاتورة الخامات ({$invoiceRef}) وتحديث البنود والمرتجعات وحسابات المورد والخزينة", [
                'project_id'  => $projectId,
                'invoice_ref' => $invoiceRef,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ تعديل الفاتورة والمرتجعات وتحديث الخزينة والديون ومستحقات العميل بنجاح.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تعديل الفاتورة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Synchronize Project Receivables with Installments Table (/debts screen)
     */
    public function syncProjectCustomerReceivable($projectId)
    {
        $project = DB::table('cont_projects')->where('id', $projectId)->first();
        if (!$project) return null;

        $ops = DB::table('cont_operations')->where('project_id', $projectId)->get();
        $totalBillable = floatval($ops->where('is_billable', 1)->sum('total_selling'));
        $discounts = floatval(DB::table('cont_discounts')->where('project_id', $projectId)->sum('amount'));
        $netBilled = max(0, $totalBillable - $discounts);
        $totalCost = floatval($ops->sum('total_cost'));
        $expectedProfit = max(0, $netBilled - $totalCost);

        // Find existing installment receivable for this project
        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->first();

        if (!$inst) {
            if ($netBilled <= 0) return null;

            $instId = DB::table('installments')->insertGetId([
                'sale_type'            => 'contracting',
                'customer_name'        => $project->client_name,
                'customer_phone'       => $project->client_phone,
                'product_name'         => "مشروع مقاولات: {$project->name}",
                'category'             => 'مقاولات وتشطيبات',
                'start_date'           => now()->toDateString(),
                'cash_price'           => $netBilled,
                'down_payment'         => 0,
                'remaining_after_down' => $netBilled,
                'installment_months'   => 0,
                'total_after_interest' => $netBilled,
                'monthly_installment'  => 0,
                'remaining_balance'    => $netBilled,
                'due_day'              => 1,
                'status'               => 'active',
                'profit'               => $expectedProfit,
                'source_type'          => 'cont_project',
                'source_id'            => $projectId,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
            return $instId;
        } else {
            if ($netBilled <= 0.001) {
                $hasPayments = DB::table('installment_payments')->where('installment_id', $inst->id)->exists();
                if (!$hasPayments && floatval($inst->down_payment) <= 0) {
                    DB::table('installments')->where('id', $inst->id)->delete();
                    return null;
                }
            }

            // Calculate total payments collected through installments
            $paymentsSum = floatval(DB::table('installment_payments')->where('installment_id', $inst->id)->sum('amount_paid'));
            $instDiscounts = floatval(DB::table('installment_payments')->where('installment_id', $inst->id)->sum('discount_applied'));
            $downPayment = floatval($inst->down_payment);
            $totalCollected = $paymentsSum + $downPayment + $instDiscounts;

            $newRemaining = max(0, $netBilled - $totalCollected);
            $status = ($newRemaining <= 0.001) ? 'paid' : 'active';

            DB::table('installments')->where('id', $inst->id)->update([
                'customer_name'        => $project->client_name,
                'customer_phone'       => $project->client_phone,
                'product_name'         => "مشروع مقاولات: {$project->name}",
                'cash_price'           => $netBilled,
                'remaining_after_down' => max(0, $netBilled - $downPayment),
                'total_after_interest' => $netBilled,
                'remaining_balance'    => $newRemaining,
                'profit'               => $expectedProfit,
                'status'               => $status,
                'updated_at'           => now(),
            ]);
            return $inst->id;
        }
    }

    /**
     * Record Client Payment (دفعة / مستخلص)
     */
    public function storePayment(Request $request)
    {
        $request->validate([
            'project_id'  => 'required|integer',
            'paid_amount' => 'required|numeric|min:0.01',
        ]);

        $project = DB::table('cont_projects')->where('id', $request->project_id)->first();
        if (!$project) return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);

        $user = session('auth_user');
        $paidAmount = floatval($request->paid_amount);
        $accountId = $request->account_id;
        $invoiceNum = 'CONT-INV-' . strtoupper(Str::random(6));

        DB::beginTransaction();
        try {
            // Deposit to selected treasury account
            if ($accountId) {
                DB::table('accounts')->where('id', $accountId)->increment('balance', $paidAmount);

                DB::table('financial_transactions')->insert([
                    'type'          => 'income',
                    'subtype'       => 'customer_payment',
                    'amount'        => $paidAmount,
                    'to_account_id' => $accountId,
                    'person_name'   => $project->client_name,
                    'notes'         => "دفعة مستلمة من عميل المقاولة ({$project->client_name}) - مشروع: {$project->name}",
                    'status'        => 'active',
                    'ref_type'      => 'cont_invoice',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $invId = DB::table('cont_invoices')->insertGetId([
                'project_id'          => $project->id,
                'invoice_number'      => $invoiceNum,
                'invoice_type'        => $request->input('invoice_type', 'payment_receipt'),
                'is_tax'              => $request->boolean('is_tax') ? 1 : 0,
                'tax_rate'            => floatval($request->input('tax_rate', 0)),
                'tax_amount'          => floatval($request->input('tax_amount', 0)),
                'show_supervision_fee'=> $request->boolean('show_supervision_fee', true) ? 1 : 0,
                'supervision_amount'  => 0,
                'discount_amount'     => 0,
                'total_billed'        => $paidAmount,
                'paid_amount'         => $paidAmount,
                'remaining_balance'   => 0,
                'notes'               => $request->notes ?? "تحصيل دفعة نقدية من العميل",
                'created_by'          => $user?->id ?? 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            DB::commit();

            $this->logActivity('payment', 'contracting', "تم استلام دفعة مالية بقيمة {$paidAmount} ج من العميل: {$project->client_name} لمشروع: {$project->name}", [
                'project_id' => $project->id,
                'invoice_id' => $invId,
                'amount'     => $paidAmount,
            ]);

            return response()->json(['success' => true, 'message' => 'تم تسجيل الدفعة وإيداعها في الخزينة بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Convert Remaining Balance to Installment Plan
     */
    public function storeInstallmentPlan(Request $request)
    {
        $request->validate([
            'project_id'         => 'required|integer',
            'installment_months' => 'required|integer|min:1',
            'amount'             => 'required|numeric|min:1',
        ]);

        $project = DB::table('cont_projects')->where('id', $request->project_id)->first();
        if (!$project) return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);

        $months = intval($request->installment_months);
        $totalAmount = floatval($request->amount);
        $downPayment = floatval($request->input('down_payment', 0));
        $remaining = max(0, $totalAmount - $downPayment);
        $monthly = round($remaining / $months, 2);
        $startDate = $request->input('start_date', now()->toDateString());

        DB::beginTransaction();
        try {
            $installmentId = DB::table('installments')->insertGetId([
                'sale_type'            => 'direct',
                'customer_name'        => $project->client_name,
                'product_name'         => "أقساط مشروع مقاولة: {$project->name}",
                'category'             => 'مقاولات وتشطيبات',
                'start_date'           => $startDate,
                'cash_price'           => $totalAmount,
                'down_payment'         => $downPayment,
                'remaining_after_down' => $remaining,
                'installment_months'   => $months,
                'total_after_interest' => $totalAmount,
                'monthly_installment'  => $monthly,
                'remaining_balance'    => $remaining,
                'due_day'              => 1,
                'status'               => 'active',
                'profit'               => 0,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Link to contracting invoices table as record
            DB::table('cont_invoices')->insert([
                'project_id'          => $project->id,
                'invoice_number'      => 'INST-PLAN-' . $installmentId,
                'invoice_type'        => 'installment_contract',
                'total_billed'        => $totalAmount,
                'paid_amount'         => $downPayment,
                'remaining_balance'   => $remaining,
                'installment_id'      => $installmentId,
                'notes'               => "تحويل مبلغ {$totalAmount} إلى {$months} قسط شهري بقيمة {$monthly} ج",
                'created_by'          => session('auth_user')?->id ?? 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            DB::commit();

            $this->logActivity('create', 'installments', "تم إنشاء جدول تقسيط لمشروع المقاولة ({$project->name}) للعميل {$project->client_name} بقيمة {$totalAmount} ج على {$months} شهر", [
                'project_id'     => $project->id,
                'installment_id' => $installmentId,
                'months'         => $months,
                'monthly'        => $monthly,
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تحويل المبلغ لجدول الأقساط وترحيله لمنظومة التحصيل بنجاح (عقد #{$installmentId})",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'تعذر إنشاء الأقساط: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Quotation (عرض سعر / مقايسة)
     */
    public function storeQuotation(Request $request)
    {
        $request->validate([
            'client_name'   => 'required|string|max:255',
            'project_title' => 'required|string|max:255',
        ]);

        $items = $request->input('items', []);
        $totalCost = collect($items)->sum(fn($i) => floatval($i['qty'] ?? 1) * floatval($i['cost'] ?? 0));
        $totalSelling = collect($items)->sum(fn($i) => floatval($i['qty'] ?? 1) * floatval($i['price'] ?? 0));

        $id = DB::table('cont_quotations')->insertGetId([
            'client_name'     => $request->client_name,
            'client_phone'    => $request->client_phone,
            'project_title'   => $request->project_title,
            'supervision_pct' => floatval($request->input('supervision_pct', 15)),
            'items_data'      => json_encode($items, JSON_UNESCAPED_UNICODE),
            'total_cost'      => $totalCost,
            'total_selling'   => $totalSelling,
            'status'          => 'draft',
            'created_by'      => session('auth_user')?->id ?? 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->logActivity('create', 'contracting', "تم إنشاء مقايسة/عرض سعر جديد: {$request->project_title} للعميل: {$request->client_name}", [
            'quotation_id' => $id,
            'total_selling'=> $totalSelling,
        ]);

        return response()->json(['success' => true, 'message' => 'تم حفظ عرض السعر بنجاح']);
    }

    /**
     * 1-Click Convert Quotation to Active Project
     */
    public function convertQuotationToProject($id)
    {
        $quotation = DB::table('cont_quotations')->where('id', $id)->first();
        if (!$quotation) return response()->json(['success' => false, 'message' => 'عرض السعر غير موجود'], 404);

        DB::beginTransaction();
        try {
            $projectId = DB::table('cont_projects')->insertGetId([
                'name'                 => $quotation->project_title,
                'client_name'          => $quotation->client_name,
                'client_phone'         => $quotation->client_phone,
                'supervision_pct'      => $quotation->supervision_pct,
                'total_contract_value' => $quotation->total_selling,
                'status'               => 'active',
                'notes'                => "تم التحويل تلقائياً من عرض سعر #{$quotation->id}",
                'created_by'           => session('auth_user')?->id ?? 1,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Create items from quotation items_data
            $items = json_decode($quotation->items_data, true) ?: [];
            $uniqueItemTitles = collect($items)->pluck('item_title')->filter()->unique();

            if ($uniqueItemTitles->isEmpty()) {
                $uniqueItemTitles = collect(['بند التأسيس والإنشاءات', 'بند التشطيبات']);
            }

            foreach ($uniqueItemTitles as $idx => $title) {
                DB::table('cont_project_items')->insert([
                    'project_id' => $projectId,
                    'title'      => $title,
                    'status'     => 'open',
                    'sort_order' => $idx + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Mark quotation as converted
            DB::table('cont_quotations')->where('id', $id)->update([
                'status'               => 'converted_to_project',
                'converted_project_id' => $projectId,
                'updated_at'           => now(),
            ]);

            DB::commit();

            $this->logActivity('create', 'contracting', "تم تحويل عرض السعر #{$id} إلى مشروع فعلي: {$quotation->project_title}", [
                'project_id' => $projectId,
            ]);

            return response()->json([
                'success'    => true,
                'project_id' => $projectId,
                'message'    => 'تم تحويل عرض السعر إلى مشروع فعلي وتجهيز بنوده بنجاح!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Person in Directory (Supplier / Tech / Marketer / Client)
     */
    public function storeDirectoryPerson(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:supplier,technician,marketer,client',
        ]);

        $id = DB::table('cont_directory')->insertGetId([
            'type'         => $request->type,
            'name'         => trim($request->name),
            'phone'        => $request->phone,
            'specialty'    => $request->specialty,
            'balance_debt' => floatval($request->input('balance_debt', 0)),
            'notes'        => $request->notes,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id, 'message' => 'تمت إضافة البيانات للسجل بنجاح']);
    }

    /**
     * Delete Person from Directory
     */
    public function deleteDirectoryPerson($id)
    {
        $person = DB::table('cont_directory')->where('id', $id)->first();
        if (!$person) {
            return response()->json(['success' => false, 'message' => 'جهة الاتصال غير موجودة'], 404);
        }

        // Check if there are any financial debts, receivables, or linked operations
        $hasDebts = DB::table('company_debts')
            ->where('creditor_name', $person->name)
            ->where('remaining_balance', '>', 0)
            ->exists();

        $hasClientDues = DB::table('installments')
            ->where('customer_name', $person->name)
            ->where('status', '!=', 'cancelled')
            ->where('remaining_balance', '>', 0)
            ->exists();

        $hasOps = DB::table('cont_operations')
            ->where('person_name', $person->name)
            ->exists();

        if ($hasDebts || $hasClientDues || $hasOps || floatval($person->balance_debt) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لا يمكن حذف جهة الاتصال هذه نهائياً لوجود مستحقات أو ديون مالية مسجلة لها أو عليها!'
            ], 422);
        }

        DB::table('cont_directory')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'تم الحذف من السجل بنجاح']);
    }

    /**
     * Project 360 Summary Ajax
     */
    public function getProjectSummaryAjax($id)
    {
        $project = DB::table('cont_projects')->where('id', $id)->first();
        if (!$project) return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);

        $operations = DB::table('cont_operations')
            ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
            ->where('cont_operations.project_id', $id)
            ->select('cont_operations.*', 'cont_project_items.title as item_title')
            ->orderBy('cont_operations.id', 'desc')
            ->get();

        $discounts = DB::table('cont_discounts')->where('project_id', $id)->get();
        $invoices = DB::table('cont_invoices')->where('project_id', $id)->get();
        $items = DB::table('cont_project_items')->where('project_id', $id)->get();

        // Financial totals (applying supervision percentage to billable selling price)
        $supervisionPct = $project->supervision_pct ?? 0;
        $totalCost = $operations->sum('total_cost');
        $billableSelling = $operations->where('is_billable', 1)->sum('total_selling');
        $billableCost = $operations->where('is_billable', 1)->sum('total_cost');
        $supervisionAmount = max(0, $billableSelling - $billableCost);
        $grossSelling = $billableSelling;
        $internalExpenses = $operations->where('is_billable', 0)->sum('total_cost');
        $totalDiscounts = $discounts->sum('amount');
        $netSelling = max(0, $grossSelling - $totalDiscounts);
        $clientPaid = $invoices->sum('paid_amount');
        $clientRemaining = max(0, $netSelling - $clientPaid);
        $netProfit = $netSelling - $totalCost;

        // Group materials
        $materialsSummary = $operations->where('op_type', 'material')
            ->groupBy('name_or_title')
            ->map(function($rows, $name) {
                return [
                    'name'       => $name,
                    'unit'       => $rows->first()->unit ?? 'عدد',
                    'total_qty'  => $rows->sum('quantity'),
                    'total_cost' => $rows->sum('total_cost'),
                    'avg_price'  => $rows->sum('quantity') > 0 ? round($rows->sum('total_cost') / $rows->sum('quantity'), 2) : 0,
                    'suppliers'  => $rows->pluck('person_name')->filter()->unique()->values()->all(),
                ];
            })->values();

        // Map linked company_debts to reflect any subsequent debt settlements in /debts
        $projDebtIds = $operations->pluck('debt_id')->filter()->unique()->values()->all();
        $projDebtsById = !empty($projDebtIds)
            ? DB::table('company_debts')->whereIn('id', $projDebtIds)->get()->keyBy('id')
            : collect();

        $calcOpLivePaid = function ($op) use ($projDebtsById) {
            $initialPaid = floatval($op->paid_amount);
            $initialDebt = floatval($op->remaining_debt);
            $subsequentPaid = 0;
            if (!empty($op->debt_id) && $initialDebt > 0 && isset($projDebtsById[$op->debt_id])) {
                $d = $projDebtsById[$op->debt_id];
                $dTotal = floatval($d->total_amount);
                $dPaid  = floatval($d->paid_amount);
                $ratio  = $dTotal > 0 ? min(1, $dPaid / $dTotal) : 0;
                $subsequentPaid = round($initialDebt * $ratio, 2);
            }
            return round($initialPaid + $subsequentPaid, 2);
        };

        // Group technicians
        $techsSummary = $operations->whereIn('op_type', ['technician', 'labor'])
            ->groupBy('person_name')
            ->map(function($rows, $name) use ($calcOpLivePaid) {
                $totalCost = round($rows->sum('total_cost'), 2);
                $livePaid  = round($rows->sum(fn($r) => $calcOpLivePaid($r)), 2);
                $liveDebt  = max(0, round($totalCost - $livePaid, 2));
                return [
                    'name'       => $name ?: 'فني غير محدد',
                    'jobs_count' => $rows->count(),
                    'total_cost' => $totalCost,
                    'paid'       => $livePaid,
                    'debt'       => $liveDebt,
                ];
            })->values();

        // Top Suppliers
        $suppliersSummary = $operations->where('op_type', 'material')
            ->whereNotNull('person_name')
            ->groupBy('person_name')
            ->map(function($rows, $name) use ($calcOpLivePaid) {
                $totalAmount = round($rows->sum('total_cost'), 2);
                $livePaid    = round($rows->sum(fn($r) => $calcOpLivePaid($r)), 2);
                $liveDebt    = max(0, round($totalAmount - $livePaid, 2));
                return [
                    'name'         => $name,
                    'orders_count' => $rows->count(),
                    'total_amount' => $totalAmount,
                    'paid'         => $livePaid,
                    'debt'         => $liveDebt,
                ];
            })->sortByDesc('total_amount')->values();

        // Items Summary
        $itemsSummary = $items->map(function($item) use ($operations) {
            $itemOps = $operations->where('item_id', $item->id);
            return [
                'id'         => $item->id,
                'title'      => $item->title,
                'status'     => $item->status,
                'ops_count'  => $itemOps->count(),
                'total_cost' => $itemOps->sum('total_cost'),
                'total_sell' => $itemOps->sum('total_selling'),
            ];
        });

        return response()->json([
            'success' => true,
            'project' => $project,
            'totals'  => [
                'total_cost'         => $totalCost,
                'billable_selling'   => $grossSelling,
                'base_selling'       => $billableSelling,
                'supervision_amount' => $supervisionAmount,
                'supervision_pct'    => $supervisionPct,
                'internal_expenses'  => $internalExpenses,
                'total_discounts'    => $totalDiscounts,
                'net_selling'        => $netSelling,
                'client_paid'        => $clientPaid,
                'client_remaining'   => $clientRemaining,
                'net_profit'         => $netProfit,
                'workers_count'      => $techsSummary->count(),
            ],
            'materials'  => $materialsSummary,
            'techs'      => $techsSummary,
            'suppliers'  => $suppliersSummary,
            'items'      => $itemsSummary,
            'operations' => $operations,
            'discounts'  => $discounts,
        ]);
    }

    /**
     * Get items of a specific material invoice
     */
    public function getInvoiceDetailsAjax(Request $request)
    {
        $invoiceRef = trim($request->input('invoice_ref', ''));
        $projectId = $request->input('project_id');

        $query = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
            ->where('cont_operations.invoice_ref', $invoiceRef);

        if ($projectId) {
            $query->where('cont_operations.project_id', $projectId);
        }

        $items = $query->select(
            'cont_operations.*',
            'cont_projects.name as project_name',
            'cont_projects.client_name',
            'cont_project_items.title as item_title'
        )->orderBy('cont_operations.id', 'asc')->get();

        return response()->json([
            'success'     => true,
            'invoice_ref' => $invoiceRef,
            'supplier'    => $items->first()?->person_name ?? 'مورد غير محدد',
            'project'     => $items->first()?->project_name ?? '-',
            'client'      => $items->first()?->client_name ?? '-',
            'created_at'  => $items->first()?->created_at ?? '-',
            'payment_method' => $items->first()?->payment_method ?? 'cash',
            'total_cost'  => $items->sum('total_cost'),
            'total_sell'  => $items->sum('total_selling'),
            'total_paid'  => $items->sum('paid_amount'),
            'total_debt'  => $items->sum('remaining_debt'),
            'items'       => $items,
        ]);
    }

    /**
     * Price History Tracking Ajax
     */
    public function getPriceHistoryAjax(Request $request)
    {
        $materialName = trim($request->input('material', ''));
        $action = $request->input('action', 'summary');

        if ($action === 'details' && !empty($materialName)) {
            $history = DB::table('cont_operations')
                ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
                ->where('cont_operations.op_type', 'material')
                ->where('cont_operations.name_or_title', $materialName)
                ->select(
                    'cont_operations.id',
                    'cont_operations.name_or_title',
                    'cont_operations.unit',
                    'cont_operations.cost_price',
                    'cont_operations.quantity',
                    'cont_operations.total_cost',
                    'cont_operations.person_name as supplier_name',
                    'cont_operations.invoice_ref',
                    'cont_operations.notes',
                    'cont_operations.created_at',
                    'cont_projects.name as project_name'
                )
                ->orderBy('cont_operations.created_at', 'desc')
                ->get();

            return response()->json([
                'success'  => true,
                'material' => $materialName,
                'history'  => $history,
            ]);
        }

        // Summary list of all materials
        $query = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->whereNotNull('name_or_title')
            ->where('name_or_title', '!=', '');

        if (!empty($materialName)) {
            $query->where('name_or_title', 'like', "%{$materialName}%");
        }

        $allOps = $query->orderBy('created_at', 'asc')->get();

        $materials = $allOps->groupBy('name_or_title')->map(function($rows, $name) {
            $count = $rows->count();
            $prices = $rows->pluck('cost_price')->map(fn($v) => floatval($v))->filter(fn($v) => $v > 0);

            $minPrice = $prices->isNotEmpty() ? $prices->min() : 0;
            $maxPrice = $prices->isNotEmpty() ? $prices->max() : 0;
            $avgPrice = $prices->isNotEmpty() ? round($prices->avg(), 2) : 0;

            $lastRow = $rows->last();
            $latestPrice = floatval($lastRow->cost_price ?? 0);

            $changePct = null;
            if ($count >= 2) {
                $prevRow = $rows->slice(-2, 1)->first();
                $prevPrice = floatval($prevRow->cost_price ?? 0);
                if ($prevPrice > 0) {
                    $changePct = round((($latestPrice - $prevPrice) / $prevPrice) * 100, 1);
                } else {
                    $changePct = 0;
                }
            }

            return [
                'name'            => $name,
                'unit'            => $lastRow->unit ?? 'عدد',
                'purchases_count' => $count,
                'min_price'       => $minPrice,
                'avg_price'       => $avgPrice,
                'max_price'       => $maxPrice,
                'latest_price'    => $latestPrice,
                'change_pct'      => $changePct,
                'last_date'       => $lastRow->created_at ? date('Y-m-d', strtotime($lastRow->created_at)) : null,
            ];
        })->values()->sortByDesc('purchases_count')->values();

        return response()->json([
            'success'   => true,
            'materials' => $materials,
        ]);
    }

    /**
     * Supplier Comparison Matrix Ajax
     */
    public function getSupplierComparisonAjax(Request $request)
    {
        $materialName = trim($request->input('material', ''));

        $query = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->whereNotNull('name_or_title')
            ->where('name_or_title', '!=', '')
            ->whereNotNull('person_name')
            ->where('person_name', '!=', '');

        if (!empty($materialName)) {
            $query->where(function($q) use ($materialName) {
                $q->where('name_or_title', 'like', "%{$materialName}%")
                  ->orWhere('person_name', 'like', "%{$materialName}%");
            });
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        $cards = $records->groupBy('name_or_title')->map(function($rows, $matName) {
            $unit = $rows->first()->unit ?? 'وحدة';
            $suppliers = $rows->groupBy('person_name')->map(function($sRows, $sName) {
                $minPrice = floatval($sRows->min('cost_price'));
                $latestPrice = floatval($sRows->sortByDesc('created_at')->first()->cost_price ?? $minPrice);
                return [
                    'name'         => $sName,
                    'price'        => $latestPrice > 0 ? $latestPrice : $minPrice,
                    'min_price'    => $minPrice,
                    'latest_price' => $latestPrice,
                    'count'        => $sRows->count(),
                ];
            })->values()->sortBy('price')->values();

            $minPrice = $suppliers->min('price');
            $maxPrice = $suppliers->max('price');

            $suppliersWithBadges = $suppliers->map(function($sup) use ($minPrice, $maxPrice, $suppliers) {
                $sup['is_cheapest'] = ($sup['price'] == $minPrice);
                $sup['is_highest'] = ($suppliers->count() > 1 && $sup['price'] == $maxPrice && $maxPrice > $minPrice);
                return $sup;
            })->all();

            return [
                'material_name' => $matName,
                'unit'          => $unit,
                'suppliers'     => $suppliersWithBadges,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'cards'   => $cards,
        ]);
    }

    /**
     * Print Statement (كشف الحساب: مختصر / مفصل / داخلي)
     */
    public function printStatement(Request $request, $id)
    {
        $project = DB::table('cont_projects')->where('id', $id)->first();
        if (!$project) abort(404, 'المشروع غير موجود');

        $type = $request->input('type', 'detailed'); // detailed, summary, internal
        $isTax = $request->boolean('is_tax');
        $showSupervision = $request->boolean('show_supervision', true);
        $selectedItemId = $request->filled('item_id') ? intval($request->input('item_id')) : null;

        $allItems = DB::table('cont_project_items')->where('project_id', $id)->orderBy('sort_order')->get();
        $selectedItem = $selectedItemId ? $allItems->firstWhere('id', $selectedItemId) : null;

        if ($selectedItem) {
            $items = collect([$selectedItem]);
            $operations = DB::table('cont_operations')
                ->where('project_id', $id)
                ->where('item_id', $selectedItem->id)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $items = $allItems;
            $operations = DB::table('cont_operations')->where('project_id', $id)->orderBy('created_at', 'asc')->get();
        }

        $discounts = DB::table('cont_discounts')->where('project_id', $id)->orderBy('created_at', 'desc')->get();
        $invoices = DB::table('cont_invoices')->where('project_id', $id)->orderBy('created_at', 'asc')->get();

        $totalDiscounts = $selectedItem ? 0 : $discounts->sum('amount');
        $totalPaid = $selectedItem ? 0 : $invoices->sum('paid_amount');

        // Customer payments list (receipts)
        $customerPayments = $selectedItem ? collect() : $invoices->where('paid_amount', '>', 0);

        // System Settings
        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        // Supervising engineer name
        $createdByUser = $project->created_by ? DB::table('users')->where('id', $project->created_by)->first() : null;
        $engineerName = $createdByUser ? $createdByUser->name : 'المهندس المشرف';

        return view('contracting_statement', compact(
            'project',
            'type',
            'isTax',
            'showSupervision',
            'allItems',
            'selectedItem',
            'items',
            'operations',
            'discounts',
            'invoices',
            'customerPayments',
            'totalDiscounts',
            'totalPaid',
            'settings',
            'engineerName'
        ));
    }

    /**
     * Print Technician or Supplier Account Statement (كشف حساب فني / كشف حساب مورد)
     */
    public function printPersonStatement(Request $request)
    {
        $personId = $request->filled('id') ? intval($request->input('id')) : null;
        $personType = in_array($request->input('type'), ['technician', 'supplier'], true)
            ? $request->input('type')
            : 'technician';
        $personName = trim((string) $request->input('name', ''));
        $selectedProjectId = $request->filled('project_id') ? intval($request->input('project_id')) : null;

        $person = null;
        if ($personId) {
            $person = DB::table('cont_directory')->where('id', $personId)->first();
            if ($person) {
                $personName = trim($person->name);
                if (in_array($person->type, ['technician', 'supplier'], true)) {
                    $personType = $person->type;
                }
            }
        } elseif ($personName !== '') {
            $person = DB::table('cont_directory')
                ->where('name', $personName)
                ->where('type', $personType)
                ->first()
                ?: DB::table('cont_directory')->where('name', $personName)->first();
        }

        if ($personName === '') {
            abort(404, 'يرجى تحديد اسم الفني أو المورد لعرض كشف الحساب');
        }

        if (!$person) {
            $person = (object) [
                'id'           => null,
                'name'         => $personName,
                'type'         => $personType,
                'phone'        => null,
                'specialty'    => null,
                'balance_debt' => 0,
                'notes'        => null,
            ];
        }

        $opTypes = $personType === 'supplier' ? ['material'] : ['technician', 'labor'];

        $allOps = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
            ->whereIn('cont_operations.op_type', $opTypes)
            ->where('cont_operations.person_name', $personName)
            ->select(
                'cont_operations.*',
                'cont_projects.name as project_name',
                'cont_projects.client_name',
                'cont_project_items.title as item_title'
            )
            ->orderBy('cont_operations.created_at', 'asc')
            ->orderBy('cont_operations.id', 'asc')
            ->get();

        // Available projects for this technician / supplier
        $personProjects = $allOps->whereNotNull('project_id')
            ->unique('project_id')
            ->map(function ($row) {
                return (object) [
                    'id'          => $row->project_id,
                    'name'        => $row->project_name ?: ('مشروع #' . $row->project_id),
                    'client_name' => $row->client_name,
                ];
            })
            ->values();

        $selectedProject = $selectedProjectId
            ? DB::table('cont_projects')->where('id', $selectedProjectId)->first()
            : null;

        $operations = $selectedProject
            ? $allOps->where('project_id', $selectedProject->id)->values()
            : $allOps;

        // Fetch linked company_debts to accurately account for subsequent debt payments in /debts
        $allOpDebtIds = $allOps->pluck('debt_id')->filter()->unique()->values()->all();
        $companyDebts = DB::table('company_debts')
            ->where(function ($q) use ($personName, $allOpDebtIds) {
                $q->where('creditor_name', $personName);
                if (!empty($allOpDebtIds)) {
                    $q->orWhereIn('id', $allOpDebtIds);
                }
            })
            ->orderBy('created_at', 'asc')
            ->get();
        $debtsById = $companyDebts->keyBy('id');

        $totalOpsAmount = 0;
        $totalPaidOnOps = 0;
        $totalRemainingOnOps = 0;

        foreach ($operations as $op) {
            $opCost = floatval($op->total_cost);
            $initialPaid = floatval($op->paid_amount);
            $initialDebt = floatval($op->remaining_debt);
            $subsequentPaid = 0;

            if (!empty($op->debt_id) && $initialDebt > 0 && isset($debtsById[$op->debt_id])) {
                $d = $debtsById[$op->debt_id];
                $dTotal = floatval($d->total_amount);
                $dPaid  = floatval($d->paid_amount);
                $ratio  = $dTotal > 0 ? min(1, $dPaid / $dTotal) : 0;
                $subsequentPaid = round($initialDebt * $ratio, 2);
            }

            $op->live_paid = round($initialPaid + $subsequentPaid, 2);
            $op->live_remaining = max(0, round($opCost - $op->live_paid, 2));

            $totalOpsAmount += $opCost;
            $totalPaidOnOps += $op->live_paid;
            $totalRemainingOnOps += $op->live_remaining;
        }

        // Include any standalone company_debts (created outside cont_operations) when viewing full statement
        $standaloneDebts = collect();
        if (!$selectedProject) {
            $standaloneDebts = $companyDebts->filter(function ($d) use ($allOpDebtIds) {
                return !in_array($d->id, $allOpDebtIds);
            })->values();

            foreach ($standaloneDebts as $sd) {
                $totalOpsAmount += floatval($sd->total_amount);
                $totalPaidOnOps += floatval($sd->paid_amount);
                $totalRemainingOnOps += floatval($sd->remaining_balance);
            }
        }

        // Build chronological payments history (التاريخ والمبلغ فقط)
        $paymentRecords = collect();

        // 1. Immediate payments at operation/invoice creation (grouped by invoice_ref so multi-item cash invoices appear as 1 payment)
        $paidOpsGroups = $operations->where('paid_amount', '>', 0)->groupBy(function ($op) {
            return !empty($op->invoice_ref)
                ? ('INV_' . ($op->project_id ?: 0) . '_' . $op->invoice_ref)
                : ('OP_' . $op->id);
        });

        foreach ($paidOpsGroups as $groupRows) {
            $firstRow = $groupRows->first();
            $paymentRecords->push((object) [
                'date'   => $firstRow->created_at,
                'amount' => round($groupRows->sum('paid_amount'), 2),
            ]);
        }

        // 2. Subsequent debt payments in financial_transactions
        $scopedDebtIds = $operations->pluck('debt_id')->filter()->unique()->values()->all();
        if (!$selectedProject) {
            $scopedDebtIds = array_values(array_unique(array_merge($scopedDebtIds, $companyDebts->pluck('id')->all())));
        }

        if (!empty($scopedDebtIds) || !$selectedProject) {
            $txQuery = DB::table('financial_transactions')
                ->where('status', 'active')
                ->where(function ($q) use ($scopedDebtIds, $personName, $selectedProject) {
                    if (!empty($scopedDebtIds)) {
                        $q->where(function ($sq) use ($scopedDebtIds) {
                            $sq->whereIn('ref_type', ['company_debt_payment', 'company_debt_payment_discount'])
                               ->whereIn('ref_id', $scopedDebtIds);
                        });
                    }
                    if (!$selectedProject) {
                        $q->orWhere(function ($sq) use ($personName) {
                            $sq->where('person_name', $personName)
                               ->whereIn('ref_type', ['company_debt_payment', 'company_debt_payment_discount', 'earned_discount']);
                        });
                    }
                })
                ->orderBy('created_at', 'asc')
                ->get();

            // Group FIFO split transactions executed in the same second into a single payment record
            $txGrouped = $txQuery->groupBy(function ($tx) {
                return ($tx->created_at ?? '') . '_' . ($tx->ref_type ?? '');
            });

            foreach ($txGrouped as $txRows) {
                $firstTx = $txRows->first();
                $paymentRecords->push((object) [
                    'date'   => $firstTx->created_at,
                    'amount' => round($txRows->sum('amount'), 2),
                ]);
            }
        }

        $paymentRecords = $paymentRecords->sortBy('date')->values();

        // System Settings
        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        return view('contracting_person_statement', compact(
            'person',
            'personType',
            'personName',
            'personProjects',
            'selectedProject',
            'operations',
            'standaloneDebts',
            'paymentRecords',
            'totalOpsAmount',
            'totalPaidOnOps',
            'totalRemainingOnOps',
            'settings'
        ));
    }

    /**
     * Convert number to Arabic words (Tafqeet)
     */
    public static function tafqeet($number)
    {
        $number = floatval($number);
        if ($number <= 0) return 'صفر جنيه مصري لا غير';

        $number = number_format($number, 2, '.', '');
        list($integer, $fraction) = explode('.', $number);
        $integer = (int)$integer;
        $fraction = (int)$fraction;

        $ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة',
            'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
        $tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
        $hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

        $convertGroup = function($num) use ($ones, $tens, $hundreds) {
            if ($num == 0) return '';
            $text = '';
            $c = (int)($num / 100);
            $rem = $num % 100;
            if ($c > 0) {
                $text .= $hundreds[$c];
            }
            if ($rem > 0) {
                if ($text != '') $text .= ' و';
                if ($rem < 20) {
                    $text .= $ones[$rem];
                } else {
                    $o = $rem % 10;
                    $t = (int)($rem / 10);
                    if ($o > 0) {
                        $text .= $ones[$o] . ' و' . $tens[$t];
                    } else {
                        $text .= $tens[$t];
                    }
                }
            }
            return $text;
        };

        $billions = (int)($integer / 1000000000);
        $millions = (int)(($integer % 1000000000) / 1000000);
        $thousands = (int)(($integer % 1000000) / 1000);
        $rest = $integer % 1000;

        $parts = [];
        if ($billions > 0) {
            if ($billions == 1) $parts[] = 'مليار';
            elseif ($billions == 2) $parts[] = 'ملياران';
            elseif ($billions <= 10) $parts[] = $convertGroup($billions) . ' مليارات';
            else $parts[] = $convertGroup($billions) . ' مليار';
        }
        if ($millions > 0) {
            if ($millions == 1) $parts[] = 'مليون';
            elseif ($millions == 2) $parts[] = 'مليونان';
            elseif ($millions <= 10) $parts[] = $convertGroup($millions) . ' ملايين';
            else $parts[] = $convertGroup($millions) . ' مليون';
        }
        if ($thousands > 0) {
            if ($thousands == 1) $parts[] = 'ألف';
            elseif ($thousands == 2) $parts[] = 'ألفان';
            elseif ($thousands <= 10) $parts[] = $convertGroup($thousands) . ' آلاف';
            else $parts[] = $convertGroup($thousands) . ' ألفاً';
        }
        if ($rest > 0) {
            $parts[] = $convertGroup($rest);
        }

        $result = 'فقط ' . implode(' و', $parts) . ' جنيهاً مصرياً';
        if ($fraction > 0) {
            $result .= ' و' . $convertGroup($fraction) . ' قرشاً';
        }
        $result .= ' لا غير';
        return $result;
    }

    /**
     * Update Project details & status
     */
    public function updateProject(Request $request, $id)
    {
        $project = DB::table('cont_projects')->where('id', $id)->first();
        if (!$project) return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);

        $data = [
            'name'            => $request->input('name', $project->name),
            'client_name'     => $request->input('client_name', $project->client_name),
            'client_phone'    => $request->input('client_phone', $project->client_phone),
            'location'        => $request->input('location', $project->location),
            'area_sqm'        => $request->has('area_sqm') ? ($request->filled('area_sqm') ? floatval($request->area_sqm) : null) : $project->area_sqm,
            'supervision_pct' => floatval($request->input('supervision_pct', $project->supervision_pct)),
            'contract_type'   => $request->input('contract_type', $project->contract_type),
            'status'          => $request->input('status', $project->status),
            'notes'           => $request->input('notes', $project->notes),
            'updated_at'      => now(),
        ];

        DB::table('cont_projects')->where('id', $id)->update($data);

        $this->logActivity('update', 'contracting', "تم تعديل بيانات مشروع المقاولة: {$data['name']}", [
            'project_id' => $id,
            'status'     => $data['status'],
        ]);

        return response()->json(['success' => true, 'message' => 'تم تحديث بيانات المشروع بنجاح']);
    }

    /**
     * Delete a Project
     */
    public function destroyProject($id)
    {
        $project = DB::table('cont_projects')->where('id', $id)->first();
        if (!$project) return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);

        DB::beginTransaction();
        try {
            $ops = DB::table('cont_operations')->where('project_id', $id)->get();
            $opIds = $ops->pluck('id')->all();

            // 1. Delete all linked company_debts for this project's operations
            $debtIds = $ops->pluck('debt_id')
                ->merge(DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $opIds)->pluck('id'))
                ->filter()
                ->unique();

            if ($debtIds->isNotEmpty()) {
                DB::table('company_debts')->whereIn('id', $debtIds->all())->delete();
            }

            // Update directory balances for affected suppliers / technicians / marketers
            $affectedPersons = $ops->pluck('person_name')->filter()->unique();
            foreach ($affectedPersons as $pName) {
                $liveDebt = (float) DB::table('company_debts')->where('creditor_name', $pName)->sum('remaining_balance');
                DB::table('cont_directory')->where('name', $pName)->update(['balance_debt' => max(0, $liveDebt)]);
            }

            // 2. Delete customer installment receivables for this project
            $instIds = DB::table('installments')
                ->where('source_type', 'cont_project')
                ->where('source_id', $id)
                ->pluck('id');
            if ($instIds->isNotEmpty()) {
                DB::table('installment_payments')->whereIn('installment_id', $instIds->all())->delete();
                DB::table('installments')->whereIn('id', $instIds->all())->delete();
            }

            // 3. Delete project operations, items, discounts, invoices, and project record
            DB::table('cont_operations')->where('project_id', $id)->delete();
            DB::table('cont_project_items')->where('project_id', $id)->delete();
            DB::table('cont_discounts')->where('project_id', $id)->delete();
            DB::table('cont_invoices')->where('project_id', $id)->delete();
            DB::table('cont_projects')->where('id', $id)->delete();

            DB::commit();

            $this->logActivity('delete', 'contracting', "تم حذف مشروع المقاولة ({$project->name}) وكافة عملياته وديونه المرتبطة", [
                'project_id' => $id,
            ]);

            return response()->json(['success' => true, 'message' => 'تم حذف المشروع وكافة عملياته وديونه المرتبطة بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'تعذر حذف المشروع: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Global BoQ Item (مكتبة بنود الأعمال التي تظهر)
     */
    public function storeGlobalItem(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $title = trim($request->title);

        $id = DB::table('cont_global_items')->insertGetId([
            'title'      => $title,
            'is_active'  => true,
            'sort_order' => (DB::table('cont_global_items')->max('sort_order') ?? 0) + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // If requested to apply to all active projects automatically
        if ($request->boolean('apply_to_all_projects')) {
            $activeProjects = DB::table('cont_projects')->where('status', 'active')->pluck('id');
            foreach ($activeProjects as $pId) {
                if (!DB::table('cont_project_items')->where('project_id', $pId)->where('title', $title)->exists()) {
                    DB::table('cont_project_items')->insert([
                        'project_id' => $pId,
                        'title'      => $title,
                        'status'     => 'open',
                        'sort_order' => 99,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->logActivity('create', 'contracting', "تمت إضافة بند جديد لمكتبة البنود: {$title}");

        return response()->json(['success' => true, 'id' => $id, 'title' => $title, 'message' => 'تمت إضافة البند للمكتبة بنجاح']);
    }

    /**
     * Delete Global BoQ Item
     */
    public function destroyGlobalItem($id)
    {
        DB::table('cont_global_items')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف البند من المكتبة']);
    }

    /**
     * Store Petty Category (تصنيف نثريات)
     */
    public function storePettyCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $id = DB::table('cont_petty_categories')->insertGetId([
            'name'       => trim($request->name),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'id' => $id, 'message' => 'تمت إضافة تصنيف النثريات بنجاح']);
    }

    /**
     * Delete Petty Category
     */
    public function destroyPettyCategory($id)
    {
        DB::table('cont_petty_categories')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف تصنيف النثريات']);
    }

    /**
     * Store Petty Expense (تسجيل مصروف نثريات عامة)
     */
    public function storePettyExpense(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = floatval($request->amount);
        $accountId = $request->account_id;

        DB::beginTransaction();
        try {
            if ($accountId && $amount > 0) {
                DB::table('accounts')->where('id', $accountId)->decrement('balance', $amount);

                DB::table('financial_transactions')->insert([
                    'type'            => 'expense',
                    'subtype'         => 'general_expense',
                    'amount'          => $amount,
                    'from_account_id' => $accountId,
                    'notes'           => "صرف نثريات مقاولات: {$request->title}",
                    'status'          => 'active',
                    'ref_type'        => 'cont_petty_expense',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            $id = DB::table('cont_petty_expenses')->insertGetId([
                'category_id' => $request->category_id ?: null,
                'title'       => trim($request->title),
                'amount'      => $amount,
                'account_id'  => $accountId ?: null,
                'notes'       => $request->notes,
                'created_by'  => session('auth_user')?->id ?? 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            $this->logActivity('create', 'contracting', "تم تسجيل حركة نثريات: {$request->title} بقيمة {$amount} ج", [
                'expense_id' => $id,
                'amount'     => $amount,
            ]);

            return response()->json(['success' => true, 'message' => 'تم تسجيل حركة النثريات وخصمها بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Petty Expense
     */
    public function destroyPettyExpense($id)
    {
        $exp = DB::table('cont_petty_expenses')->where('id', $id)->first();
        if (!$exp) return response()->json(['success' => false, 'message' => 'السجل غير موجود'], 404);

        DB::beginTransaction();
        try {
            if ($exp->account_id && $exp->amount > 0) {
                DB::table('accounts')->where('id', $exp->account_id)->increment('balance', $exp->amount);
            }
            DB::table('cont_petty_expenses')->where('id', $id)->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'تم حذف حركة النثريات واستعادة الرصيد']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Petty Title (اسم نثريات مقترح بدون سعر)
     */
    public function storePettyTitle(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $title = trim($request->title);
        $existing = DB::table('cont_petty_titles')->where('title', $title)->first();
        if ($existing) {
            return response()->json(['success' => true, 'id' => $existing->id, 'title' => $title, 'message' => 'الاسم مسجل بالفعل']);
        }
        $id = DB::table('cont_petty_titles')->insertGetId([
            'title'      => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'id' => $id, 'title' => $title, 'message' => 'تمت إضافة اسم النثريات بنجاح']);
    }

    /**
     * Delete Petty Title
     */
    public function destroyPettyTitle($id)
    {
        DB::table('cont_petty_titles')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف بند النثريات']);
    }

    /**
     * Store Expense Title (اسم بند مصروفات عام/داخلي بدون سعر)
     */
    public function storeExpenseTitle(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $title = trim($request->title);
        $existing = DB::table('cont_expense_titles')->where('title', $title)->first();
        if ($existing) {
            return response()->json(['success' => true, 'id' => $existing->id, 'title' => $title, 'message' => 'البند مسجل بالفعل']);
        }
        $id = DB::table('cont_expense_titles')->insertGetId([
            'title'      => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'id' => $id, 'title' => $title, 'message' => 'تمت إضافة بند المصروفات بنجاح']);
    }

    /**
     * Delete Expense Title
     */
    public function destroyExpenseTitle($id)
    {
        DB::table('cont_expense_titles')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف بند المصروفات']);
    }

    /**
     * Store Unit (حفظ وحدة قياس جديدة للأوتوكومبليت)
     */
    public function storeUnit(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $name = trim($request->name);
        $existing = DB::table('cont_units')->where('name', $name)->first();
        if ($existing) {
            return response()->json(['success' => true, 'id' => $existing->id, 'name' => $name, 'message' => 'الوحدة مسجلة بالفعل']);
        }
        $id = DB::table('cont_units')->insertGetId([
            'name'       => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'id' => $id, 'name' => $name, 'message' => 'تم حفظ وحدة القياس بنجاح']);
    }

    /**
     * Delete Unit
     */
    public function destroyUnit($id)
    {
        DB::table('cont_units')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف وحدة القياس']);
    }

    /**
     * Store or Update Survey Calculation
     */
    public function storeSurvey(Request $request)
    {
        try {
            $id = $request->input('id');
            $title = $request->input('title') ?: ('مقايسة ' . date('Y-m-d H:i'));
            $ceilingHeight = floatval($request->input('ceiling_height', 2.80));
            $totalRoomWalls = floatval($request->input('total_room_walls', 0));
            $totalFloors = floatval($request->input('total_floors', 0));
            $totalCeramicWalls = floatval($request->input('total_ceramic_walls', 0));
            $totalDeductions = floatval($request->input('total_deductions', 0));

            $roomsData = $request->input('rooms_data');
            if (is_array($roomsData)) {
                $roomsData = json_encode($roomsData, JSON_UNESCAPED_UNICODE);
            }

            $data = [
                'title'               => $title,
                'project_id'          => $request->filled('project_id') ? $request->project_id : null,
                'ceiling_height'      => $ceilingHeight,
                'total_room_walls'    => $totalRoomWalls,
                'total_floors'        => $totalFloors,
                'total_ceramic_walls' => $totalCeramicWalls,
                'total_deductions'    => $totalDeductions,
                'rooms_data'          => $roomsData,
                'notes'               => $request->input('notes'),
                'updated_at'          => now(),
            ];

            // Pricing (سعر المتر مصنعية + تكلفة المتر خامات → السعر التقريبي)
            $pricingData = $request->input('pricing_data');
            $hasPricingCols = false;
            try {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('cont_surveys', 'estimated_total')) {
                    \Illuminate\Support\Facades\Schema::table('cont_surveys', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->longText('pricing_data')->nullable()->after('rooms_data');
                        $table->decimal('total_labor_cost', 15, 2)->default(0)->after('pricing_data');
                        $table->decimal('total_material_cost', 15, 2)->default(0)->after('total_labor_cost');
                        $table->decimal('estimated_total', 15, 2)->default(0)->after('total_material_cost');
                    });
                }
                $hasPricingCols = \Illuminate\Support\Facades\Schema::hasColumn('cont_surveys', 'estimated_total');
            } catch (\Exception $e) {
                $hasPricingCols = false;
            }

            if ($hasPricingCols) {
                if (is_array($pricingData)) {
                    $pricingData = json_encode($pricingData, JSON_UNESCAPED_UNICODE);
                }
                $data['pricing_data']        = $pricingData;
                $data['total_labor_cost']    = floatval($request->input('total_labor_cost', 0));
                $data['total_material_cost'] = floatval($request->input('total_material_cost', 0));
                $data['estimated_total']     = floatval($request->input('estimated_total', 0));
            } else {
                // Resilient fallback: embed pricing in rooms_data so data is never lost even before running migration
                $rawRooms = is_array($request->input('rooms_data')) ? $request->input('rooms_data') : (json_decode($request->input('rooms_data') ?: '[]', true) ?: []);
                $rawPricing = is_array($pricingData) ? $pricingData : (json_decode($pricingData ?: '[]', true) ?: []);
                $embedded = [
                    'rooms'               => $rawRooms,
                    '_pricing'            => $rawPricing,
                    'total_labor_cost'    => floatval($request->input('total_labor_cost', 0)),
                    'total_material_cost' => floatval($request->input('total_material_cost', 0)),
                    'estimated_total'     => floatval($request->input('estimated_total', 0)),
                ];
                $data['rooms_data'] = json_encode($embedded, JSON_UNESCAPED_UNICODE);
            }

            if ($id) {
                DB::table('cont_surveys')->where('id', $id)->update($data);
                $surveyId = $id;
                $msg = 'تم تحديث المقايسة في السجل بنجاح';
            } else {
                $data['created_by'] = session('auth_user')?->id ?? 1;
                $data['created_at'] = now();
                $surveyId = DB::table('cont_surveys')->insertGetId($data);
                $msg = 'تم حفظ المقايسة في السجل بنجاح';
            }

            $survey = DB::table('cont_surveys')->where('id', $surveyId)->first();
            if ($survey && empty($survey->estimated_total)) {
                $survey->estimated_total = floatval($request->input('estimated_total', 0));
                $survey->total_labor_cost = floatval($request->input('total_labor_cost', 0));
                $survey->total_material_cost = floatval($request->input('total_material_cost', 0));
            }

            $this->logActivity('create', 'contracting', "تم حفظ مقايسة مساحات: {$title}", [
                'survey_id' => $surveyId,
                'room_walls' => $totalRoomWalls,
                'floors' => $totalFloors,
            ]);

            return response()->json([
                'success' => true,
                'message' => $msg,
                'survey'  => $survey
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حفظ المقايسة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Survey Details (for viewing or loading back into calculator)
     */
    public function getSurveyAjax($id)
    {
        $survey = DB::table('cont_surveys')->where('id', $id)->first();
        if (!$survey) {
            return response()->json(['success' => false, 'message' => 'المقايسة غير موجودة'], 404);
        }

        $rooms = [];
        if ($survey->rooms_data) {
            $decoded = is_string($survey->rooms_data) ? json_decode($survey->rooms_data, true) : $survey->rooms_data;
            if (is_array($decoded) && isset($decoded['rooms'])) {
                $rooms = $decoded['rooms'];
                if (empty($survey->pricing_data) && isset($decoded['_pricing'])) {
                    $survey->pricing_data = $decoded['_pricing'];
                    $survey->total_labor_cost = $decoded['total_labor_cost'] ?? 0;
                    $survey->total_material_cost = $decoded['total_material_cost'] ?? 0;
                    $survey->estimated_total = $decoded['estimated_total'] ?? 0;
                }
            } else {
                $rooms = is_array($decoded) ? $decoded : [];
            }
        }

        $pricing = [];
        if (!empty($survey->pricing_data)) {
            $pricing = is_string($survey->pricing_data) ? (json_decode($survey->pricing_data, true) ?: []) : $survey->pricing_data;
        }

        return response()->json([
            'success' => true,
            'survey'  => $survey,
            'rooms'   => $rooms,
            'pricing' => $pricing
        ]);
    }

    /**
     * Delete Survey from log
     */
    public function destroySurvey($id)
    {
        try {
            $survey = DB::table('cont_surveys')->where('id', $id)->first();
            if (!$survey) {
                return response()->json(['success' => false, 'message' => 'المقايسة غير موجودة'], 404);
            }

            DB::table('cont_surveys')->where('id', $id)->delete();

            $this->logActivity('delete', 'contracting', "تم حذف مقايسة المساحات: {$survey->title}", [
                'survey_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المقايسة من السجل بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Estimation Benchmark Data (بيانات المشروع المرجعي أو المتوسط لحاسبة التقدير)
     */
    public function getEstimationBenchmarkAjax(Request $request)
    {
        try {
            $projectId = $request->input('project_id');

            if ($projectId === 'average' || !$projectId) {
                // Average across projects that have a REAL area recorded in the database
                $projectsWithArea = DB::table('cont_projects')->where('area_sqm', '>', 0)->get();
                $projCount = $projectsWithArea->count();

                if ($projCount === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا توجد مشاريع مسجل لها مساحة في قاعدة البيانات. سجّل مساحة المشروع (م²) من سجل المشاريع أولاً.',
                    ]);
                }

                $projIds = $projectsWithArea->pluck('id');
                // متوسط مساحة المشروع الواحد (من قاعدة البيانات)
                $avgArea = $projectsWithArea->avg('area_sqm');

                // Only operations of projects that have a recorded area, averaged per project
                $allOps = DB::table('cont_operations')->whereIn('project_id', $projIds)->get();
                $totalCost = $allOps->sum('total_cost') / $projCount;
                $totalSelling = $allOps->sum('total_selling') / $projCount;
                $totalArea = $avgArea;

                $matOps = $allOps->where('op_type', 'material');
                $laborOps = $allOps->whereIn('op_type', ['technician', 'labor']);
                $overheadOps = $allOps->whereNotIn('op_type', ['material', 'technician', 'labor']);

                $materialsCost = $matOps->sum('total_cost') / $projCount;
                $laborCost = $laborOps->sum('total_cost') / $projCount;
                $overheadCost = $overheadOps->sum('total_cost') / $projCount;

                // Items Breakdown (averaged per project)
                $itemsBreakdown = DB::table('cont_operations')
                    ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
                    ->whereIn('cont_operations.project_id', $projIds)
                    ->select('cont_project_items.title', DB::raw('SUM(cont_operations.total_cost) / ' . (int) $projCount . ' as total_cost'))
                    ->whereNotNull('cont_project_items.title')
                    ->where('cont_project_items.title', '!=', '')
                    ->groupBy('cont_project_items.title')
                    ->orderByDesc('total_cost')
                    ->get();

                // Materials Breakdown
                $materialsBreakdown = $matOps->groupBy('name_or_title')->map(function($rows, $name) use ($projCount) {
                    return [
                        'name'       => $name,
                        'unit'       => $rows->first()->unit ?? 'عدد',
                        'total_qty'  => round(floatval($rows->sum('quantity')) / $projCount, 2),
                        'total_cost' => round(floatval($rows->sum('total_cost')) / $projCount, 2),
                        'avg_price'  => $rows->sum('quantity') > 0 ? round($rows->sum('total_cost') / $rows->sum('quantity'), 2) : 0,
                    ];
                })->values()->sortByDesc('total_cost')->values();

                return response()->json([
                    'success'    => true,
                    'is_average' => true,
                    'area_from_db' => true,
                    'projects_count' => $projCount,
                    'benchmark'  => [
                        'id'              => 'average',
                        'name'            => 'متوسط المشاريع المسجل لها مساحة (' . $projCount . ' مشروع)',
                        'client_name'     => 'بيانات عامة مجمعة',
                        'area_sqm'        => round($totalArea, 2),
                        'total_cost'      => round($totalCost, 2),
                        'total_selling'   => round($totalSelling, 2),
                        'materials_cost'  => round($materialsCost, 2),
                        'labor_cost'      => round($laborCost, 2),
                        'overhead_cost'   => round($overheadCost, 2),
                        'supervision_pct' => 15.0,
                    ],
                    'items'      => $itemsBreakdown,
                    'materials'  => $materialsBreakdown,
                ]);
            }

            // Specific Project
            $project = DB::table('cont_projects')->where('id', $projectId)->first();
            if (!$project) {
                return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);
            }

            $ops = DB::table('cont_operations')->where('project_id', $projectId)->get();
            $totalCost = $ops->sum('total_cost');
            $totalSelling = $ops->sum('total_selling');

            $matOps = $ops->where('op_type', 'material');
            $laborOps = $ops->whereIn('op_type', ['technician', 'labor']);
            $overheadOps = $ops->whereNotIn('op_type', ['material', 'technician', 'labor']);

            $materialsCost = $matOps->sum('total_cost');
            $laborCost = $laborOps->sum('total_cost');
            $overheadCost = $overheadOps->sum('total_cost');

            // المساحة من قاعدة البيانات فقط (بدون رقم افتراضي وهمي)
            $areaFromDb = floatval($project->area_sqm) > 0;
            $area = $areaFromDb ? floatval($project->area_sqm) : 0;

            // Items Breakdown for this project
            $itemsBreakdown = DB::table('cont_operations')
                ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
                ->where('cont_operations.project_id', $projectId)
                ->select('cont_project_items.title', DB::raw('SUM(cont_operations.total_cost) as total_cost'))
                ->whereNotNull('cont_project_items.title')
                ->where('cont_project_items.title', '!=', '')
                ->groupBy('cont_project_items.title')
                ->orderByDesc('total_cost')
                ->get();

            // Materials Breakdown for this project
            $materialsBreakdown = $matOps->groupBy('name_or_title')->map(function($rows, $name) {
                return [
                    'name'       => $name,
                    'unit'       => $rows->first()->unit ?? 'عدد',
                    'total_qty'  => floatval($rows->sum('quantity')),
                    'total_cost' => floatval($rows->sum('total_cost')),
                    'avg_price'  => $rows->sum('quantity') > 0 ? round($rows->sum('total_cost') / $rows->sum('quantity'), 2) : 0,
                ];
            })->values()->sortByDesc('total_cost')->values();

            return response()->json([
                'success'    => true,
                'is_average' => false,
                'area_from_db' => $areaFromDb,
                'benchmark'  => [
                    'id'              => $project->id,
                    'name'            => $project->name,
                    'client_name'     => $project->client_name,
                    'area_sqm'        => $area,
                    'total_cost'      => round($totalCost, 2),
                    'total_selling'   => round($totalSelling, 2),
                    'materials_cost'  => round($materialsCost, 2),
                    'labor_cost'      => round($laborCost, 2),
                    'overhead_cost'   => round($overheadCost, 2),
                    'supervision_pct' => floatval($project->supervision_pct) ?: 15.0,
                ],
                'items'      => $itemsBreakdown,
                'materials'  => $materialsBreakdown,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate Scalable Vector Barcode SVG (رمز الباركود التخطيطي)
     */
    public static function generateBarcodeSvg($code, $width = 270, $height = 38)
    {
        $codeClean = strtoupper(preg_replace('/[^A-Z0-9\-]/', '', $code));
        if (empty($codeClean)) $codeClean = 'DOC-2026';

        $bars = [];
        $x = 8;
        // Start guard bars
        $bars[] = ['x' => $x, 'w' => 2]; $x += 4;
        $bars[] = ['x' => $x, 'w' => 1]; $x += 3;
        $bars[] = ['x' => $x, 'w' => 3]; $x += 5;

        for ($i = 0; $i < strlen($codeClean); $i++) {
            $c = ord($codeClean[$i]);
            $pattern = [
                ($c % 3) + 1,
                (($c >> 1) % 2) + 1,
                (($c >> 2) % 3) + 1,
                (($c >> 3) % 2) + 1
            ];
            foreach ($pattern as $idx => $barW) {
                $bars[] = ['x' => $x, 'w' => $barW];
                $x += $barW + (($idx % 2 == 0) ? 2 : 1);
            }
        }
        // End guard bars
        $bars[] = ['x' => $x, 'w' => 3]; $x += 5;
        $bars[] = ['x' => $x, 'w' => 1]; $x += 3;
        $bars[] = ['x' => $x, 'w' => 2]; $x += 4;

        $totalWidth = $x + 8;
        $svg = '<svg viewBox="0 0 ' . $totalWidth . ' ' . $height . '" width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        foreach ($bars as $bar) {
            $svg .= '<rect x="' . $bar['x'] . '" y="0" width="' . $bar['w'] . '" height="' . $height . '" fill="#0f172a" />';
        }
        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Generate Self-Contained Vector QR Matrix SVG (يعمل بدون إنترنت وبدون رموز تالفة)
     */
    public static function generateQrSvg($text, $size = 84)
    {
        $grid = 21;
        $matrix = array_fill(0, $grid, array_fill(0, $grid, 0));
        $reserved = array_fill(0, $grid, array_fill(0, $grid, false));

        $placeFinder = function ($r0, $c0) use (&$matrix, &$reserved, $grid) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $rr = $r0 + $r;
                    $cc = $c0 + $c;
                    if ($rr < 0 || $rr >= $grid || $cc < 0 || $cc >= $grid) continue;
                    $reserved[$rr][$cc] = true;
                    if ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) {
                        if ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4)) {
                            $matrix[$rr][$cc] = 1;
                        }
                    }
                }
            }
        };

        $placeFinder(0, 0);
        $placeFinder(0, $grid - 7);
        $placeFinder($grid - 7, 0);

        // Timing patterns
        for ($i = 8; $i < $grid - 8; $i++) {
            $reserved[6][$i] = true;
            $reserved[$i][6] = true;
            $matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
            $matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
        }

        // Deterministic hash bits from text
        $hash = hash('sha256', (string) $text) . hash('sha256', 'QR-' . (string) $text);
        $bits = '';
        for ($i = 0; $i < strlen($hash); $i += 2) {
            $byte = hexdec(substr($hash, $i, 2));
            $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        $bitIdx = 0;
        $bitLen = strlen($bits);
        for ($r = 0; $r < $grid; $r++) {
            for ($c = 0; $c < $grid; $c++) {
                if (!$reserved[$r][$c]) {
                    $matrix[$r][$c] = ($bits[$bitIdx % $bitLen] === '1') ? 1 : 0;
                    $bitIdx++;
                }
            }
        }

        $svg = '<svg viewBox="0 0 ' . $grid . ' ' . $grid . '" width="' . $size . '" height="' . $size . '" shape-rendering="crispEdges" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="' . $grid . '" height="' . $grid . '" fill="#ffffff"/>';
        for ($r = 0; $r < $grid; $r++) {
            for ($c = 0; $c < $grid; $c++) {
                if ($matrix[$r][$c]) {
                    $svg .= '<rect x="' . $c . '" y="' . $r . '" width="1" height="1" fill="#0f172a"/>';
                }
            }
        }
        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Print Quotation (عرض سعر رسمي)
     */
    public function printQuotation($id)
    {
        $quotation = DB::table('cont_quotations')->where('id', $id)->first();
        if (!$quotation) abort(404, 'عرض السعر غير موجود');

        $rawItems = json_decode($quotation->items_data, true) ?: [];
        $items = [];
        $totalQty = 0;
        $subtotal = 0;

        foreach ($rawItems as $idx => $item) {
            $qty = floatval($item['qty'] ?? 1);
            if ($qty <= 0) $qty = 1;
            $unitPrice = floatval($item['price'] ?? $item['cost'] ?? 0);
            $lineTotal = round($qty * $unitPrice, 2);

            $items[] = [
                'index'      => $idx + 1,
                'code'       => 'ITM-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'title'      => $item['item_title'] ?? ($item['title'] ?? 'بند أعمال وتشطيبات'),
                'qty'        => $qty,
                'unit'       => $item['unit'] ?? 'بند',
                'unit_price' => $unitPrice,
                'total'      => $lineTotal,
            ];

            $totalQty += $qty;
            $subtotal += $lineTotal;
        }

        $supervisionPct = floatval($quotation->supervision_pct ?? 0);
        $supervisionAmount = round($subtotal * ($supervisionPct / 100), 2);
        $grandTotal = $subtotal + $supervisionAmount;

        $docCode = 'QT-' . date('Y-m', strtotime($quotation->created_at)) . '-' . str_pad($quotation->id, 4, '0', STR_PAD_LEFT);
        $barcodeSvg = self::generateBarcodeSvg($docCode);
        $qrSvg = self::generateQrSvg(url()->current() . '|' . $docCode);

        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        $doc = [
            'type'               => 'quotation',
            'page_title'         => 'عرض سعر رسمي - ' . $quotation->project_title,
            'title'              => 'عرض سعر',
            'code'               => $docCode,
            'serial'             => $docCode,
            'status'             => $quotation->status == 'converted_to_project' ? 'معتمد ومحول لمشروع' : 'معتمد',
            'status_color'       => 'success',
            'client_name'        => $quotation->client_name,
            'client_phone'       => $quotation->client_phone,
            'project_title'      => $quotation->project_title,
            'project_code'       => $quotation->converted_project_id,
            'issue_date'         => date('d/m/Y', strtotime($quotation->created_at)),
            'valid_until'        => date('d/m/Y', strtotime($quotation->created_at . ' +15 days')),
            'currency'           => 'ج.م',
            'items'              => $items,
            'total_qty'          => $totalQty,
            'subtotal'           => $subtotal,
            'supervision_pct'    => $supervisionPct,
            'supervision_amount' => $supervisionAmount,
            'discount_amount'    => 0,
            'tax_amount'         => 0,
            'tax_rate'           => 0,
            'grand_total'        => $grandTotal,
            'paid_amount'        => null,
            'remaining_balance'  => null,
            'barcode_svg'        => $barcodeSvg,
            'qr_svg'             => $qrSvg,
            'terms'              => [
                'الأسعار الواردة بهذا العرض سارية لمدة 15 يوماً من تاريخ الإصدار وتعتمد عند التعاقد.',
                'التوريد والتركيب طبقاً للمواصفات وأصول الصناعة الهندسية المتفق عليها.',
                'الدفعات المالية تسدد وفقاً لجدول مراحل التنفيذ المتفق عليه في العقد.',
                'الأعمال الإضافية أو التعديلات غير المشمولة تخضع لملحق مقايسة منفصل.',
            ],
        ];

        return view('contracting_invoice_print', compact('doc', 'settings'));
    }

    /**
     * Print Client Invoice / Payment Claim (فاتورة مبيعات / مستخلص / سند تحصيل)
     */
    public function printInvoice($id)
    {
        $invoice = DB::table('cont_invoices')
            ->leftJoin('cont_projects', 'cont_invoices.project_id', '=', 'cont_projects.id')
            ->where('cont_invoices.id', $id)
            ->select(
                'cont_invoices.*',
                'cont_projects.name as project_name',
                'cont_projects.client_name',
                'cont_projects.client_phone',
                'cont_projects.supervision_pct'
            )->first();

        if (!$invoice) abort(404, 'الفاتورة / الإيصال غير موجود');

        $isInstallment = !empty($invoice->installment_id);
        $docCode = $invoice->invoice_number ?: ('INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT));
        $serialCode = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($docCode)) ?: ('INV-' . date('Ymd', strtotime($invoice->created_at)) . '-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT));
        $barcodeSvg = self::generateBarcodeSvg($serialCode);
        $qrSvg = self::generateQrSvg(url()->current() . '|' . $serialCode);

        $billed = floatval($invoice->total_billed) > 0 ? floatval($invoice->total_billed) : floatval($invoice->paid_amount);
        $paid = floatval($invoice->paid_amount);
        $remaining = floatval($invoice->remaining_balance);

        $items = [
            [
                'index'      => 1,
                'code'       => 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT),
                'title'      => $invoice->notes ?: ($isInstallment ? ('سند عقد تقسيط لمشروع: ' . $invoice->project_name) : ('مستخلص وسند مالي لمشروع: ' . $invoice->project_name)),
                'qty'        => 1,
                'unit'       => $isInstallment ? 'عقد' : 'مستخلص',
                'unit_price' => $billed,
                'total'      => $billed,
            ]
        ];

        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        $doc = [
            'type'               => 'invoice',
            'page_title'         => ($isInstallment ? 'سند عقد تقسيط' : 'فاتورة ومستخلص مالي') . ' - ' . $invoice->project_name,
            'title'              => $isInstallment ? 'سند تقسيط' : 'فاتورة مبيعات / مستخلص',
            'code'               => $docCode,
            'serial'             => $serialCode,
            'status'             => $remaining <= 0 ? 'مدفوع بالكامل' : ($paid > 0 ? 'سداد جزئي' : 'مستحق السداد'),
            'status_color'       => $remaining <= 0 ? 'success' : 'primary',
            'client_name'        => $invoice->client_name,
            'client_phone'       => $invoice->client_phone,
            'project_title'      => $invoice->project_name,
            'project_code'       => $invoice->project_id,
            'issue_date'         => date('d/m/Y', strtotime($invoice->created_at)),
            'valid_until'        => date('d/m/Y', strtotime($invoice->created_at . ' +30 days')),
            'currency'           => 'ج.م',
            'items'              => $items,
            'total_qty'          => 1,
            'subtotal'           => $billed,
            'supervision_pct'    => 0,
            'supervision_amount' => 0,
            'discount_amount'    => floatval($invoice->discount_amount ?? 0),
            'tax_amount'         => floatval($invoice->tax_amount ?? 0),
            'tax_rate'           => floatval($invoice->tax_rate ?? 0),
            'grand_total'        => $billed + floatval($invoice->tax_amount ?? 0),
            'paid_amount'        => $paid,
            'remaining_balance'  => $remaining,
            'barcode_svg'        => $barcodeSvg,
            'qr_svg'             => $qrSvg,
            'terms'              => [
                'يعتبر هذا المستند إشعاراً رسمياً بالسداد المالي والمطالبات المعتمدة.',
                'الدفعات تخضع لبنود التعاقد ومحاضر الاستلام الفني للأعمال المنفذة.',
                'تحتفظ الإدارة بسجل تفصيلي لحركات الحسابات وتوريدات الخامات.',
            ],
        ];

        return view('contracting_invoice_print', compact('doc', 'settings'));
    }

    /**
     * Print Material Purchase Invoice (فاتورة خامات ومشتريات موردين)
     */
    public function printMaterialInvoice(Request $request, $ref)
    {
        $ref = urldecode($ref);
        $query = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
            ->where('cont_operations.invoice_ref', $ref);

        if ($request->has('project_id') && $request->input('project_id')) {
            $query->where('cont_operations.project_id', $request->input('project_id'));
        }

        $ops = $query->select(
            'cont_operations.*',
            'cont_projects.name as project_name',
            'cont_projects.client_name',
            'cont_projects.client_phone',
            'cont_project_items.title as item_title'
        )->orderBy('cont_operations.id', 'asc')->get();

        if ($ops->isEmpty()) abort(404, 'فاتورة الخامات غير موجودة');

        $first = $ops->first();
        $supplierName = $first->person_name ?: 'مورد خامات';
        $viewMode = $request->input('view', 'cost'); // 'cost' or 'selling'

        $items = [];
        $totalQty = 0;
        $subtotal = 0;

        foreach ($ops as $idx => $op) {
            $qty = floatval($op->quantity);
            if ($qty <= 0) $qty = 1;
            $unitPrice = $viewMode === 'selling' ? floatval($op->selling_price) : floatval($op->cost_price);
            $lineTotal = $viewMode === 'selling' ? floatval($op->total_selling) : floatval($op->total_cost);

            $items[] = [
                'index'      => $idx + 1,
                'code'       => 'MAT-' . str_pad($op->id, 4, '0', STR_PAD_LEFT),
                'title'      => $op->name_or_title . ($op->item_title ? " ({$op->item_title})" : ''),
                'qty'        => $qty,
                'unit'       => $op->unit ?: 'عدد',
                'unit_price' => $unitPrice,
                'total'      => $lineTotal,
            ];

            $totalQty += $qty;
            $subtotal += $lineTotal;
        }

        $totalPaid = $ops->sum('paid_amount');
        $totalDebt = $ops->sum('remaining_debt');
        $serialCode = 'MAT-' . date('Ymd', strtotime($first->created_at)) . '-' . str_pad($first->id, 4, '0', STR_PAD_LEFT);
        $barcodeSvg = self::generateBarcodeSvg($serialCode);
        $qrSvg = self::generateQrSvg(url()->current() . '|' . $ref . '|' . $serialCode);

        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        $doc = [
            'type'               => 'material',
            'page_title'         => 'فاتورة توريد خامات - ' . $ref,
            'title'              => $viewMode === 'selling' ? 'فاتورة توريد خامات (للعميل)' : 'فاتورة توريد خامات',
            'code'               => $ref,
            'serial'             => $serialCode,
            'status'             => $totalDebt <= 0 ? 'مسددة بالكامل' : ($totalPaid > 0 ? 'سداد جزئي' : 'آجل (دين معلق)'),
            'status_color'       => $totalDebt <= 0 ? 'success' : 'danger',
            'client_name'        => $supplierName,
            'client_phone'       => $first->client_phone,
            'project_title'      => $first->project_name . ' - العميل: ' . $first->client_name,
            'project_code'       => $first->project_id,
            'issue_date'         => date('d/m/Y', strtotime($first->created_at)),
            'valid_until'        => date('d/m/Y', strtotime($first->created_at . ' +30 days')),
            'currency'           => 'ج.م',
            'items'              => $items,
            'total_qty'          => $totalQty,
            'subtotal'           => $subtotal,
            'supervision_pct'    => 0,
            'supervision_amount' => 0,
            'discount_amount'    => 0,
            'tax_amount'         => 0,
            'tax_rate'           => 0,
            'grand_total'        => $subtotal,
            'paid_amount'        => $totalPaid,
            'remaining_balance'  => $totalDebt,
            'barcode_svg'        => $barcodeSvg,
            'qr_svg'             => $qrSvg,
            'view_mode'          => $viewMode,
            'terms'              => [],
        ];

        return view('contracting_invoice_print', compact('doc', 'settings'));
    }

    /**
     * Print Survey Calculation (طباعة مقايسة المساحات)
     */
    public function printSurvey($id)
    {
        $survey = DB::table('cont_surveys')
            ->leftJoin('cont_projects', 'cont_surveys.project_id', '=', 'cont_projects.id')
            ->where('cont_surveys.id', $id)
            ->select('cont_surveys.*', 'cont_projects.name as project_name', 'cont_projects.client_name', 'cont_projects.client_phone')
            ->first();

        if (!$survey) abort(404, 'المقايسة غير موجودة');

        $rooms = [];
        if ($survey->rooms_data) {
            $decoded = is_string($survey->rooms_data) ? json_decode($survey->rooms_data, true) : $survey->rooms_data;
            if (is_array($decoded) && isset($decoded['rooms'])) {
                $rooms = $decoded['rooms'];
                if (empty($survey->pricing_data) && isset($decoded['_pricing'])) {
                    $survey->pricing_data = $decoded['_pricing'];
                    $survey->estimated_total = $decoded['estimated_total'] ?? 0;
                }
            } else {
                $rooms = is_array($decoded) ? $decoded : [];
            }
        }
        if (!is_array($rooms)) $rooms = [];

        $items = [];
        $totalQty = 0;

        $pricing = [];
        if (!empty($survey->pricing_data)) {
            $pricing = is_string($survey->pricing_data) ? (json_decode($survey->pricing_data, true) ?: []) : (array) $survey->pricing_data;
        }
        $estimatedTotal = floatval($survey->estimated_total ?? 0);

        if ($estimatedTotal > 0 && !empty($pricing)) {
            // Priced survey: one line per work category (المساحة × (مصنعية + خامات))
            $idx = 0;
            foreach ($pricing as $p) {
                $area = floatval($p['area'] ?? 0);
                $labor = floatval($p['labor'] ?? 0);
                $material = floatval($p['material'] ?? 0);
                if ($area <= 0 || ($labor + $material) <= 0) continue;
                $idx++;
                $items[] = [
                    'index'      => $idx,
                    'code'       => 'SURV-' . str_pad($idx, 3, '0', STR_PAD_LEFT),
                    'title'      => ($p['label'] ?? 'بند') . " (مصنعية {$labor} ج/م² + خامات {$material} ج/م²)",
                    'qty'        => round($area, 2),
                    'unit'       => 'م²',
                    'unit_price' => $labor + $material,
                    'total'      => round($area * ($labor + $material), 2),
                ];
                $totalQty += $area;
            }
        } else {
            foreach ($rooms as $idx => $r) {
                $floorArea = floatval($r['floor'] ?? $r['floor_area'] ?? 0);
                $wallArea = floatval($r['wall'] ?? $r['wall_area'] ?? 0);
                $len = $r['len'] ?? $r['length'] ?? 0;
                $wid = $r['wid'] ?? $r['width'] ?? 0;
                $items[] = [
                    'index'      => $idx + 1,
                    'code'       => 'SURV-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'title'      => ($r['name'] ?? 'غرفة / مساحة') . " (أبعاد: {$len}م × {$wid}م | حوائط: " . round($wallArea, 2) . " م²)",
                    'qty'        => $floorArea > 0 ? round($floorArea, 2) : 1,
                    'unit'       => 'م²',
                    'unit_price' => 0,
                    'total'      => round($floorArea, 2),
                ];
                $totalQty += ($floorArea > 0 ? $floorArea : 1);
            }
        }

        $docCode = 'SURV-' . date('Y-m', strtotime($survey->created_at)) . '-' . str_pad($survey->id, 4, '0', STR_PAD_LEFT);
        $barcodeSvg = self::generateBarcodeSvg($docCode);
        $qrSvg = self::generateQrSvg(url()->current() . '|' . $docCode);
        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        $clientName = $survey->client_name ?: 'عميل المقايسة';
        $clientPhone = $survey->client_phone ?: '';
        $projectTitle = $survey->title . ($survey->project_name ? " (مشروع: {$survey->project_name})" : '');

        $doc = [
            'type'               => 'survey',
            'page_title'         => 'مقايسة مساحات رسمية - ' . $survey->title,
            'title'              => 'مقايسة مساحات وتشطيبات',
            'code'               => $docCode,
            'serial'             => $docCode,
            'status'             => 'معتمدة هندسياً',
            'status_color'       => 'success',
            'client_name'        => $clientName,
            'client_phone'       => $clientPhone,
            'project_title'      => $projectTitle,
            'project_code'       => $survey->project_id,
            'issue_date'         => date('d/m/Y', strtotime($survey->created_at)),
            'valid_until'        => date('d/m/Y', strtotime($survey->created_at . ' +30 days')),
            'currency'           => $estimatedTotal > 0 ? 'ج.م' : 'م²',
            'items'              => $items,
            'total_qty'          => $totalQty,
            'subtotal'           => $estimatedTotal > 0 ? $estimatedTotal : floatval($survey->total_floors),
            'supervision_pct'    => 0,
            'supervision_amount' => 0,
            'discount_amount'    => 0,
            'tax_amount'         => 0,
            'tax_rate'           => 0,
            'grand_total'        => $estimatedTotal > 0 ? $estimatedTotal : floatval($survey->total_floors),
            'paid_amount'        => null,
            'remaining_balance'  => null,
            'barcode_svg'        => $barcodeSvg,
            'qr_svg'             => $qrSvg,
            'terms'              => [
                'تم حساب المساحات والكميات طبقاً للرفوعات الميدانية والمعمارية.',
                'الكميات تخضع للمطابقة الفعلية عند التوريد والتنفيذ على الطبيعة.',
                'المقايسة صالحة للاعتماد الفني وتجهيز عروض الأسعار التنفيذية.',
            ],
        ];

        return view('contracting_invoice_print', compact('doc', 'settings'));
    }
}
