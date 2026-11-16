<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reconcile existing contracting installment contracts:
     * 1. Consolidate duplicate cont_project rows so converted projects no longer appear in /debts.
     * 2. Aggregate pre-conversion payments into down_payment (مقدم) while preserving cont_invoices receipts.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cont_projects') || !Schema::hasTable('installments') || !Schema::hasTable('cont_invoices')) {
            return;
        }

        DB::transaction(function () {
            $projectIds = collect()
                ->merge(DB::table('cont_invoices')->where('invoice_type', 'installment_contract')->pluck('project_id'))
                ->merge(
                    DB::table('installments')
                        ->where('source_type', 'cont_project')
                        ->where('installment_months', '>', 0)
                        ->where('status', '!=', 'cancelled')
                        ->pluck('source_id')
                )
                ->filter()
                ->unique()
                ->values();

            foreach ($projectIds as $projectId) {
                $project = DB::table('cont_projects')->where('id', $projectId)->first();
                if (!$project) {
                    continue;
                }

                $planInv = DB::table('cont_invoices')
                    ->where('project_id', $projectId)
                    ->where('invoice_type', 'installment_contract')
                    ->orderByDesc('id')
                    ->first();

                $candidates = DB::table('installments')
                    ->where('source_type', 'cont_project')
                    ->where('source_id', $projectId)
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('id')
                    ->get()
                    ->keyBy('id');

                $planInstId = $planInv && !empty($planInv->installment_id) ? intval($planInv->installment_id) : null;
                if ($planInstId) {
                    $linked = DB::table('installments')
                        ->where('id', $planInstId)
                        ->where('status', '!=', 'cancelled')
                        ->first();
                    if ($linked) {
                        $candidates->put($linked->id, $linked);
                    }
                }

                if ($candidates->isEmpty()) {
                    continue;
                }

                $primary = $candidates->sortByDesc(function ($row) use ($planInstId, $projectId) {
                    $isPlan = intval($row->installment_months ?? 0) > 0 ? 10 : 0;
                    $inInv = ($planInstId && intval($row->id) === $planInstId) ? 2 : 0;
                    $hasSrc = (($row->source_type ?? '') === 'cont_project' && intval($row->source_id ?? 0) === intval($projectId)) ? 1 : 0;
                    return $isPlan + $inInv + $hasSrc;
                })->first();

                $dupIds = $candidates->keys()->reject(fn ($id) => intval($id) === intval($primary->id))->values()->all();

                if (!empty($dupIds)) {
                    $extraDown = floatval($candidates->whereIn('id', $dupIds)->sum('down_payment'));
                    $extraDisc = floatval($candidates->whereIn('id', $dupIds)->sum('discount'));

                    if (Schema::hasTable('installment_payments')) {
                        DB::table('installment_payments')
                            ->whereIn('installment_id', $dupIds)
                            ->update(['installment_id' => $primary->id]);
                    }

                    DB::table('cont_invoices')
                        ->where('project_id', $projectId)
                        ->whereIn('installment_id', $dupIds)
                        ->update(['installment_id' => $primary->id]);

                    $mergeUpd = [
                        'sale_type'      => 'contracting',
                        'source_type'    => 'cont_project',
                        'source_id'      => $projectId,
                        'customer_phone' => $project->client_phone,
                        'updated_at'     => now(),
                    ];
                    if ($extraDown > 0) {
                        $mergeUpd['down_payment'] = round(floatval($primary->down_payment ?? 0) + $extraDown, 2);
                    }
                    if ($extraDisc > 0) {
                        $mergeUpd['discount'] = round(floatval($primary->discount ?? 0) + $extraDisc, 2);
                    }
                    DB::table('installments')->where('id', $primary->id)->update($mergeUpd);
                    DB::table('installments')->whereIn('id', $dupIds)->delete();
                    $primary = DB::table('installments')->where('id', $primary->id)->first();
                } else {
                    DB::table('installments')->where('id', $primary->id)->update([
                        'sale_type'      => 'contracting',
                        'source_type'    => 'cont_project',
                        'source_id'      => $projectId,
                        'customer_phone' => $project->client_phone,
                        'updated_at'     => now(),
                    ]);
                    $primary = DB::table('installments')->where('id', $primary->id)->first();
                }

                // Absorb pre-conversion installment_payments into down_payment
                if ($planInv && !empty($planInv->created_at) && Schema::hasTable('installment_payments')) {
                    $cutoffTs = strtotime($planInv->created_at);
                    $allPays = DB::table('installment_payments')
                        ->where('installment_id', $primary->id)
                        ->orderBy('payment_date', 'asc')
                        ->get();

                    $priorPays = $allPays->filter(function ($p) use ($cutoffTs) {
                        return $p->payment_date && strtotime($p->payment_date) <= $cutoffTs;
                    })->values();

                    $priorIds = $priorPays->pluck('id')->all();
                    $absorbedCash = round(floatval($priorPays->sum('amount_paid')), 2);
                    $absorbedDisc = round(floatval($priorPays->sum('discount_applied')), 2);

                    if (!empty($priorIds)) {
                        $receipts = DB::table('cont_invoices')
                            ->where('project_id', $projectId)
                            ->where('invoice_type', 'payment_receipt')
                            ->get();

                        foreach ($receipts as $rc) {
                            if (preg_match('/(?:إيصال #|REC-PAY-)(\d+)/', ($rc->invoice_number ?? '') . ' ' . ($rc->notes ?? ''), $m)) {
                                $pid = intval($m[1]);
                                if (in_array($pid, $priorIds, true)) {
                                    $newInvNum = $rc->invoice_number;
                                    if (str_starts_with($rc->invoice_number ?? '', 'REC-PAY-')) {
                                        $cand = 'DOWN-PAY-' . $pid;
                                        if (!DB::table('cont_invoices')->where('invoice_number', $cand)->where('id', '!=', $rc->id)->exists()) {
                                            $newInvNum = $cand;
                                        }
                                    }
                                    $newNotes = preg_replace('/\(?\s*إيصال\s*#\d+\s*\)?/', "(محتسب كمقدم تقسيط - مرجع #{$pid})", $rc->notes ?? '');
                                    if (!str_contains($newNotes, 'محتسب كمقدم')) {
                                        $newNotes = trim($newNotes . " — محتسب كمقدم تقسيط (مرجع #{$pid})");
                                    }
                                    DB::table('cont_invoices')->where('id', $rc->id)->update([
                                        'invoice_number' => $newInvNum,
                                        'installment_id' => $primary->id,
                                        'notes'          => $newNotes,
                                        'updated_at'     => now(),
                                    ]);
                                }
                            }
                        }

                        DB::table('installment_payments')->whereIn('id', $priorIds)->delete();
                    }

                    // Also attach any unlinked receipts
                    $unlinked = DB::table('cont_invoices')
                        ->where('project_id', $projectId)
                        ->where('invoice_type', 'payment_receipt')
                        ->whereNull('installment_id')
                        ->get();
                    foreach ($unlinked as $ur) {
                        $absorbedCash = round($absorbedCash + floatval($ur->paid_amount), 2);
                        DB::table('cont_invoices')->where('id', $ur->id)->update([
                            'installment_id' => $primary->id,
                            'notes'          => trim(($ur->notes ?: 'دفعة نقدية') . ' (محتسب كمقدم تقسيط)'),
                            'updated_at'     => now(),
                        ]);
                    }

                    $newDown = round(floatval($primary->down_payment ?? 0) + $absorbedCash, 2);
                    $newDisc = round(floatval($primary->discount ?? 0) + $absorbedDisc, 2);
                    $ops = DB::table('cont_operations')->where('project_id', $projectId)->get();
                    $netBilled = max(0, round(
                        floatval($ops->where('is_billable', 1)->sum('total_selling'))
                        - floatval(DB::table('cont_discounts')->where('project_id', $projectId)->sum('amount')),
                        2
                    ));
                    $expectedProfit = max(0, round($netBilled - floatval($ops->sum('total_cost')), 2));
                    $postPaid = floatval(DB::table('installment_payments')->where('installment_id', $primary->id)->sum('amount_paid'));
                    $postDisc = floatval(DB::table('installment_payments')->where('installment_id', $primary->id)->sum('discount_applied'));
                    $totalAfterInterest = floatval($primary->total_after_interest) > 0 ? floatval($primary->total_after_interest) : $netBilled;
                    $newRemaining = max(0, round($totalAfterInterest - ($newDown + $newDisc + $postPaid + $postDisc), 2));
                    $interestExtra = max(0, round($totalAfterInterest - floatval($primary->cash_price ?: $totalAfterInterest), 2));

                    DB::table('installments')->where('id', $primary->id)->update([
                        'down_payment'         => $newDown,
                        'discount'             => $newDisc,
                        'remaining_after_down' => max(0, round($totalAfterInterest - $newDown, 2)),
                        'remaining_balance'    => $newRemaining,
                        'profit'               => max(floatval($primary->profit ?? 0), round($expectedProfit + $interestExtra, 2)),
                        'updated_at'           => now(),
                    ]);

                    if ($newDown > 0 && !str_contains($planInv->notes ?? '', 'مقدم محتسب')) {
                        DB::table('cont_invoices')->where('id', $planInv->id)->update([
                            'installment_id' => $primary->id,
                            'paid_amount'    => 0,
                            'notes'          => trim(($planInv->notes ?? '') . " | مقدم محتسب من الدفعات السابقة: {$newDown} ج"),
                            'updated_at'     => now(),
                        ]);
                    } elseif (intval($planInv->installment_id ?? 0) !== intval($primary->id) || floatval($planInv->paid_amount) > 0) {
                        DB::table('cont_invoices')->where('id', $planInv->id)->update([
                            'installment_id' => $primary->id,
                            'paid_amount'    => 0,
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Data reconciliation is non-destructive and not reverted automatically.
    }
};
