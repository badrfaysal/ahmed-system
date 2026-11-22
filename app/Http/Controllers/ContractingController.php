<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    protected function ensureMaterialReturnsTableExists(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('cont_material_returns')) {
            \Illuminate\Support\Facades\Schema::create('cont_material_returns', function ($table) {
                $table->id();
                $table->string('return_number')->nullable();
                $table->string('invoice_ref')->nullable();
                $table->unsignedBigInteger('project_id')->nullable();
                $table->string('project_name')->nullable();
                $table->string('client_name')->nullable();
                $table->string('supplier_name')->nullable();
                $table->unsignedBigInteger('operation_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_title')->nullable();
                $table->string('material_name')->nullable();
                $table->string('unit')->nullable();
                $table->decimal('original_qty', 15, 2)->default(0);
                $table->decimal('returned_qty', 15, 2)->default(0);
                $table->decimal('remaining_qty', 15, 2)->default(0);
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->decimal('selling_price', 15, 2)->default(0);
                $table->decimal('supervision_pct', 8, 2)->default(0);
                $table->decimal('total_cost_returned', 15, 2)->default(0);
                $table->decimal('total_selling_returned', 15, 2)->default(0);
                $table->string('settlement_method')->default('debt_deduction');
                $table->decimal('debt_deducted', 15, 2)->default(0);
                $table->decimal('cash_refunded', 15, 2)->default(0);
                $table->decimal('supplier_credit', 15, 2)->default(0);
                $table->unsignedBigInteger('account_id')->nullable();
                $table->text('reason')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('cont_operations', 'original_quantity')) {
            \Illuminate\Support\Facades\Schema::table('cont_operations', function ($table) {
                $table->decimal('original_quantity', 15, 2)->nullable()->after('quantity');
                $table->decimal('returned_quantity', 15, 2)->default(0)->after('original_quantity');
            });
            DB::statement('UPDATE cont_operations SET original_quantity = quantity WHERE original_quantity IS NULL');
        }
    }

    protected function ensureWorkerPaymentsTableExists(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('cont_worker_payments')) {
            \Illuminate\Support\Facades\Schema::create('cont_worker_payments', function ($table) {
                $table->id();
                $table->unsignedBigInteger('sy2_payment_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->unsignedBigInteger('operation_id')->nullable()->index();
                $table->unsignedBigInteger('debt_id')->nullable()->index();
                $table->unsignedBigInteger('person_id')->nullable()->index();
                $table->string('person_name')->index();
                $table->string('person_type', 30)->default('technician')->index();
                $table->decimal('amount', 15, 2)->default(0);
                $table->decimal('discount', 15, 2)->default(0);
                $table->string('discount_reason', 500)->nullable();
                $table->date('payment_date')->nullable()->index();
                $table->string('method', 50)->nullable();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->text('notes')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Fetch active debt settlement & discount transactions for the given company_debts IDs.
     * Note: company_debt_payment and earned_discount store company_debts.id in ref_id,
     * whereas company_debt_payment_discount stores the parent company_debt_payment FT id in ref_id.
     */
    protected function fetchSubsequentDebtTransactions(array $debtIds, string $direction = 'asc')
    {
        $debtIds = array_values(array_filter(array_unique(array_map('intval', $debtIds))));
        if (empty($debtIds)) {
            return collect();
        }

        $directTxs = DB::table('financial_transactions')
            ->where('status', 'active')
            ->whereIn('ref_type', ['company_debt_payment', 'earned_discount'])
            ->whereIn('ref_id', $debtIds)
            ->get();

        $cashFtIds = $directTxs->where('ref_type', 'company_debt_payment')->pluck('id')->filter()->values()->all();
        $discountTxs = !empty($cashFtIds)
            ? DB::table('financial_transactions')
                ->where('status', 'active')
                ->where('ref_type', 'company_debt_payment_discount')
                ->whereIn('ref_id', $cashFtIds)
                ->get()
            : collect();

        $all = $directTxs->concat($discountTxs);
        return strtolower($direction) === 'desc'
            ? $all->sortByDesc('created_at')->values()
            : $all->sortBy('created_at')->values();
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
        $this->ensureMaterialReturnsTableExists();
        $this->ensureWorkerPaymentsTableExists();

        // تنظيف أي مسميات مصروفات قديمة سُجلت بالخطأ كموردين في سجل المتعاملين
        DB::table('cont_directory')
            ->whereIn('name', ['نثريات موقع', 'مصروف موقع / إدارة', 'مصروف داخلي', 'نثريات المقاولات', 'مصروفات داخلية - المقاولات'])
            ->delete();
        $projects = DB::table('cont_projects')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate metrics for each project
        $projectMetrics = [];

        // Sync & consolidate customer receivables & mirror payments BEFORE loading metrics
        $projIdsToSync = collect()
            ->merge(DB::table('installments')->where('source_type', 'cont_project')->where('status', '!=', 'cancelled')->pluck('source_id'))
            ->merge(DB::table('cont_invoices')->where('invoice_type', 'installment_contract')->pluck('project_id'))
            ->filter()
            ->unique()
            ->values();
        foreach ($projIdsToSync as $projId) {
            $this->syncProjectCustomerReceivable($projId);
        }

        // Pre-load installments & payments for all contracting projects to reflect collections from receivables screen
        $contInstallments = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->keyBy('source_id');
        $allInstPayments = DB::table('installment_payments')
            ->select('installment_id', DB::raw('SUM(amount_paid + COALESCE(discount_applied, 0)) as total_paid'))
            ->groupBy('installment_id')
            ->pluck('total_paid', 'installment_id');
        $instContractInvByProj = DB::table('cont_invoices')
            ->where('invoice_type', 'installment_contract')
            ->orderByDesc('id')
            ->get()
            ->unique('project_id')
            ->keyBy('project_id');
        $directPlanInstIds = $instContractInvByProj->pluck('installment_id')->filter()->unique()->values()->all();
        $directPlanInstsById = !empty($directPlanInstIds)
            ? DB::table('installments')->whereIn('id', $directPlanInstIds)->where('status', '!=', 'cancelled')->get()->keyBy('id')
            : collect();

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

            $inst = $contInstallments->get($p->id);
            $instPlanInv = $instContractInvByProj->get($p->id);
            $fallbackInst = ($instPlanInv && !empty($instPlanInv->installment_id)) ? $directPlanInstsById->get($instPlanInv->installment_id) : null;
            $resolvedInst = ($inst && intval($inst->installment_months ?? 0) > 0) ? $inst : (($fallbackInst && intval($fallbackInst->installment_months ?? 0) > 0) ? $fallbackInst : null);
            $isInstPlan = ($resolvedInst !== null) || !empty($instPlanInv);

            $clientPaid = $this->computeProjectClientPaid($inst, $invoices, $inst ? floatval($allInstPayments[$inst->id] ?? 0) : 0.0);
            $instInterestExtra = $resolvedInst
                ? max(0, round(floatval($resolvedInst->total_after_interest ?? 0) - floatval($resolvedInst->cash_price ?? 0), 2))
                : 0.0;
            $bal = $this->computeProjectBalanceMetrics($netSelling, $instInterestExtra, $clientPaid);
            $netProfit = $netSelling - $totalCost;

            $projectMetrics[$p->id] = [
                'ops_count'           => $ops->count(),
                'total_cost'          => $totalCost,
                'billable_selling'    => $grossSelling,
                'supervision_amount'  => $supervisionAmount,
                'internal_expenses'   => $internalExpenses,
                'discounts'           => $discounts,
                'net_selling'         => $netSelling,
                'client_paid'         => $clientPaid,
                'client_remaining'    => $bal['client_remaining'],
                'client_credit'       => $bal['client_credit'],
                'net_profit'          => $netProfit,
                'is_installment_plan' => $isInstPlan,
                'installment_id'      => $resolvedInst->id ?? ($instPlanInv->installment_id ?? null),
                'installment_months'  => intval($resolvedInst->installment_months ?? 0),
                'monthly_installment' => floatval($resolvedInst->monthly_installment ?? 0),
            ];
        }

        $items = DB::table('cont_project_items')->orderBy('sort_order')->get();

        // Sync any historical suppliers/technicians from cont_operations into cont_directory
        if (function_exists('ensureSupplierCreditSchema')) {
            ensureSupplierCreditSchema();
        }
        $existingSuppliers = DB::table('cont_directory')->where('type', 'supplier')->pluck('name')->flip();
        $opSuppliers = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->whereNotNull('person_name')
            ->where('person_name', '!=', '')
            ->whereNotIn('person_name', ['نثريات موقع', 'مصروف موقع / إدارة', 'مصروف داخلي', 'نثريات المقاولات', 'مصروفات داخلية - المقاولات'])
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

        $suppliers   = $directory->where('type', 'supplier')->unique(fn($d) => mb_strtolower(trim($d->name)))->values();
        $technicians = $directory->where('type', 'technician')->unique(fn($d) => mb_strtolower(trim($d->name)))->values();
        $marketers   = $directory->where('type', 'marketer')->unique(fn($d) => mb_strtolower(trim($d->name)))->values();
        $clientsList = $directory->where('type', 'client')->unique(fn($d) => mb_strtolower(trim($d->name)))->values();

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
                DB::raw("GROUP_CONCAT(cont_operations.name_or_title SEPARATOR ' ، ') as materials_names"),
                DB::raw('SUM(cont_operations.total_cost) as total_cost'),
                DB::raw('SUM(cont_operations.total_selling) as total_selling'),
                DB::raw('SUM(cont_operations.paid_amount) as total_paid'),
                DB::raw('SUM(cont_operations.remaining_debt) as total_debt'),
                DB::raw('SUM(cont_operations.quantity) as total_net_qty'),
                DB::raw('SUM(COALESCE(cont_operations.returned_quantity, 0)) as total_returned_qty'),
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

        // Sync live paid & remaining debt for materialInvoices from company_debts (to reflect settlements in /debts2)
        $minvDebtIds = $materialInvoices->pluck('debt_id')->filter()->unique()->values()->all();
        $minvDebtsById = !empty($minvDebtIds)
            ? DB::table('company_debts')->whereIn('id', $minvDebtIds)->get()->keyBy('id')
            : collect();
        $projectsById = $projects->keyBy('id');
        foreach ($materialInvoices as $minv) {
            $tCost = floatval($minv->total_cost);
            if (!empty($minv->debt_id)) {
                $liveDebt = isset($minvDebtsById[$minv->debt_id])
                    ? floatval($minvDebtsById[$minv->debt_id]->remaining_balance)
                    : 0.0;
                $livePaid = max(0, round($tCost - $liveDebt, 2));
                $minv->total_paid = $livePaid;
                $minv->total_debt = $liveDebt;
                $minv->payment_method = $liveDebt <= 0.01 ? 'cash' : ($livePaid > 0.01 ? 'partial' : 'later');
            } elseif (floatval($minv->total_net_qty ?? 0) <= 0 && $tCost <= 0.01) {
                $minv->total_paid = 0.0;
                $minv->total_debt = 0.0;
                $minv->payment_method = 'cash';
            }
            $mProj = $projectsById->get($minv->project_id);
            $mPm = $projectMetrics[$minv->project_id] ?? [];
            $minv->project_status = $mProj->status ?? 'active';
            $minv->is_installment_plan = !empty($mPm['is_installment_plan']);
        }

        $cancelledInvoices = DB::table('cont_cancelled_invoices')
            ->orderByDesc('id')
            ->get();

        // Material Returns Log & Statistics (سجل وإحصائيات المرتجعات)
        $materialReturns = DB::table('cont_material_returns')
            ->leftJoin('accounts', 'cont_material_returns.account_id', '=', 'accounts.id')
            ->select('cont_material_returns.*', 'accounts.account_name')
            ->orderByDesc('cont_material_returns.id')
            ->get();

        $returnsStats = [
            'total_records'          => $materialReturns->count(),
            'total_batches'          => $materialReturns->pluck('return_number')->filter()->unique()->count(),
            'total_qty_returned'     => round($materialReturns->sum('returned_qty'), 2),
            'total_cost_returned'    => round($materialReturns->sum('total_cost_returned'), 2),
            'total_selling_returned' => round($materialReturns->sum('total_selling_returned'), 2),
            'total_debt_deducted'    => round($materialReturns->sum('debt_deducted'), 2),
            'total_cash_refunded'    => round($materialReturns->sum('cash_refunded'), 2),
            'total_supplier_credit'  => round($materialReturns->sum('supplier_credit'), 2),
            'top_suppliers'          => $materialReturns->groupBy('supplier_name')->map(function ($rows, $name) {
                return [
                    'name'       => $name ?: 'غير محدد',
                    'count'      => $rows->count(),
                    'total_cost' => round($rows->sum('total_cost_returned'), 2),
                ];
            })->sortByDesc('total_cost')->take(5)->values(),
            'top_materials'          => $materialReturns->groupBy('material_name')->map(function ($rows, $name) {
                return [
                    'name'       => $name ?: 'خامة',
                    'qty'        => round($rows->sum('returned_qty'), 2),
                    'unit'       => $rows->first()->unit ?? '',
                    'total_cost' => round($rows->sum('total_cost_returned'), 2),
                ];
            })->sortByDesc('total_cost')->take(5)->values(),
            'top_projects'           => $materialReturns->groupBy('project_name')->map(function ($rows, $name) {
                return [
                    'name'          => $name ?: 'مشروع غير محدد',
                    'count'         => $rows->count(),
                    'total_cost'    => round($rows->sum('total_cost_returned'), 2),
                    'total_selling' => round($rows->sum('total_selling_returned'), 2),
                ];
            })->sortByDesc('total_cost')->take(5)->values(),
        ];

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
            ->reject(fn($n) => in_array(trim($n), ['نثريات موقع', 'مصروف موقع / إدارة', 'مصروف داخلي', 'نثريات المقاولات', 'مصروفات داخلية - المقاولات'], true))
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
            ->where('quantity', '>', 0)
            ->whereNotNull('name_or_title')
            ->where('name_or_title', '!=', '')
            ->orderBy('created_at', 'asc')
            ->get();

        $materialPricesSummary = $materialOps->groupBy(fn($r) => trim($r->name_or_title))->map(function($rows, $name) {
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
                'unit'            => trim((string)($lastRow->unit ?? '')),
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
            ->where('quantity', '>', 0)
            ->whereNotNull('name_or_title')
            ->where('name_or_title', '!=', '')
            ->whereNotNull('person_name')
            ->where('person_name', '!=', '')
            ->orderBy('created_at', 'desc')
            ->get();

        $supplierComparisonCards = $supplierOps->groupBy(fn($r) => trim($r->name_or_title))
            ->map(function($rows, $matName) {
                $unit = trim((string)($rows->first()->unit ?? ''));
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
        $repContInstallments = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->keyBy('source_id');
        $repInstPayQuery = DB::table('installment_payments')
            ->select('installment_id', DB::raw('SUM(amount_paid + COALESCE(discount_applied, 0)) as total_paid'));
        if ($filterPreset !== 'all') {
            $repInstPayQuery->whereBetween('payment_date', [$filterFrom->toDateString(), $filterTo->toDateString()]);
        }
        $repAllInstPayments = $repInstPayQuery
            ->groupBy('installment_id')
            ->pluck('total_paid', 'installment_id');

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

            $pInst = $repContInstallments->get($p->id);
            $instDownInPeriod = 0;
            if ($pInst && floatval($pInst->down_payment) > 0) {
                $instCreated = $pInst->created_at ? Carbon::parse($pInst->created_at) : null;
                if ($filterPreset === 'all' || !$instCreated || $instCreated->between($filterFrom, $filterTo)) {
                    $instDownInPeriod = floatval($pInst->down_payment);
                }
            }
            $pInstPaid = $pInst ? (floatval($repAllInstPayments[$pInst->id] ?? 0) + $instDownInPeriod) : 0;
            $pReceiptInvoices = $pInvoices->where('invoice_type', '!=', 'installment_contract');
            $pPositiveReceipts = $pReceiptInvoices->where('invoice_type', '!=', 'client_refund');
            $pUnabsorbedRefunds = $this->sumUnabsorbedClientRefunds($pReceiptInvoices);
            $pDirectPaid = floatval($pPositiveReceipts->whereNull('installment_id')->sum('paid_amount'));
            $pClientPaid = max(0.0, round(max(
                floatval($pReceiptInvoices->sum('paid_amount')),
                max(0.0, ($pInstPaid + $pDirectPaid) - $pUnabsorbedRefunds)
            ), 2));
            $pBal = $this->computeProjectBalanceMetrics($pNetSelling, 0.0, $pClientPaid);
            $pClientRemaining = $pBal['client_remaining'];
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
                'client_credit'      => $pBal['client_credit'],
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
        $opTypeLabelsAr = [
            'material'   => 'خامات',
            'technician' => 'مصنعية فني',
            'labor'      => 'مصنعية',
            'petty'      => 'نثريات للعميل',
            'overhead'   => 'مصروف داخلي',
            'expense'    => 'مصروف داخلي',
            'commission' => 'عمولة مسوق',
        ];
        $topCostOpRaw = $repOps->sortByDesc('total_cost')->first();
        $topCostOp = null;
        if ($topCostOpRaw && $topCostOpRaw->total_cost > 0) {
            $p = $projectsMap[$topCostOpRaw->project_id] ?? null;
            $topCostOp = [
                'title'        => $topCostOpRaw->name_or_title,
                'cost'         => floatval($topCostOpRaw->total_cost),
                'qty'          => floatval($topCostOpRaw->quantity),
                'unit'         => $topCostOpRaw->unit,
                'op_type'      => $opTypeLabelsAr[$topCostOpRaw->op_type] ?? $topCostOpRaw->op_type,
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

        // 7. Top technician (with live debt/paid calculation from company_debts)
        $repTechDebtIds = $repOps->whereIn('op_type', ['technician', 'labor'])->pluck('debt_id')->filter()->unique()->values()->all();
        $repTechDebtsById = !empty($repTechDebtIds)
            ? DB::table('company_debts')->whereIn('id', $repTechDebtIds)->get()->keyBy('id')
            : collect();
        $topTech = $repOps->whereIn('op_type', ['technician', 'labor'])->whereNotNull('person_name')->where('person_name', '!=', '')->groupBy(fn($r) => trim($r->person_name))->map(function($rows, $name) use ($repTechDebtsById) {
            $totCost = 0;
            $totPaid = 0;
            $totRem  = 0;
            foreach ($rows as $r) {
                $c = floatval($r->total_cost);
                $initPaid = floatval($r->paid_amount);
                $initDebt = floatval($r->remaining_debt);
                $subPaid = 0;
                if (!empty($r->debt_id) && $initDebt > 0 && isset($repTechDebtsById[$r->debt_id])) {
                    $d = $repTechDebtsById[$r->debt_id];
                    $dTotal = floatval($d->total_amount);
                    $ratio = $dTotal > 0 ? min(1, floatval($d->paid_amount) / $dTotal) : 0;
                    $subPaid = round($initDebt * $ratio, 2);
                }
                $lPaid = round($initPaid + $subPaid, 2);
                $lRem = max(0, round($c - $lPaid, 2));
                $totCost += $c;
                $totPaid += $lPaid;
                $totRem  += $lRem;
            }
            return [
                'name'           => $name ?: 'فني عام',
                'total_cost'     => $totCost,
                'paid_amount'    => $totPaid,
                'remaining_debt' => $totRem,
                'jobs_count'     => $rows->count(),
            ];
        })->sortByDesc('total_cost')->first();

        // 8. Top material
        $topMaterial = $repOps->where('op_type', 'material')->where('quantity', '>', 0)->whereNotNull('name_or_title')->where('name_or_title', '!=', '')->groupBy(fn($r) => trim($r->name_or_title))->map(function($rows, $name) {
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

        // 9.b Detailed Petty & General Expenses (تفصيل النثريات والمصروفات العامة والداخلية)
        $itemsTitleMap = $items->pluck('title', 'id');

        // A) نثريات محملة على العميل داخل المشاريع (op_type = 'petty')
        $repPettyOps = $repOps->where('op_type', 'petty')->sortByDesc('created_at')->values();
        $pettyOpsDetailed = $repPettyOps->map(function($o) use ($projectsMap, $itemsTitleMap) {
            $p = $projectsMap[$o->project_id] ?? null;
            return [
                'id'           => $o->id,
                'date'         => $o->created_at ? substr((string) $o->created_at, 0, 10) : '-',
                'title'        => trim($o->name_or_title ?: 'نثريات موقع'),
                'project_id'   => $o->project_id,
                'project_name' => $p ? $p->name : 'مشروع غير محدد',
                'client_name'  => $p ? $p->client_name : '-',
                'item_title'   => $o->item_id ? ($itemsTitleMap[$o->item_id] ?? 'عام') : 'عام',
                'cost'         => floatval($o->total_cost),
                'selling'      => floatval($o->total_selling),
                'profit'       => floatval($o->total_selling) - floatval($o->total_cost),
                'notes'        => $o->notes ?: '',
                'type_key'     => 'petty_client',
                'type_label'   => 'نثريات على العميل',
            ];
        })->values();

        $pettyByTitle = $pettyOpsDetailed->groupBy('title')->map(function($rows, $title) {
            return [
                'title'      => $title,
                'count'      => $rows->count(),
                'total_cost' => round($rows->sum('cost'), 2),
                'total_sell' => round($rows->sum('selling'), 2),
                'profit'     => round($rows->sum('profit'), 2),
                'projects'   => $rows->pluck('project_name')->filter()->unique()->values()->implode('، '),
            ];
        })->sortByDesc('total_cost')->values();

        // B) مصروفات داخلية للمشاريع على حساب الشركة (op_type IN ('overhead', 'expense'))
        $repOverheadOps = $repOps->whereIn('op_type', ['overhead', 'expense'])->sortByDesc('created_at')->values();
        $overheadOpsDetailed = $repOverheadOps->map(function($o) use ($projectsMap, $itemsTitleMap) {
            $p = $projectsMap[$o->project_id] ?? null;
            return [
                'id'           => $o->id,
                'date'         => $o->created_at ? substr((string) $o->created_at, 0, 10) : '-',
                'title'        => trim($o->name_or_title ?: 'مصروف داخلي'),
                'project_id'   => $o->project_id,
                'project_name' => $p ? $p->name : 'مشروع غير محدد',
                'client_name'  => $p ? $p->client_name : '-',
                'item_title'   => $o->item_id ? ($itemsTitleMap[$o->item_id] ?? 'عام') : 'عام',
                'cost'         => floatval($o->total_cost),
                'selling'      => 0,
                'profit'       => -floatval($o->total_cost),
                'notes'        => $o->notes ?: '',
                'type_key'     => 'overhead_project',
                'type_label'   => 'مصروف داخلي للمشروع',
            ];
        })->values();

        $overheadByTitle = $overheadOpsDetailed->groupBy('title')->map(function($rows, $title) {
            return [
                'title'      => $title,
                'count'      => $rows->count(),
                'total_cost' => round($rows->sum('cost'), 2),
                'projects'   => $rows->pluck('project_name')->filter()->unique()->values()->implode('، '),
            ];
        })->sortByDesc('total_cost')->values();

        // C) نثريات ومصروفات عامة سريعة من جدول cont_petty_expenses (خارج المشاريع أو عامة)
        $repGeneralPettyRows = DB::table('cont_petty_expenses')
            ->leftJoin('cont_petty_categories', 'cont_petty_expenses.category_id', '=', 'cont_petty_categories.id')
            ->leftJoin('accounts', 'cont_petty_expenses.account_id', '=', 'accounts.id')
            ->whereBetween('cont_petty_expenses.created_at', [$filterFrom, $filterTo])
            ->select(
                'cont_petty_expenses.*',
                'cont_petty_categories.name as category_name',
                'accounts.account_name'
            )
            ->orderBy('cont_petty_expenses.created_at', 'desc')
            ->get();

        $generalPettyDetailed = $repGeneralPettyRows->map(function($r) {
            return [
                'id'           => $r->id,
                'date'         => $r->created_at ? substr((string) $r->created_at, 0, 10) : '-',
                'title'        => trim($r->title ?: 'نثريات عامة'),
                'project_id'   => null,
                'project_name' => $r->category_name ?: 'مصروف عام (الإدارة)',
                'client_name'  => $r->account_name ?: 'بدون خزنة',
                'item_title'   => $r->category_name ?: 'نثريات عامة',
                'cost'         => floatval($r->amount),
                'selling'      => 0,
                'profit'       => -floatval($r->amount),
                'notes'        => $r->account_name ? ('تم الصرف من: ' . $r->account_name) : '',
                'type_key'     => 'general_petty',
                'type_label'   => 'نثريات ومصروفات عامة',
            ];
        })->values();

        $generalPettyByTitle = $generalPettyDetailed->groupBy('title')->map(function($rows, $title) {
            return [
                'title'      => $title,
                'category'   => $rows->first()['item_title'] ?? 'عام',
                'count'      => $rows->count(),
                'total_cost' => round($rows->sum('cost'), 2),
                'accounts'   => $rows->pluck('client_name')->filter()->unique()->values()->implode('، '),
            ];
        })->sortByDesc('total_cost')->values();

        $generalPettyTotal = floatval($generalPettyDetailed->sum('cost'));
        $allExpensesAndPettyLog = $pettyOpsDetailed
            ->concat($overheadOpsDetailed)
            ->concat($generalPettyDetailed)
            ->sortByDesc('date')
            ->values();

        // 9.c إحصائيات شاملة لكل قسم في المقاولات خلال الفترة (عشان المستخدم يفهم كل معلومة في المقاولات)
        $repReturns = Schema::hasTable('cont_material_returns')
            ? DB::table('cont_material_returns')->whereBetween('created_at', [$filterFrom, $filterTo])->get()
            : collect();

        $repMatOps = $repOps->where('op_type', 'material');
        $repTechOps = $repOps->whereIn('op_type', ['technician', 'labor']);
        $repCommOps = $repOps->where('op_type', 'commission');
        $repGrossSelling = collect($reportProjectMetrics)->sum('billable_selling');
        $repTotalDiscounts = collect($reportProjectMetrics)->sum('discounts');

        // حساب ديون الموردين والفنيين كل على حدة
        $supplierNamesSet = $suppliers->pluck('name')->map(fn($n) => trim($n))->filter()->unique()->values()->all();
        $techNamesSet = $technicians->pluck('name')->map(fn($n) => trim($n))->filter()->unique()->values()->all();
        $liveSupplierDebtsOnly = !empty($supplierNamesSet)
            ? floatval(DB::table('company_debts')->whereIn('creditor_name', $supplierNamesSet)->sum('remaining_balance'))
            : 0.0;
        $liveTechDebtsOnly = !empty($techNamesSet)
            ? floatval(DB::table('company_debts')->whereIn('creditor_name', $techNamesSet)->sum('remaining_balance'))
            : 0.0;

        // 10. Expenses breakdown
        $expensesBreakdown = [
            'material'      => floatval($repMatOps->sum('total_cost')),
            'technician'    => floatval($repTechOps->sum('total_cost')),
            'petty'         => floatval($repPettyOps->sum('total_cost')),
            'expense'       => floatval($repOverheadOps->sum('total_cost')),
            'general_petty' => $generalPettyTotal,
            'commission'    => floatval($repCommOps->sum('total_cost')),
            'total'         => floatval($repOps->sum('total_cost')) + $generalPettyTotal,
        ];

        $contractingOverview = [
            // 1. المشاريع والعملاء
            'projects_total_count'  => $projects->count(),
            'projects_active_count' => $projects->where('status', 'active')->count(),
            'gross_selling'         => $repGrossSelling,
            'total_discounts'       => $repTotalDiscounts,
            'net_selling'           => $grandSelling,
            'client_paid'           => $grandPaid,
            'client_remaining'      => $grandRemaining,
            // 2. الخامات والموردين والمرتجعات
            'materials_ops_count'   => $repMatOps->where('quantity', '>', 0)->count(),
            'materials_invoices_cnt'=> $repMatOps->where('quantity', '>', 0)->pluck('invoice_ref')->filter()->unique()->count(),
            'materials_cost'        => floatval($repMatOps->sum('total_cost')),
            'materials_selling'     => floatval($repMatOps->sum('total_selling')),
            'materials_profit'      => floatval($repMatOps->sum('total_selling')) - floatval($repMatOps->sum('total_cost')),
            'suppliers_debt_now'    => $liveSupplierDebtsOnly,
            'suppliers_credit_now'  => floatval($suppliers->sum(fn($s) => floatval($s->balance_credit ?? 0))),
            'returns_count'         => $repReturns->count(),
            'returns_cost_total'    => floatval($repReturns->sum('return_cost_total')),
            'returns_sell_total'    => floatval($repReturns->sum('return_selling_total')),
            // 3. الفنيين والمصنعيات
            'tech_ops_count'        => $repTechOps->count(),
            'tech_cost'             => floatval($repTechOps->sum('total_cost')),
            'tech_selling'          => floatval($repTechOps->sum('total_selling')),
            'tech_profit'           => floatval($repTechOps->sum('total_selling')) - floatval($repTechOps->sum('total_cost')),
            'tech_debt_now'         => $liveTechDebtsOnly,
            // 4. النثريات والمصروفات والعمولات
            'petty_count'           => $pettyOpsDetailed->count(),
            'petty_cost'            => floatval($repPettyOps->sum('total_cost')),
            'petty_selling'         => floatval($repPettyOps->sum('total_selling')),
            'petty_profit'          => floatval($repPettyOps->sum('total_selling')) - floatval($repPettyOps->sum('total_cost')),
            'overhead_count'        => $overheadOpsDetailed->count(),
            'overhead_cost'         => floatval($repOverheadOps->sum('total_cost')),
            'general_petty_count'   => $generalPettyDetailed->count(),
            'general_petty_cost'    => $generalPettyTotal,
            'commission_count'      => $repCommOps->count(),
            'commission_cost'       => floatval($repCommOps->sum('total_cost')),
            // 5. الصافي النهائي بعد النثريات العامة
            'net_profit_projects'   => $grandProfit,
            'net_profit_after_gen'  => $grandProfit - $generalPettyTotal,
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
            'overview'                 => $contractingOverview,
            'petty_ops_list'           => $pettyOpsDetailed,
            'petty_by_title'           => $pettyByTitle,
            'overhead_ops_list'        => $overheadOpsDetailed,
            'overhead_by_title'        => $overheadByTitle,
            'general_petty_list'       => $generalPettyDetailed,
            'general_petty_by_title'   => $generalPettyByTitle,
            'all_expenses_petty_log'   => $allExpensesAndPettyLog,
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

        // ═══ بيانات تبويب الفنيين والمصنعيات ═══
        $laborOpsAll = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id')
            ->whereIn('cont_operations.op_type', ['technician', 'labor'])
            ->whereNotNull('cont_operations.person_name')
            ->where('cont_operations.person_name', '!=', '')
            ->select('cont_operations.*', 'cont_projects.name as project_name', 'cont_project_items.title as item_title')
            ->orderBy('cont_operations.created_at', 'desc')
            ->orderBy('cont_operations.id', 'desc')
            ->get();

        $allLinkedOpDebtIds = DB::table('cont_operations')->whereNotNull('debt_id')->pluck('debt_id')->unique()->flip()->all();
        $laborDebtIds = $laborOpsAll->pluck('debt_id')->filter()->unique()->values()->all();
        $laborDebtsById = !empty($laborDebtIds)
            ? DB::table('company_debts')->whereIn('id', $laborDebtIds)->get()->keyBy('id')
            : collect();
        $allTechNames = $technicians->pluck('name')->merge($laborOpsAll->pluck('person_name'))->map(fn($n) => trim($n))->filter()->unique()->values()->all();
        $laborStandaloneDebts = !empty($allTechNames)
            ? DB::table('company_debts')
                ->whereIn('creditor_name', $allTechNames)
                ->where(function ($q) {
                    $q->where('source_type', 'cont_operation')
                      ->orWhere('category', 'مقاولات');
                })
                ->where(function ($q) {
                    $q->whereNull('source_type')
                      ->orWhere('source_type', '!=', 'cont_operation_invoice');
                })
                ->get()
                ->filter(fn($d) => !isset($laborDebtsById[$d->id]) && !isset($allLinkedOpDebtIds[$d->id]))
                ->groupBy(fn($d) => trim($d->creditor_name))
            : collect();

        $techWorkerPaymentsByName = Schema::hasTable('cont_worker_payments')
            ? DB::table('cont_worker_payments')
                ->leftJoin('cont_projects', 'cont_worker_payments.project_id', '=', 'cont_projects.id')
                ->leftJoin('cont_project_items', 'cont_worker_payments.item_id', '=', 'cont_project_items.id')
                ->where('cont_worker_payments.person_type', 'technician')
                ->select(
                    'cont_worker_payments.*',
                    'cont_projects.name as project_name',
                    'cont_project_items.title as item_title'
                )
                ->orderBy('cont_worker_payments.payment_date', 'desc')
                ->orderBy('cont_worker_payments.id', 'desc')
                ->get()
                ->groupBy(fn($r) => trim($r->person_name))
            : collect();

        $allTechDebtIds = array_values(array_unique(array_merge(
            $laborDebtIds,
            $laborStandaloneDebts->flatten(1)->pluck('id')->all()
        )));

        $techSubsequentDebtTxsByName = $this->fetchSubsequentDebtTransactions($allTechDebtIds, 'desc')
            ->groupBy(fn($r) => trim((string) $r->person_name));

        $laborByName = $laborOpsAll->groupBy(fn($o) => trim($o->person_name));
        $techLaborRows = [];
        foreach ($technicians as $tech) {
            $techName = trim($tech->name);
            $rows = $laborByName->get($techName, collect());
            $projectsAgg = [];
            $opsList = [];
            $total = 0; $paid = 0; $remaining = 0;
            foreach ($rows as $op) {
                $opCost = floatval($op->total_cost);
                $initialPaid = floatval($op->paid_amount);
                $initialDebt = floatval($op->remaining_debt);
                $subsequentPaid = 0;
                if (!empty($op->debt_id) && $initialDebt > 0 && isset($laborDebtsById[$op->debt_id])) {
                    $d = $laborDebtsById[$op->debt_id];
                    $dTotal = floatval($d->total_amount);
                    $ratio = $dTotal > 0 ? min(1, floatval($d->paid_amount) / $dTotal) : 0;
                    $subsequentPaid = round($initialDebt * $ratio, 2);
                }
                $livePaid = round($initialPaid + $subsequentPaid, 2);
                $liveRem = max(0, round($opCost - $livePaid, 2));
                $total += $opCost; $paid += $livePaid; $remaining += $liveRem;

                $pk = $op->project_id ?: 0;
                if (!isset($projectsAgg[$pk])) {
                    $projectsAgg[$pk] = ['name' => $op->project_name ?: 'بدون مشروع', 'ops' => 0, 'total' => 0, 'paid' => 0, 'remaining' => 0];
                }
                $projectsAgg[$pk]['ops']++;
                $projectsAgg[$pk]['total'] += $opCost;
                $projectsAgg[$pk]['paid'] += $livePaid;
                $projectsAgg[$pk]['remaining'] += $liveRem;

                $opsList[] = [
                    'date' => $op->created_at ? date('Y-m-d', strtotime($op->created_at)) : '-',
                    'project' => $op->project_name ?: '-',
                    'item' => $op->item_title,
                    'title' => $op->name_or_title,
                    'qty' => floatval($op->quantity),
                    'unit' => $op->unit,
                    'price' => floatval($op->cost_price),
                    'total' => $opCost,
                    'paid' => $livePaid,
                    'remaining' => $liveRem,
                    'notes' => $op->notes,
                ];
            }
            $otherRem = 0;
            foreach ($laborStandaloneDebts->get($techName, collect()) as $sd) {
                $total += floatval($sd->total_amount);
                $paid += floatval($sd->paid_amount);
                $otherRem += floatval($sd->remaining_balance);
            }
            $remaining += $otherRem;

            $paymentsList = [];
            foreach ($techWorkerPaymentsByName->get($techName, collect()) as $wp) {
                $paymentsList[] = [
                    'date'            => $wp->payment_date ?: ($wp->created_at ? date('Y-m-d', strtotime($wp->created_at)) : '-'),
                    'project'         => $wp->project_name ?: '-',
                    'item'            => $wp->item_title ?: '-',
                    'amount'          => floatval($wp->amount),
                    'discount'        => floatval($wp->discount ?? 0),
                    'discount_reason' => $wp->discount_reason ?? null,
                    'notes'           => $wp->notes ?? null,
                ];
            }
            $techSubGrouped = $techSubsequentDebtTxsByName->get($techName, collect())->groupBy(function ($tx) {
                return ($tx->created_at ?? '') . '_' . ($tx->ref_type ?? '');
            });
            foreach ($techSubGrouped as $txRows) {
                $dTx = $txRows->first();
                $isDisc = in_array($dTx->ref_type, ['company_debt_payment_discount', 'earned_discount'], true);
                $sumAmt = round($txRows->sum('amount'), 2);
                $paymentsList[] = [
                    'date'            => $dTx->created_at ? date('Y-m-d', strtotime($dTx->created_at)) : '-',
                    'project'         => 'سداد مديونية',
                    'item'            => '-',
                    'amount'          => $isDisc ? 0.0 : $sumAmt,
                    'discount'        => $isDisc ? $sumAmt : 0.0,
                    'discount_reason' => $isDisc ? ($dTx->notes ?: 'خصم سداد مديونية') : null,
                    'notes'           => $dTx->notes ?? null,
                ];
            }
            usort($paymentsList, fn($a, $b) => strcmp((string) $b['date'], (string) $a['date']));

            $lastOpDate = $opsList[0]['date'] ?? null;
            $lastPayDate = $paymentsList[0]['date'] ?? null;
            $lastDate = ($lastPayDate && (!$lastOpDate || $lastPayDate > $lastOpDate)) ? $lastPayDate : $lastOpDate;

            $techLaborRows[] = [
                'id' => $tech->id,
                'name' => $techName,
                'phone' => $tech->phone ?? null,
                'specialty' => $tech->specialty ?? null,
                'credit' => floatval($tech->balance_credit ?? 0),
                'ops_count' => count($opsList),
                'payments_count' => count($paymentsList),
                'projects' => array_values($projectsAgg),
                'total' => $total,
                'paid' => $paid,
                'remaining' => $remaining,
                'other_remaining' => $otherRem,
                'last_date' => $lastDate,
                'ops' => $opsList,
                'payments' => $paymentsList,
            ];
        }
        usort($techLaborRows, fn($a, $b) => $b['remaining'] <=> $a['remaining']);

        $laborProjectsAgg = [];
        foreach ($techLaborRows as $tr) {
            foreach ($tr['projects'] as $pr) {
                $k = $pr['name'];
                if (!isset($laborProjectsAgg[$k])) {
                    $laborProjectsAgg[$k] = ['name' => $k, 'techs' => 0, 'ops' => 0, 'total' => 0, 'paid' => 0, 'remaining' => 0];
                }
                $laborProjectsAgg[$k]['techs']++;
                $laborProjectsAgg[$k]['ops'] += $pr['ops'];
                $laborProjectsAgg[$k]['total'] += $pr['total'];
                $laborProjectsAgg[$k]['paid'] += $pr['paid'];
                $laborProjectsAgg[$k]['remaining'] += $pr['remaining'];
            }
        }
        $laborProjectsAgg = collect($laborProjectsAgg)->sortByDesc('remaining')->values()->all();
        $techLaborSummary = [
            'techs_count' => count($techLaborRows),
            'techs_owed_count' => count(array_filter($techLaborRows, fn($r) => $r['remaining'] > 0.01)),
            'total' => array_sum(array_column($techLaborRows, 'total')),
            'paid' => array_sum(array_column($techLaborRows, 'paid')),
            'remaining' => array_sum(array_column($techLaborRows, 'remaining')),
            'credit' => array_sum(array_column($techLaborRows, 'credit')),
            'ops_count' => array_sum(array_column($techLaborRows, 'ops_count')),
            'payments_count' => array_sum(array_column($techLaborRows, 'payments_count')),
        ];
        // ═══ بيانات تبويب حسابات الموردين ═══
        $supOpsAll = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->where('cont_operations.op_type', 'material')
            ->whereNotNull('cont_operations.person_name')
            ->where('cont_operations.person_name', '!=', '')
            ->select('cont_operations.*', 'cont_projects.name as project_name')
            ->orderBy('cont_operations.created_at', 'desc')
            ->orderBy('cont_operations.id', 'desc')
            ->get();

        $supDebtIds = $supOpsAll->pluck('debt_id')->filter()->unique()->values()->all();
        $supDebtsById = !empty($supDebtIds)
            ? DB::table('company_debts')->whereIn('id', $supDebtIds)->get()->keyBy('id')
            : collect();
        $allSupNames = $suppliers->pluck('name')->merge($supOpsAll->pluck('person_name'))->map(fn($n) => trim($n))->filter()->unique()->values()->all();
        $supStandaloneDebts = !empty($allSupNames)
            ? DB::table('company_debts')
                ->whereIn('creditor_name', $allSupNames)
                ->where(function ($q) {
                    $q->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
                      ->orWhere('category', 'مقاولات');
                })
                ->get()
                ->filter(fn($d) => !isset($supDebtsById[$d->id]) && !isset($allLinkedOpDebtIds[$d->id]))
                ->groupBy(fn($d) => trim($d->creditor_name))
            : collect();

        $supWorkerPaymentsByName = Schema::hasTable('cont_worker_payments')
            ? DB::table('cont_worker_payments')
                ->leftJoin('cont_projects', 'cont_worker_payments.project_id', '=', 'cont_projects.id')
                ->leftJoin('cont_project_items', 'cont_worker_payments.item_id', '=', 'cont_project_items.id')
                ->leftJoin('cont_operations', 'cont_worker_payments.operation_id', '=', 'cont_operations.id')
                ->where('cont_worker_payments.person_type', 'supplier')
                ->select(
                    'cont_worker_payments.*',
                    'cont_projects.name as project_name',
                    'cont_project_items.title as item_title',
                    'cont_operations.invoice_ref as op_invoice_ref'
                )
                ->orderBy('cont_worker_payments.payment_date', 'desc')
                ->orderBy('cont_worker_payments.id', 'desc')
                ->get()
                ->groupBy(fn($r) => trim($r->person_name))
            : collect();

        $allSupDebtIds = array_values(array_unique(array_merge(
            $supDebtIds,
            $supStandaloneDebts->flatten(1)->pluck('id')->all()
        )));

        $supSubsequentDebtTxsByName = $this->fetchSubsequentDebtTransactions($allSupDebtIds, 'desc')
            ->groupBy(fn($r) => trim((string) $r->person_name));

        $supReturnsByName = $materialReturns->groupBy(fn($r) => trim((string) ($r->supplier_name ?? '')));
        $supByName = $supOpsAll->groupBy(fn($o) => trim($o->person_name));
        $supplierRows = [];
        foreach ($suppliers as $sup) {
            $supName = trim($sup->name);
            $rows = $supByName->get($supName, collect());
            $projectsAgg = [];
            $invoiceRefs = [];
            $opsList = [];
            $total = 0; $paid = 0; $remaining = 0;
            foreach ($rows as $op) {
                $opCost = floatval($op->total_cost);
                $initialPaid = floatval($op->paid_amount);
                $initialDebt = floatval($op->remaining_debt);
                $subsequentPaid = 0;
                if (!empty($op->debt_id) && $initialDebt > 0 && isset($supDebtsById[$op->debt_id])) {
                    $d = $supDebtsById[$op->debt_id];
                    $dTotal = floatval($d->total_amount);
                    $ratio = $dTotal > 0 ? min(1, floatval($d->paid_amount) / $dTotal) : 0;
                    $subsequentPaid = round($initialDebt * $ratio, 2);
                }
                $livePaid = round($initialPaid + $subsequentPaid, 2);
                $liveRem = max(0, round($opCost - $livePaid, 2));
                $total += $opCost; $paid += $livePaid; $remaining += $liveRem;

                if (!empty($op->invoice_ref)) { $invoiceRefs[$op->invoice_ref] = true; }
                $pk = $op->project_id ?: 0;
                if (!isset($projectsAgg[$pk])) {
                    $projectsAgg[$pk] = ['name' => $op->project_name ?: 'بدون مشروع', 'ops' => 0, 'total' => 0, 'paid' => 0, 'remaining' => 0];
                }
                $projectsAgg[$pk]['ops']++;
                $projectsAgg[$pk]['total'] += $opCost;
                $projectsAgg[$pk]['paid'] += $livePaid;
                $projectsAgg[$pk]['remaining'] += $liveRem;

                $opsList[] = [
                    'date' => $op->created_at ? date('Y-m-d', strtotime($op->created_at)) : '-',
                    'project' => $op->project_name ?: '-',
                    'invoice' => $op->invoice_ref,
                    'title' => $op->name_or_title,
                    'qty' => floatval($op->quantity),
                    'returned' => floatval($op->returned_quantity ?? 0),
                    'unit' => $op->unit,
                    'price' => floatval($op->cost_price),
                    'total' => $opCost,
                    'paid' => $livePaid,
                    'remaining' => $liveRem,
                ];
            }
            $otherRem = 0;
            foreach ($supStandaloneDebts->get($supName, collect()) as $sd) {
                $total += floatval($sd->total_amount);
                $paid += floatval($sd->paid_amount);
                $otherRem += floatval($sd->remaining_balance);
            }
            $remaining += $otherRem;

            $supPaymentsList = [];
            $supWps = $supWorkerPaymentsByName->get($supName, collect());
            foreach ($supWps as $wp) {
                $supPaymentsList[] = [
                    'date'            => $wp->payment_date ?: ($wp->created_at ? date('Y-m-d', strtotime($wp->created_at)) : '-'),
                    'project'         => $wp->project_name ?: '-',
                    'invoice'         => $wp->op_invoice_ref ?? null,
                    'amount'          => floatval($wp->amount),
                    'discount'        => floatval($wp->discount ?? 0),
                    'discount_reason' => $wp->discount_reason ?? null,
                    'notes'           => $wp->notes ?? null,
                ];
            }
            $coveredSupOpIds = $supWps->pluck('operation_id')->filter()->unique()->flip()->all();
            $coveredSupCashRefs = $supWps
                ->filter(fn($wp) => empty($wp->debt_id) && !empty($wp->op_invoice_ref))
                ->pluck('op_invoice_ref')
                ->unique()
                ->flip()
                ->all();
            $uncoveredSupPaidOps = $rows->filter(function ($op) use ($coveredSupOpIds, $coveredSupCashRefs) {
                if (floatval($op->paid_amount) <= 0) {
                    return false;
                }
                if (isset($coveredSupOpIds[$op->id])) {
                    return false;
                }
                if (empty($op->debt_id) && !empty($op->invoice_ref) && isset($coveredSupCashRefs[$op->invoice_ref])) {
                    return false;
                }
                return true;
            });
            $uncoveredSupGroups = $uncoveredSupPaidOps->groupBy(function ($op) {
                return !empty($op->invoice_ref)
                    ? ('INV_' . ($op->project_id ?: 0) . '_' . $op->invoice_ref)
                    : ('OP_' . $op->id);
            });
            foreach ($uncoveredSupGroups as $grpRows) {
                $firstGrp = $grpRows->first();
                $supPaymentsList[] = [
                    'date'            => $firstGrp->created_at ? date('Y-m-d', strtotime($firstGrp->created_at)) : '-',
                    'project'         => $firstGrp->project_name ?: '-',
                    'invoice'         => $firstGrp->invoice_ref ?? null,
                    'amount'          => round($grpRows->sum('paid_amount'), 2),
                    'discount'        => 0.0,
                    'discount_reason' => null,
                    'notes'           => !empty($firstGrp->invoice_ref) ? ("سداد فاتورة #" . $firstGrp->invoice_ref) : ($firstGrp->name_or_title ?? null),
                ];
            }
            $supSubGrouped = $supSubsequentDebtTxsByName->get($supName, collect())->groupBy(function ($tx) {
                return ($tx->created_at ?? '') . '_' . ($tx->ref_type ?? '');
            });
            foreach ($supSubGrouped as $txRows) {
                $dTx = $txRows->first();
                $isDisc = in_array($dTx->ref_type, ['company_debt_payment_discount', 'earned_discount'], true);
                $sumAmt = round($txRows->sum('amount'), 2);
                $supPaymentsList[] = [
                    'date'            => $dTx->created_at ? date('Y-m-d', strtotime($dTx->created_at)) : '-',
                    'project'         => 'سداد مديونية',
                    'invoice'         => null,
                    'amount'          => $isDisc ? 0.0 : $sumAmt,
                    'discount'        => $isDisc ? $sumAmt : 0.0,
                    'discount_reason' => $isDisc ? ($dTx->notes ?: 'خصم سداد مديونية') : null,
                    'notes'           => $dTx->notes ?? null,
                ];
            }
            usort($supPaymentsList, fn($a, $b) => strcmp((string) $b['date'], (string) $a['date']));

            $lastSupOpDate = $opsList[0]['date'] ?? null;
            $lastSupPayDate = $supPaymentsList[0]['date'] ?? null;
            $lastSupDate = ($lastSupPayDate && (!$lastSupOpDate || $lastSupPayDate > $lastSupOpDate)) ? $lastSupPayDate : $lastSupOpDate;

            $supRets = $supReturnsByName->get($supName, collect());
            $supReturnsList = [];
            foreach ($supRets as $srRet) {
                $supReturnsList[] = [
                    'date'              => $srRet->created_at ? date('Y-m-d', strtotime($srRet->created_at)) : '-',
                    'return_number'     => $srRet->return_number ?: '-',
                    'project'           => $srRet->project_name ?: '-',
                    'invoice'           => $srRet->invoice_ref ?: null,
                    'material_name'     => $srRet->material_name ?: '-',
                    'returned_qty'      => floatval($srRet->returned_qty ?? 0),
                    'unit'              => $srRet->unit ?: '',
                    'total_cost'        => floatval($srRet->total_cost_returned ?? 0),
                    'settlement_method' => $srRet->settlement_method ?: 'debt_deduction',
                    'cash_refunded'     => floatval($srRet->cash_refunded ?? 0),
                    'debt_deducted'     => floatval($srRet->debt_deducted ?? 0),
                    'supplier_credit'   => floatval($srRet->supplier_credit ?? 0),
                    'reason'            => $srRet->reason ?: null,
                ];
            }
            $supCashRefundedTotal = round((float) $supRets->sum('cash_refunded'), 2);
            $supReturnedCostTotal = round((float) $supRets->sum('total_cost_returned'), 2);

            $supplierRows[] = [
                'id' => $sup->id,
                'name' => $supName,
                'phone' => $sup->phone ?? null,
                'specialty' => $sup->specialty ?? null,
                'credit' => floatval($sup->balance_credit ?? 0),
                'invoices_count' => count($invoiceRefs),
                'ops_count' => count($opsList),
                'payments_count' => count($supPaymentsList),
                'returns_count' => count($supReturnsList),
                'cash_refunded' => $supCashRefundedTotal,
                'total_returned_cost' => $supReturnedCostTotal,
                'projects' => array_values($projectsAgg),
                'total' => $total,
                'paid' => $paid,
                'remaining' => $remaining,
                'other_remaining' => $otherRem,
                'last_date' => $lastSupDate,
                'ops' => $opsList,
                'payments' => $supPaymentsList,
                'returns' => $supReturnsList,
            ];
        }
        usort($supplierRows, fn($a, $b) => $b['remaining'] <=> $a['remaining']);

        $supProjectsAgg = [];
        foreach ($supplierRows as $sr) {
            foreach ($sr['projects'] as $pr) {
                $k = $pr['name'];
                if (!isset($supProjectsAgg[$k])) {
                    $supProjectsAgg[$k] = ['name' => $k, 'sups' => 0, 'ops' => 0, 'total' => 0, 'paid' => 0, 'remaining' => 0];
                }
                $supProjectsAgg[$k]['sups']++;
                $supProjectsAgg[$k]['ops'] += $pr['ops'];
                $supProjectsAgg[$k]['total'] += $pr['total'];
                $supProjectsAgg[$k]['paid'] += $pr['paid'];
                $supProjectsAgg[$k]['remaining'] += $pr['remaining'];
            }
        }
        $supProjectsAgg = collect($supProjectsAgg)->sortByDesc('remaining')->values()->all();
        $supplierSummary = [
            'count' => count($supplierRows),
            'owed_count' => count(array_filter($supplierRows, fn($r) => $r['remaining'] > 0.01)),
            'total' => array_sum(array_column($supplierRows, 'total')),
            'paid' => array_sum(array_column($supplierRows, 'paid')),
            'remaining' => array_sum(array_column($supplierRows, 'remaining')),
            'credit' => array_sum(array_column($supplierRows, 'credit')),
            'invoices_count' => array_sum(array_column($supplierRows, 'invoices_count')),
            'payments_count' => array_sum(array_column($supplierRows, 'payments_count')),
        ];
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
            'materialReturns',
            'returnsStats',
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
            'filterPreset',
            'techLaborRows',
            'laborProjectsAgg',
            'techLaborSummary',
            'supplierRows',
            'supProjectsAgg',
            'supplierSummary'
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
            'status'               => $request->input('status', 'active'),
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
                $cashPaidFromTreasury = floatval($request->input('paid_amount', 0));
                $remainingDebt = floatval($request->input('remaining_debt', 0));
                $accountId = $request->input('account_id');
                $defaultItemId = $request->input('item_id');

                // Auto-deduct from supplier credit balance if any exists!
                $autoDeductedCredit = 0.0;
                if ($remainingDebt > 0 && !empty($supplierName) && function_exists('consumeSupplierCredit')) {
                    $autoDeductedCredit = consumeSupplierCredit(
                        $supplierName,
                        $remainingDebt,
                        "خصم تلقائي لسداد فاتورة خامات ({$invoiceRef}) - مشروع: {$project->name}",
                        ['invoice_ref' => $invoiceRef, 'project_id' => $project->id]
                    );
                }
                $paidAmount = round($cashPaidFromTreasury + $autoDeductedCredit, 2);
                $remainingDebt = max(0, round($remainingDebt - $autoDeductedCredit, 2));

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
                    if ($autoDeductedCredit > 0) {
                        $reason .= " (بعد خصم {$autoDeductedCredit} ج من رصيدنا الدائن)";
                    }
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
                if ($cashPaidFromTreasury > 0) {
                    if (empty($accountId)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'طالما يوجد سداد نقدي للمورد، يجب اختيار الخزينة أو الحساب البنكي للسحب منه!'
                        ], 422);
                    }
                    $acc = DB::table('accounts')->where('id', $accountId)->first();
                    if ($acc) {
                        DB::table('accounts')->where('id', $accountId)->decrement('balance', $cashPaidFromTreasury);
                        DB::table('financial_transactions')->insert([
                            'type'            => 'expense',
                            'subtype'         => 'supplier_payment',
                            'amount'          => $cashPaidFromTreasury,
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
                        'project_id'        => $project->id,
                        'item_id'           => $lineItemId,
                        'op_type'           => 'material',
                        'is_billable'       => 1,
                        'name_or_title'     => $item['name_or_title'] ?? 'خامة بدون اسم',
                        'quantity'          => $qty,
                        'original_quantity' => $qty,
                        'returned_quantity' => 0,
                        'unit'              => trim((string)($item['unit'] ?? '')),
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
                        'notes'           => !empty($item['notes']) ? $item['notes'] : ($request->input('notes') ?: null),
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

                $this->syncProjectCustomerReceivable($project->id, true);

                DB::commit();

                $this->logActivity('create', 'contracting', "تم تسجيل فاتورة خامات ({$invoiceRef}) للمورد {$supplierName} تحتوي على {$savedCount} خامة بمشروع: {$project->name}", [
                    'project_id'  => $project->id,
                    'invoice_ref' => $invoiceRef,
                    'supplier'    => $supplierName,
                    'count'       => $savedCount,
                ]);

                $msgInvoice = "تم حفظ فاتورة الخامات بنجاح بإجمالي ({$savedCount}) خامة وترحيل حساب المورد.";
                if ($autoDeductedCredit > 0) {
                    $msgInvoice .= " 🔔 تم خصم (" . number_format($autoDeductedCredit, 2) . " ج) تلقائياً من رصيدنا الدائن لدى ({$supplierName}).";
                }

                return response()->json([
                    'success'       => true,
                    'message'       => $msgInvoice,
                    'auto_deducted' => $autoDeductedCredit,
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
                                'initial_debt'  => 0,
                                'auto_deducted' => 0,
                                'total_debt'    => 0,
                                'debt_id'       => null,
                            ];
                        }
                        $groupedMaterialDebts[$groupKey]['initial_debt'] += $remDebt;
                        $groupedMaterialDebts[$groupKey]['total_debt'] += $remDebt;
                    }
                }
            }

            foreach ($groupedMaterialDebts as $gKey => &$gData) {
                $autoDeductedGroup = 0.0;
                if ($gData['total_debt'] > 0 && function_exists('consumeSupplierCredit')) {
                    $autoDeductedGroup = consumeSupplierCredit(
                        $gData['creditor_name'],
                        $gData['total_debt'],
                        "خصم تلقائي لسداد فاتورة خامات ({$gData['invoice_ref']}) - مشروع: {$project->name}",
                        ['project_id' => $project->id, 'invoice_ref' => $gData['invoice_ref']]
                    );
                }
                $gData['auto_deducted'] = $autoDeductedGroup;
                $gData['total_debt'] = max(0, round($gData['total_debt'] - $autoDeductedGroup, 2));

                if ($gData['total_debt'] > 0) {
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

                $cashPaidFromTreasury = floatval($item['paid_amount'] ?? 0);
                $remainingDebt = max(0, $totalCost - $cashPaidFromTreasury);
                $paymentMethod = $item['payment_method'] ?? 'later';
                $accountId = $item['account_id'] ?? null;

                // المصروفات (النثرية والداخلية) تُسجل آجل وتنزل في شاشة الديون بلون صف مميز
                if (in_array($opType, ['petty', 'overhead'], true)) {
                    $paymentMethod = 'later';
                    $cashPaidFromTreasury = 0;
                    $remainingDebt = $totalCost;
                    $accountId = null;
                }

                $personName = trim($item['person_name'] ?? '');
                if ($opType === 'petty') {
                    $personName = 'نثريات المقاولات';
                } elseif ($opType === 'overhead') {
                    $personName = 'مصروفات داخلية - المقاولات';
                }
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

                // Auto-deduct from supplier/technician credit balance if any exists (when not part of a grouped material invoice)
                $matGroupKey = ($opType === 'material' && !empty($item['invoice_ref']) && $personName !== '')
                    ? ($personName . '||' . trim($item['invoice_ref']))
                    : null;

                $autoDeductedSingle = 0.0;
                if ($matGroupKey && isset($groupedMaterialDebts[$matGroupKey])) {
                    $gInfo = $groupedMaterialDebts[$matGroupKey];
                    if ($gInfo['auto_deducted'] > 0 && $gInfo['initial_debt'] > 0 && $remainingDebt > 0) {
                        $autoDeductedSingle = round($gInfo['auto_deducted'] * ($remainingDebt / $gInfo['initial_debt']), 2);
                    }
                } elseif (!$matGroupKey && $remainingDebt > 0 && !empty($personName) && in_array($opType, ['material', 'technician'], true) && function_exists('consumeSupplierCredit')) {
                    $autoDeductedSingle = consumeSupplierCredit(
                        $personName,
                        $remainingDebt,
                        "خصم تلقائي لسداد حركة ({$item['name_or_title']}) - مشروع: {$project->name}",
                        ['project_id' => $project->id]
                    );
                }

                $paidAmount = round($cashPaidFromTreasury + $autoDeductedSingle, 2);
                $remainingDebt = max(0, round($remainingDebt - $autoDeductedSingle, 2));
                if ($autoDeductedSingle > 0 && $remainingDebt <= 0) {
                    $paymentMethod = 'cash';
                } elseif ($autoDeductedSingle > 0 && $remainingDebt > 0) {
                    $paymentMethod = 'partial';
                }

                // 1. If remaining debt exists, record to company_debts (or use consolidated invoice debt)
                $debtId = null;

                if ($matGroupKey && isset($groupedMaterialDebts[$matGroupKey])) {
                    $debtId = $groupedMaterialDebts[$matGroupKey]['debt_id'];
                } elseif ($remainingDebt > 0 && !empty($personName) && in_array($opType, ['material', 'technician', 'commission', 'petty', 'overhead'], true)) {
                    $reasonTitle = match($opType) {
                        'commission' => "متبقي عمولة مسوق ({$personName})",
                        'technician' => "متبقي مصنعية ({$item['name_or_title']})",
                        'petty'      => "مصروف نثري ({$item['name_or_title']})",
                        'overhead'   => "مصروف داخلي ({$item['name_or_title']})",
                        default      => "متبقي حساب {$opType} ({$item['name_or_title']})"
                    };
                    $reason = "{$reasonTitle} - مشروع: " . $project->name;
                    if (!empty($item['notes'])) {
                        $reason .= ' | ملاحظات: ' . trim($item['notes']);
                    }
                    $debtData = [
                        'creditor_name'     => $personName,
                        'reason'            => Str::limit($reason, 255),
                        'total_amount'      => $remainingDebt,
                        'paid_amount'       => 0,
                        'remaining_balance' => $remainingDebt,
                        'source_type'       => 'cont_operation',
                        'source_id'         => null,
                        'created_at'        => now(),
                    ];
                    if ($opType === 'petty') {
                        $debtData['category'] = 'مصروف نثري';
                    } elseif ($opType === 'overhead') {
                        $debtData['category'] = 'مصروف داخلي';
                    }
                    $debtId = DB::table('company_debts')->insertGetId($debtData);

                    // Update directory balance (suppliers, technicians, marketers only)
                    if (in_array($opType, ['material', 'technician', 'commission'], true)) {
                        DB::table('cont_directory')
                            ->where('name', $personName)
                            ->increment('balance_debt', $remainingDebt);
                    }
                }

                // 2. If cash/bank paid, validate and deduct from account
                if ($cashPaidFromTreasury > 0) {
                    if (empty($accountId)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "طالما يوجد سداد نقدي لحركة ({$item['name_or_title']})، يجب اختيار الخزينة أو الحساب البنكي للسحب منه!"
                        ], 422);
                    }
                    $acc = DB::table('accounts')->where('id', $accountId)->first();
                    if ($acc) {
                        DB::table('accounts')->where('id', $accountId)->decrement('balance', $cashPaidFromTreasury);

                        $subtype = match($opType) {
                            'commission' => 'marketer_commission',
                            'technician' => 'technician_payment',
                            'petty', 'overhead' => 'general_expense',
                            default      => 'supplier_payment'
                        };

                        DB::table('financial_transactions')->insert([
                            'type'            => 'expense',
                            'subtype'         => $subtype,
                            'amount'          => $cashPaidFromTreasury,
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
                    'project_id'        => $project->id,
                    'item_id'           => $itemId,
                    'op_type'           => $opType,
                    'is_billable'       => $isBillable,
                    'name_or_title'     => $item['name_or_title'] ?? 'حركة بدون اسم',
                    'quantity'          => $quantity,
                    'original_quantity' => $quantity,
                    'returned_quantity' => 0,
                    'unit'              => in_array($opType, ['material', 'petty', 'overhead', 'commission'], true) ? trim((string)($item['unit'] ?? '')) : ($item['unit'] ?? 'عدد'),
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

                if ($opType === 'technician' && $cashPaidFromTreasury > 0 && !empty($personName)) {
                    $this->ensureWorkerPaymentsTableExists();
                    DB::table('cont_worker_payments')->insert([
                        'sy2_payment_id'  => null,
                        'project_id'      => $project->id,
                        'item_id'         => $itemId,
                        'operation_id'    => $opId,
                        'debt_id'         => $debtId,
                        'person_id'       => $item['person_id'] ?? null,
                        'person_name'     => $personName,
                        'person_type'     => 'technician',
                        'amount'          => $cashPaidFromTreasury,
                        'discount'        => 0,
                        'discount_reason' => null,
                        'payment_date'    => date('Y-m-d'),
                        'method'          => $paymentMethod,
                        'account_id'      => $accountId,
                        'notes'           => !empty($item['notes']) ? $item['notes'] : ($item['name_or_title'] ?? 'دفعة فورية'),
                        'created_by'      => (string) ($user?->id ?? 1),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
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

            $this->syncProjectCustomerReceivable($project->id, true);

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

        $project = DB::table('cont_projects')->where('id', $op->project_id)->first();
        if ($lockMsg = $this->buildProjectLockMessage($project, 'حذف هذه العملية')) {
            return response()->json(['success' => false, 'message' => $lockMsg], 422);
        }

        // Block deletion of any material item — returns system must be used instead
        if ($op->op_type === 'material') {
            return response()->json([
                'success' => false,
                'message' => "⛔ تم إلغاء الحذف النهائي للخامات والفواتير. يرجى استخدام نظام (المرتجعات) لإرجاع الخامة جزئياً أو بالكامل مع تسوية الديون والمستحقات تلقائياً."
            ], 422);
        }

        DB::beginTransaction();
        try {
            $totalRefundedToSafe = 0.0;
            $totalDebtCancelled  = 0.0;

            // 1. Revert linked company_debts & refund any payments made later in /debts2 (ديون على الشركة)
            $opIds = [$id];
            $debtIdsToDelete = collect([$op->debt_id])
                ->merge(DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $opIds)->pluck('id'))
                ->filter()
                ->unique();

            foreach ($debtIdsToDelete as $dId) {
                $debtRow = DB::table('company_debts')->where('id', $dId)->lockForUpdate()->first();
                if (!$debtRow) continue;

                $otherOpsCount = DB::table('cont_operations')->where('debt_id', $dId)->where('id', '!=', $id)->count();
                if ($otherOpsCount > 0) {
                    $deductRem = min(floatval($debtRow->remaining_balance), floatval($op->remaining_debt));
                    $excessPaidOnShared = max(0, round(floatval($op->remaining_debt) - $deductRem, 2));

                    DB::table('company_debts')->where('id', $dId)->update([
                        'total_amount'      => max(0, round(floatval($debtRow->total_amount) - floatval($op->remaining_debt), 2)),
                        'remaining_balance' => max(0, round(floatval($debtRow->remaining_balance) - $deductRem, 2)),
                        'paid_amount'       => max(0, round(floatval($debtRow->paid_amount) - $excessPaidOnShared, 2)),
                        'updated_at'        => now(),
                    ]);
                    $totalDebtCancelled += $deductRem;

                    if ($excessPaidOnShared > 0) {
                        $lastPayTx = DB::table('financial_transactions')
                            ->where('ref_type', 'company_debt_payment')
                            ->where('ref_id', $dId)
                            ->where('status', 'active')
                            ->orderBy('id', 'desc')
                            ->first();
                        $refundAccId = $lastPayTx?->from_account_id ?: DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                        if ($refundAccId) {
                            DB::table('accounts')->where('id', $refundAccId)->increment('balance', $excessPaidOnShared);
                            DB::table('financial_transactions')->insert([
                                'type'          => 'income',
                                'subtype'       => 'refund',
                                'amount'        => $excessPaidOnShared,
                                'to_account_id' => $refundAccId,
                                'person_name'   => $op->person_name,
                                'notes'         => "استرداد سداد مديونية لحذف حركة ({$op->name_or_title}) بمشروع ID #{$op->project_id}",
                                'status'        => 'active',
                                'ref_type'      => 'cont_operation_cancelled',
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ]);
                            $totalRefundedToSafe += $excessPaidOnShared;
                        }
                    }
                } else {
                    // Refund any debt payments made later in /debts2 (company_debt_payment)
                    $debtPaymentTxs = DB::table('financial_transactions')
                        ->where('ref_type', 'company_debt_payment')
                        ->where('ref_id', $dId)
                        ->where('status', 'active')
                        ->get();

                    if ($debtPaymentTxs->isNotEmpty()) {
                        foreach ($debtPaymentTxs as $dpTx) {
                            $payAmt = floatval($dpTx->amount);
                            if ($payAmt > 0) {
                                $refundAccId = $dpTx->from_account_id ?: DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                                if ($refundAccId) {
                                    DB::table('accounts')->where('id', $refundAccId)->increment('balance', $payAmt);
                                    DB::table('financial_transactions')->insert([
                                        'type'          => 'income',
                                        'subtype'       => 'refund',
                                        'amount'        => $payAmt,
                                        'to_account_id' => $refundAccId,
                                        'person_name'   => $op->person_name,
                                        'notes'         => "استرداد سداد مديونية لحذف حركة ({$op->name_or_title}) - المستفيد: {$op->person_name} - مشروع ID #{$op->project_id}",
                                        'status'        => 'active',
                                        'ref_type'      => 'cont_operation_cancelled',
                                        'created_at'    => now(),
                                        'updated_at'    => now(),
                                    ]);
                                    $totalRefundedToSafe += $payAmt;
                                }
                            }

                            // Cancel any earned discount linked to this debt payment
                            DB::table('financial_transactions')
                                ->where('ref_type', 'company_debt_payment_discount')
                                ->where('ref_id', $dpTx->id)
                                ->where('status', 'active')
                                ->update([
                                    'status'        => 'cancelled',
                                    'cancelled_at'  => now(),
                                    'cancel_reason' => "حذف حركة ({$op->name_or_title})",
                                    'updated_at'    => now(),
                                ]);

                            DB::table('financial_transactions')
                                ->where('id', $dpTx->id)
                                ->update([
                                    'status'        => 'cancelled',
                                    'cancelled_at'  => now(),
                                    'cancel_reason' => "حذف حركة ({$op->name_or_title})",
                                    'updated_at'    => now(),
                                ]);
                        }
                    } elseif (floatval($debtRow->paid_amount) > 0) {
                        // Fallback if paid_amount was recorded on company_debts without a ref_id match
                        $fallbackAmt = floatval($debtRow->paid_amount);
                        $fallbackAccId = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                        if ($fallbackAccId) {
                            DB::table('accounts')->where('id', $fallbackAccId)->increment('balance', $fallbackAmt);
                            DB::table('financial_transactions')->insert([
                                'type'          => 'income',
                                'subtype'       => 'refund',
                                'amount'        => $fallbackAmt,
                                'to_account_id' => $fallbackAccId,
                                'person_name'   => $op->person_name,
                                'notes'         => "استرداد سداد مديونية لحذف حركة ({$op->name_or_title}) بمشروع ID #{$op->project_id}",
                                'status'        => 'active',
                                'ref_type'      => 'cont_operation_cancelled',
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ]);
                            $totalRefundedToSafe += $fallbackAmt;
                        }
                    }

                    // Cancel any standalone earned_discount linked directly to the debt row
                    DB::table('financial_transactions')
                        ->where('ref_type', 'earned_discount')
                        ->where('ref_id', $dId)
                        ->where('status', 'active')
                        ->update([
                            'status'        => 'cancelled',
                            'cancelled_at'  => now(),
                            'cancel_reason' => "حذف حركة ({$op->name_or_title})",
                            'updated_at'    => now(),
                        ]);

                    $totalDebtCancelled += floatval($debtRow->remaining_balance);
                    DB::table('company_debts')->where('id', $dId)->delete();
                }
            }

            if ($op->person_name) {
                $liveDebt = (float) DB::table('company_debts')->where('creditor_name', $op->person_name)->sum('remaining_balance');
                DB::table('cont_directory')
                    ->where('name', $op->person_name)
                    ->update(['balance_debt' => max(0, $liveDebt)]);
            }

            // 2. If cash was paid immediately from treasury account when creating this operation, refund it
            if (floatval($op->paid_amount) > 0) {
                $ftQuery = DB::table('financial_transactions')
                    ->where('ref_type', 'cont_operation')
                    ->where('amount', $op->paid_amount)
                    ->where('notes', 'like', "%{$op->name_or_title}%")
                    ->where('status', 'active');
                if (!empty($op->person_name)) {
                    $ftQuery->where('person_name', $op->person_name);
                }
                $ft = $ftQuery->orderBy('id', 'desc')->first();

                $refundAccId = $ft?->from_account_id ?: DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                if ($refundAccId) {
                    DB::table('accounts')->where('id', $refundAccId)->increment('balance', $op->paid_amount);
                    DB::table('financial_transactions')->insert([
                        'type'          => 'income',
                        'subtype'       => 'refund',
                        'amount'        => $op->paid_amount,
                        'to_account_id' => $refundAccId,
                        'person_name'   => $op->person_name,
                        'notes'         => "استرداد سداد فوري لحذف حركة ({$op->name_or_title}) بمشروع ID #{$op->project_id}",
                        'status'        => 'active',
                        'ref_type'      => 'cont_operation_cancelled',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    if ($ft) {
                        DB::table('financial_transactions')->where('id', $ft->id)->update([
                            'status'        => 'cancelled',
                            'cancelled_at'  => now(),
                            'cancel_reason' => "حذف حركة ({$op->name_or_title})",
                            'updated_at'    => now(),
                        ]);
                    }
                    $totalRefundedToSafe += floatval($op->paid_amount);
                }
            }

            if (Schema::hasTable('cont_worker_payments')) {
                DB::table('cont_worker_payments')
                    ->where('operation_id', $id)
                    ->whereNull('sy2_payment_id')
                    ->delete();
            }

            DB::table('cont_operations')->where('id', $id)->delete();

            // 3. Synchronize customer receivable
            $this->syncProjectCustomerReceivable($op->project_id, true);

            DB::commit();

            $this->logActivity('delete', 'contracting', "تم حذف حركة صرف ({$op->name_or_title}) بمشروع ID #{$op->project_id}", [
                'operation_id'   => $id,
                'cost'           => $op->total_cost,
                'refunded_cash'  => $totalRefundedToSafe,
                'debt_cancelled' => $totalDebtCancelled,
            ]);

            $msgParts = ["تم حذف حركة ({$op->name_or_title}) وتحديث مستحقات العميل بنجاح."];
            if ($totalRefundedToSafe > 0) {
                $msgParts[] = "💰 تم استرداد مبلغ (" . number_format($totalRefundedToSafe, 2) . " ج) وإيداعه في الخزينة تلقائياً.";
            }
            if ($totalDebtCancelled > 0) {
                $msgParts[] = "📉 تم إلغاء مديونية متبقية بقيمة (" . number_format($totalDebtCancelled, 2) . " ج) من سجل الديون.";
            }

            return response()->json([
                'success' => true,
                'message' => implode("\n", $msgParts),
            ]);
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

        $this->syncProjectCustomerReceivable($project->id, true);

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
        if ($lockMsg = $this->buildProjectLockMessage($project, 'تعديل هذه العملية')) {
            return response()->json(['success' => false, 'message' => $lockMsg], 422);
        }
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
            $oldTotalCost = floatval($op->total_cost);
            $costDiff = round($totalCost - $oldTotalCost, 2);
            $newPaidAmount = floatval($op->paid_amount);
            $newRemainingDebt = floatval($op->remaining_debt);
            $newDebtId = $op->debt_id;

            if ($newDebtId) {
                $debtRow = DB::table('company_debts')->where('id', $newDebtId)->lockForUpdate()->first();
                if ($debtRow) {
                    $curRem = floatval($debtRow->remaining_balance);
                    $curPaid = floatval($debtRow->paid_amount);
                    $curTotal = floatval($debtRow->total_amount);

                    if ($costDiff > 0) {
                        // Cost increased -> add increase to remaining_balance and total_amount
                        $updatedRem = round($curRem + $costDiff, 2);
                        $updatedTotal = round($curTotal + $costDiff, 2);
                        DB::table('company_debts')->where('id', $newDebtId)->update([
                            'total_amount'      => $updatedTotal,
                            'remaining_balance' => $updatedRem,
                            'creditor_name'     => $personName ?: $debtRow->creditor_name,
                            'updated_at'        => now(),
                        ]);
                        $newRemainingDebt = round($newRemainingDebt + $costDiff, 2);
                    } elseif ($costDiff < 0) {
                        // Cost decreased -> deduct from remaining_balance first, then refund any overpaid cash
                        $absDecrease = abs($costDiff);
                        $deductFromRem = min($curRem, $absDecrease);
                        $overpaidDiff = max(0, round($absDecrease - $deductFromRem, 2));

                        $updatedRem = max(0, round($curRem - $deductFromRem, 2));
                        $updatedPaid = max(0, round($curPaid - $overpaidDiff, 2));
                        $updatedTotal = max(0, round($curTotal - $absDecrease, 2));

                        if ($updatedTotal <= 0.001 && $updatedRem <= 0.001) {
                            DB::table('company_debts')->where('id', $newDebtId)->delete();
                            $newDebtId = null;
                        } else {
                            DB::table('company_debts')->where('id', $newDebtId)->update([
                                'total_amount'      => $updatedTotal,
                                'paid_amount'       => $updatedPaid,
                                'remaining_balance' => $updatedRem,
                                'creditor_name'     => $personName ?: $debtRow->creditor_name,
                                'updated_at'        => now(),
                            ]);
                        }
                        $newRemainingDebt = max(0, round($totalCost - ($newPaidAmount + $updatedPaid), 2));

                        if ($overpaidDiff > 0) {
                            $lastPayTx = DB::table('financial_transactions')
                                ->where('ref_type', 'company_debt_payment')
                                ->where('ref_id', $debtRow->id)
                                ->where('status', 'active')
                                ->orderBy('id', 'desc')
                                ->first();
                            $refundAccId = $lastPayTx?->from_account_id ?: DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                            if ($refundAccId) {
                                DB::table('accounts')->where('id', $refundAccId)->increment('balance', $overpaidDiff);
                                DB::table('financial_transactions')->insert([
                                    'type'          => 'income',
                                    'subtype'       => 'refund',
                                    'amount'        => $overpaidDiff,
                                    'to_account_id' => $refundAccId,
                                    'person_name'   => $personName,
                                    'notes'         => "استرداد فارق تخفيض حركة ({$name}) بمشروع {$project?->name}",
                                    'status'        => 'active',
                                    'ref_type'      => 'cont_operation_edit_refund',
                                    'created_at'    => now(),
                                    'updated_at'    => now(),
                                ]);
                            }
                        }
                    }

                    if ($newDebtId && in_array($op->op_type, ['technician', 'commission', 'petty', 'overhead'], true)) {
                        $reasonTitle = match($op->op_type) {
                            'commission' => "متبقي عمولة مسوق ({$personName})",
                            'petty'      => "مصروف نثري ({$name})",
                            'overhead'   => "مصروف داخلي ({$name})",
                            default      => "متبقي مصنعية ({$name})",
                        };
                        $updatedReason = "{$reasonTitle} - مشروع: " . ($project->name ?? '');
                        if (!empty($notes)) {
                            $updatedReason .= ' | ملاحظات: ' . trim($notes);
                        }
                        DB::table('company_debts')->where('id', $newDebtId)->update([
                            'reason'        => Str::limit($updatedReason, 255),
                            'creditor_name' => $personName ?: $debtRow->creditor_name,
                        ]);
                    }
                }
            } elseif ($costDiff != 0) {
                // Operation had no company_debt row (e.g., paid in full as cash at creation)
                if ($costDiff < 0 && $newPaidAmount > $totalCost) {
                    $overpaidCash = round($newPaidAmount - $totalCost, 2);
                    $ft = DB::table('financial_transactions')
                        ->where('ref_type', 'cont_operation')
                        ->where('notes', 'like', "%{$op->name_or_title}%")
                        ->where('status', 'active')
                        ->orderBy('id', 'desc')
                        ->first();
                    $refundAccId = $ft?->from_account_id ?: DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->value('id');
                    if ($refundAccId && $overpaidCash > 0) {
                        DB::table('accounts')->where('id', $refundAccId)->increment('balance', $overpaidCash);
                        DB::table('financial_transactions')->insert([
                            'type'          => 'income',
                            'subtype'       => 'refund',
                            'amount'        => $overpaidCash,
                            'to_account_id' => $refundAccId,
                            'person_name'   => $personName,
                            'notes'         => "استرداد فارق تخفيض حركة ({$name}) بمشروع {$project?->name}",
                            'status'        => 'active',
                            'ref_type'      => 'cont_operation_edit_refund',
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                    $newPaidAmount = $totalCost;
                    $newRemainingDebt = 0;
                } elseif ($costDiff > 0 && !empty($personName) && in_array($op->op_type, ['technician', 'commission', 'material', 'petty', 'overhead'], true)) {
                    $unpaidIncrease = max(0, round($totalCost - $newPaidAmount, 2));
                    if ($unpaidIncrease > 0) {
                        $reasonTitle = match($op->op_type) {
                            'commission' => "متبقي عمولة مسوق ({$personName})",
                            'petty'      => "مصروف نثري ({$name})",
                            'overhead'   => "مصروف داخلي ({$name})",
                            default      => "متبقي مصنعية ({$name})",
                        };
                        $reasonStr = "{$reasonTitle} - مشروع: " . ($project->name ?? '');
                        if (!empty($notes)) {
                            $reasonStr .= ' | ملاحظات: ' . trim($notes);
                        }
                        $debtIns = [
                            'creditor_name'     => $personName,
                            'reason'            => Str::limit($reasonStr, 255),
                            'total_amount'      => $unpaidIncrease,
                            'paid_amount'       => 0,
                            'remaining_balance' => $unpaidIncrease,
                            'source_type'       => 'cont_operation',
                            'source_id'         => $id,
                            'created_at'        => now(),
                        ];
                        if ($op->op_type === 'petty') {
                            $debtIns['category'] = 'مصروف نثري';
                        } elseif ($op->op_type === 'overhead') {
                            $debtIns['category'] = 'مصروف داخلي';
                        }
                        $newDebtId = DB::table('company_debts')->insertGetId($debtIns);
                        $newRemainingDebt = $unpaidIncrease;
                    }
                }
            }

            if ($personName && in_array($op->op_type, ['material', 'technician', 'commission'], true)) {
                $liveDebt = (float) DB::table('company_debts')->where('creditor_name', $personName)->sum('remaining_balance');
                DB::table('cont_directory')->where('name', $personName)->update(['balance_debt' => max(0, $liveDebt)]);
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
                'paid_amount'     => $newPaidAmount,
                'remaining_debt'  => $newRemainingDebt,
                'debt_id'         => $newDebtId,
                'notes'           => $notes,
                'updated_at'      => now(),
            ]);

            $this->syncProjectCustomerReceivable($op->project_id, true);

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
     * Store Material Return (partial or full return of materials from an invoice or operation)
     * Automatically adjusts supplier debt, treasury cash/supplier credit, and customer receivables.
     */
    public function storeMaterialReturn(Request $request)
    {
        $this->ensureMaterialReturnsTableExists();

        $request->validate([
            'project_id' => 'required|integer',
            'items'      => 'required|array|min:1',
        ]);

        $projectId = intval($request->input('project_id'));
        $invoiceRef = trim($request->input('invoice_ref', ''));
        $itemsInput = $request->input('items', []);
        $excessSettlement = $request->input('excess_settlement', 'supplier_credit'); // cash_refund | supplier_credit
        $accountId = $request->input('account_id');
        $reason = trim($request->input('reason', ''));

        $project = DB::table('cont_projects')->where('id', $projectId)->first();
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);
        }

        if ($lockMsg = $this->buildProjectLockMessage($project, 'عمل مرتجع خامات')) {
            return response()->json(['success' => false, 'message' => $lockMsg], 422);
        }

        // Filter items with return_qty > 0
        $validReturns = [];
        foreach ($itemsInput as $it) {
            $rQty = floatval($it['return_qty'] ?? 0);
            $opId = intval($it['id'] ?? 0);
            if ($rQty > 0 && $opId > 0) {
                $validReturns[$opId] = $rQty;
            }
        }

        if (empty($validReturns)) {
            return response()->json(['success' => false, 'message' => 'يرجى تحديد كمية المرتجع لصنف واحد على الأقل (أكبر من صفر).'], 422);
        }

        DB::beginTransaction();
        try {
            $ops = DB::table('cont_operations')
                ->whereIn('id', array_keys($validReturns))
                ->where('project_id', $projectId)
                ->lockForUpdate()
                ->get();

            if ($ops->isEmpty()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'لم يتم العثور على أصناف الفاتورة المطلوبة.'], 404);
            }

            $firstOp = $ops->first();
            $supplierName = trim($firstOp->person_name ?: 'مورد غير محدد');
            if (empty($invoiceRef)) {
                $invoiceRef = $firstOp->invoice_ref ?: ('حركة #' . $firstOp->id);
            }

            // Fetch all operations belonging to this invoice to accurately inspect invoice debt
            $allInvoiceOps = !empty($firstOp->invoice_ref)
                ? DB::table('cont_operations')->where('project_id', $projectId)->where('invoice_ref', $firstOp->invoice_ref)->get()
                : $ops;

            $totalReturnCost = 0;
            $totalReturnSelling = 0;
            $preparedUpdates = [];

            foreach ($ops as $op) {
                $returnQty = $validReturns[$op->id];
                $currentNetQty = floatval($op->quantity);

                if ($returnQty > $currentNetQty + 0.0001) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "الكمية المراد إرجاعها للصنف ({$op->name_or_title}) هي ({$returnQty}) وهي أكبر من الكمية المتاحة حالياً ({$currentNetQty})!"
                    ], 422);
                }

                $origQty = floatval($op->original_quantity ?: ($currentNetQty + floatval($op->returned_quantity ?? 0)));
                $prevRetQty = floatval($op->returned_quantity ?? 0);
                $newNetQty = max(0, round($currentNetQty - $returnQty, 2));
                $newRetQty = round($prevRetQty + $returnQty, 2);

                $unitCost = floatval($op->cost_price);
                $itemReturnCost = round($returnQty * $unitCost, 2);
                $newTotalCost = round($newNetQty * $unitCost, 2);

                $supPct = floatval($op->supervision_pct ?? 0);
                $baseSell = floatval($op->selling_price ?? 0);
                $effectiveUnitSell = $currentNetQty > 0
                    ? (floatval($op->total_selling) / $currentNetQty)
                    : ($baseSell * (1 + ($supPct / 100)));

                $itemReturnSelling = $op->is_billable ? round($returnQty * $effectiveUnitSell, 2) : 0;
                $newTotalSelling = $op->is_billable ? max(0, round(floatval($op->total_selling) - $itemReturnSelling, 2)) : 0;
                $newProfit = round($newTotalSelling - $newTotalCost, 2);

                $totalReturnCost += $itemReturnCost;
                $totalReturnSelling += $itemReturnSelling;

                $itemTitle = null;
                if ($op->item_id) {
                    $itemTitle = DB::table('cont_project_items')->where('id', $op->item_id)->value('title');
                }

                $preparedUpdates[] = [
                    'op'                   => $op,
                    'item_title'           => $itemTitle,
                    'orig_qty'             => $origQty,
                    'return_qty'           => $returnQty,
                    'new_net_qty'          => $newNetQty,
                    'new_ret_qty'          => $newRetQty,
                    'unit_cost'            => $unitCost,
                    'item_return_cost'     => $itemReturnCost,
                    'new_total_cost'       => $newTotalCost,
                    'item_return_selling'  => $itemReturnSelling,
                    'new_total_selling'    => $newTotalSelling,
                    'new_profit'           => $newProfit,
                ];
            }

            $totalReturnCost = round($totalReturnCost, 2);
            $totalReturnSelling = round($totalReturnSelling, 2);

            // 1. Deduct from linked invoice debt in company_debts first
            $allOpIds = $allInvoiceOps->pluck('id')->all();
            $linkedDebtIds = $allInvoiceOps->pluck('debt_id')
                ->merge(DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $allOpIds)->pluck('id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $debtDeductedFromInvoice = 0;
            if (!empty($linkedDebtIds)) {
                $invoiceDebts = DB::table('company_debts')->whereIn('id', $linkedDebtIds)->lockForUpdate()->get();
                $remToDeduct = $totalReturnCost;

                foreach ($invoiceDebts as $d) {
                    if ($remToDeduct <= 0) break;
                    $dRem = floatval($d->remaining_balance);
                    if ($dRem > 0) {
                        $deductNow = min($remToDeduct, $dRem);
                        $newDRem = max(0, round($dRem - $deductNow, 2));
                        $newDTotal = max(floatval($d->paid_amount), round(floatval($d->total_amount) - $deductNow, 2));

                        if ($newDRem <= 0.001 && floatval($d->paid_amount) <= 0.001) {
                            DB::table('company_debts')->where('id', $d->id)->delete();
                        } else {
                            DB::table('company_debts')->where('id', $d->id)->update([
                                'total_amount'      => $newDTotal,
                                'remaining_balance' => $newDRem,
                                'updated_at'        => now(),
                            ]);
                        }

                        $debtDeductedFromInvoice = round($debtDeductedFromInvoice + $deductNow, 2);
                        $remToDeduct = round($remToDeduct - $deductNow, 2);
                    }
                }
            }

            // 2. Handle any paid excess (if return cost > unpaid debt on this invoice)
            $paidExcess = max(0, round($totalReturnCost - $debtDeductedFromInvoice, 2));
            $cashRefunded = 0;
            $supplierCredit = 0;
            $otherDebtDeducted = 0;
            $usedAccountId = null;

            if ($paidExcess > 0) {
                // If this invoice had a company_debts record that was already paid in /debts2, adjust its total_amount and paid_amount
                if (!empty($linkedDebtIds)) {
                    $paidInvoiceDebts = DB::table('company_debts')->whereIn('id', $linkedDebtIds)->lockForUpdate()->get();
                    $remPaidToReduce = $paidExcess;
                    foreach ($paidInvoiceDebts as $pidRow) {
                        if ($remPaidToReduce <= 0) break;
                        $curP = floatval($pidRow->paid_amount);
                        if ($curP > 0) {
                            $reduceP = min($curP, $remPaidToReduce);
                            $newP = max(0, round($curP - $reduceP, 2));
                            $newT = max(floatval($pidRow->remaining_balance), round(floatval($pidRow->total_amount) - $reduceP, 2));
                            if ($newT <= 0.001 && floatval($pidRow->remaining_balance) <= 0.001) {
                                DB::table('company_debts')->where('id', $pidRow->id)->delete();
                            } else {
                                DB::table('company_debts')->where('id', $pidRow->id)->update([
                                    'total_amount' => $newT,
                                    'paid_amount'  => $newP,
                                    'updated_at'   => now(),
                                ]);
                            }
                            $remPaidToReduce = round($remPaidToReduce - $reduceP, 2);
                        }
                    }
                }

                if ($excessSettlement === 'cash_refund') {
                    $usedAccountId = $accountId;
                    if (empty($usedAccountId) && !empty($linkedDebtIds)) {
                        $debtPayTx = DB::table('financial_transactions')
                            ->where('ref_type', 'company_debt_payment')
                            ->whereIn('ref_id', $linkedDebtIds)
                            ->where('status', 'active')
                            ->orderBy('id', 'desc')
                            ->first();
                        $usedAccountId = $debtPayTx?->from_account_id;
                    }
                    if (empty($usedAccountId)) {
                        $ft = DB::table('financial_transactions')
                            ->whereIn('ref_type', ['cont_operation_invoice', 'cont_operation'])
                            ->where('notes', 'like', "%{$invoiceRef}%")
                            ->first();
                        $usedAccountId = $ft?->from_account_id;
                    }
                    if (empty($usedAccountId)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "يرجى اختيار الخزينة التي سيتم إيداع المبلغ المسترد نقداً بها ({$paidExcess} ج)!"
                        ], 422);
                    }

                    DB::table('accounts')->where('id', $usedAccountId)->increment('balance', $paidExcess);

                    DB::table('financial_transactions')->insert([
                        'type'          => 'income',
                        'subtype'       => 'supplier_refund',
                        'amount'        => $paidExcess,
                        'to_account_id' => $usedAccountId,
                        'person_name'   => $supplierName,
                        'notes'         => "استرداد نقدي لمرتجع خامات من الفاتورة ({$invoiceRef}) - المورد: {$supplierName} - مشروع: {$project->name}",
                        'status'        => 'active',
                        'ref_type'      => 'cont_material_return',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    $cashRefunded = $paidExcess;
                } else {
                    // Deduct from other active debts of the same supplier if any, otherwise keep as supplier credit
                    $otherDebts = DB::table('company_debts')
                        ->where('creditor_name', $supplierName)
                        ->where('remaining_balance', '>', 0)
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $remCredit = $paidExcess;
                    foreach ($otherDebts as $od) {
                        if ($remCredit <= 0) break;
                        $odRem = floatval($od->remaining_balance);
                        $deductOd = min($remCredit, $odRem);
                        $newOdRem = max(0, round($odRem - $deductOd, 2));
                        $newOdTotal = max(floatval($od->paid_amount), round(floatval($od->total_amount) - $deductOd, 2));

                        DB::table('company_debts')->where('id', $od->id)->update([
                            'total_amount'      => $newOdTotal,
                            'remaining_balance' => $newOdRem,
                            'updated_at'        => now(),
                        ]);

                        $otherDebtDeducted = round($otherDebtDeducted + $deductOd, 2);
                        $remCredit = round($remCredit - $deductOd, 2);
                    }

                    $supplierCredit = max(0, round($paidExcess - $otherDebtDeducted, 2));
                }
            }

            $totalDebtDeducted = round($debtDeductedFromInvoice + $otherDebtDeducted, 2);

            if ($cashRefunded > 0 && ($totalDebtDeducted > 0 || $supplierCredit > 0)) {
                $settlementMethod = 'mixed';
            } elseif ($cashRefunded > 0) {
                $settlementMethod = 'cash_refund';
            } elseif ($supplierCredit > 0 && $totalDebtDeducted <= 0) {
                $settlementMethod = 'supplier_credit';
            } else {
                $settlementMethod = 'debt_deduction';
            }

            // 3. Update cont_operations rows & record cont_material_returns rows
            $retCount = DB::table('cont_material_returns')->count() + 1;
            $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad($retCount, 3, '0', STR_PAD_LEFT);
            $user = session('auth_user');
            $userName = $user?->name ?? (auth()->user()?->name ?? 'الإدارة');

            $remainingInvoiceDebtAfter = !empty($linkedDebtIds)
                ? floatval(DB::table('company_debts')->whereIn('id', $linkedDebtIds)->sum('remaining_balance'))
                : max(0, floatval($allInvoiceOps->sum('remaining_debt')) - $debtDeductedFromInvoice);

            foreach ($preparedUpdates as $upd) {
                $op = $upd['op'];
                $ratio = $totalReturnCost > 0 ? ($upd['item_return_cost'] / $totalReturnCost) : (1 / max(1, count($preparedUpdates)));

                $itemDebtDeducted = round($totalDebtDeducted * $ratio, 2);
                $itemCashRefunded = round($cashRefunded * $ratio, 2);
                $itemSupplierCredit = round($supplierCredit * $ratio, 2);

                if ($remainingInvoiceDebtAfter <= 0.01) {
                    $newOpDebt = 0;
                    $newOpPaid = $upd['new_total_cost'];
                } else {
                    $newOpDebt = min($upd['new_total_cost'], max(0, round(floatval($op->remaining_debt) - round($debtDeductedFromInvoice * $ratio, 2), 2)));
                    $newOpPaid = max(0, round($upd['new_total_cost'] - $newOpDebt, 2));
                }

                DB::table('cont_operations')->where('id', $op->id)->update([
                    'quantity'          => $upd['new_net_qty'],
                    'original_quantity' => $upd['orig_qty'],
                    'returned_quantity' => $upd['new_ret_qty'],
                    'total_cost'        => $upd['new_total_cost'],
                    'total_selling'     => $upd['new_total_selling'],
                    'profit_amount'     => $upd['new_profit'],
                    'paid_amount'       => $newOpPaid,
                    'remaining_debt'    => $newOpDebt,
                    'updated_at'        => now(),
                ]);

                DB::table('cont_material_returns')->insert([
                    'return_number'          => $returnNumber,
                    'invoice_ref'            => $invoiceRef,
                    'project_id'             => $projectId,
                    'project_name'           => $project->name,
                    'client_name'            => $project->client_name,
                    'supplier_name'          => $supplierName,
                    'operation_id'           => $op->id,
                    'item_id'                => $op->item_id,
                    'item_title'             => $upd['item_title'],
                    'material_name'          => $op->name_or_title,
                    'unit'                   => $op->unit,
                    'original_qty'           => $upd['orig_qty'],
                    'returned_qty'           => $upd['return_qty'],
                    'remaining_qty'          => $upd['new_net_qty'],
                    'cost_price'             => $upd['unit_cost'],
                    'selling_price'          => $op->selling_price,
                    'supervision_pct'        => $op->supervision_pct,
                    'total_cost_returned'    => $upd['item_return_cost'],
                    'total_selling_returned' => $upd['item_return_selling'],
                    'settlement_method'      => $settlementMethod,
                    'debt_deducted'          => $itemDebtDeducted,
                    'cash_refunded'          => $itemCashRefunded,
                    'supplier_credit'        => $itemSupplierCredit,
                    'account_id'             => $usedAccountId,
                    'reason'                 => $reason ?: null,
                    'created_by'             => $userName,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
            }

            // 4. Update supplier directory balance_debt & balance_credit
            if ($supplierName) {
                $liveSuppDebt = (float) DB::table('company_debts')->where('creditor_name', $supplierName)->sum('remaining_balance');
                DB::table('cont_directory')
                    ->where('name', $supplierName)
                    ->where('type', 'supplier')
                    ->update(['balance_debt' => max(0, $liveSuppDebt)]);

                if ($supplierCredit > 0 && function_exists('addSupplierCredit')) {
                    addSupplierCredit(
                        $supplierName,
                        $supplierCredit,
                        'return_credit',
                        "رصيد دائن من مرتجع خامات رقم ({$returnNumber}) على فاتورة ({$invoiceRef})",
                        [
                            'invoice_ref'     => $invoiceRef,
                            'project_id'      => $projectId,
                            'skip_open_debts' => true,
                        ]
                    );
                }
            }

            // 5. Synchronize customer receivable in /debts & project KPIs
            $this->syncProjectCustomerReceivable($projectId, true);

            DB::commit();

            $this->logActivity('return', 'contracting', "تم تسجيل مرتجع خامات رقم ({$returnNumber}) على فاتورة ({$invoiceRef}) للمورد {$supplierName} بمشروع {$project->name} بقيمة تكلفة {$totalReturnCost} ج وتخفيض للعميل {$totalReturnSelling} ج", [
                'return_number' => $returnNumber,
                'project_id'    => $projectId,
                'invoice_ref'   => $invoiceRef,
                'cost_returned' => $totalReturnCost,
                'debt_deducted' => $totalDebtDeducted,
                'cash_refunded' => $cashRefunded,
            ]);

            $msgParts = ["تم تسجيل المرتجع بنجاح برقم ({$returnNumber}) وتخفيض مطالبة العميل بقيمة ({$totalReturnSelling} ج)"];
            if ($totalDebtDeducted > 0) {
                $msgParts[] = "خصم ({$totalDebtDeducted} ج) من مديونية المورد";
            }
            if ($cashRefunded > 0) {
                $msgParts[] = "استرداد ({$cashRefunded} ج) نقداً للخزينة";
            }
            if ($supplierCredit > 0) {
                $msgParts[] = "تسجيل ({$supplierCredit} ج) كرصيد دائن لنا عند المورد";
            }

            return response()->json([
                'success' => true,
                'message' => implode(' + ', $msgParts) . '.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل المرتجع: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * استرداد نقدي من رصيدنا الدائن لدى مورد / مقاول وإيداعه في الخزنة
     */
    public function refundSupplierCredit(Request $request)
    {
        if (function_exists('ensureSupplierCreditSchema')) {
            ensureSupplierCreditSchema();
        }

        $personName = trim((string) $request->input('person_name', ''));
        $amount     = round(floatval($request->input('amount', 0)), 2);
        $accountId  = intval($request->input('account_id', 0));
        $notes      = trim((string) $request->input('notes', ''));

        if ($personName === '' || $amount <= 0 || $accountId <= 0) {
            $msg = 'يرجى تحديد المورد والمبلغ والخزينة بشكل صحيح!';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        try {
            $newCredit = 0.0;
            DB::transaction(function () use ($personName, $amount, $accountId, $notes, &$newCredit) {
                $dirRow = DB::table('cont_directory')
                    ->where('name', $personName)
                    ->lockForUpdate()
                    ->first();

                if (!$dirRow) {
                    throw new \Exception('المورد غير مسجل في دليل المقاولات!');
                }

                $currentCredit = round(floatval($dirRow->balance_credit ?? 0), 2);
                if ($amount > $currentCredit + 0.009) {
                    throw new \Exception('المبلغ المطلوب استرداده (' . number_format($amount, 2) . ' ج) أكبر من الرصيد الدائن المتاح لنا عند المورد (' . number_format($currentCredit, 2) . ' ج)!');
                }

                $account = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->first();
                if (!$account) {
                    throw new \Exception('الخزنة المختارة غير موجودة!');
                }

                $newCredit = max(0, round($currentCredit - $amount, 2));

                DB::table('cont_directory')
                    ->where('id', $dirRow->id)
                    ->update([
                        'balance_credit' => $newCredit,
                        'updated_at'     => now(),
                    ]);

                DB::table('accounts')
                    ->where('id', $accountId)
                    ->increment('balance', $amount);

                $txNotes = "استرداد نقدي للخزنة من رصيدنا الدائن لدى المورد ({$personName})";
                if ($notes !== '') {
                    $txNotes .= " — {$notes}";
                }

                DB::table('financial_transactions')->insert([
                    'type'          => 'income',
                    'subtype'       => 'supplier_refund',
                    'amount'        => $amount,
                    'to_account_id' => $accountId,
                    'person_name'   => $personName,
                    'notes'         => $txNotes,
                    'ref_id'        => $dirRow->id,
                    'ref_type'      => 'cont_supplier_credit_refund',
                    'status'        => 'active',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                $user = session('auth_user');
                $userName = $user?->name ?? (auth()->user()?->name ?? 'الإدارة');

                DB::table('cont_supplier_credit_logs')->insert([
                    'person_name'   => $personName,
                    'tx_type'       => 'cash_refund',
                    'amount'        => $amount,
                    'balance_after' => $newCredit,
                    'account_id'    => $accountId,
                    'notes'         => $notes !== '' ? $notes : "استرداد نقدي وإيداع في خزنة ({$account->account_name})",
                    'created_by'    => $userName,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            });

            $this->logActivity('refund', 'contracting', "💵 استرداد نقدي من رصيدنا الدائن لدى ({$personName}) بمبلغ " . number_format($amount, 2) . " ج للخزينة", [
                'person_name'   => $personName,
                'amount'        => $amount,
                'account_id'    => $accountId,
                'balance_after' => $newCredit,
            ]);

            $successMsg = "✅ تم استرداد مبلغ (" . number_format($amount, 2) . " ج) نقداً من ({$personName}) وإيداعه في الخزنة بنجاح. الرصيد المتبقي لنا عنده: (" . number_format($newCredit, 2) . " ج).";
            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $successMsg, 'new_credit' => $newCredit])
                : back()->with('success', $successMsg);
        } catch (\Exception $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Material Invoice (Disabled — replaced by Material Returns System)
     */
    public function deleteMaterialInvoice(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => '⛔ تم إلغاء خاصية الحذف النهائي للفواتير. يرجى استخدام زر (مرتجع) لإرجاع الفاتورة جزئياً أو بالكامل مع تسوية الديون والمستحقات تلقائياً.'
        ], 422);
    }

    protected function legacyDeleteMaterialInvoiceUnused(Request $request)
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
            $this->syncProjectCustomerReceivable($projectId, true);

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

        $opIds = $ops->pluck('id')->all();
        $debtIds = $ops->pluck('debt_id')
            ->merge(!empty($opIds) ? DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $opIds)->pluck('id') : collect())
            ->filter()
            ->unique()
            ->values()
            ->all();

        $totalCost = floatval($ops->sum('total_cost'));
        $liveDebt = floatval($ops->sum('remaining_debt'));
        if (!empty($debtIds)) {
            $debts = DB::table('company_debts')->whereIn('id', $debtIds)->get();
            $liveDebt = $debts->isNotEmpty() ? floatval($debts->sum('remaining_balance')) : 0.0;
        }
        $livePaid = max(0, round($totalCost - $liveDebt, 2));
        $livePayMethod = $liveDebt <= 0.01 ? 'cash' : ($livePaid > 0.01 ? 'partial' : 'later');
        $lockState = $this->getProjectLockState($project);
        $lockMessage = $this->buildProjectLockMessage($project, 'تعديل فاتورة الخامات');

        return response()->json([
            'success'             => true,
            'invoice_ref'         => $invoiceRef,
            'project_id'          => $projectId,
            'project_name'        => $project?->name,
            'project_status'      => $project?->status ?? 'active',
            'is_installment_plan' => $lockState['is_installment'],
            'is_locked'           => $lockState['is_locked'],
            'lock_message'        => $lockMessage,
            'client_name'         => $project?->client_name,
            'supplier_name'  => $firstOp->person_name,
            'payment_method' => $livePayMethod,
            'account_id'     => $ft?->from_account_id,
            'total_cost'     => $totalCost,
            'total_selling'  => floatval($ops->sum('total_selling')),
            'paid_amount'    => $livePaid,
            'remaining_debt' => $liveDebt,
            'created_at'     => $firstOp->created_at,
            'items'          => $ops->map(function($op) {
                return [
                    'id'                => $op->id,
                    'name_or_title'     => $op->name_or_title,
                    'quantity'          => floatval($op->quantity),
                    'original_quantity' => floatval($op->original_quantity ?? $op->quantity),
                    'returned_quantity' => floatval($op->returned_quantity ?? 0),
                    'unit'              => $op->unit,
                    'cost_price'        => floatval($op->cost_price),
                    'supervision_pct'   => floatval($op->supervision_pct),
                    'selling_price'     => floatval($op->selling_price),
                    'total_cost'        => floatval($op->total_cost),
                    'total_selling'     => floatval($op->total_selling),
                    'notes'             => $op->notes,
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
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);
        }

        if ($lockMsg = $this->buildProjectLockMessage($project, 'تعديل فاتورة الخامات')) {
            return response()->json(['success' => false, 'message' => $lockMsg], 422);
        }

        $supplierName = trim($request->input('supplier_name', $oldOps->first()->person_name));
        $newAccountId = $request->input('account_id');

        // Calculate live oldPaidAmount (including any subsequent settlements in company_debts)
        $oldOpIds = $oldOps->pluck('id')->all();
        $oldDebtIdsList = $oldOps->pluck('debt_id')
            ->merge(!empty($oldOpIds) ? DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $oldOpIds)->pluck('id') : collect())
            ->filter()
            ->unique()
            ->values()
            ->all();

        $oldTotalCost = floatval($oldOps->sum('total_cost'));
        $oldLiveDebt = floatval($oldOps->sum('remaining_debt'));
        if (!empty($oldDebtIdsList)) {
            $existingDebtsRows = DB::table('company_debts')->whereIn('id', $oldDebtIdsList)->get();
            $oldLiveDebt = $existingDebtsRows->isNotEmpty() ? floatval($existingDebtsRows->sum('remaining_balance')) : 0.0;
        }
        $oldPaidAmount = max(0, round($oldTotalCost - $oldLiveDebt, 2));

        // Calculate new total cost
        $newGrandCost = 0;
        foreach ($items as $it) {
            $q = floatval($it['quantity'] ?? 1);
            $c = floatval($it['cost_price'] ?? 0);
            $newGrandCost += ($q * $c);
        }
        $newGrandCost = round($newGrandCost, 2);

        // Paid amount is locked to historical oldPaidAmount:
        // - If newGrandCost > oldPaidAmount: extra cost becomes remaining debt on company to supplier
        // - If newGrandCost < oldPaidAmount: difference is refunded to selected treasury account, and effective paid becomes newGrandCost
        $refundAmount = max(0.0, round($oldPaidAmount - $newGrandCost, 2));
        $effectivePaidAmount = min($oldPaidAmount, $newGrandCost);
        $newRemainingDebt = max(0.0, round($newGrandCost - $oldPaidAmount, 2));

        if ($refundAmount > 0.009 && empty($newAccountId)) {
            return response()->json([
                'success' => false,
                'message' => 'تكلفة الفاتورة بعد التعديل أقل من المسدد سابقاً. يرجى اختيار الخزينة / الحساب البنكي لاسترداد فارق المبلغ (' . number_format($refundAmount, 2) . ' ج).',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Refund excess paid cash to selected treasury account when new cost < oldPaidAmount
            if ($refundAmount > 0.009) {
                $refundAccId = intval($newAccountId);
                DB::table('accounts')->where('id', $refundAccId)->increment('balance', $refundAmount);
                DB::table('financial_transactions')->insert([
                    'type'          => 'income',
                    'subtype'       => 'supplier_refund',
                    'amount'        => $refundAmount,
                    'to_account_id' => $refundAccId,
                    'person_name'   => $supplierName,
                    'notes'         => "استرداد فارق تخفيض تكلفة فاتورة خامات ({$invoiceRef}) - مشروع {$project->name}",
                    'status'        => 'active',
                    'ref_type'      => 'cont_operation_invoice_edit',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
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

            if ($newRemainingDebt > 0.009) {
                $reason = "متبقي فاتورة خامات ({$invoiceRef}) - مشروع: " . $project->name;
                if ($existingDebtId && DB::table('company_debts')->where('id', $existingDebtId)->exists()) {
                    // Since $effectivePaidAmount is stored directly on cont_operations.paid_amount,
                    // company_debts represents the currently unpaid remaining balance on this invoice.
                    DB::table('company_debts')->where('id', $existingDebtId)->update([
                        'creditor_name'     => $supplierName,
                        'reason'            => Str::limit($reason, 255),
                        'total_amount'      => $newRemainingDebt,
                        'paid_amount'       => 0,
                        'remaining_balance' => $newRemainingDebt,
                        'category'          => 'مقاولات',
                        'source_type'       => 'cont_operation_invoice',
                        'updated_at'        => now(),
                    ]);
                    $debtId = $existingDebtId;
                } else {
                    $debtId = DB::table('company_debts')->insertGetId([
                        'creditor_name'     => $supplierName,
                        'reason'            => Str::limit($reason, 255),
                        'total_amount'      => $newRemainingDebt,
                        'paid_amount'       => 0,
                        'remaining_balance' => $newRemainingDebt,
                        'category'          => 'مقاولات',
                        'source_type'       => 'cont_operation_invoice',
                        'source_id'         => null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
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
            $firstOpPersonId = $oldOps->first()->person_id;
            DB::table('cont_operations')
                ->where('invoice_ref', $invoiceRef)
                ->where('project_id', $projectId)
                ->delete();

            $paymentMethod = ($newRemainingDebt > 0.009 && $effectivePaidAmount <= 0.009) ? 'later' : (($newRemainingDebt > 0.009 && $effectivePaidAmount > 0.009) ? 'partial' : 'cash');
            $firstNewOpId = null;

            foreach ($items as $it) {
                $qty = floatval($it['quantity'] ?? 1);
                if ($qty <= 0) continue; // skip 0 quantity items (returned completely)
                $costPrice = floatval($it['cost_price'] ?? 0);
                $totalCost = round($qty * $costPrice, 2);
                $supPct = isset($it['supervision_pct']) ? floatval($it['supervision_pct']) : floatval($project->supervision_pct);
                $baseSellingPrice = floatval($it['selling_price'] ?? 0);
                if ($baseSellingPrice <= 0 && $costPrice > 0) {
                    $baseSellingPrice = $costPrice;
                }
                $sellingPrice = $baseSellingPrice;
                $totalSelling = round($qty * $baseSellingPrice * (1 + ($supPct / 100)), 2);
                $profit = round($totalSelling - $totalCost, 2);

                $itemRatio = $newGrandCost > 0 ? ($totalCost / $newGrandCost) : (1 / max(1, count($items)));
                $itemPaid = round($effectivePaidAmount * $itemRatio, 2);
                $itemDebt = round($newRemainingDebt * $itemRatio, 2);

                $retQty = floatval($it['returned_quantity'] ?? 0);
                $newOpId = DB::table('cont_operations')->insertGetId([
                    'project_id'        => $projectId,
                    'item_id'           => $it['item_id'] ?? $firstOpItem,
                    'op_type'           => 'material',
                    'is_billable'       => 1,
                    'name_or_title'     => $it['name_or_title'] ?? 'خامة',
                    'quantity'          => $qty,
                    'original_quantity' => $qty + $retQty,
                    'returned_quantity' => $retQty,
                    'unit'              => trim((string)($it['unit'] ?? '')),
                    'cost_price'        => $costPrice,
                    'supervision_pct'   => $supPct,
                    'selling_price'     => $sellingPrice,
                    'total_cost'        => $totalCost,
                    'total_selling'     => $totalSelling,
                    'profit_amount'     => $profit,
                    'person_id'         => $firstOpPersonId,
                    'person_name'       => $supplierName,
                    'payment_method'    => $paymentMethod,
                    'paid_amount'       => $itemPaid,
                    'remaining_debt'    => $itemDebt,
                    'debt_id'           => $debtId,
                    'invoice_ref'       => $invoiceRef,
                    'notes'             => $it['notes'] ?? null,
                    'created_by'        => session('auth_user')?->id ?? 1,
                    'created_at'        => $firstOpDate,
                    'updated_at'        => now(),
                ]);

                if (!$firstNewOpId) $firstNewOpId = $newOpId;
            }

            if ($debtId && $firstNewOpId) {
                DB::table('company_debts')->where('id', $debtId)->update(['source_id' => $firstNewOpId]);
            }

            // 3B. Re-link cont_worker_payments rows for this invoice to the new operation_id & adjust if refunded
            if ($firstNewOpId && Schema::hasTable('cont_worker_payments')) {
                $wpRows = DB::table('cont_worker_payments')
                    ->where('person_type', 'supplier')
                    ->where(function ($q) use ($oldOpIds, $oldDebtIdsList) {
                        if (!empty($oldOpIds)) {
                            $q->whereIn('operation_id', $oldOpIds);
                        }
                        if (!empty($oldDebtIdsList)) {
                            $q->orWhereIn('debt_id', $oldDebtIdsList);
                        }
                    })
                    ->orderBy('id', 'desc')
                    ->get();

                if ($wpRows->isNotEmpty()) {
                    DB::table('cont_worker_payments')
                        ->whereIn('id', $wpRows->pluck('id')->all())
                        ->update([
                            'operation_id' => $firstNewOpId,
                            'debt_id'      => $debtId,
                            'person_name'  => $supplierName,
                            'updated_at'   => now(),
                        ]);

                    if ($refundAmount > 0.009) {
                        $remToDeduct = $refundAmount;
                        foreach ($wpRows as $wpRow) {
                            if ($remToDeduct <= 0.009) break;
                            $curAmt = round(floatval($wpRow->amount), 2);
                            if ($curAmt > 0) {
                                $deduct = min($curAmt, $remToDeduct);
                                $newAmt = round($curAmt - $deduct, 2);
                                $remToDeduct = round($remToDeduct - $deduct, 2);
                                $newNotes = trim(($wpRow->notes ? $wpRow->notes . ' | ' : '') . "استرداد فارق تعديل فاتورة: {$deduct} ج");
                                DB::table('cont_worker_payments')->where('id', $wpRow->id)->update([
                                    'amount'     => $newAmt,
                                    'notes'      => $newNotes,
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            // 4. Synchronize customer receivable in /debts
            $this->syncProjectCustomerReceivable($projectId, true);

            DB::commit();

            $this->logActivity('update', 'contracting', "تم تعديل فاتورة الخامات ({$invoiceRef}) وتحديث البنود وحسابات المورد والخزينة", [
                'project_id'    => $projectId,
                'invoice_ref'   => $invoiceRef,
                'refund_amount' => $refundAmount,
                'new_debt'      => $newRemainingDebt,
            ]);

            $msg = 'تم حفظ تعديل الفاتورة وتحديث حسابات المورد ومستحقات العميل بنجاح.';
            if ($refundAmount > 0.009) {
                $msg .= "\n💰 تم استرداد فارق (" . number_format($refundAmount, 2) . " ج) وإيداعه في الخزينة المختارة.";
            } elseif ($newRemainingDebt > 0.009) {
                $msg .= "\n📌 تم تسجيل المبلغ المتبقي (" . number_format($newRemainingDebt, 2) . " ج) كمديونية للمورد على الشركة.";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تعديل الفاتورة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Compute client remaining due and advance credit balance consistently across the module.
     */
    protected function computeProjectBalanceMetrics(float $netSelling, float $instInterestExtra, float $clientPaid): array
    {
        $totalDue        = max(0.0, round($netSelling + $instInterestExtra, 2));
        $clientRemaining = max(0.0, round($totalDue - $clientPaid, 2));
        $clientCredit    = max(0.0, round($clientPaid - $totalDue, 2));

        return [
            'total_due'        => $totalDue,
            'client_remaining' => $clientRemaining,
            'client_credit'    => $clientCredit,
        ];
    }

    /**
     * Sum client excess refunds that have NOT already been netted into an installment plan's down_payment.
     */
    protected function sumUnabsorbedClientRefunds($invoices): float
    {
        return abs(floatval(
            collect($invoices)
                ->where('invoice_type', 'client_refund')
                ->reject(fn ($inv) => str_contains((string) ($inv->notes ?? ''), 'محتسب ضمن تسوية مقدم التقسيط'))
                ->sum('paid_amount')
        ));
    }

    /**
     * Compute total client paid amount consistently from installment record and payment receipts.
     */
    protected function computeProjectClientPaid(?object $inst, $invoices, ?float $instPaymentsSum = null): float
    {
        if ($instPaymentsSum === null) {
            $instPaymentsSum = $inst
                ? floatval(DB::table('installment_payments')
                    ->where('installment_id', $inst->id)
                    ->selectRaw('COALESCE(SUM(amount_paid + COALESCE(discount_applied, 0)), 0) as total_paid')
                    ->value('total_paid'))
                : 0.0;
        }
        $instPaid = $inst ? ($instPaymentsSum + floatval($inst->down_payment ?? 0)) : 0.0;
        $receiptInvoices = collect($invoices)->where('invoice_type', '!=', 'installment_contract');
        $positiveReceipts = $receiptInvoices->where('invoice_type', '!=', 'client_refund');
        $directPaid = floatval($positiveReceipts->whereNull('installment_id')->sum('paid_amount'));
        $unabsorbedRefunds = $this->sumUnabsorbedClientRefunds($receiptInvoices);

        $receiptsNet = floatval($receiptInvoices->sum('paid_amount'));
        $instNet = max(0.0, ($instPaid + $directPaid) - $unabsorbedRefunds);

        return max(0.0, round(max($receiptsNet, $instNet), 2));
    }

    /**
     * Determine whether a project has been converted into an installment contract.
     */
    protected function isProjectConvertedToInstallment(int $projectId): bool
    {
        if ($projectId <= 0) {
            return false;
        }

        $hasInstPlan = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->where('status', '!=', 'cancelled')
            ->where('installment_months', '>', 0)
            ->exists();

        if ($hasInstPlan) {
            return true;
        }

        $planInvoices = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'installment_contract')
            ->get(['installment_id']);

        if ($planInvoices->isEmpty()) {
            return false;
        }

        $linkedIds = $planInvoices->pluck('installment_id')->filter()->unique()->values()->all();
        if (empty($linkedIds)) {
            return true;
        }

        $cancelledCount = DB::table('installments')
            ->whereIn('id', $linkedIds)
            ->where('status', 'cancelled')
            ->count();

        return $cancelledCount < count($linkedIds);
    }

    /**
     * Inspect project completion and installment-conversion lock flags.
     */
    protected function getProjectLockState(?object $project): array
    {
        if (!$project) {
            return [
                'is_completed'   => false,
                'is_installment' => false,
                'is_locked'      => false,
                'lock_type'      => null,
            ];
        }

        $isCompleted = ($project->status ?? 'active') === 'completed';
        $isInstallment = $this->isProjectConvertedToInstallment((int) $project->id);

        return [
            'is_completed'   => $isCompleted,
            'is_installment' => $isInstallment,
            'is_locked'      => $isCompleted || $isInstallment,
            'lock_type'      => $isInstallment ? 'installment' : ($isCompleted ? 'completed' : null),
        ];
    }

    /**
     * Return a lock error message if delete / edit / return is blocked for this project.
     */
    protected function buildProjectLockMessage(?object $project, string $actionLabel = 'هذا الإجراء'): ?string
    {
        $lock = $this->getProjectLockState($project);
        if (!$lock['is_locked']) {
            return null;
        }

        if ($lock['is_installment']) {
            return "⛔ لا يمكن تنفيذ {$actionLabel} لأن المشروع محوّل إلى عقد تقسيط، ولا يمكن تغيير حالته لأي حالة أخرى وبالتالي الحذف والتعديل والمرتجعات مقفولة.";
        }

        return "⛔ لا يمكن تنفيذ {$actionLabel} لأن حالة المشروع (مكتمل). قم بتغيير حالة المشروع إلى (نشط) أولاً لإتاحة الحذف والتعديل والمرتجعات.";
    }

    /**
     * Ensure at most one active installment/receivable row exists per contracting project,
     * preferring any active installment plan (installment_months > 0) and merging duplicates.
     */
    protected function consolidateProjectInstallments(int $projectId): ?object
    {
        $candidates = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $planInstIds = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'installment_contract')
            ->whereNotNull('installment_id')
            ->pluck('installment_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($planInstIds)) {
            $linkedInsts = DB::table('installments')
                ->whereIn('id', $planInstIds)
                ->where('status', '!=', 'cancelled')
                ->orderBy('id')
                ->get();
            foreach ($linkedInsts as $li) {
                $candidates->put($li->id, $li);
            }
        }

        if ($candidates->isEmpty()) {
            return null;
        }

        $primary = $candidates
            ->sortByDesc(function ($row) use ($planInstIds, $projectId) {
                $isPlan = intval($row->installment_months ?? 0) > 0 ? 10 : 0;
                $inInvoice = in_array($row->id, $planInstIds, true) ? 2 : 0;
                $hasSource = (($row->source_type ?? '') === 'cont_project' && intval($row->source_id ?? 0) === $projectId) ? 1 : 0;
                return $isPlan + $inInvoice + $hasSource;
            })
            ->first();

        $dupIds = $candidates->keys()->reject(fn ($id) => intval($id) === intval($primary->id))->values()->all();

        if (!empty($dupIds)) {
            $extraDown = floatval($candidates->whereIn('id', $dupIds)->sum('down_payment'));
            $extraDisc = floatval($candidates->whereIn('id', $dupIds)->sum('discount'));

            DB::table('installment_payments')
                ->whereIn('installment_id', $dupIds)
                ->update(['installment_id' => $primary->id]);

            DB::table('cont_invoices')
                ->where('project_id', $projectId)
                ->whereIn('installment_id', $dupIds)
                ->update(['installment_id' => $primary->id]);

            $mergeUpdates = [
                'sale_type'   => 'contracting',
                'source_type' => 'cont_project',
                'source_id'   => $projectId,
                'updated_at'  => now(),
            ];
            if ($extraDown > 0) {
                $mergeUpdates['down_payment'] = round(floatval($primary->down_payment ?? 0) + $extraDown, 2);
            }
            if ($extraDisc > 0) {
                $mergeUpdates['discount'] = round(floatval($primary->discount ?? 0) + $extraDisc, 2);
            }
            DB::table('installments')->where('id', $primary->id)->update($mergeUpdates);
            DB::table('installments')->whereIn('id', $dupIds)->delete();

            $primary = DB::table('installments')->where('id', $primary->id)->first();
        } elseif (($primary->source_type ?? '') !== 'cont_project' || intval($primary->source_id ?? 0) !== $projectId) {
            DB::table('installments')->where('id', $primary->id)->update([
                'sale_type'   => 'contracting',
                'source_type' => 'cont_project',
                'source_id'   => $projectId,
                'updated_at'  => now(),
            ]);
            $primary = DB::table('installments')->where('id', $primary->id)->first();
        }

        return $primary;
    }

    /**
     * Absorb pre-conversion project payments into down_payment and preserve their receipts in cont_invoices.
     */
    protected function absorbProjectPriorPaymentsIntoDownPayment(int $projectId, int $installmentId, object $inst, ?string $planCutoffAt = null): array
    {
        $wasConvertedBefore = intval($inst->installment_months ?? 0) > 0;
        $allInstPayRows = DB::table('installment_payments')
            ->where('installment_id', $installmentId)
            ->orderBy('payment_date', 'asc')
            ->get();

        if (!$wasConvertedBefore) {
            $priorPayments = $allInstPayRows;
        } elseif ($planCutoffAt) {
            $cutoffTs = strtotime($planCutoffAt);
            $priorPayments = $allInstPayRows->filter(function ($p) use ($cutoffTs) {
                return $p->payment_date && strtotime($p->payment_date) <= $cutoffTs;
            })->values();
        } else {
            $priorPayments = collect();
        }

        $priorPaymentIds = $priorPayments->pluck('id')->all();
        $postPaymentIds = $allInstPayRows->pluck('id')->reject(fn ($id) => in_array($id, $priorPaymentIds, true))->values()->all();
        $allActivePaymentIds = array_merge($priorPaymentIds, $postPaymentIds);

        $unlinkedReceipts = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'payment_receipt')
            ->whereNull('installment_id')
            ->get();

        $linkedReceipts = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'payment_receipt')
            ->whereNotNull('installment_id')
            ->get();

        $orphanUnabsorbedReceipts = $linkedReceipts->filter(function ($inv) use ($allActivePaymentIds) {
            if (str_starts_with($inv->invoice_number ?? '', 'DOWN-PAY-') || str_contains($inv->notes ?? '', 'محتسب كمقدم')) {
                return false;
            }
            if (preg_match('/(?:إيصال #|REC-PAY-)(\d+)/', ($inv->invoice_number ?? '') . ' ' . ($inv->notes ?? ''), $m)) {
                return !in_array(intval($m[1]), $allActivePaymentIds, true);
            }
            return true;
        })->values();

        $unabsorbedRefunds = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'client_refund')
            ->where(function ($q) use ($wasConvertedBefore, $planCutoffAt) {
                $q->whereNull('notes')
                  ->orWhere('notes', 'not like', '%محتسب ضمن تسوية مقدم التقسيط%');
                if ($wasConvertedBefore && $planCutoffAt) {
                    $q->where('created_at', '<=', $planCutoffAt);
                }
            })
            ->get()
            ->filter(function ($rf) use ($wasConvertedBefore, $planCutoffAt) {
                if (str_contains((string) ($rf->notes ?? ''), 'محتسب ضمن تسوية مقدم التقسيط')) {
                    return false;
                }
                if ($wasConvertedBefore && $planCutoffAt && $rf->created_at) {
                    return strtotime($rf->created_at) <= strtotime($planCutoffAt);
                }
                return true;
            })
            ->values();

        $newlyAbsorbedCash = round(
            floatval($priorPayments->sum('amount_paid'))
            + floatval($unlinkedReceipts->sum('paid_amount'))
            + floatval($orphanUnabsorbedReceipts->sum('paid_amount'))
            - abs(floatval($unabsorbedRefunds->sum('paid_amount'))),
            2
        );
        $newlyAbsorbedDisc = round(floatval($priorPayments->sum('discount_applied')), 2);

        if (!empty($priorPaymentIds)) {
            foreach ($linkedReceipts as $li) {
                if (preg_match('/(?:إيصال #|REC-PAY-)(\d+)/', ($li->invoice_number ?? '') . ' ' . ($li->notes ?? ''), $m)) {
                    $pid = intval($m[1]);
                    if (in_array($pid, $priorPaymentIds, true)) {
                        $newInvNum = $li->invoice_number;
                        if (str_starts_with($li->invoice_number ?? '', 'REC-PAY-')) {
                            $candidateNum = 'DOWN-PAY-' . $pid;
                            $numTaken = DB::table('cont_invoices')
                                ->where('invoice_number', $candidateNum)
                                ->where('id', '!=', $li->id)
                                ->exists();
                            if (!$numTaken) {
                                $newInvNum = $candidateNum;
                            }
                        }
                        $newNotes = preg_replace('/\(?\s*إيصال\s*#\d+\s*\)?/', "(محتسب كمقدم تقسيط - مرجع #{$pid})", $li->notes ?? '');
                        if (!str_contains($newNotes, 'محتسب كمقدم')) {
                            $newNotes = trim($newNotes . " — محتسب كمقدم تقسيط (مرجع #{$pid})");
                        }
                        DB::table('cont_invoices')->where('id', $li->id)->update([
                            'invoice_number' => $newInvNum,
                            'installment_id' => $installmentId,
                            'notes'          => $newNotes,
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            DB::table('installment_payments')->whereIn('id', $priorPaymentIds)->delete();
        }

        foreach ($unlinkedReceipts->merge($orphanUnabsorbedReceipts) as $ur) {
            $newNotes = trim(($ur->notes ?: 'دفعة نقدية') . ' (محتسب كمقدم تقسيط)');
            DB::table('cont_invoices')->where('id', $ur->id)->update([
                'installment_id' => $installmentId,
                'notes'          => $newNotes,
                'updated_at'     => now(),
            ]);
        }

        foreach ($unabsorbedRefunds as $rf) {
            $newNotes = trim(($rf->notes ?: 'رد فائض للعميل') . ' (محتسب ضمن تسوية مقدم التقسيط)');
            DB::table('cont_invoices')->where('id', $rf->id)->update([
                'installment_id' => $installmentId,
                'notes'          => $newNotes,
                'updated_at'     => now(),
            ]);
        }

        return [
            'newly_absorbed_cash' => $newlyAbsorbedCash,
            'newly_absorbed_disc' => $newlyAbsorbedDisc,
            'post_payment_ids'    => $postPaymentIds,
        ];
    }

    /**
     * Synchronize Project Receivables with Installments Table (/debts screen)
     */
    public function syncProjectCustomerReceivable($projectId, bool $fromProjectMutation = false)
    {
        $project = DB::table('cont_projects')->where('id', $projectId)->first();
        if (!$project) return null;

        $ops = DB::table('cont_operations')->where('project_id', $projectId)->get();
        $totalBillable = floatval($ops->where('is_billable', 1)->sum('total_selling'));
        $discounts = floatval(DB::table('cont_discounts')->where('project_id', $projectId)->sum('amount'));
        $netBilled = max(0, $totalBillable - $discounts);
        $totalCost = floatval($ops->sum('total_cost'));
        $expectedProfit = max(0, $netBilled - $totalCost);

        // Consolidate & locate existing receivable or installment plan for this project
        $inst = $this->consolidateProjectInstallments(intval($projectId));

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

            $unlinkedReceipts = DB::table('cont_invoices')
                ->where('project_id', $projectId)
                ->where('invoice_type', 'payment_receipt')
                ->whereNull('installment_id')
                ->where('paid_amount', '>', 0)
                ->get();
            foreach ($unlinkedReceipts as $ur) {
                $payId = DB::table('installment_payments')->insertGetId([
                    'installment_id'    => $instId,
                    'amount_paid'       => floatval($ur->paid_amount),
                    'payment_method_id' => null,
                    'payment_date'      => $ur->created_at ?: now(),
                    'notes'             => $ur->notes ?: 'تحصيل دفعة نقدية من شاشة المقاولات',
                ]);
                DB::table('cont_invoices')->where('id', $ur->id)->update([
                    'installment_id' => $instId,
                    'notes'          => trim(($ur->notes ?? 'تحصيل دفعة نقدية من العميل') . " (إيصال #{$payId})"),
                    'updated_at'     => now(),
                ]);
            }

            $unlinkedRefunds = DB::table('cont_invoices')
                ->where('project_id', $projectId)
                ->where('invoice_type', 'client_refund')
                ->whereNull('installment_id')
                ->get();
            if ($unlinkedRefunds->isNotEmpty()) {
                DB::table('cont_invoices')
                    ->whereIn('id', $unlinkedRefunds->pluck('id')->all())
                    ->update(['installment_id' => $instId, 'updated_at' => now()]);
            }

            if ($unlinkedReceipts->isNotEmpty() || $unlinkedRefunds->isNotEmpty()) {
                return $this->syncProjectCustomerReceivable($projectId, $fromProjectMutation);
            }

            return $instId;
        } else {
            $isInstPlan = intval($inst->installment_months ?? 0) > 0;
            if ($netBilled <= 0.001 && !$isInstPlan) {
                $hasPayments = DB::table('installment_payments')->where('installment_id', $inst->id)->exists();
                if (!$hasPayments && floatval($inst->down_payment) <= 0) {
                    DB::table('installments')->where('id', $inst->id)->delete();
                    return null;
                }
            }

            // Calculate total payments collected through installments net of any unabsorbed client excess refunds
            $paymentsSum = floatval(DB::table('installment_payments')->where('installment_id', $inst->id)->sum('amount_paid'));
            $instDiscounts = floatval(DB::table('installment_payments')->where('installment_id', $inst->id)->sum('discount_applied'));
            $downPayment = floatval($inst->down_payment);
            $projectRefundInvoices = DB::table('cont_invoices')
                ->where('project_id', $projectId)
                ->where('invoice_type', 'client_refund')
                ->get();
            $unabsorbedRefunds = $this->sumUnabsorbedClientRefunds($projectRefundInvoices);
            $totalCollected = max(0.0, ($paymentsSum + $downPayment + $instDiscounts) - $unabsorbedRefunds);

            if (!$isInstPlan) {
                $cashPrice = $netBilled;
                $effectiveTotal = $netBilled;
            } elseif ($fromProjectMutation) {
                $instRate = max(0, floatval($inst->interest_rate ?? 0));
                $interestExtra = $instRate > 0
                    ? round(max(0, $netBilled - $downPayment) * ($instRate / 100), 2)
                    : max(0, round(floatval($inst->total_after_interest) - floatval($inst->cash_price), 2));
                $cashPrice = $netBilled;
                $effectiveTotal = round($netBilled + $interestExtra, 2);
            } else {
                $effectiveTotal = floatval($inst->total_after_interest) > 0 ? floatval($inst->total_after_interest) : $netBilled;
                $cashPrice = floatval($inst->cash_price) > 0 ? min(floatval($inst->cash_price), $effectiveTotal) : $effectiveTotal;
            }

            $newRemaining = max(0, round($effectiveTotal - $totalCollected, 2));
            $isWrittenOff = Schema::hasColumn('installments', 'close_reason') && ($inst->close_reason ?? '') === 'written_off';
            $status = ($newRemaining <= 0.001) ? 'paid' : 'active';

            $updateFields = [
                'customer_name'        => $project->client_name,
                'customer_phone'       => $project->client_phone,
                'product_name'         => $isInstPlan ? "أقساط مشروع مقاولة: {$project->name}" : "مشروع مقاولات: {$project->name}",
                'cash_price'           => $cashPrice,
                'remaining_after_down' => max(0, round($effectiveTotal - $downPayment, 2)),
                'total_after_interest' => $effectiveTotal,
                'profit'               => $isInstPlan ? round($expectedProfit + max(0, $effectiveTotal - $cashPrice), 2) : $expectedProfit,
                'updated_at'           => now(),
            ];
            if (!$isWrittenOff) {
                $updateFields['remaining_balance'] = $newRemaining;
                $updateFields['status'] = $status;
                if (Schema::hasColumn('installments', 'close_reason')) {
                    $updateFields['close_reason'] = $newRemaining <= 0.001 ? 'paid' : null;
                }
            }
            DB::table('installments')->where('id', $inst->id)->update($updateFields);

            // Sync all installment payments into cont_invoices
            $instPayments = DB::table('installment_payments')->where('installment_id', $inst->id)->get();
            $validPaymentIds = [];

            foreach ($instPayments as $ip) {
                if (floatval($ip->amount_paid) <= 0) continue;
                $validPaymentIds[] = $ip->id;

                $alreadyInvoiced = DB::table('cont_invoices')
                    ->where('project_id', $projectId)
                    ->where(function($q) use ($ip) {
                        $q->where('notes', 'like', "%إيصال #{$ip->id}%")
                          ->orWhere('invoice_number', 'REC-PAY-' . $ip->id);
                    })->exists();

                if (!$alreadyInvoiced) {
                    $payDate = $ip->payment_date ?: now();
                    DB::table('cont_invoices')->insert([
                        'project_id'          => $projectId,
                        'invoice_number'      => 'REC-PAY-' . $ip->id,
                        'invoice_type'        => 'payment_receipt',
                        'is_tax'              => 0,
                        'tax_rate'            => 0,
                        'tax_amount'          => 0,
                        'show_supervision_fee'=> 1,
                        'supervision_amount'  => 0,
                        'discount_amount'     => floatval($ip->discount_applied ?? 0),
                        'total_billed'        => $ip->amount_paid,
                        'paid_amount'         => $ip->amount_paid,
                        'remaining_balance'   => 0,
                        'installment_id'      => $inst->id,
                        'notes'               => "تحصيل دفعة من شاشة المستحقات (إيصال #{$ip->id})" . ($ip->notes ? " - {$ip->notes}" : ""),
                        'created_by'          => session('auth_user')?->id ?? 1,
                        'created_at'          => $payDate,
                        'updated_at'          => $payDate,
                    ]);
                }
            }

            // Remove any orphan mirrored invoices if an installment payment was reversed or deleted
            $orphanInvoices = DB::table('cont_invoices')
                ->where('project_id', $projectId)
                ->where('installment_id', $inst->id)
                ->where('invoice_type', 'payment_receipt')
                ->get();
            foreach ($orphanInvoices as $oi) {
                if (str_starts_with($oi->invoice_number ?? '', 'DOWN-PAY-') || str_contains($oi->notes ?? '', 'محتسب كمقدم')) {
                    continue;
                }
                if (preg_match('/(?:إيصال #|REC-PAY-)(\d+)/', $oi->invoice_number . ' ' . $oi->notes, $matches)) {
                    $pid = intval($matches[1]);
                    if (!in_array($pid, $validPaymentIds)) {
                        DB::table('cont_invoices')->where('id', $oi->id)->delete();
                    }
                }
            }

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

            $instId = $this->syncProjectCustomerReceivable($project->id);

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
                'installment_id'      => $instId,
                'notes'               => $request->notes ?? "تحصيل دفعة نقدية من العميل",
                'created_by'          => $user?->id ?? 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            if ($instId && $paidAmount > 0) {
                $payId = DB::table('installment_payments')->insertGetId([
                    'installment_id'    => $instId,
                    'amount_paid'       => $paidAmount,
                    'payment_method_id' => $accountId,
                    'payment_date'      => now(),
                    'notes'             => "تحصيل دفعة نقدية من شاشة المقاولات (إيصال #{$invoiceNum})" . ($request->notes ? " - {$request->notes}" : ""),
                ]);
                DB::table('cont_invoices')->where('id', $invId)->update([
                    'notes' => ($request->notes ?? "تحصيل دفعة نقدية من العميل") . " (إيصال #{$payId})"
                ]);
                $this->syncProjectCustomerReceivable($project->id);
            }

            DB::commit();

            // Compute live client remaining / advance credit after payment
            $postOps = DB::table('cont_operations')->where('project_id', $project->id)->get();
            $postNetSelling = max(0.0, round(
                floatval($postOps->where('is_billable', 1)->sum('total_selling'))
                - floatval(DB::table('cont_discounts')->where('project_id', $project->id)->sum('amount')),
                2
            ));
            $postInst = $instId ? DB::table('installments')->where('id', $instId)->first() : null;
            $postInterestExtra = ($postInst && intval($postInst->installment_months ?? 0) > 0)
                ? max(0.0, round(floatval($postInst->total_after_interest ?? 0) - floatval($postInst->cash_price ?? 0), 2))
                : 0.0;
            $postInvoices = DB::table('cont_invoices')->where('project_id', $project->id)->get();
            $postClientPaid = $this->computeProjectClientPaid($postInst, $postInvoices);
            $postBal = $this->computeProjectBalanceMetrics($postNetSelling, $postInterestExtra, $postClientPaid);

            $this->logActivity('payment', 'contracting', "تم استلام دفعة مالية بقيمة {$paidAmount} ج من العميل: {$project->client_name} لمشروع: {$project->name}", [
                'project_id'    => $project->id,
                'invoice_id'    => $invId,
                'amount'        => $paidAmount,
                'client_credit' => $postBal['client_credit'],
            ]);

            $msg = $postBal['client_credit'] > 0
                ? 'تم تسجيل الدفعة وإيداعها في الخزينة بنجاح، وسُجّل للعميل رصيد مقدم بقيمة ' . number_format($postBal['client_credit'], 2) . ' ج تحت الحساب.'
                : 'تم تسجيل الدفعة وإيداعها في الخزينة بنجاح';

            return response()->json([
                'success'          => true,
                'message'          => $msg,
                'client_paid'      => $postClientPaid,
                'client_remaining' => $postBal['client_remaining'],
                'client_credit'    => $postBal['client_credit'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Refund Client Excess Advance Credit from Treasury (رد فائض الرصيد المقدم للعميل)
     */
    public function refundClientCredit(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|integer',
            'notes'      => 'nullable|string|max:500',
        ]);

        $projectId = intval($request->project_id);
        $amount    = round(floatval($request->amount), 2);
        $accountId = intval($request->account_id);
        $notes     = trim((string) $request->input('notes', ''));
        $user      = session('auth_user');

        DB::beginTransaction();
        try {
            $project = DB::table('cont_projects')->where('id', $projectId)->lockForUpdate()->first();
            if (!$project) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);
            }

            $account = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->first();
            if (!$account) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'الخزنة المختارة غير موجودة'], 404);
            }

            $this->syncProjectCustomerReceivable($projectId);

            $ops = DB::table('cont_operations')->where('project_id', $projectId)->get();
            $netSelling = max(0.0, round(
                floatval($ops->where('is_billable', 1)->sum('total_selling'))
                - floatval(DB::table('cont_discounts')->where('project_id', $projectId)->sum('amount')),
                2
            ));

            $inst = $this->consolidateProjectInstallments($projectId);
            $instPlanInv = DB::table('cont_invoices')
                ->where('project_id', $projectId)
                ->where('invoice_type', 'installment_contract')
                ->orderByDesc('id')
                ->first();
            $resolvedInstPlan = ($inst && intval($inst->installment_months ?? 0) > 0)
                ? $inst
                : (($instPlanInv && !empty($instPlanInv->installment_id))
                    ? DB::table('installments')->where('id', $instPlanInv->installment_id)->where('status', '!=', 'cancelled')->first()
                    : null);
            $instInterestExtra = $resolvedInstPlan
                ? max(0.0, round(floatval($resolvedInstPlan->total_after_interest ?? 0) - floatval($resolvedInstPlan->cash_price ?? 0), 2))
                : 0.0;

            $invoices = DB::table('cont_invoices')->where('project_id', $projectId)->get();
            $clientPaid = $this->computeProjectClientPaid($inst, $invoices);
            $bal = $this->computeProjectBalanceMetrics($netSelling, $instInterestExtra, $clientPaid);
            $availableCredit = $bal['client_credit'];

            if ($availableCredit <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد رصيد مقدم فائض للعميل في هذا المشروع لرده.',
                ], 422);
            }

            if ($amount > $availableCredit + 0.009) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'المبلغ المطلوب رده (' . number_format($amount, 2) . ' ج) أكبر من الرصيد المقدم المتاح للعميل (' . number_format($availableCredit, 2) . ' ج).',
                ], 422);
            }

            if (floatval($account->balance) < $amount - 0.009) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'رصيد الخزنة المختارة (' . number_format(floatval($account->balance), 2) . ' ج) لا يكفي لصرف مبلغ الرد.',
                ], 422);
            }

            DB::table('accounts')->where('id', $accountId)->decrement('balance', $amount);

            $txNotes = "رد فائض الرصيد المقدم للعميل ({$project->client_name}) - مشروع: {$project->name}";
            if ($notes !== '') {
                $txNotes .= " — {$notes}";
            }

            DB::table('financial_transactions')->insert([
                'type'            => 'expense',
                'subtype'         => 'client_refund',
                'amount'          => $amount,
                'from_account_id' => $accountId,
                'person_name'     => $project->client_name,
                'notes'           => $txNotes,
                'status'          => 'active',
                'ref_id'          => $project->id,
                'ref_type'        => 'cont_client_refund',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $refundInvNum = 'CONT-REF-' . strtoupper(Str::random(6));
            $invNotes = 'رد فائض الرصيد المقدم للعميل' . ($notes !== '' ? " - {$notes}" : '');

            $invId = DB::table('cont_invoices')->insertGetId([
                'project_id'           => $project->id,
                'invoice_number'       => $refundInvNum,
                'invoice_type'         => 'client_refund',
                'is_tax'               => 0,
                'tax_rate'             => 0,
                'tax_amount'           => 0,
                'show_supervision_fee' => 1,
                'supervision_amount'   => 0,
                'discount_amount'      => 0,
                'total_billed'         => $amount,
                'paid_amount'          => -$amount,
                'remaining_balance'    => 0,
                'installment_id'       => $inst?->id,
                'notes'                => $invNotes,
                'created_by'           => $user?->id ?? 1,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            $this->syncProjectCustomerReceivable($projectId);

            $updatedInvoices = DB::table('cont_invoices')->where('project_id', $projectId)->get();
            $updatedInst = $this->consolidateProjectInstallments($projectId);
            $newClientPaid = $this->computeProjectClientPaid($updatedInst, $updatedInvoices);
            $newBal = $this->computeProjectBalanceMetrics($netSelling, $instInterestExtra, $newClientPaid);

            DB::commit();

            $this->logActivity('refund', 'contracting', "تم رد فائض الرصيد المقدم بقيمة {$amount} ج للعميل: {$project->client_name} في مشروع: {$project->name}", [
                'project_id'    => $project->id,
                'invoice_id'    => $invId,
                'amount'        => $amount,
                'client_credit' => $newBal['client_credit'],
            ]);

            return response()->json([
                'success'          => true,
                'message'          => 'تم رد مبلغ ' . number_format($amount, 2) . ' ج للعميل وخصمه من الخزينة بنجاح.',
                'client_paid'      => $newClientPaid,
                'client_remaining' => $newBal['client_remaining'],
                'client_credit'    => $newBal['client_credit'],
            ]);
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
            'interest_rate'      => 'nullable|numeric|min:0',
        ]);

        $project = DB::table('cont_projects')->where('id', $request->project_id)->first();
        if (!$project) return response()->json(['success' => false, 'message' => 'المشروع غير موجود'], 404);

        if (($project->status ?? 'active') !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تحويل المشروع لعقد تقسيط إلا عندما تكون حالة المشروع (مكتمل)! يرجى تغيير حالة المشروع إلى مكتمل أولاً.',
            ], 422);
        }

        $months = intval($request->installment_months);
        $baseFinancedAmount = round(floatval($request->amount), 2);
        $interestRate = max(0, round(floatval($request->input('interest_rate', 0)), 2));
        $interestValue = round($baseFinancedAmount * ($interestRate / 100), 2);
        $financedTotal = round($baseFinancedAmount + $interestValue, 2);
        $extraDown = max(0, round(floatval($request->input('down_payment', 0)), 2));
        $monthly = round($financedTotal / $months, 2);
        $startDate = $request->input('start_date') ?: now()->toDateString();

        DB::beginTransaction();
        try {
            $installmentId = $this->syncProjectCustomerReceivable($project->id);
            $inst = $installmentId
                ? DB::table('installments')->where('id', $installmentId)->lockForUpdate()->first()
                : null;

            $existingPlanInv = DB::table('cont_invoices')
                ->where('project_id', $project->id)
                ->where('invoice_type', 'installment_contract')
                ->orderByDesc('id')
                ->first();

            $ops = DB::table('cont_operations')->where('project_id', $project->id)->get();
            $totalBillable = floatval($ops->where('is_billable', 1)->sum('total_selling'));
            $projectDiscounts = floatval(DB::table('cont_discounts')->where('project_id', $project->id)->sum('amount'));
            $netBilled = max(0, round($totalBillable - $projectDiscounts, 2));
            $totalCost = floatval($ops->sum('total_cost'));
            $expectedProfit = max(0, round($netBilled - $totalCost, 2));

            $existingDown = floatval($inst?->down_payment ?? 0);
            $existingInstDiscount = floatval($inst?->discount ?? 0);
            $newlyAbsorbedCash = 0.0;
            $newlyAbsorbedDisc = 0.0;
            $postPaymentIds = [];

            if ($inst) {
                $absorbed = $this->absorbProjectPriorPaymentsIntoDownPayment(
                    intval($project->id),
                    intval($inst->id),
                    $inst,
                    $existingPlanInv?->created_at
                );
                $newlyAbsorbedCash = $absorbed['newly_absorbed_cash'];
                $newlyAbsorbedDisc = $absorbed['newly_absorbed_disc'];
                $postPaymentIds = $absorbed['post_payment_ids'];
            } else {
                $unlinkedReceipts = DB::table('cont_invoices')
                    ->where('project_id', $project->id)
                    ->where('invoice_type', 'payment_receipt')
                    ->whereNull('installment_id')
                    ->get();
                $unlinkedRefunds = DB::table('cont_invoices')
                    ->where('project_id', $project->id)
                    ->where('invoice_type', 'client_refund')
                    ->whereNull('installment_id')
                    ->get();
                $newlyAbsorbedCash = max(0.0, round(
                    floatval($unlinkedReceipts->sum('paid_amount')) - abs(floatval($unlinkedRefunds->sum('paid_amount'))),
                    2
                ));
            }

            $downPayment = round($existingDown + $newlyAbsorbedCash + $extraDown, 2);
            $totalInstDiscount = round($existingInstDiscount + $newlyAbsorbedDisc, 2);

            $postPaymentsSum = 0.0;
            $postDiscountsSum = 0.0;
            if ($inst && !empty($postPaymentIds)) {
                $postPaymentsSum = floatval(DB::table('installment_payments')->whereIn('id', $postPaymentIds)->sum('amount_paid'));
                $postDiscountsSum = floatval(DB::table('installment_payments')->whereIn('id', $postPaymentIds)->sum('discount_applied'));
            }

            $cashPriceTotal = round($downPayment + $postPaymentsSum + $postDiscountsSum + $baseFinancedAmount, 2);
            $effectiveNetBilled = max(0, round($netBilled - $totalInstDiscount - $postDiscountsSum, 2));
            $cashPrice = ($effectiveNetBilled > 0 && $cashPriceTotal > $effectiveNetBilled)
                ? $effectiveNetBilled
                : $cashPriceTotal;
            $totalAfterInterest = round($cashPriceTotal + $interestValue, 2);
            $interestExtra = max(0, round($totalAfterInterest - $cashPrice, 2));
            $contractProfit = round($expectedProfit + $interestExtra, 2);
            $remainingAfterDown = max(0, round($totalAfterInterest - $downPayment, 2));

            $storedInterestRate = $interestRate;
            if ($interestRate == 0.0 && $postPaymentsSum > 0 && $inst && floatval($inst->interest_rate ?? 0) > 0 && abs($baseFinancedAmount - floatval($inst->remaining_balance ?? 0)) <= 0.01) {
                $storedInterestRate = floatval($inst->interest_rate);
            }

            if ($inst) {
                $upd = [
                    'sale_type'            => 'contracting',
                    'source_type'          => 'cont_project',
                    'source_id'            => $project->id,
                    'customer_name'        => $project->client_name,
                    'customer_phone'       => $project->client_phone,
                    'product_name'         => "أقساط مشروع مقاولة: {$project->name}",
                    'category'             => 'مقاولات وتشطيبات',
                    'start_date'           => $startDate,
                    'cash_price'           => $cashPrice,
                    'discount'             => $totalInstDiscount,
                    'down_payment'         => $downPayment,
                    'remaining_after_down' => $remainingAfterDown,
                    'installment_months'   => $months,
                    'interest_rate'        => $storedInterestRate,
                    'total_after_interest' => $totalAfterInterest,
                    'monthly_installment'  => $monthly,
                    'remaining_balance'    => $financedTotal,
                    'profit'               => $contractProfit,
                    'due_day'              => intval($inst->due_day ?: 1),
                    'status'               => 'active',
                    'updated_at'           => now(),
                ];
                if (Schema::hasColumn('installments', 'close_reason')) {
                    $upd['close_reason'] = null;
                }
                DB::table('installments')->where('id', $inst->id)->update($upd);
                $installmentId = $inst->id;
            } else {
                $installmentId = DB::table('installments')->insertGetId([
                    'sale_type'            => 'contracting',
                    'source_type'          => 'cont_project',
                    'source_id'            => $project->id,
                    'customer_name'        => $project->client_name,
                    'customer_phone'       => $project->client_phone,
                    'product_name'         => "أقساط مشروع مقاولة: {$project->name}",
                    'category'             => 'مقاولات وتشطيبات',
                    'start_date'           => $startDate,
                    'cash_price'           => $cashPrice,
                    'discount'             => $totalInstDiscount,
                    'down_payment'         => $downPayment,
                    'remaining_after_down' => $remainingAfterDown,
                    'installment_months'   => $months,
                    'interest_rate'        => $storedInterestRate,
                    'total_after_interest' => $totalAfterInterest,
                    'monthly_installment'  => $monthly,
                    'remaining_balance'    => $financedTotal,
                    'due_day'              => 1,
                    'status'               => 'active',
                    'profit'               => $contractProfit,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                // Attach any unlinked payment receipts that existed before the installment row was created
                $unlinkedReceipts = DB::table('cont_invoices')
                    ->where('project_id', $project->id)
                    ->where('invoice_type', 'payment_receipt')
                    ->whereNull('installment_id')
                    ->get();
                foreach ($unlinkedReceipts as $ur) {
                    DB::table('cont_invoices')->where('id', $ur->id)->update([
                        'installment_id' => $installmentId,
                        'notes'          => trim(($ur->notes ?: 'دفعة نقدية') . ' (محتسب كمقدم تقسيط)'),
                        'updated_at'     => now(),
                    ]);
                }
                $unlinkedRefunds = DB::table('cont_invoices')
                    ->where('project_id', $project->id)
                    ->where('invoice_type', 'client_refund')
                    ->whereNull('installment_id')
                    ->get();
                foreach ($unlinkedRefunds as $rf) {
                    DB::table('cont_invoices')->where('id', $rf->id)->update([
                        'installment_id' => $installmentId,
                        'notes'          => trim(($rf->notes ?: 'رد فائض للعميل') . ' (محتسب ضمن تسوية مقدم التقسيط)'),
                        'updated_at'     => now(),
                    ]);
                }
            }

            // Guarantee NO leftover receivable row (installment_months <= 0) remains in /debts for this project
            DB::table('installments')
                ->where('source_type', 'cont_project')
                ->where('source_id', $project->id)
                ->where('id', '!=', $installmentId)
                ->delete();

            $planNotes = "تحويل مبلغ {$baseFinancedAmount} ج"
                . ($interestRate > 0 ? " + فائدة {$interestRate}% ({$interestValue} ج) = {$financedTotal} ج" : '')
                . " إلى {$months} قسط شهري بقيمة {$monthly} ج"
                . ($downPayment > 0 ? " | مقدم محتسب من الدفعات السابقة: {$downPayment} ج" : '');

            if ($existingPlanInv) {
                DB::table('cont_invoices')->where('id', $existingPlanInv->id)->update([
                    'total_billed'      => $financedTotal,
                    'paid_amount'       => 0,
                    'remaining_balance' => $financedTotal,
                    'installment_id'    => $installmentId,
                    'notes'             => $planNotes,
                    'updated_at'        => now(),
                ]);
            } else {
                DB::table('cont_invoices')->insert([
                    'project_id'          => $project->id,
                    'invoice_number'      => 'INST-PLAN-' . $installmentId,
                    'invoice_type'        => 'installment_contract',
                    'total_billed'        => $financedTotal,
                    'paid_amount'         => 0,
                    'remaining_balance'   => $financedTotal,
                    'installment_id'      => $installmentId,
                    'notes'               => $planNotes,
                    'created_by'          => session('auth_user')?->id ?? 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            DB::commit();

            $this->logActivity('create', 'installments', "تم إنشاء جدول تقسيط لمشروع المقاولة ({$project->name}) للعميل {$project->client_name} بقيمة {$financedTotal} ج على {$months} شهر" . ($interestRate > 0 ? " (فائدة {$interestRate}%: {$interestValue} ج)" : '') . ($downPayment > 0 ? " (مقدم: {$downPayment} ج)" : ''), [
                'project_id'     => $project->id,
                'installment_id' => $installmentId,
                'down_payment'   => $downPayment,
                'interest_rate'  => $storedInterestRate,
                'interest_value' => $interestValue,
                'months'         => $months,
                'monthly'        => $monthly,
            ]);

            $msg = "تم تحويل المبلغ لجدول الأقساط وترحيله لمنظومة التحصيل بنجاح (عقد #{$installmentId})";
            if ($interestRate > 0) {
                $msg .= " بنسبة فائدة {$interestRate}% (" . number_format($interestValue, 2) . " ج)";
            }
            if ($downPayment > 0) {
                $msg .= " وتم تجميع الدفعات السابقة واحتسابها كمقدم (" . number_format($downPayment, 2) . " ج)";
            }
            $msg .= " وإزالة العميل من شاشة المستحقات.";

            return response()->json([
                'success' => true,
                'message' => $msg,
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

        $cleanName = trim($request->name);
        $existing = DB::table('cont_directory')
            ->where('type', $request->type)
            ->where('name', $cleanName)
            ->first();

        if ($existing) {
            DB::table('cont_directory')->where('id', $existing->id)->update([
                'phone'      => $request->filled('phone') ? $request->phone : $existing->phone,
                'specialty'  => $request->filled('specialty') ? $request->specialty : $existing->specialty,
                'notes'      => $request->filled('notes') ? $request->notes : $existing->notes,
                'updated_at' => now(),
            ]);
            return response()->json([
                'success' => true,
                'id'      => $existing->id,
                'message' => 'الاسم مسجل بالفعل في الدليل، تم تحديث بياناته بنجاح',
            ]);
        }

        $id = DB::table('cont_directory')->insertGetId([
            'type'         => $request->type,
            'name'         => $cleanName,
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
     * Update Person in Directory (and propagate name changes to operations/debts)
     */
    public function updateDirectoryPerson(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:supplier,technician,marketer,client',
        ]);

        $person = DB::table('cont_directory')->where('id', $id)->first();
        if (!$person) {
            return response()->json(['success' => false, 'message' => 'جهة الاتصال غير موجودة'], 404);
        }

        $oldName = trim($person->name);
        $newName = trim($request->name);

        DB::beginTransaction();
        try {
            DB::table('cont_directory')->where('id', $id)->update([
                'type'       => $request->type,
                'name'       => $newName,
                'phone'      => $request->phone,
                'specialty'  => $request->specialty,
                'notes'      => $request->notes,
                'updated_at' => now(),
            ]);

            if ($oldName !== '' && $newName !== '' && $oldName !== $newName) {
                DB::table('cont_operations')->where('person_name', $oldName)->update(['person_name' => $newName]);
                DB::table('company_debts')->where('creditor_name', $oldName)->update(['creditor_name' => $newName]);
                if (Schema::hasTable('cont_material_returns')) {
                    DB::table('cont_material_returns')->where('supplier_name', $oldName)->update(['supplier_name' => $newName]);
                }
                if ($request->type === 'client') {
                    DB::table('cont_projects')->where('client_name', $oldName)->update(['client_name' => $newName]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تحديث بيانات جهة الاتصال بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'تعذر تحديث البيانات: ' . $e->getMessage()], 500);
        }
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

        // Sync customer receivables and payments from debts/installments screen
        $this->syncProjectCustomerReceivable($id);

        // تصحيح تلقائي لحركات المصنعية المسجلة سابقاً بالمتر وحفظت بالخطأ مقطوع أو عدد
        DB::table('cont_operations')
            ->where('project_id', $id)
            ->where('op_type', 'technician')
            ->whereIn('unit', ['مقطوع', 'عدد'])
            ->where('quantity', '>', 1)
            ->update(['unit' => 'متر']);

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

        // Fetch installments payments if linked to capture receivables screen collections
        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $id)
            ->where('status', '!=', 'cancelled')
            ->first();
        $clientPaid = $this->computeProjectClientPaid($inst, $invoices);
        $baseRemaining = max(0, round($netSelling - $clientPaid, 2));
        $netProfit = $netSelling - $totalCost;

        // سجل المرتجعات لكل حركة (يظهر في سجل العمليات)
        $this->ensureMaterialReturnsTableExists();
        $returnsByOp = DB::table('cont_material_returns')
            ->where('project_id', $id)
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('operation_id');

        // Group materials
        $materialsSummary = $operations->where('op_type', 'material')
            ->groupBy('name_or_title')
            ->map(function($rows, $name) {
                return [
                    'name'       => $name,
                    'unit'       => trim((string)($rows->first()->unit ?? '')),
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

        $dirCredits = collect();
        if (Schema::hasTable('cont_directory') && Schema::hasColumn('cont_directory', 'balance_credit')) {
            $dirCredits = DB::table('cont_directory')->pluck('balance_credit', 'name');
        }

        $this->ensureWorkerPaymentsTableExists();
        $projWorkerPaymentsByName = Schema::hasTable('cont_worker_payments')
            ? DB::table('cont_worker_payments')
                ->leftJoin('cont_project_items', 'cont_worker_payments.item_id', '=', 'cont_project_items.id')
                ->where('cont_worker_payments.project_id', $id)
                ->where('cont_worker_payments.person_type', 'technician')
                ->select('cont_worker_payments.*', 'cont_project_items.title as item_title')
                ->orderBy('cont_worker_payments.payment_date', 'desc')
                ->orderBy('cont_worker_payments.id', 'desc')
                ->get()
                ->groupBy(fn($r) => trim($r->person_name))
            : collect();

        $projSupplierPaymentsByName = Schema::hasTable('cont_worker_payments')
            ? DB::table('cont_worker_payments')
                ->leftJoin('cont_project_items', 'cont_worker_payments.item_id', '=', 'cont_project_items.id')
                ->leftJoin('cont_operations', 'cont_worker_payments.operation_id', '=', 'cont_operations.id')
                ->where('cont_worker_payments.project_id', $id)
                ->where('cont_worker_payments.person_type', 'supplier')
                ->select(
                    'cont_worker_payments.*',
                    'cont_project_items.title as item_title',
                    'cont_operations.invoice_ref as op_invoice_ref'
                )
                ->orderBy('cont_worker_payments.payment_date', 'desc')
                ->orderBy('cont_worker_payments.id', 'desc')
                ->get()
                ->groupBy(fn($r) => trim($r->person_name))
            : collect();

        // Group technicians
        $techsSummary = $operations->whereIn('op_type', ['technician', 'labor'])
            ->groupBy('person_name')
            ->map(function($rows, $name) use ($calcOpLivePaid, $dirCredits, $projWorkerPaymentsByName) {
                $totalCost = round($rows->sum('total_cost'), 2);
                $livePaid  = round($rows->sum(fn($r) => $calcOpLivePaid($r)), 2);
                $liveDebt  = max(0, round($totalCost - $livePaid, 2));
                $techPays  = ($projWorkerPaymentsByName->get(trim((string) $name)) ?? collect())->values();
                return [
                    'name'           => $name ?: 'فني غير محدد',
                    'jobs_count'     => $rows->count(),
                    'payments_count' => $techPays->count(),
                    'payments'       => $techPays,
                    'total_cost'     => $totalCost,
                    'paid'           => $livePaid,
                    'debt'           => $liveDebt,
                    'credit'         => round(floatval($dirCredits[$name] ?? 0), 2),
                ];
            })->values();

        // Top Suppliers
        $suppliersSummary = $operations->where('op_type', 'material')
            ->whereNotNull('person_name')
            ->groupBy('person_name')
            ->map(function($rows, $name) use ($calcOpLivePaid, $dirCredits, $projSupplierPaymentsByName) {
                $totalAmount = round($rows->sum('total_cost'), 2);
                $livePaid    = round($rows->sum(fn($r) => $calcOpLivePaid($r)), 2);
                $liveDebt    = max(0, round($totalAmount - $livePaid, 2));
                $supPays     = ($projSupplierPaymentsByName->get(trim((string) $name)) ?? collect())->values();
                return [
                    'name'           => $name,
                    'orders_count'   => $rows->count(),
                    'payments_count' => $supPays->count(),
                    'payments'       => $supPays,
                    'total_amount'   => $totalAmount,
                    'paid'           => $livePaid,
                    'debt'           => $liveDebt,
                    'credit'         => round(floatval($dirCredits[$name] ?? 0), 2),
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

        $instPlanInv = DB::table('cont_invoices')
            ->where('project_id', $id)
            ->where('invoice_type', 'installment_contract')
            ->orderByDesc('id')
            ->first();
        $resolvedInstPlan = ($inst && ($inst->status ?? '') !== 'cancelled' && intval($inst->installment_months ?? 0) > 0)
            ? $inst
            : (($instPlanInv && !empty($instPlanInv->installment_id))
                ? DB::table('installments')->where('id', $instPlanInv->installment_id)->where('status', '!=', 'cancelled')->first()
                : null);
        $isInstConverted = ($resolvedInstPlan && intval($resolvedInstPlan->installment_months ?? 0) > 0) || !empty($instPlanInv);
        $instCashPaidAfter = $resolvedInstPlan
            ? floatval(DB::table('installment_payments')->where('installment_id', $resolvedInstPlan->id)->sum('amount_paid'))
            : 0.0;
        $instCashPaidAll = $inst
            ? floatval(DB::table('installment_payments')->where('installment_id', $inst->id)->sum('amount_paid'))
            : 0.0;
        $receiptCashSum = floatval($invoices->where('invoice_type', '!=', 'installment_contract')->sum('paid_amount'));
        $directCashPaid = floatval($invoices->where('invoice_type', '!=', 'installment_contract')->where('invoice_type', '!=', 'client_refund')->whereNull('installment_id')->sum('paid_amount'));
        $unabsorbedRefunds = $this->sumUnabsorbedClientRefunds($invoices);
        $preConvCashPaid = max(0.0, round(max(
            $receiptCashSum,
            (($inst ? floatval($inst->down_payment ?? 0) + $instCashPaidAll : 0.0) + $directCashPaid) - $unabsorbedRefunds
        ), 2));
        $planDownPayment = $isInstConverted
            ? floatval($resolvedInstPlan->down_payment ?? 0)
            : $preConvCashPaid;
        $planInterestRate = $resolvedInstPlan ? floatval($resolvedInstPlan->interest_rate ?? 0) : 0.0;
        $planInterestAmount = $resolvedInstPlan
            ? max(0, round(floatval($resolvedInstPlan->total_after_interest ?? 0) - floatval($resolvedInstPlan->cash_price ?? 0), 2))
            : 0.0;
        $bal = $this->computeProjectBalanceMetrics($netSelling, $planInterestAmount, $clientPaid);
        $clientRemaining = $bal['client_remaining'];
        $clientCredit = $bal['client_credit'];
        $isCompletedStatus = ($project->status ?? 'active') === 'completed';
        $lockState = [
            'is_completed'   => $isCompletedStatus,
            'is_installment' => $isInstConverted,
            'is_locked'      => $isCompletedStatus || $isInstConverted,
            'lock_type'      => $isInstConverted ? 'installment' : ($isCompletedStatus ? 'completed' : null),
            'lock_message'   => $isInstConverted
                ? 'هذا المشروع محوّل إلى عقد تقسيط، لذلك لا يمكن تغيير حالته والحذف والتعديل والمرتجعات مقفولة.'
                : ($isCompletedStatus
                    ? 'حالة المشروع (مكتمل) حالياً — الحذف والتعديل والمرتجعات مقفولة وتعود تلقائياً عند تنشيط المشروع.'
                    : null),
        ];

        return response()->json([
            'success' => true,
            'project' => $project,
            'locks'   => $lockState,
            'installment_plan' => [
                'is_converted'           => $isInstConverted,
                'installment_id'         => $resolvedInstPlan->id ?? ($instPlanInv->installment_id ?? null),
                'installment_months'     => intval($resolvedInstPlan->installment_months ?? 0),
                'monthly_installment'    => floatval($resolvedInstPlan->monthly_installment ?? 0),
                'interest_rate'          => $planInterestRate,
                'interest_amount'        => $planInterestAmount,
                'base_remaining'         => $baseRemaining,
                'down_payment'           => $planDownPayment,
                'post_installments_paid' => $instCashPaidAfter,
                'start_date'             => $resolvedInstPlan->start_date ?? null,
            ],
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
                'client_credit'      => $clientCredit,
                'net_profit'         => $netProfit,
                'workers_count'      => $techsSummary->count(),
            ],
            'materials'  => $materialsSummary,
            'techs'      => $techsSummary,
            'suppliers'  => $suppliersSummary,
            'items'      => $itemsSummary,
            'operations' => $operations->map(function ($o) use ($returnsByOp, $calcOpLivePaid) {
                $livePaid = $calcOpLivePaid($o);
                $liveRem  = max(0, round(floatval($o->total_cost) - $livePaid, 2));
                $o->paid_amount    = $livePaid;
                $o->remaining_debt = $liveRem;
                if (!empty($o->debt_id) || in_array($o->payment_method, ['later', 'partial', 'cash'], true)) {
                    $o->payment_method = $liveRem <= 0.01 ? 'cash' : ($livePaid > 0.01 ? 'partial' : 'later');
                }
                $o->returns = ($returnsByOp->get($o->id) ?? collect())->values();
                return $o;
            })->values(),
            'discounts'  => $discounts,
        ]);
    }

    /**
     * Get items of a specific material invoice
     */
    public function getInvoiceDetailsAjax(Request $request)
    {
        $this->ensureMaterialReturnsTableExists();

        $invoiceRef = trim($request->input('invoice_ref', ''));
        $projectId = $request->input('project_id');
        $operationId = $request->input('operation_id');

        $query = DB::table('cont_operations')
            ->leftJoin('cont_projects', 'cont_operations.project_id', '=', 'cont_projects.id')
            ->leftJoin('cont_project_items', 'cont_operations.item_id', '=', 'cont_project_items.id');

        if (!empty($operationId) && empty($invoiceRef)) {
            $query->where('cont_operations.id', intval($operationId));
        } else {
            $query->where('cont_operations.invoice_ref', $invoiceRef);
            if ($projectId) {
                $query->where('cont_operations.project_id', $projectId);
            }
        }

        $items = $query->select(
            'cont_operations.*',
            'cont_projects.name as project_name',
            'cont_projects.client_name',
            'cont_project_items.title as item_title'
        )->orderBy('cont_operations.id', 'asc')->get();

        $opIds = $items->pluck('id')->all();
        $debtIds = $items->pluck('debt_id')
            ->merge(!empty($opIds) ? DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $opIds)->pluck('id') : collect())
            ->filter()
            ->unique()
            ->values()
            ->all();

        $liveDebt = floatval($items->sum('remaining_debt'));
        if (!empty($debtIds)) {
            $debts = DB::table('company_debts')->whereIn('id', $debtIds)->get();
            if ($debts->isNotEmpty()) {
                $liveDebt = floatval($debts->sum('remaining_balance'));
            } else {
                $liveDebt = 0;
            }
        }

        $totalCost = floatval($items->sum('total_cost'));
        $livePaid = max(0, round($totalCost - $liveDebt, 2));

        $returnsHistory = collect();
        if (!empty($invoiceRef)) {
            $retQuery = DB::table('cont_material_returns')->where('invoice_ref', $invoiceRef);
            if ($projectId) $retQuery->where('project_id', $projectId);
            $returnsHistory = $retQuery->orderBy('id', 'desc')->get();
        } elseif (!empty($operationId)) {
            $returnsHistory = DB::table('cont_material_returns')->where('operation_id', intval($operationId))->orderBy('id', 'desc')->get();
        }

        $resolvedProjId = $items->first()?->project_id ?? $projectId;
        $projectObj = $resolvedProjId ? DB::table('cont_projects')->where('id', $resolvedProjId)->first() : null;
        $lockState = $this->getProjectLockState($projectObj);
        $lockMessage = $this->buildProjectLockMessage($projectObj, 'عمل مرتجع خامات');

        return response()->json([
            'success'             => true,
            'invoice_ref'         => $invoiceRef ?: ($items->first()?->invoice_ref ?: ('حركة #' . ($items->first()?->id ?? ''))),
            'project_id'          => $resolvedProjId,
            'project_status'      => $projectObj?->status ?? 'active',
            'is_installment_plan' => $lockState['is_installment'],
            'is_locked'           => $lockState['is_locked'],
            'lock_message'        => $lockMessage,
            'supplier'            => $items->first()?->person_name ?? 'مورد غير محدد',
            'project'         => $items->first()?->project_name ?? '-',
            'client'          => $items->first()?->client_name ?? '-',
            'created_at'      => $items->first()?->created_at ?? '-',
            'payment_method'  => $items->first()?->payment_method ?? 'later',
            'total_cost'      => $totalCost,
            'total_sell'      => floatval($items->sum('total_selling')),
            'total_paid'      => $livePaid,
            'total_debt'      => $liveDebt,
            'items'           => $items,
            'returns_history' => $returnsHistory,
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
                ->where('cont_operations.quantity', '>', 0)
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
            ->where('quantity', '>', 0)
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
            ->where('quantity', '>', 0)
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

        // Sync receivables and payments from installments screen
        $this->syncProjectCustomerReceivable($id);

        DB::table('cont_operations')
            ->where('project_id', $id)
            ->where('op_type', 'technician')
            ->whereIn('unit', ['مقطوع', 'عدد'])
            ->where('quantity', '>', 1)
            ->update(['unit' => 'متر']);

        $type = in_array($request->input('type'), ['detailed', 'summary', 'internal'], true)
            ? $request->input('type')
            : 'detailed';
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

        // In customer statement, exclude completely returned materials (quantity == 0) and display net quantities
        $operations = $operations->filter(function ($op) {
            return $op->op_type !== 'material' || floatval($op->quantity) > 0;
        })->values();

        $discounts = DB::table('cont_discounts')->where('project_id', $id)->orderBy('created_at', 'desc')->get();
        $invoices = DB::table('cont_invoices')->where('project_id', $id)->orderBy('created_at', 'asc')->get();

        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $id)
            ->where('status', '!=', 'cancelled')
            ->first();

        $totalDiscounts = $selectedItem ? 0 : $discounts->sum('amount');
        $totalPaid = $selectedItem ? 0 : $this->computeProjectClientPaid($inst, $invoices);

        // Customer payments list (receipts & excess refunds)
        $customerPayments = $selectedItem ? collect() : $invoices->where('invoice_type', '!=', 'installment_contract')->where('paid_amount', '!=', 0);

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

        if ($personType !== 'supplier') {
            DB::table('cont_operations')
                ->where('op_type', 'technician')
                ->where('person_name', $personName)
                ->whereIn('unit', ['مقطوع', 'عدد'])
                ->where('quantity', '>', 1)
                ->update(['unit' => 'متر']);
        }

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
        $otherRoleLinkedDebtIds = DB::table('cont_operations')
            ->whereNotNull('debt_id')
            ->whereNotIn('op_type', $opTypes)
            ->pluck('debt_id')
            ->unique()
            ->all();

        $companyDebts = DB::table('company_debts')
            ->where(function ($q) use ($personName, $allOpDebtIds, $otherRoleLinkedDebtIds) {
                $q->where(function ($sq) use ($personName, $otherRoleLinkedDebtIds) {
                    $sq->where('creditor_name', $personName)
                       ->where(function ($cq) {
                           $cq->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
                              ->orWhere('category', 'مقاولات');
                       });
                    if (!empty($otherRoleLinkedDebtIds)) {
                        $sq->whereNotIn('id', $otherRoleLinkedDebtIds);
                    }
                });
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

        // Build chronological payments history
        $this->ensureWorkerPaymentsTableExists();
        $paymentRecords = collect();

        $wpQuery = DB::table('cont_worker_payments')
            ->leftJoin('cont_projects', 'cont_worker_payments.project_id', '=', 'cont_projects.id')
            ->leftJoin('cont_project_items', 'cont_worker_payments.item_id', '=', 'cont_project_items.id')
            ->leftJoin('cont_operations', 'cont_worker_payments.operation_id', '=', 'cont_operations.id')
            ->where('cont_worker_payments.person_name', $personName)
            ->where('cont_worker_payments.person_type', $personType);
        if ($selectedProject) {
            $wpQuery->where('cont_worker_payments.project_id', $selectedProject->id);
        }
        $workerPayRows = $wpQuery->select(
                'cont_worker_payments.*',
                'cont_projects.name as project_name',
                'cont_project_items.title as item_title',
                'cont_operations.invoice_ref as op_invoice_ref'
            )
            ->orderBy('cont_worker_payments.payment_date', 'asc')
            ->orderBy('cont_worker_payments.id', 'asc')
            ->get();

        foreach ($workerPayRows as $wp) {
            $paymentRecords->push((object) [
                'date'            => $wp->payment_date ?: $wp->created_at,
                'amount'          => round(floatval($wp->amount), 2),
                'discount'        => round(floatval($wp->discount ?? 0), 2),
                'discount_reason' => $wp->discount_reason ?? null,
                'project_name'    => $wp->project_name ?? null,
                'item_title'      => $wp->item_title ?? null,
                'notes'           => $wp->notes ?? null,
            ]);
        }

        // 1. Immediate payments at operation/invoice creation (for supplier cash invoices, or if no cont_worker_payments exist for technician)
        if ($personType === 'supplier' || $workerPayRows->isEmpty()) {
            $coveredOpIds = $workerPayRows->pluck('operation_id')->filter()->unique()->flip()->all();
            $coveredCashInvRefs = $workerPayRows
                ->filter(fn($wp) => empty($wp->debt_id) && !empty($wp->op_invoice_ref))
                ->pluck('op_invoice_ref')
                ->unique()
                ->flip()
                ->all();
            $uncoveredPaidOps = $operations->filter(function ($op) use ($coveredOpIds, $coveredCashInvRefs) {
                if (floatval($op->paid_amount) <= 0) {
                    return false;
                }
                if (isset($coveredOpIds[$op->id])) {
                    return false;
                }
                if (empty($op->debt_id) && !empty($op->invoice_ref) && isset($coveredCashInvRefs[$op->invoice_ref])) {
                    return false;
                }
                return true;
            });

            $paidOpsGroups = $uncoveredPaidOps->groupBy(function ($op) {
                return !empty($op->invoice_ref)
                    ? ('INV_' . ($op->project_id ?: 0) . '_' . $op->invoice_ref)
                    : ('OP_' . $op->id);
            });

            foreach ($paidOpsGroups as $groupRows) {
                $firstRow = $groupRows->first();
                $paymentRecords->push((object) [
                    'date'            => $firstRow->created_at,
                    'amount'          => round($groupRows->sum('paid_amount'), 2),
                    'discount'        => 0.0,
                    'discount_reason' => null,
                    'project_name'    => $firstRow->project_name ?? null,
                    'item_title'      => $firstRow->item_title ?? null,
                    'notes'           => !empty($firstRow->invoice_ref) ? ("سداد فاتورة #" . $firstRow->invoice_ref) : ($firstRow->name_or_title ?? null),
                ]);
            }
        }

        // 2. Subsequent debt payments in financial_transactions
        $scopedDebtIds = $operations->pluck('debt_id')->filter()->unique()->values()->all();
        if (!$selectedProject) {
            $scopedDebtIds = array_values(array_unique(array_merge($scopedDebtIds, $companyDebts->pluck('id')->all())));
        }

        if (!empty($scopedDebtIds)) {
            $txQuery = $this->fetchSubsequentDebtTransactions($scopedDebtIds, 'asc');

            // Group FIFO split transactions executed in the same second into a single payment record
            $txGrouped = $txQuery->groupBy(function ($tx) {
                return ($tx->created_at ?? '') . '_' . ($tx->ref_type ?? '');
            });

            foreach ($txGrouped as $txRows) {
                $firstTx = $txRows->first();
                $isDisc = in_array($firstTx->ref_type, ['company_debt_payment_discount', 'earned_discount'], true);
                $paymentRecords->push((object) [
                    'date'            => $firstTx->created_at,
                    'amount'          => $isDisc ? 0.0 : round($txRows->sum('amount'), 2),
                    'discount'        => $isDisc ? round($txRows->sum('amount'), 2) : 0.0,
                    'discount_reason' => $isDisc ? ($firstTx->notes ?: 'خصم سداد مديونية') : null,
                    'project_name'    => null,
                    'item_title'      => null,
                    'notes'           => $firstTx->notes ?? 'سداد مديونية',
                ]);
            }
        }

        $paymentRecords = $paymentRecords->sortBy('date')->values();

        // Fetch supplier material returns if supplier
        $this->ensureMaterialReturnsTableExists();
        if (function_exists('ensureSupplierCreditSchema')) {
            ensureSupplierCreditSchema();
        }
        $supplierReturns = collect();
        if ($personType === 'supplier') {
            $retQuery = DB::table('cont_material_returns')->where('supplier_name', $personName);
            if ($selectedProject) {
                $retQuery->where('project_id', $selectedProject->id);
            }
            $supplierReturns = $retQuery->orderBy('created_at', 'asc')->get();
        }

        $personCreditBalance = round(floatval($person->balance_credit ?? 0), 2);
        $creditLogs = collect();
        if (Schema::hasTable('cont_supplier_credit_logs')) {
            $creditLogs = DB::table('cont_supplier_credit_logs')
                ->where('person_name', $personName)
                ->orderBy('id', 'desc')
                ->get();
        }

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
            'supplierReturns',
            'personCreditBalance',
            'creditLogs',
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

        $lock = $this->getProjectLockState($project);
        if ($lock['is_installment']) {
            return response()->json([
                'success' => false,
                'message' => '⛔ هذا المشروع محوّل إلى عقد تقسيط، لذلك لا يمكن تغيير حالته لأي حالة أخرى، وبالتالي التعديل والحذف مقفولان.',
            ], 422);
        }

        $requestedStatus = $request->input('status', $project->status);
        if ($lock['is_completed'] && $requestedStatus === 'completed') {
            return response()->json([
                'success' => false,
                'message' => '⛔ حالة المشروع (مكتمل) حالياً ولا يمكن تعديل بياناته. قم بتغيير حالة المشروع إلى (نشط) أولاً لإتاحة التعديل.',
            ], 422);
        }

        $data = [
            'name'            => $request->input('name', $project->name),
            'client_name'     => $request->input('client_name', $project->client_name),
            'client_phone'    => $request->input('client_phone', $project->client_phone),
            'location'        => $request->input('location', $project->location),
            'area_sqm'        => $request->has('area_sqm') ? ($request->filled('area_sqm') ? floatval($request->area_sqm) : null) : $project->area_sqm,
            'supervision_pct' => floatval($request->input('supervision_pct', $project->supervision_pct)),
            'contract_type'   => $request->input('contract_type', $project->contract_type),
            'status'          => $requestedStatus,
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

        if ($lockMsg = $this->buildProjectLockMessage($project, 'حذف المشروع')) {
            return response()->json(['success' => false, 'message' => $lockMsg], 422);
        }

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
                'notes'      => $item['notes'] ?? null,
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

        $isClientRefund = ($invoice->invoice_type ?? '') === 'client_refund';
        $isInstallment = !$isClientRefund && !empty($invoice->installment_id);
        $docCode = $invoice->invoice_number ?: ('INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT));
        $serialCode = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($docCode)) ?: ('INV-' . date('Ymd', strtotime($invoice->created_at)) . '-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT));
        $barcodeSvg = self::generateBarcodeSvg($serialCode);
        $qrSvg = self::generateQrSvg(url()->current() . '|' . $serialCode);

        $billed = floatval($invoice->total_billed) > 0 ? floatval($invoice->total_billed) : abs(floatval($invoice->paid_amount));
        $paid = $isClientRefund ? abs(floatval($invoice->paid_amount)) : floatval($invoice->paid_amount);
        $remaining = floatval($invoice->remaining_balance);

        $items = [
            [
                'index'      => 1,
                'code'       => 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT),
                'title'      => $invoice->notes ?: ($isClientRefund ? ('رد فائض الرصيد المقدم لمشروع: ' . $invoice->project_name) : ($isInstallment ? ('سند عقد تقسيط لمشروع: ' . $invoice->project_name) : ('مستخلص وسند مالي لمشروع: ' . $invoice->project_name))),
                'qty'        => 1,
                'unit'       => $isClientRefund ? 'إيصال رد' : ($isInstallment ? 'عقد' : 'مستخلص'),
                'unit_price' => $billed,
                'total'      => $billed,
            ]
        ];

        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        $doc = [
            'type'               => 'invoice',
            'page_title'         => ($isClientRefund ? 'سند رد فائض نقدي للعميل' : ($isInstallment ? 'سند عقد تقسيط' : 'فاتورة ومستخلص مالي')) . ' - ' . $invoice->project_name,
            'title'              => $isClientRefund ? 'سند رد فائض نقدي' : ($isInstallment ? 'سند تقسيط' : 'فاتورة مبيعات / مستخلص'),
            'code'               => $docCode,
            'serial'             => $serialCode,
            'status'             => $isClientRefund ? 'تم الرد للعميل' : ($remaining <= 0 ? 'مدفوع بالكامل' : ($paid > 0 ? 'سداد جزئي' : 'مستحق السداد')),
            'status_color'       => $isClientRefund ? 'info' : ($remaining <= 0 ? 'success' : 'primary'),
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
                'unit'       => trim((string)($op->unit ?? '')) ?: '-',
                'unit_price' => $unitPrice,
                'total'      => $lineTotal,
                'notes'      => $op->notes,
            ];

            $totalQty += $qty;
            $subtotal += $lineTotal;
        }

        $opIds = $ops->pluck('id')->all();
        $debtIds = $ops->pluck('debt_id')
            ->merge(!empty($opIds) ? DB::table('company_debts')->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])->whereIn('source_id', $opIds)->pluck('id') : collect())
            ->filter()
            ->unique()
            ->values()
            ->all();

        $totalCost = floatval($ops->sum('total_cost'));
        $totalDebt = floatval($ops->sum('remaining_debt'));
        if (!empty($debtIds)) {
            $debts = DB::table('company_debts')->whereIn('id', $debtIds)->get();
            $totalDebt = $debts->isNotEmpty() ? floatval($debts->sum('remaining_balance')) : 0.0;
        }
        $totalPaid = max(0, round($totalCost - $totalDebt, 2));
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
