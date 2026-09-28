const fs = require('fs');

let content = fs.readFileSync('app/Http/Controllers/AcController.php', 'utf8');

const regex = /\$globalStats\['total_expenses'\] = \$expenses->sum\('amount'\);\s*\$globalStats\['net_profit'\] -= \$globalStats\['total_expenses'\]; \/\/ Adjust net profit/g;

const replacement = `$globalStats['total_expenses'] = $expenses->sum('amount');
        $globalStats['net_profit'] -= $globalStats['total_expenses']; // Adjust net profit
        
        $expensesByClientName = $expenses->groupBy('client_name')->map(function($exps) {
            return $exps->sum('amount');
        })->toArray();

        foreach ($topClients as &$c) {
            $clientExp = $expensesByClientName[$c['name']] ?? 0;
            $c['expenses'] = $clientExp;
            $c['profit'] -= $clientExp;
        }
        unset($c);`;

content = content.replace(regex, replacement);
fs.writeFileSync('app/Http/Controllers/AcController.php', content, 'utf8');
console.log('Patched AcController');
