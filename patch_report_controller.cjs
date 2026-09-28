const fs = require('fs');

let content = fs.readFileSync('app/Http/Controllers/ReportController.php', 'utf8');

content = content.replace(/'total_deals' => \$activeClientOps->sum\('total_amount'\),/g, "'total_deals' => $activeClientOps->sum('total_amount'),\n                'total_cost' => $activeClientOps->sum('cost_amount'),");

content = content.replace(/'revenue' => \$r\['total_deals'\],/g, "'revenue' => $r['total_deals'],\n                'cost' => $r['total_cost'] ?? 0,");

fs.writeFileSync('app/Http/Controllers/ReportController.php', content, 'utf8');
console.log('Patched ReportController.php');
