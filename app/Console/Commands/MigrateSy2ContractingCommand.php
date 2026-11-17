<?php

namespace App\Console\Commands;

use App\Http\Controllers\ContractingController;
use App\Services\InstallmentFinanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSy2ContractingCommand extends Command
{
    protected $signature = 'contracting:migrate-sy2 {--dry-run : Preview and verify reconciliation without committing changes}';

    protected $description = 'Migrate all contracting data from legacy sy2_* tables into the new cont_* module, installments, company_debts, and manual_invoices.';

    private const FALLBACK_SUPPLIER_NAME = 'مورد عام (نثريات ومشتريات)';
    private const SY2_MAIN_ACCOUNT_ID = 37;

    /**
     * Execute the migration command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (!Schema::hasTable('sy2_projects')) {
            $this->error('Legacy sy2_projects table was not found in database.');
            return self::FAILURE;
        }

        $existingCount = (int) DB::table('cont_projects')->count()
            + (int) DB::table('cont_operations')->count()
            + (int) DB::table('cont_petty_expenses')->count()
            + (int) DB::table('manual_invoices')->where('invoice_number', 'like', 'MI-%')->count();

        if ($existingCount > 0) {
            $this->error("Target tables already contain contracting/manual invoice records ({$existingCount} rows). Aborting to prevent duplicate migration.");
            return self::FAILURE;
        }

        // Run schema ensures outside DB::transaction because DDL causes implicit commit in MySQL.
        $this->ensureTargetSchemas();
        if (!$dryRun) {
            $this->resetEmptyAutoIncrements();
        }

        $creatorUserId = $this->resolveCreatorUserId();

        DB::beginTransaction();
        try {
            $summary = $this->runMigration($creatorUserId);
            $reconciliation = $this->verifyReconciliation($summary);

            $this->printSummary($summary, $reconciliation, $dryRun);

            if (!$reconciliation['all_projects_exact']) {
                DB::rollBack();
                $this->error('Reconciliation mismatch detected! Transaction rolled back without saving.');
                return self::FAILURE;
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info('DRY-RUN complete. All changes rolled back cleanly.');
            } else {
                DB::commit();
                $this->info('Migration committed successfully to database.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Migration failed and rolled back: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    private function invokeController(ContractingController $ctrl, string $method, ...$args): mixed
    {
        $ref = new \ReflectionMethod($ctrl, $method);
        $ref->setAccessible(true);
        return $ref->invokeArgs($ctrl, $args);
    }

    /**
     * Ensure supplementary contracting tables and columns exist.
     */
    private function ensureTargetSchemas(): void
    {
        $ctrl = app(ContractingController::class);
        foreach (['ensureCancelledInvoicesTableExists', 'ensureMaterialReturnsTableExists'] as $method) {
            if (method_exists($ctrl, $method)) {
                $this->invokeController($ctrl, $method);
            }
        }
        if (function_exists('ensureSupplierCreditSchema')) {
            ensureSupplierCreditSchema();
        }
    }

    private function resetEmptyAutoIncrements(): void
    {
        $tables = [
            'cont_projects',
            'cont_project_items',
            'cont_directory',
            'cont_operations',
            'cont_discounts',
            'cont_invoices',
            'cont_material_returns',
            'cont_supplier_credit_logs',
            'cont_petty_expenses',
            'cont_quotations',
            'cont_surveys',
            'manual_invoices',
        ];
        foreach ($tables as $t) {
            if (Schema::hasTable($t) && (int) DB::table($t)->count() === 0) {
                DB::statement("ALTER TABLE `{$t}` AUTO_INCREMENT = 1");
            }
        }
    }

    /**
     * Resolve default creator user ID in the new users table.
     */
    private function resolveCreatorUserId(): ?int
    {
        $devId = DB::table('users')->where('email', 'faysal@gmail.com')->value('id');
        if ($devId) {
            return (int) $devId;
        }
        $firstId = DB::table('users')->orderBy('id')->value('id');
        return $firstId ? (int) $firstId : null;
    }

    /**
     * Execute full data migration inside the active DB transaction.
     */
    private function runMigration(?int $creatorUserId): array
    {
        $counts = [
            'projects'           => 0,
            'project_items'      => 0,
            'directory'          => 0,
            'operations'         => 0,
            'discounts'          => 0,
            'client_receipts'    => 0,
            'returns'            => 0,
            'debts_created'      => 0,
            'open_debts_total'   => 0.0,
            'credit_logs'        => 0,
            'petty_categories'   => 0,
            'petty_expenses'     => 0,
            'quotations'         => 0,
            'surveys'            => 0,
            'manual_invoices'    => 0,
        ];

        $directoryIds = [];
        $clientsById = DB::table('sy2_clients')->get()->keyBy('id');
        $suppliersById = DB::table('sy2_suppliers')->get()->keyBy('id');

        // 1. Directory: clients & marketers
        foreach ($clientsById as $c) {
            $name = $this->normalizeName($c->name);
            $directoryIds['client:' . $name] = $this->upsertDirectory('client', $name, $c->phone, null, $c->notes, $c->created_at, $c->updated_at, $counts);
        }
        foreach (DB::table('sy2_marketers')->get() as $m) {
            $name = $this->normalizeName($m->name);
            $directoryIds['marketer:' . $name] = $this->upsertDirectory('marketer', $name, $m->phone, null, null, $m->created_at, $m->updated_at, $counts);
        }

        // Seed canonical suppliers from sy2_suppliers only if used in materials/debts (excludes tech-only entries like ابراهيم الوراق)
        $usedSupplierIds = DB::table('sy2_materials')->whereNotNull('supplier_id')->pluck('supplier_id')
            ->merge(DB::table('sy2_material_invoices')->whereNotNull('supplier_id')->pluck('supplier_id'))
            ->merge(DB::table('sy2_supplier_debts')->whereNotNull('supplier_id')->pluck('supplier_id'))
            ->unique()
            ->all();
        foreach ($suppliersById as $s) {
            if (!in_array($s->id, $usedSupplierIds, true)) {
                continue;
            }
            $name = $this->normalizeName($s->name);
            $directoryIds['supplier:' . $name] = $this->upsertDirectory('supplier', $name, $s->phone, $s->activity, $s->notes, $s->created_at, $s->updated_at, $counts);
        }

        // 2. Auxiliary units & expense titles
        $this->migrateUnitsAndTitles();

        // 3. Projects & Project Items
        $projectMap = []; // old sy2_projects.id => new cont_projects.id
        $projectsByOldId = [];
        foreach (DB::table('sy2_projects')->orderBy('id')->get() as $p) {
            $client = $clientsById->get($p->client_id);
            $clientName = $client ? $this->normalizeName($client->name) : 'عميل غير مسمى';
            $clientPhone = $client->phone ?? null;

            $newProjId = DB::table('cont_projects')->insertGetId([
                'name'                 => trim((string) $p->name),
                'client_name'          => $clientName,
                'client_phone'         => $clientPhone,
                'location'             => $p->address,
                'area_sqm'             => $p->area,
                'supervision_pct'      => (float) ($p->default_supervision_pct ?? 0),
                'contract_type'        => 'cost_plus',
                'total_contract_value' => 0,
                'status'               => $p->status === 'done' ? 'completed' : 'active',
                'notes'                => $p->notes,
                'created_by'           => $creatorUserId,
                'created_at'           => $p->created_at,
                'updated_at'           => $p->updated_at,
            ]);

            $projectMap[$p->id] = $newProjId;
            $projectsByOldId[$p->id] = $p;
            $counts['projects']++;
        }

        $bandMap = []; // old sy2_project_bands.id => new cont_project_items.id
        $bandsByOldId = DB::table('sy2_project_bands')->orderBy('id')->get()->keyBy('id');
        foreach ($bandsByOldId as $b) {
            if (!isset($projectMap[$b->project_id])) {
                continue;
            }
            $newItemId = DB::table('cont_project_items')->insertGetId([
                'project_id' => $projectMap[$b->project_id],
                'title'      => trim((string) $b->name),
                'status'     => 'open',
                'sort_order' => (int) ($b->sort_order ?? 0),
                'created_at' => $b->created_at,
                'updated_at' => $b->updated_at,
            ]);
            $bandMap[$b->id] = $newItemId;
            $counts['project_items']++;
        }

        // Preload returns indexed by material_id
        $returnsRaw = DB::table('sy2_material_returns')->orderBy('id')->get();
        $returnsByMatId = [];
        foreach ($returnsRaw as $ret) {
            $returnsByMatId[$ret->material_id][] = $ret;
        }

        // 4A. Technicians / Subcontractors: sy2_band_workers + linked sy2_materials (band_worker_id IS NOT NULL)
        $bwAll = DB::table('sy2_band_workers')->orderBy('id')->get();
        $bwCountByBand = $bwAll->groupBy('project_band_id')->map->count();
        $workerPaymentsByBw = DB::table('sy2_worker_payments')->orderBy('date')->orderBy('id')->get()->groupBy('band_worker_id');
        $miscByBw = DB::table('sy2_materials')->whereNotNull('band_worker_id')->orderBy('id')->get()->groupBy('band_worker_id');

        foreach ($bwAll as $bw) {
            $band = $bandsByOldId->get($bw->project_band_id);
            if (!$band || !isset($projectMap[$band->project_id])) {
                continue;
            }
            $oldProjId = $band->project_id;
            $newProjId = $projectMap[$oldProjId];
            $newItemId = $bandMap[$band->id] ?? null;
            $projName = $projectsByOldId[$oldProjId]->name;
            $workerName = $this->normalizeName($bw->name);

            $dirId = $this->upsertDirectory('technician', $workerName, $bw->phone, $bw->specialty, $bw->notes, $bw->created_at, $bw->updated_at, $counts);
            $directoryIds['technician:' . $workerName] = $dirId;

            $miscItems = $miscByBw->get($bw->id, collect());

            // Resolve selling amount (handles Project 11 bw#45 where sell_amount=0 on bw while band.labor_sell_price=27000)
            $bwSell = (float) $bw->sell_amount;
            if ($bwSell <= 0.0 && (int) ($bwCountByBand->get($bw->project_band_id, 0)) === 1 && (float) $band->labor_sell_price > 0) {
                $bwSell = (float) $band->labor_sell_price;
            }

            $bwCost = (float) $bw->amount;
            $hasValidQty = ($bw->contract_type !== 'lump_sum')
                && ((float) ($bw->contract_qty ?? 0) > 0)
                && abs(((float) $bw->contract_qty * (float) ($bw->contract_unit_rate ?? 0)) - $bwCost) < 0.01
                && abs(((float) $bw->contract_qty * (float) ($bw->sell_rate ?? 0)) - $bwSell) < 0.01;

            if ($hasValidQty) {
                $qty = (float) $bw->contract_qty;
                $costPrice = (float) $bw->contract_unit_rate;
                $sellPrice = (float) $bw->sell_rate;
                $unit = match ($bw->contract_type) {
                    'per_meter' => 'متر',
                    'per_piece' => 'قطعة',
                    'daily'     => 'يومية',
                    default     => 'مقطوعية',
                };
            } else {
                $qty = 1.00;
                $costPrice = $bwCost;
                $sellPrice = $bwSell;
                $unit = 'مقطوعية';
            }

            $supPct = (float) ($bw->supervision_pct ?? 0);
            $mainTotalCost = round($bwCost, 2);
            $mainTotalSell = round($bwSell * (1 + $supPct / 100), 2);

            // Build candidate ops array for this band worker (main contract + misc sub-tasks)
            $opsToCreate = [];
            $includeMain = !($mainTotalCost == 0.0 && $mainTotalSell == 0.0 && $miscItems->isNotEmpty());
            if ($miscItems->isEmpty() && $mainTotalCost == 0.0 && $mainTotalSell == 0.0) {
                $includeMain = false;
            }

            $wpList = $workerPaymentsByBw->get($bw->id, collect());
            $wpPool = round((float) $wpList->sum(fn ($wp) => (float) $wp->amount + (float) $wp->discount), 2);
            $wpNotes = $this->formatWorkerPaymentNotes($wpList, $bw->notes);

            if ($includeMain) {
                $opsToCreate[] = [
                    'kind'        => 'main',
                    'title'       => trim((string) $band->name),
                    'qty'         => $qty,
                    'unit'        => $unit,
                    'cost_price'  => $costPrice,
                    'sell_price'  => $sellPrice,
                    'sup_pct'     => $supPct,
                    'total_cost'  => $mainTotalCost,
                    'total_sell'  => $mainTotalSell,
                    'direct_paid' => 0.0,
                    'notes'       => $wpNotes,
                    'created_at'  => $this->combineDateTimestamp($bw->start_date, $bw->created_at),
                    'updated_at'  => $bw->updated_at,
                ];
            }

            foreach ($miscItems as $m) {
                $mCost = round((float) $m->qty * (float) $m->unit_price, 2);
                $mSup = (float) ($m->supervision_pct ?? 0);
                $mSell = round((float) $m->qty * (float) $m->sell_price * (1 + $mSup / 100), 2);
                $directPaid = min($mCost, round((float) $m->paid_amount, 2));

                $opsToCreate[] = [
                    'kind'        => 'misc',
                    'title'       => trim((string) $m->item),
                    'qty'         => (float) $m->qty,
                    'unit'        => $this->normalizeUnit($m->unit),
                    'cost_price'  => (float) $m->unit_price,
                    'sell_price'  => (float) $m->sell_price,
                    'sup_pct'     => $mSup,
                    'total_cost'  => $mCost,
                    'total_sell'  => $mSell,
                    'direct_paid' => $directPaid,
                    'notes'       => $m->notes,
                    'created_at'  => $this->combineDateTimestamp($m->date, $m->created_at),
                    'updated_at'  => $m->updated_at,
                ];
            }

            // Allocate wpPool cleanly after accounting for direct_paid on misc items
            $remainingWp = $wpPool;
            foreach ($opsToCreate as $idx => $opData) {
                $paidOnOp = $opData['direct_paid'];
                $stillOwed = max(0.0, round($opData['total_cost'] - $paidOnOp, 2));
                if ($remainingWp > 0 && $stillOwed > 0) {
                    $alloc = min($remainingWp, $stillOwed);
                    $paidOnOp = round($paidOnOp + $alloc, 2);
                    $remainingWp = round($remainingWp - $alloc, 2);
                }
                $unpaidOnOp = max(0.0, round($opData['total_cost'] - $paidOnOp, 2));

                $excessNote = '';
                if ($idx === 0 && $remainingWp > 0.009) {
                    $excessNote = trim(($opData['notes'] ? $opData['notes'] . ' | ' : '') . "دُفع إجمالي {$wpPool} ج (بزيادة {$remainingWp} ج رصيد دائن لنا)");
                }

                $paymentMethod = $unpaidOnOp <= 0.001
                    ? 'cash'
                    : ($paidOnOp > 0.0 ? 'partial' : 'later');

                $opId = DB::table('cont_operations')->insertGetId([
                    'project_id'        => $newProjId,
                    'item_id'           => $newItemId,
                    'op_type'           => 'technician',
                    'is_billable'       => 1,
                    'supervision_pct'   => $opData['sup_pct'],
                    'name_or_title'     => $opData['title'],
                    'quantity'          => $opData['qty'],
                    'original_quantity' => $opData['qty'],
                    'returned_quantity' => 0,
                    'unit'              => $opData['unit'],
                    'cost_price'        => $opData['cost_price'],
                    'selling_price'     => $opData['sell_price'],
                    'total_cost'        => $opData['total_cost'],
                    'total_selling'     => $opData['total_sell'],
                    'profit_amount'     => round($opData['total_sell'] - $opData['total_cost'], 2),
                    'person_id'         => $dirId,
                    'person_name'       => $workerName,
                    'payment_method'    => $paymentMethod,
                    'paid_amount'       => $paidOnOp,
                    'remaining_debt'    => $unpaidOnOp,
                    'debt_id'           => null,
                    'invoice_ref'       => null,
                    'notes'             => $excessNote ?: $opData['notes'],
                    'created_by'        => $creatorUserId,
                    'created_at'        => $opData['created_at'],
                    'updated_at'        => $opData['updated_at'],
                ]);
                $counts['operations']++;

                if ($unpaidOnOp > 0.009) {
                    $debtId = DB::table('company_debts')->insertGetId([
                        'creditor_name'     => $workerName,
                        'reason'            => "مستحق صنايعي: {$opData['title']} — مشروع: {$projName}",
                        'total_amount'      => $unpaidOnOp,
                        'paid_amount'       => 0.00,
                        'remaining_balance' => $unpaidOnOp,
                        'category'          => 'مقاولات',
                        'source_type'       => 'cont_operation',
                        'source_id'         => $opId,
                        'created_at'        => $opData['created_at'],
                        'updated_at'        => $opData['updated_at'],
                    ]);
                    DB::table('cont_operations')->where('id', $opId)->update(['debt_id' => $debtId]);
                    $counts['debts_created']++;
                    $counts['open_debts_total'] = round($counts['open_debts_total'] + $unpaidOnOp, 2);
                }
            }

            // Record any genuine excess overpayment (e.g. Hani Aboumetwally 7,400 EGP) as technician credit for us
            if ($remainingWp > 0.009) {
                $currentCredit = (float) DB::table('cont_directory')->where('id', $dirId)->value('balance_credit');
                $newCredit = round($currentCredit + $remainingWp, 2);
                DB::table('cont_directory')->where('id', $dirId)->update([
                    'balance_credit' => $newCredit,
                    'updated_at'     => $bw->updated_at,
                ]);
                DB::table('cont_supplier_credit_logs')->insert([
                    'person_name'   => $workerName,
                    'person_type'   => 'technician',
                    'tx_type'       => 'overpayment',
                    'amount'        => $remainingWp,
                    'balance_after' => $newCredit,
                    'invoice_ref'   => null,
                    'project_id'    => $newProjId,
                    'debt_id'       => null,
                    'account_id'    => null,
                    'notes'         => "رصيد دفعة زائدة مرحّل من السيستم القديم sy2 — مشروع: {$projName} ({$band->name})",
                    'created_by'    => $creatorUserId ? (string) $creatorUserId : 'system',
                    'created_at'    => $bw->updated_at,
                    'updated_at'    => $bw->updated_at,
                ]);
                $counts['credit_logs']++;
            }
        }

        // 4B. Material Invoices (sy2_material_invoices + items)
        $debtsByInvoiceId = DB::table('sy2_supplier_debts')->whereNotNull('invoice_id')->get()->groupBy('invoice_id');
        // Special sy2 debt #8 belongs to invoice #4 even though invoice_id was null in sy2
        $specialDebt8 = DB::table('sy2_supplier_debts')->where('id', 8)->first();

        foreach (DB::table('sy2_material_invoices')->orderBy('id')->get() as $inv) {
            if (!isset($projectMap[$inv->project_id])) {
                continue;
            }
            $newProjId = $projectMap[$inv->project_id];
            $projName = $projectsByOldId[$inv->project_id]->name;
            $rawSup = $inv->supplier_id ? ($suppliersById->get($inv->supplier_id)->name ?? null) : null;
            $supName = $this->normalizeName($rawSup ?: self::FALLBACK_SUPPLIER_NAME);
            $dirId = $directoryIds['supplier:' . $supName] ?? $this->upsertDirectory('supplier', $supName, null, null, null, $inv->created_at, $inv->updated_at, $counts);
            $directoryIds['supplier:' . $supName] = $dirId;

            $invoiceRef = 'SY2-INV-' . $inv->id;
            $items = DB::table('sy2_materials')->where('invoice_id', $inv->id)->orderBy('id')->get();
            if ($items->isEmpty()) {
                continue;
            }

            $invDebts = $debtsByInvoiceId->get($inv->id, collect());
            if ((int) $inv->id === 4 && $invDebts->isEmpty() && $specialDebt8) {
                $invDebts = collect([$specialDebt8]);
            }
            $syDebt = $invDebts->first();

            // Create company_debt first if linked (except handle #47 special case on item basis)
            $newDebtId = null;
            if ($syDebt) {
                $debtTot = round((float) $syDebt->total_amount, 2);
                $debtPaid = round((float) $syDebt->paid_amount, 2);
                $debtRem = max(0.0, round($debtTot - $debtPaid, 2));

                $newDebtId = DB::table('company_debts')->insertGetId([
                    'creditor_name'     => $supName,
                    'reason'            => "فاتورة خامات ({$invoiceRef}): {$inv->name} — مشروع: {$projName}",
                    'total_amount'      => $debtTot,
                    'paid_amount'       => $debtPaid,
                    'remaining_balance' => $debtRem,
                    'category'          => 'مقاولات',
                    'source_type'       => 'cont_operation_invoice',
                    'source_id'         => null,
                    'created_at'        => $syDebt->created_at,
                    'updated_at'        => $syDebt->updated_at,
                ]);
                $counts['debts_created']++;
                $counts['open_debts_total'] = round($counts['open_debts_total'] + $debtRem, 2);
            }

            $firstDebtLinkedOpId = null;
            foreach ($items as $m) {
                $matRets = $returnsByMatId[$m->id] ?? [];
                $retQtyTotal = round((float) collect($matRets)->sum('qty'), 2);
                $origQty = (float) $m->qty;
                $netQty = max(0.0, round($origQty - $retQtyTotal, 2));
                $supPct = (float) ($m->supervision_pct ?? 0);

                $totalCost = round($netQty * (float) $m->unit_price, 2);
                $totalSell = round($netQty * (float) $m->sell_price * (1 + $supPct / 100), 2);
                $newItemId = $m->band_id ? ($bandMap[$m->band_id] ?? null) : null;

                // Determine payment allocation matching sy2 debt state cleanly
                $itemDebtId = null;
                $paymentMethod = 'cash';
                $paidAmount = $totalCost;
                $remainingDebt = 0.0;

                if ($syDebt) {
                    if ((int) $inv->id === 47 && (int) $m->id === 278) {
                        // sy2 debt #45 (180) only covered mat #277 (180); mat #278 (150 transfer) was settled outside that debt
                        $paymentMethod = 'cash';
                        $paidAmount = $totalCost;
                        $remainingDebt = 0.0;
                    } elseif ((int) $inv->id === 71) {
                        // sy2 debt #70 was settled at 740/740 while item cost was 870 (130 difference adjusted/paid)
                        $paymentMethod = 'partial';
                        $paidAmount = round(max(0.0, $totalCost - (float) $syDebt->total_amount), 2);
                        $remainingDebt = round((float) $syDebt->total_amount, 2);
                        $itemDebtId = $newDebtId;
                    } else {
                        $paymentMethod = 'later';
                        $paidAmount = 0.0;
                        $remainingDebt = $totalCost;
                        $itemDebtId = $newDebtId;
                    }
                } else {
                    // Direct cash/partial invoice with no sy2_supplier_debt
                    $paidDirect = min($totalCost, round((float) $m->paid_amount, 2));
                    if ($paidDirect >= $totalCost - 0.01) {
                        $paymentMethod = 'cash';
                        $paidAmount = $totalCost;
                        $remainingDebt = 0.0;
                    } else {
                        $paymentMethod = $paidDirect > 0 ? 'partial' : 'later';
                        $paidAmount = $paidDirect;
                        $remainingDebt = round($totalCost - $paidDirect, 2);
                    }
                }

                $opNotes = trim(implode(' | ', array_filter([$inv->name, $m->notes])));

                $opId = DB::table('cont_operations')->insertGetId([
                    'project_id'        => $newProjId,
                    'item_id'           => $newItemId,
                    'op_type'           => 'material',
                    'is_billable'       => 1,
                    'supervision_pct'   => $supPct,
                    'name_or_title'     => trim((string) $m->item),
                    'quantity'          => $netQty,
                    'original_quantity' => $origQty,
                    'returned_quantity' => $retQtyTotal,
                    'unit'              => $this->normalizeUnit($m->unit),
                    'cost_price'        => (float) $m->unit_price,
                    'selling_price'     => (float) $m->sell_price,
                    'total_cost'        => $totalCost,
                    'total_selling'     => $totalSell,
                    'profit_amount'     => round($totalSell - $totalCost, 2),
                    'person_id'         => $dirId,
                    'person_name'       => $supName,
                    'payment_method'    => $paymentMethod,
                    'paid_amount'       => $paidAmount,
                    'remaining_debt'    => $remainingDebt,
                    'debt_id'           => $itemDebtId,
                    'invoice_ref'       => $invoiceRef,
                    'notes'             => $opNotes ?: null,
                    'created_by'        => $creatorUserId,
                    'created_at'        => $this->combineDateTimestamp($m->date, $m->created_at),
                    'updated_at'        => $m->updated_at,
                ]);
                $counts['operations']++;

                if ($itemDebtId && $firstDebtLinkedOpId === null) {
                    $firstDebtLinkedOpId = $opId;
                }

                // Record material return history rows into cont_material_returns
                foreach ($matRets as $ret) {
                    $retQty = (float) $ret->qty;
                    $retCost = round($retQty * (float) $m->unit_price, 2);
                    $retSell = round($retQty * (float) $m->sell_price * (1 + $supPct / 100), 2);
                    DB::table('cont_material_returns')->insert([
                        'return_number'          => 'RET-SY2-' . $ret->id,
                        'invoice_ref'            => $invoiceRef,
                        'project_id'             => $newProjId,
                        'project_name'           => $projName,
                        'client_name'            => $clientsById->get($projectsByOldId[$inv->project_id]->client_id)->name ?? 'عميل',
                        'supplier_name'          => $supName,
                        'operation_id'           => $opId,
                        'item_id'                => $newItemId,
                        'item_title'             => $newItemId ? ($bandsByOldId->get($m->band_id)->name ?? null) : null,
                        'material_name'          => trim((string) $m->item),
                        'unit'                   => $this->normalizeUnit($m->unit),
                        'original_qty'           => $origQty,
                        'returned_qty'           => $retQty,
                        'remaining_qty'          => $netQty,
                        'cost_price'             => (float) $m->unit_price,
                        'selling_price'          => (float) $m->sell_price,
                        'supervision_pct'        => $supPct,
                        'total_cost_returned'    => $retCost,
                        'total_selling_returned' => $retSell,
                        'settlement_method'      => 'debt_deduction',
                        'debt_deducted'          => $retCost,
                        'cash_refunded'          => 0,
                        'supplier_credit'        => 0,
                        'account_id'             => null,
                        'reason'                 => $ret->notes ?: 'مرتجع خامات مرحّل من السيستم القديم sy2',
                        'created_by'             => $creatorUserId,
                        'created_at'             => $this->combineDateTimestamp($ret->date, $ret->created_at),
                        'updated_at'             => $ret->updated_at,
                    ]);
                    $counts['returns']++;
                }
            }

            if ($newDebtId && $firstDebtLinkedOpId) {
                DB::table('company_debts')->where('id', $newDebtId)->update(['source_id' => $firstDebtLinkedOpId]);
            }
        }

        // 4C. Standalone sy2_materials (invoice_id IS NULL AND band_worker_id IS NULL)
        $standaloneMats = DB::table('sy2_materials')
            ->whereNull('invoice_id')
            ->whereNull('band_worker_id')
            ->orderBy('id')
            ->get();
        $debtsByMatId = DB::table('sy2_supplier_debts')->whereNotNull('material_id')->get()->keyBy('material_id');

        foreach ($standaloneMats as $m) {
            if (!isset($projectMap[$m->project_id])) {
                continue;
            }
            $newProjId = $projectMap[$m->project_id];
            $newItemId = $m->band_id ? ($bandMap[$m->band_id] ?? null) : null;
            $projName = $projectsByOldId[$m->project_id]->name;
            $cost = round((float) $m->qty * (float) $m->unit_price, 2);
            $supPct = (float) ($m->supervision_pct ?? 0);
            $sell = round((float) $m->qty * (float) $m->sell_price * (1 + $supPct / 100), 2);

            // Skip completely empty placeholder row (e.g. id=168 "بند جديد" 0/0)
            if ($cost == 0.0 && $sell == 0.0 && trim((string) $m->item) === 'بند جديد') {
                continue;
            }

            $syDebt = $debtsByMatId->get($m->id);
            $rawSup = $m->supplier_id ? ($suppliersById->get($m->supplier_id)->name ?? null) : $m->supplier_name;

            $isTechnician = in_array((int) $m->id, [121, 122, 134], true);
            $isMaterial = ($syDebt !== null) || !empty(trim((string) $rawSup)) || ((int) $m->id === 128);

            if ($isTechnician) {
                $opType = 'technician';
                $personType = 'technician';
                $personName = ((int) $m->id === 134) ? 'فني صيانة تكييفات' : $this->normalizeName($m->item);
            } elseif ($isMaterial) {
                $opType = 'material';
                $personType = 'supplier';
                $personName = $this->normalizeName($rawSup ?: self::FALLBACK_SUPPLIER_NAME);
            } else {
                $opType = 'petty';
                $personType = null;
                $personName = null;
            }

            $dirId = null;
            if ($personName && $personType) {
                $key = "{$personType}:{$personName}";
                $dirId = $directoryIds[$key] ?? $this->upsertDirectory($personType, $personName, null, null, null, $m->created_at, $m->updated_at, $counts);
                $directoryIds[$key] = $dirId;
            }

            $newDebtId = null;
            $paidAmount = $cost;
            $remainingDebt = 0.0;
            $paymentMethod = 'cash';
            $notes = $m->notes;

            if ($syDebt) {
                $debtTot = round((float) $syDebt->total_amount, 2);
                $debtPaid = round((float) $syDebt->paid_amount, 2);

                // Special sy2 debt #26: marked paid at 450/500
                if ((int) $syDebt->id === 26 && $syDebt->status === 'paid') {
                    $debtRem = 0.0;
                    $debtTotForRecord = $debtPaid;
                    $notes = trim(($notes ? $notes . ' | ' : '') . 'تسوية تاريخية sy2: سداد 450 + خصم 50');
                } else {
                    $debtRem = max(0.0, round($debtTot - $debtPaid, 2));
                    $debtTotForRecord = $debtTot;
                }

                $newDebtId = DB::table('company_debts')->insertGetId([
                    'creditor_name'     => $personName,
                    'reason'            => trim((string) ($syDebt->description ?: $m->item)) . " — مشروع: {$projName}",
                    'total_amount'      => $debtTotForRecord,
                    'paid_amount'       => $debtPaid,
                    'remaining_balance' => $debtRem,
                    'category'          => 'مقاولات',
                    'source_type'       => 'cont_operation',
                    'source_id'         => null,
                    'created_at'        => $syDebt->created_at,
                    'updated_at'        => $syDebt->updated_at,
                ]);
                $counts['debts_created']++;
                $counts['open_debts_total'] = round($counts['open_debts_total'] + $debtRem, 2);

                if ((int) $syDebt->id === 26 && $syDebt->status === 'paid') {
                    $paymentMethod = 'partial';
                    $paidAmount = round(max(0.0, $cost - $debtTotForRecord), 2);
                    $remainingDebt = $debtTotForRecord;
                } else {
                    $paymentMethod = 'later';
                    $paidAmount = 0.0;
                    $remainingDebt = $cost;
                }
            }

            $invoiceRef = ($opType === 'material' && ($syDebt || !empty($rawSup))) ? ('SY2-MAT-' . $m->id) : null;

            $opId = DB::table('cont_operations')->insertGetId([
                'project_id'        => $newProjId,
                'item_id'           => $newItemId,
                'op_type'           => $opType,
                'is_billable'       => ($sell > 0 || $opType !== 'petty') ? 1 : 0,
                'supervision_pct'   => $supPct,
                'name_or_title'     => trim((string) $m->item),
                'quantity'          => (float) $m->qty,
                'original_quantity' => (float) $m->qty,
                'returned_quantity' => 0,
                'unit'              => $this->normalizeUnit($m->unit),
                'cost_price'        => (float) $m->unit_price,
                'selling_price'     => (float) $m->sell_price,
                'total_cost'        => $cost,
                'total_selling'     => $sell,
                'profit_amount'     => round($sell - $cost, 2),
                'person_id'         => $dirId,
                'person_name'       => $personName,
                'payment_method'    => $paymentMethod,
                'paid_amount'       => $paidAmount,
                'remaining_debt'    => $remainingDebt,
                'debt_id'           => $newDebtId,
                'invoice_ref'       => $invoiceRef,
                'notes'             => $notes,
                'created_by'        => $creatorUserId,
                'created_at'        => $this->combineDateTimestamp($m->date, $m->created_at),
                'updated_at'        => $m->updated_at,
            ]);
            $counts['operations']++;

            if ($newDebtId) {
                DB::table('company_debts')->where('id', $newDebtId)->update(['source_id' => $opId]);
            }
        }

        // 4D. Project-linked sy2_expenses -> non-billable overhead cont_operations
        foreach (DB::table('sy2_expenses')->whereNotNull('project_id')->orderBy('id')->get() as $exp) {
            if (!isset($projectMap[$exp->project_id])) {
                continue;
            }
            $amt = round((float) $exp->amount, 2);
            DB::table('cont_operations')->insert([
                'project_id'        => $projectMap[$exp->project_id],
                'item_id'           => null,
                'op_type'           => 'overhead',
                'is_billable'       => 0,
                'supervision_pct'   => 0,
                'name_or_title'     => trim((string) $exp->description) ?: 'مصروف داخلي للمشروع',
                'quantity'          => 1.00,
                'original_quantity' => 1.00,
                'returned_quantity' => 0,
                'unit'              => 'مقطوعية',
                'cost_price'        => $amt,
                'selling_price'     => 0,
                'total_cost'        => $amt,
                'total_selling'     => 0,
                'profit_amount'     => -$amt,
                'person_id'         => null,
                'person_name'       => null,
                'payment_method'    => 'cash',
                'paid_amount'       => $amt,
                'remaining_debt'    => 0,
                'debt_id'           => null,
                'invoice_ref'       => null,
                'notes'             => $exp->notes,
                'created_by'        => $creatorUserId,
                'created_at'        => $this->combineDateTimestamp($exp->date, $exp->created_at),
                'updated_at'        => $exp->updated_at,
            ]);
            $counts['operations']++;
        }

        // 5. Project Discounts
        foreach (DB::table('sy2_project_discounts')->orderBy('id')->get() as $disc) {
            if (!isset($projectMap[$disc->project_id])) {
                continue;
            }
            DB::table('cont_discounts')->insert([
                'project_id' => $projectMap[$disc->project_id],
                'amount'     => round((float) $disc->amount, 2),
                'reason'     => $disc->notes ?: 'خصم مرحّل من السيستم القديم sy2',
                'created_by' => $creatorUserId,
                'created_at' => $this->combineDateTimestamp($disc->date, $disc->created_at),
                'updated_at' => $disc->updated_at,
            ]);
            $counts['discounts']++;
        }

        // 6. Client Payments (sy2_transactions ref_type=client_payment) -> cont_invoices + syncProjectCustomerReceivable
        $ctrl = app(ContractingController::class);
        $clientPaymentsByProj = DB::table('sy2_transactions')
            ->where('ref_type', 'client_payment')
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        foreach ($projectMap as $oldProjId => $newProjId) {
            $metrics = $this->computeProjectActualMetrics($newProjId);
            $netBill = $metrics['net_bill'];
            $runningPaid = 0.0;

            foreach ($clientPaymentsByProj->get($oldProjId, collect()) as $cp) {
                $amt = round((float) $cp->amount, 2);
                $runningPaid = round($runningPaid + $amt, 2);
                DB::table('cont_invoices')->insert([
                    'project_id'           => $newProjId,
                    'invoice_number'       => 'REC-SY2-' . $cp->id,
                    'invoice_type'         => 'payment_receipt',
                    'is_tax'               => 0,
                    'tax_rate'             => 0,
                    'tax_amount'           => 0,
                    'show_supervision_fee' => 1,
                    'supervision_amount'   => 0,
                    'discount_amount'      => 0,
                    'total_billed'         => $netBill,
                    'paid_amount'          => $amt,
                    'remaining_balance'    => max(0.0, round($netBill - $runningPaid, 2)),
                    'installment_id'       => null,
                    'notes'                => $cp->description ?: 'تحصيل عميل مرحّل من السيستم القديم sy2',
                    'created_by'           => $creatorUserId,
                    'created_at'           => $this->combineDateTimestamp($cp->date, $cp->created_at),
                    'updated_at'           => $cp->updated_at,
                ]);
                $counts['client_receipts']++;
            }

            // Reconcile into installments (/debts) for any open client receivables
            $ctrl->syncProjectCustomerReceivable($newProjId);
        }

        // 7. General Petty Expenses (sy2_expenses WHERE project_id IS NULL) & Categories
        $categoryIdByName = [];
        foreach (DB::table('cont_petty_categories')->get() as $cat) {
            $categoryIdByName[trim((string) $cat->name)] = $cat->id;
        }
        foreach (DB::table('sy2_expense_categories')->orderBy('id')->get() as $syCat) {
            $cName = trim((string) $syCat->name);
            if ($cName !== '' && !isset($categoryIdByName[$cName])) {
                $catId = DB::table('cont_petty_categories')->insertGetId([
                    'name'       => $cName,
                    'created_at' => $syCat->created_at,
                    'updated_at' => $syCat->updated_at,
                ]);
                $categoryIdByName[$cName] = $catId;
                $counts['petty_categories']++;
            }
        }

        foreach (DB::table('sy2_expenses')->whereNull('project_id')->orderBy('id')->get() as $exp) {
            $title = trim((string) $exp->description) ?: 'مصروف نثري عام';
            $catId = $categoryIdByName[$title] ?? null;
            if (!$catId) {
                $catId = DB::table('cont_petty_categories')->insertGetId([
                    'name'       => $title,
                    'created_at' => $exp->created_at,
                    'updated_at' => $exp->updated_at,
                ]);
                $categoryIdByName[$title] = $catId;
                $counts['petty_categories']++;
            }

            DB::table('cont_petty_expenses')->insert([
                'category_id' => $catId,
                'title'       => $title,
                'amount'      => round((float) $exp->amount, 2),
                'account_id'  => self::SY2_MAIN_ACCOUNT_ID,
                'notes'       => $exp->notes,
                'created_by'  => $creatorUserId,
                'created_at'  => $this->combineDateTimestamp($exp->date, $exp->created_at),
                'updated_at'  => $exp->updated_at,
            ]);
            $counts['petty_expenses']++;
        }

        // 8. Quotations (sy2_quotes)
        foreach (DB::table('sy2_quotes')->orderBy('id')->get() as $q) {
            $qBands = DB::table('sy2_quote_bands')->where('quote_id', $q->id)->orderBy('sort_order')->orderBy('id')->get();
            $itemsData = [];
            $totalCost = 0.0;
            $totalSell = 0.0;

            foreach ($qBands as $qb) {
                $qItems = DB::table('sy2_quote_band_items')->where('quote_band_id', $qb->id)->orderBy('sort_order')->orderBy('id')->get();
                foreach ($qItems as $qi) {
                    $qty = (float) ($qi->qty ?? 1);
                    $price = (float) ($qi->unit_price ?? 0);
                    $supPct = (float) ($qi->supervision_pct ?? 0);
                    $sellTotal = round($qty * $price * (1 + $supPct / 100), 2);
                    $itemsData[] = [
                        'item_title' => "{$qb->name} — {$qi->name}",
                        'unit'       => 'عدد',
                        'qty'        => $qty,
                        'cost'       => $price,
                        'price'      => round($price * (1 + $supPct / 100), 2),
                        'notes'      => '',
                    ];
                    $totalCost = round($totalCost + ($qty * $price), 2);
                    $totalSell = round($totalSell + $sellTotal, 2);
                }

                $qWorkers = DB::table('sy2_quote_band_workers')->where('quote_band_id', $qb->id)->orderBy('sort_order')->orderBy('id')->get();
                foreach ($qWorkers as $qw) {
                    $qty = ($qw->contract_type !== 'lump_sum' && (float) ($qw->contract_qty ?? 0) > 0) ? (float) $qw->contract_qty : 1.0;
                    $cost = (float) ($qw->amount ?? 0);
                    $sell = (float) ($qw->sell_amount ?? 0);
                    $supPct = (float) ($qw->supervision_pct ?? 0);
                    $sellWithSup = round($sell * (1 + $supPct / 100), 2);
                    $itemsData[] = [
                        'item_title' => "مصنعية {$qb->name} ({$qw->name})",
                        'unit'       => 'مقطوعية',
                        'qty'        => 1,
                        'cost'       => $cost,
                        'price'      => $sellWithSup,
                        'notes'      => $qw->notes ?? '',
                    ];
                    $totalCost = round($totalCost + $cost, 2);
                    $totalSell = round($totalSell + $sellWithSup, 2);
                }
            }

            $qClientName = $q->client_name ?: ($clientsById->get($q->client_id)->name ?? 'عميل');
            $projectTitle = $qBands->pluck('name')->implode(' + ') ?: ($q->ref . ($q->address ? " - {$q->address}" : ''));

            DB::table('cont_quotations')->insert([
                'client_name'          => $this->normalizeName($qClientName),
                'client_phone'         => $q->phone ?: ($clientsById->get($q->client_id)->phone ?? null),
                'project_title'        => trim((string) $projectTitle) . " ({$q->ref})",
                'supervision_pct'      => 0,
                'items_data'           => json_encode($itemsData, JSON_UNESCAPED_UNICODE),
                'total_cost'           => $totalCost,
                'total_selling'        => $totalSell,
                'status'               => $q->project_id ? 'converted_to_project' : 'draft',
                'converted_project_id' => $q->project_id ? ($projectMap[$q->project_id] ?? null) : null,
                'created_by'           => $creatorUserId,
                'created_at'           => $this->combineDateTimestamp($q->date, $q->created_at),
                'updated_at'           => $q->updated_at,
            ]);
            $counts['quotations']++;
        }

        // 9. Area Surveys (sy2_calculators -> cont_surveys)
        foreach (DB::table('sy2_calculators')->orderBy('id')->get() as $calc) {
            $h = (float) ($calc->global_height ?: 2.80);
            $spaces = json_decode($calc->spaces ?: '[]', true) ?: [];
            $rooms = [];
            foreach ($spaces as $sp) {
                $len = (float) ($sp['length'] ?? $sp['len'] ?? 0);
                $wid = (float) ($sp['width'] ?? $sp['wid'] ?? 0);
                $openings = (float) ($sp['deduction'] ?? $sp['openings'] ?? 0);
                $type = (($sp['type'] ?? 'room') === 'bath') ? 'bath' : 'room';
                $floor = round($len * $wid, 2);
                $wall = round(max(0, 2 * ($len + $wid) * $h - $openings), 2);
                $ceiling = $type === 'room' ? $floor : 0.0;

                $rooms[] = [
                    'name'     => (string) ($sp['name'] ?? 'فراغ'),
                    'type'     => $type,
                    'len'      => $len,
                    'wid'      => $wid,
                    'openings' => $openings,
                    'wall'     => $wall,
                    'floor'    => $floor,
                    'ceiling'  => $ceiling,
                ];
            }

            // Link Salah Gamal 2nd floor survey to project #19
            $linkedProjectId = ((int) $calc->id === 3 && isset($projectMap[19])) ? $projectMap[19] : null;

            DB::table('cont_surveys')->insert([
                'title'               => trim((string) $calc->title),
                'project_id'          => $linkedProjectId,
                'ceiling_height'      => $h,
                'total_room_walls'    => (float) $calc->total_paints,
                'total_floors'        => (float) $calc->total_floor_ceramics,
                'total_ceramic_walls' => (float) $calc->total_wall_ceramics,
                'total_deductions'    => (float) $calc->total_deductions,
                'rooms_data'          => json_encode($rooms, JSON_UNESCAPED_UNICODE),
                'pricing_data'        => null,
                'total_labor_cost'    => 0,
                'total_material_cost' => 0,
                'estimated_total'     => 0,
                'notes'               => 'مقايسة مرحّلة من السيستم القديم sy2',
                'created_by'          => $creatorUserId,
                'created_at'          => $calc->created_at,
                'updated_at'          => $calc->updated_at,
            ]);
            $counts['surveys']++;
        }

        // 10. Manual Invoices (sy2_manual_invoices + items -> manual_invoices)
        $creatorName = DB::table('users')->where('id', $creatorUserId)->value('name') ?? 'Developer';
        foreach (DB::table('sy2_manual_invoices')->orderBy('id')->get() as $mi) {
            $miItemsRaw = DB::table('sy2_manual_invoice_items')->where('manual_invoice_id', $mi->id)->orderBy('id')->get();
            $cleanItems = [];
            foreach ($miItemsRaw as $idx => $it) {
                $qty = (float) $it->qty;
                $price = (float) $it->unit_price;
                $total = round((float) $it->total, 2);
                $cleanItems[] = [
                    'index' => $idx + 1,
                    'title' => trim((string) $it->description),
                    'qty'   => $qty,
                    'unit'  => trim((string) ($it->unit ?: 'وحدة')),
                    'price' => $price,
                    'total' => $total,
                ];
            }
            $grandTotal = round((float) $mi->total, 2);
            $paidAmt = $mi->paid_amount !== null ? round((float) $mi->paid_amount, 2) : null;
            $remAmt = $paidAmt !== null ? max(0.0, round($grandTotal - $paidAmt, 2)) : null;

            DB::table('manual_invoices')->insert([
                'invoice_number'      => $mi->invoice_number,
                'invoice_title'       => 'فاتورة مبيعات',
                'client_name'         => $this->normalizeName($mi->client_name),
                'client_phone'        => $mi->client_phone ?: null,
                'client_address'      => $mi->client_address ?: null,
                'invoice_date'        => $mi->date,
                'items_json'          => json_encode($cleanItems, JSON_UNESCAPED_UNICODE),
                'subtotal'            => round((float) $mi->subtotal, 2),
                'discount_amount'     => round((float) $mi->discount, 2),
                'tax_rate'            => round((float) $mi->tax_pct, 2),
                'tax_amount'          => round((float) $mi->tax_amount, 2),
                'grand_total'         => $grandTotal,
                'paid_amount'         => $paidAmt,
                'remaining_amount'    => $remAmt,
                'notes'               => trim(implode(' | ', array_filter([
                    $mi->notes,
                    $mi->tax_number ? "الرقم الضريبي: {$mi->tax_number}" : null,
                    $mi->commercial_register ? "سجل تجاري: {$mi->commercial_register}" : null,
                ]))) ?: null,
                'terms'               => null,
                'status'              => 'active',
                'is_edited'           => 0,
                'edit_count'          => 0,
                'last_edited_at'      => null,
                'last_edited_by'      => null,
                'edit_history_json'   => null,
                'is_deleted'          => 0,
                'deleted_at'          => null,
                'deleted_by'          => null,
                'delete_reason'       => null,
                'created_by_name'     => $creatorName,
                'created_by'          => $creatorUserId,
                'created_at'          => $mi->created_at,
                'updated_at'          => $mi->updated_at,
            ]);
            $counts['manual_invoices']++;
        }

        // 11. Reconcile accounts.id=37 (المقاولات) with remaining cash in sy2_accounts
        // Prevents capital double-counting on /treasury once contracting open receivables & debts enter ahmed1
        $sy2CashTotal = round((float) DB::table('sy2_accounts')->where('status', 'active')->sum('balance'), 2);
        $oldAcc37Balance = (float) DB::table('accounts')->where('id', self::SY2_MAIN_ACCOUNT_ID)->value('balance');
        DB::table('accounts')->where('id', self::SY2_MAIN_ACCOUNT_ID)->update([
            'balance'    => $sy2CashTotal,
            'updated_at' => now(),
        ]);

        return [
            'counts'          => $counts,
            'projectMap'      => $projectMap,
            'projectsByOldId' => $projectsByOldId,
            'oldAcc37Balance' => $oldAcc37Balance,
            'newAcc37Balance' => $sy2CashTotal,
        ];
    }

    /**
     * Verify every migrated project's financial totals match legacy sy2_projects exactly.
     */
    private function verifyReconciliation(array $summary): array
    {
        $ctrl = app(ContractingController::class);
        $mismatches = [];

        foreach ($summary['projectMap'] as $oldId => $newId) {
            $oldProj = $summary['projectsByOldId'][$oldId];
            $metrics = $this->computeProjectActualMetrics($newId);

            $expNetBill = round((float) $oldProj->cached_actual_total, 2);
            $actNetBill = round((float) $metrics['net_bill'], 2);
            $expPaid = round((float) $oldProj->cached_collected, 2);
            $actPaid = round((float) $metrics['client_paid'], 2);

            if (abs($expNetBill - $actNetBill) > 0.02 || abs($expPaid - $actPaid) > 0.02) {
                $mismatches[] = [
                    'old_id'   => $oldId,
                    'new_id'   => $newId,
                    'name'     => $oldProj->name,
                    'exp_bill' => $expNetBill,
                    'act_bill' => $actNetBill,
                    'exp_paid' => $expPaid,
                    'act_paid' => $actPaid,
                ];
            }
        }

        $openInstallments = round((float) DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('status', '!=', 'cancelled')
            ->sum('remaining_balance'), 2);

        $openContDebts = round((float) DB::table('company_debts')
            ->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
            ->sum('remaining_balance'), 2);

        $treasury = InstallmentFinanceService::treasurySummary();

        return [
            'all_projects_exact' => empty($mismatches),
            'mismatches'         => $mismatches,
            'open_installments'  => $openInstallments,
            'open_cont_debts'    => $openContDebts,
            'treasury'           => $treasury,
        ];
    }

    private function computeProjectActualMetrics(int $projectId): array
    {
        $totalBillable = (float) DB::table('cont_operations')
            ->where('project_id', $projectId)
            ->where('is_billable', 1)
            ->sum('total_selling');
        $discounts = (float) DB::table('cont_discounts')
            ->where('project_id', $projectId)
            ->sum('amount');
        $netBill = max(0.0, round($totalBillable - $discounts, 2));

        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('id')
            ->first();
        $invoices = DB::table('cont_invoices')->where('project_id', $projectId)->get();

        $ctrl = app(ContractingController::class);
        $clientPaid = round((float) $this->invokeController($ctrl, 'computeProjectClientPaid', $inst, $invoices), 2);

        return [
            'net_bill'    => $netBill,
            'client_paid' => $clientPaid,
        ];
    }

    private function printSummary(array $summary, array $rec, bool $dryRun): void
    {
        $c = $summary['counts'];
        $this->line('=== MIGRATION SUMMARY (' . ($dryRun ? 'DRY-RUN' : 'LIVE') . ') ===');
        $this->line("Projects: {$c['projects']} | Items: {$c['project_items']} | Directory: {$c['directory']}");
        $this->line("Operations: {$c['operations']} | Discounts: {$c['discounts']} | Receipts: {$c['client_receipts']} | Returns: {$c['returns']}");
        $this->line("Debts created: {$c['debts_created']} (open sum: {$c['open_debts_total']}) | Credit logs: {$c['credit_logs']}");
        $this->line("Petty categories added: {$c['petty_categories']} | General petty expenses: {$c['petty_expenses']}");
        $this->line("Quotations: {$c['quotations']} | Surveys: {$c['surveys']} | Manual invoices: {$c['manual_invoices']}");
        $this->line("Account #37 balance: {$summary['oldAcc37Balance']} -> {$summary['newAcc37Balance']}");
        $this->line("Verified Open Receivables (installments): {$rec['open_installments']}");
        $this->line("Verified Open Debts (company_debts): {$rec['open_cont_debts']}");
        $this->line("Treasury Capital: {$rec['treasury']['capital']} | Profit Contracting: {$rec['treasury']['profitContracting']}");

        if (!empty($rec['mismatches'])) {
            foreach ($rec['mismatches'] as $m) {
                $this->error("MISMATCH P#{$m['old_id']} ({$m['name']}): exp={$m['exp_bill']}/{$m['exp_paid']} vs act={$m['act_bill']}/{$m['act_paid']}");
            }
        }
    }

    private function normalizeName(?string $name): string
    {
        $name = trim((string) $name);
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        $map = [
            'عبدالصمد'         => 'عبد الصمد صبحي',
            'مجمد حسن دبور'    => 'محمد حسن دبور',
            'محمود مجديلفه'    => 'محمود مجدي',
        ];

        return $map[$name] ?? $name;
    }

    private function normalizeUnit(?string $unit): string
    {
        $unit = trim((string) $unit);
        return $unit !== '' ? $unit : 'عدد';
    }

    private function combineDateTimestamp(?string $date, ?string $timestamp): string
    {
        $timePart = ($timestamp && strlen($timestamp) >= 19) ? substr($timestamp, 11, 8) : '12:00:00';
        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date . ' ' . $timePart;
        }
        return $timestamp ?: now()->format('Y-m-d H:i:s');
    }

    private function formatWorkerPaymentNotes($wpList, ?string $existingNotes): ?string
    {
        $parts = [];
        if ($existingNotes) {
            $parts[] = trim($existingNotes);
        }
        foreach ($wpList as $wp) {
            if ((float) $wp->discount > 0) {
                $parts[] = "خصم صنايعي {$wp->discount} ج" . ($wp->discount_reason ? " ({$wp->discount_reason})" : '');
            }
        }
        $joined = implode(' | ', array_filter(array_unique($parts)));
        return $joined !== '' ? $joined : null;
    }

    private function upsertDirectory(
        string $type,
        string $name,
        ?string $phone,
        ?string $specialty,
        ?string $notes,
        ?string $createdAt,
        ?string $updatedAt,
        array &$counts
    ): int {
        $existing = DB::table('cont_directory')
            ->where('type', $type)
            ->where('name', $name)
            ->first();

        if ($existing) {
            $updates = [];
            if (empty($existing->phone) && !empty($phone)) {
                $updates['phone'] = $phone;
            }
            if (empty($existing->specialty) && !empty($specialty)) {
                $updates['specialty'] = $specialty;
            }
            if (!empty($updates)) {
                DB::table('cont_directory')->where('id', $existing->id)->update($updates);
            }
            return (int) $existing->id;
        }

        $counts['directory']++;
        return DB::table('cont_directory')->insertGetId([
            'type'           => $type,
            'name'           => $name,
            'phone'          => $phone ?: null,
            'specialty'      => $specialty ?: null,
            'balance_debt'   => 0,
            'balance_credit' => 0,
            'notes'          => $notes ?: null,
            'created_at'     => $createdAt ?: now(),
            'updated_at'     => $updatedAt ?: now(),
        ]);
    }

    private function migrateUnitsAndTitles(): void
    {
        $existingUnits = DB::table('cont_units')->pluck('name')->map(fn ($v) => trim((string) $v))->all();
        $sy2Units = DB::table('sy2_materials')->whereNotNull('unit')->distinct()->pluck('unit')->all();
        foreach ($sy2Units as $u) {
            $u = trim((string) $u);
            if ($u !== '' && !in_array($u, $existingUnits, true)) {
                DB::table('cont_units')->insert(['name' => $u, 'created_at' => now(), 'updated_at' => now()]);
                $existingUnits[] = $u;
            }
        }

        $existingPettyTitles = DB::table('cont_petty_titles')->pluck('title')->map(fn ($v) => trim((string) $v))->all();
        foreach (DB::table('sy2_expenses')->whereNull('project_id')->whereNotNull('description')->distinct()->pluck('description') as $t) {
            $t = trim((string) $t);
            if ($t !== '' && !in_array($t, $existingPettyTitles, true)) {
                DB::table('cont_petty_titles')->insert(['title' => $t, 'created_at' => now(), 'updated_at' => now()]);
                $existingPettyTitles[] = $t;
            }
        }

        $existingExpTitles = DB::table('cont_expense_titles')->pluck('title')->map(fn ($v) => trim((string) $v))->all();
        foreach (DB::table('sy2_expenses')->whereNotNull('project_id')->whereNotNull('description')->distinct()->pluck('description') as $t) {
            $t = trim((string) $t);
            if ($t !== '' && !in_array($t, $existingExpTitles, true)) {
                DB::table('cont_expense_titles')->insert(['title' => $t, 'created_at' => now(), 'updated_at' => now()]);
                $existingExpTitles[] = $t;
            }
        }
    }
}
