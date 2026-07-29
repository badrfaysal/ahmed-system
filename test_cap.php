<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$construction_net_transactions = DB::table('sy2_accounts')->where('status', 'active')->sum('balance') ?? 0;

$construction_direct_dues = DB::table('sy2_projects')
    ->whereNotIn('id', function($q) {
        $q->select('project_id')->from('sy2_installment_contracts');
    })
    ->selectRaw("SUM(CASE WHEN cached_actual_total > cached_collected THEN cached_actual_total - cached_collected ELSE 0 END) as dues")
    ->value('dues') ?? 0;

$hasClientPayments = \Illuminate\Support\Facades\Schema::hasTable('sy2_client_payments');

if ($hasClientPayments) {
    $construction_excess = DB::table('sy2_projects as p')
        ->join('sy2_installment_contracts as c', 'p.id', '=', 'c.project_id')
        ->selectRaw("SUM(
            CASE WHEN (p.cached_actual_total - (c.total_after_interest + c.discount)) > 0 THEN
                CASE WHEN ((p.cached_actual_total - (c.total_after_interest + c.discount)) - (
                    SELECT COALESCE(SUM(amount + discount), 0) FROM sy2_client_payments cp WHERE cp.project_id = p.id
                )) > 0 THEN
                    (p.cached_actual_total - (c.total_after_interest + c.discount)) - (
                        SELECT COALESCE(SUM(amount + discount), 0) FROM sy2_client_payments cp WHERE cp.project_id = p.id
                    )
                ELSE 0 END
            ELSE 0 END
        ) as excess")
        ->value('excess') ?? 0;
    $construction_direct_dues += $construction_excess;
}

$construction_installment_dues = DB::table('sy2_installment_contracts')
    ->where('status', '!=', 'cancelled')
    ->sum('remaining_balance') ?? 0;

$construction_supplier_debts = DB::table('sy2_supplier_debts')
    ->where('status', '!=', 'paid')
    ->selectRaw("SUM(total_amount - paid_amount) as debts")
    ->value('debts') ?? 0;

$construction_workers_total = DB::table('sy2_band_workers')->sum('amount') ?? 0;
$construction_workers_paid = DB::table('sy2_worker_payments')->selectRaw("SUM(amount + discount) as total_paid")->value('total_paid') ?? 0;
$construction_worker_fees = max(0, $construction_workers_total - $construction_workers_paid);

$clientOverpayments = DB::table('sy2_projects')
    ->whereNotIn('id', function($q) { $q->select('project_id')->from('sy2_installment_contracts'); })
    ->selectRaw("SUM(CASE WHEN cached_collected > cached_actual_total THEN (cached_collected - cached_actual_total) ELSE 0 END) as overpaid")
    ->value('overpaid') ?? 0;

echo json_encode([
    'net_transactions' => $construction_net_transactions,
    'direct_dues' => $construction_direct_dues,
    'installment_dues' => $construction_installment_dues,
    'supplier_debts' => $construction_supplier_debts,
    'worker_fees' => $construction_worker_fees,
    'clientOverpayments' => $clientOverpayments,
    'Total Capital' => ($construction_net_transactions + $construction_direct_dues + $construction_installment_dues) - ($construction_supplier_debts + $construction_worker_fees + $clientOverpayments)
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
