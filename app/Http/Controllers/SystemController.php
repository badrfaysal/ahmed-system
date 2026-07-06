<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AppliesAccountCommission;
use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Concerns\NormalizesArabicText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemController extends Controller
{
    use LogsActivity, AppliesAccountCommission, NormalizesArabicText;

    public function resetDatabase()
    {
        $user = session('auth_user');
        if (!$user || ($user->role ?? '') !== 'admin') {
            return back()->with('error', 'غير مصرح.');
        }

        try {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql')  DB::statement('SET FOREIGN_KEY_CHECKS=0');
            if ($driver === 'sqlite') DB::statement('PRAGMA foreign_keys = OFF');

            $truncate = [
                'fuel_transactions', 'installments', 'installment_payments',
                'company_debts', 'financial_transactions', 'sales',
                'inventory_movements', 'sale_returns', 'batch_expenses',
                'installment_expenses', 'operation_revisions', 'capital_snapshots',
                'shift_closures', 'activity_logs', 'goals', 'customer_inquiries',
                'customers', 'messages', 'assets', 'hr_transactions',
            ];

            foreach ($truncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            DB::table('accounts')->update(['balance' => 0]);

            if (Schema::hasTable('partners')) {
                DB::table('partners')->update(['profit_wallet' => 0]);
            }
            if (Schema::hasTable('hr_employees')) {
                DB::table('hr_employees')->update([
                    'total_paid'    => 0,
                    'total_deducted'=> 0,
                    'net_balance'   => 0,
                ]);
            }

            if ($driver === 'mysql')  DB::statement('SET FOREIGN_KEY_CHECKS=1');
            if ($driver === 'sqlite') DB::statement('PRAGMA foreign_keys = ON');

            return redirect('/dashboard')->with('success', '✅ تم تصفير قاعدة البيانات — النظام جاهز للتيست.');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل التصفير: ' . $e->getMessage());
        }
    }

    public static function getTotalCapital()
    {
        // 1. السيولة في الخزن والبنوك
        $liquidity = \Illuminate\Support\Facades\DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->sum('balance');

        // 2. البضاعة في المخازن (سعر الشراء × الكمية المتبقية)
        $inventory = \Illuminate\Support\Facades\DB::table('sales')
            ->where('inventory_status', 'to_inventory')
            ->selectRaw('COALESCE(SUM(purchase_price * remaining_quantity), 0) as total')
            ->value('total');

        // 3. الأصول الثابتة (النشطة فقط)
        $fixedAssets = \Illuminate\Support\Facades\DB::table('assets')
            ->where('status', 'active')
            ->sum('current_value');

        // 4. الديون اللي لينا (مستحقات عند العملاء)
        $debtsForUs = \Illuminate\Support\Facades\DB::table('installments')
            ->sum('remaining_balance');

        // 5. الديون اللي علينا (للموردين والصنايعية)
        $debtsOnUs = \Illuminate\Support\Facades\DB::table('company_debts')
            ->sum('remaining_balance');

        // صافي رأس المال = الأصول (سيولة + مخزون + أصول + ديون لينا) - الخصوم (ديون علينا)
        return floatval($liquidity + $inventory + $fixedAssets + $debtsForUs - $debtsOnUs);
    }

public function exportDatabase()
    {
        $filename = "database_backup_" . date('Y-m-d_H-i-s') . ".sql";
        $path     = storage_path('app' . DIRECTORY_SEPARATOR . $filename);

        // نقرأ من الـ config (مش env مباشرة) عشان يشتغل صح بعد config:cache على السيرفر —
        // env() بترجّع null لما الكونفيج يكون cached. القيم دي مصدرها ملف .env عبر config/database.php.
        $connection = config('database.default', 'mysql');
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $database = config("database.connections.{$connection}.database");
        $host     = config("database.connections.{$connection}.host", '127.0.0.1');
        $port     = config("database.connections.{$connection}.port", '3306');

        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]
            );
        } catch (\Exception $e) {
            return back()->with('error', 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
        }

        $sql  = "-- ============================================================\n";
        $sql .= "-- backup: {$database}  |  date: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- generated by Ahmed-System (PHP PDO) — compatible export\n";
        $sql .= "-- ============================================================\n\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET CHARACTER SET utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET TIME_ZONE = '+00:00';\n\n";

        // قائمة الجداول
        $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // ── CREATE TABLE ──
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createRow['Create Table'] ?? $createRow[array_key_last($createRow)];

            // توافق: استبدل أي collation خاص بإصدار جديد بـ utf8mb4_general_ci
            $createSql = preg_replace(
                '/utf8mb4_0900[a-z_]*/i',
                'utf8mb4_general_ci',
                $createSql
            );
            // استبدل ENGINE InnoDB row format الاختيارية اللي بتسبب مشاكل أحياناً
            $createSql = preg_replace('/\s*ROW_FORMAT\s*=\s*\w+/i', '', $createSql);

            $sql .= "-- ── table: `{$table}` ──\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            // ── INSERT DATA ──
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $columns = '`' . implode('`, `', array_keys($rows[0])) . '`';
                // نجمّع كل الصفوف في INSERT واحد (أسرع في الاستيراد)
                $valueGroups = [];
                foreach ($rows as $row) {
                    $vals = array_map(function ($v) use ($pdo) {
                        if ($v === null) return 'NULL';
                        return $pdo->quote($v);
                    }, array_values($row));
                    $valueGroups[] = '(' . implode(', ', $vals) . ')';
                    // كل 500 صف: اكتب INSERT وابدأ من جديد عشان الملف ما يكبرش
                    if (count($valueGroups) >= 500) {
                        $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES\n"
                              . implode(",\n", $valueGroups) . ";\n";
                        $valueGroups = [];
                    }
                }
                if (!empty($valueGroups)) {
                    $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES\n"
                          . implode(",\n", $valueGroups) . ";\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sql .= "-- ── end of backup ──\n";

        file_put_contents($path, $sql);

        return response()->download($path, $filename, [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }
}
