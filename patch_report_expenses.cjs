const fs = require('fs');

let content = fs.readFileSync('app/Http/Controllers/ReportController.php', 'utf8');

const regexGlobalStats = /'net_profit' => \$activeOps->sum\('profit_amount'\) - \$activeOps->sum\('discount_amount'\),\s*\];/g;

const replacementGlobalStats = `'net_profit' => $activeOps->sum('profit_amount') - $activeOps->sum('discount_amount'),
        ];

        $expenses = \DB::table('ac_expenses')
            ->leftJoin('ac_clients', 'ac_expenses.ac_client_id', '=', 'ac_clients.id')
            ->leftJoin('ac_expense_categories', 'ac_expenses.ac_expense_category_id', '=', 'ac_expense_categories.id')
            ->select('ac_expenses.*', 'ac_clients.name as client_name', 'ac_expense_categories.name as category_name')
            ->whereDate('ac_expenses.date', '>=', $startDate)
            ->whereDate('ac_expenses.date', '<=', $endDate)
            ->get();
            
        $globalStats['total_expenses'] = $expenses->sum('amount');
        $globalStats['net_profit'] -= $globalStats['total_expenses'];

        $expensesByClientName = $expenses->groupBy('client_name')->map(function($exps) {
            return $exps->sum('amount');
        })->toArray();

        foreach ($topClients as &$c) {
            $clientExp = $expensesByClientName[$c['name']] ?? 0;
            $c['expenses'] = $clientExp;
            $c['profit'] -= $clientExp;
        }
        unset($c);`;

content = content.replace(regexGlobalStats, replacementGlobalStats);

const regexReturn = /return compact\('reports', 'globalStats', 'dailyTrend', 'topClients'\);/g;
const replacementReturn = `return compact('reports', 'globalStats', 'dailyTrend', 'topClients', 'expenses');`;
content = content.replace(regexReturn, replacementReturn);

fs.writeFileSync('app/Http/Controllers/ReportController.php', content, 'utf8');
console.log('Patched ReportController.php for expenses');
