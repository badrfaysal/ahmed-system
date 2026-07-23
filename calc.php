<?php
$net_transactions = DB::table('financial_transactions')
    ->whereNotNull('construction_id')
    ->where('ref_type', 'construction')
    ->where('status', '!=', 'cancelled')
    ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net_amount")
    ->value('net_amount') ?? 0;

$direct_dues = DB::table('ahmed1.sy2_projects')
    ->whereNotIn('id', function($q) {
        $q->select('project_id')->from('ahmed1.sy2_installment_contracts');
    })
    ->selectRaw("SUM(cached_actual_total - cached_collected) as dues")
    ->value('dues') ?? 0;

$installment_dues = DB::table('ahmed1.sy2_projects')
    ->whereIn('id', function($q) {
        $q->select('project_id')->from('ahmed1.sy2_installment_contracts');
    })
    ->selectRaw("SUM(cached_actual_total - cached_collected) as dues")
    ->value('dues') ?? 0;

$supplier_debts = DB::table('ahmed1.sy2_supplier_debts')
    ->where('status', '!=', 'paid')
    ->selectRaw("SUM(total_amount - paid_amount) as debts")
    ->value('debts') ?? 0;

$workers_total = DB::table('ahmed1.sy2_band_workers')->sum('amount') ?? 0;
$workers_paid = DB::table('ahmed1.sy2_worker_payments')->selectRaw("SUM(amount + discount) as total_paid")->value('total_paid') ?? 0;
$worker_fees = $workers_total - $workers_paid;

$total_construction_capital = ($net_transactions + $direct_dues + $installment_dues) - ($supplier_debts + $worker_fees);

echo json_encode([
    'net_transactions' => $net_transactions,
    'direct_dues' => $direct_dues,
    'installment_dues' => $installment_dues,
    'supplier_debts' => $supplier_debts,
    'worker_fees' => $worker_fees,
    'total_construction_capital' => $total_construction_capital
], JSON_PRETTY_PRINT);
