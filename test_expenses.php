<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$expenses = \DB::table('ac_expenses')
    ->leftJoin('ac_clients', 'ac_expenses.ac_client_id', '=', 'ac_clients.id')
    ->select('ac_expenses.*', 'ac_clients.name as client_name')
    ->get();

$topExpenseClients = $expenses->groupBy('client_name')->map(function($exps) {
    return $exps->sum('amount');
})->sortDesc()->take(5)->toArray();

print_r($topExpenseClients);
