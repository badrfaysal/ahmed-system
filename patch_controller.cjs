const fs = require('fs');

let content = fs.readFileSync('app/Http/Controllers/AcController.php', 'utf8');

const regex = /public function clientProfileAjax\(\$id\)\s*\{[\s\S]*?\$maintProfit = \$maintOps->sum\('profit_amount'\);/g;

const replacement = `public function clientProfileAjax(Request $request, $id)
    {
        $client = DB::table('ac_clients')->where('id', $id)->first();
        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client not found'], 404);
        }

        $floorsCount = DB::table('ac_floors')->where('ac_client_id', $id)->count();
        $classesCount = DB::table('ac_classes')->where('ac_client_id', $id)->count();

        $opsQuery = DB::table('ac_operations')->where('ac_client_id', $id);
        $expQuery = DB::table('ac_expenses')->where('ac_client_id', $id);

        if ($request->filled('start_date')) {
            $opsQuery->whereDate('date', '>=', $request->start_date);
            $expQuery->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $opsQuery->whereDate('date', '<=', $request->end_date);
            $expQuery->whereDate('date', '<=', $request->end_date);
        }

        $ops = $opsQuery->get();
        $salesOps = $ops->where('type', 'sale');
        $maintOps = $ops->where('type', 'maintenance');

        $expensesTotal = $expQuery->sum('amount');
        $discountsTotal = $ops->sum('discount_amount');

        $salesCount = $salesOps->count();
        $salesRevenue = $salesOps->sum('total_amount');
        $salesCost = $salesOps->sum('cost_amount');
        $salesProfit = $salesOps->sum('profit_amount');

        $maintCount = $maintOps->count();
        $maintRevenue = $maintOps->sum('total_amount');
        $maintCost = $maintOps->sum('cost_amount');
        $maintProfit = $maintOps->sum('profit_amount');`;

content = content.replace(regex, replacement);
fs.writeFileSync('app/Http/Controllers/AcController.php', content, 'utf8');
console.log('Patched Controller');
