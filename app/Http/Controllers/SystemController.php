<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AppliesAccountCommission;
use App\Http\Controllers\Concerns\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemController extends Controller
{
    use LogsActivity, AppliesAccountCommission;

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
        // تحديد اسم الملف وتاريخه
        $filename = "database_backup_" . date('Y-m-d_H-i-s') . ".sql";
        
        // استخدمنا DIRECTORY_SEPARATOR عشان الويندوز يظبط مسار الفولدر صح بدون لخبطة
        $path = storage_path('app' . DIRECTORY_SEPARATOR . $filename);

        // سحب بيانات الاتصال من ملف .env
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');
        $host = env('DB_HOST');

        // 🔴 هنا الحل: حط المسار الكامل لأداة mysqldump 
        // ده مسار XAMPP الافتراضي، لو بتستخدم Laragon عدله لمسار مجلد bin الخاص بـ mysql عندك
        $mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe'; 

        // إعداد أمر الـ mysqldump
        $passwordOption = $password ? "--password={$password}" : "";
        
        // وضعنا المسار بالكامل بين علامات تنصيص لتجنب أي مشاكل في المسافات
$command = "\"{$mysqldumpPath}\" --user={$username} {$passwordOption} --host={$host} {$database} --skip-extended-insert > \"{$path}\" 2>&1";
        // تنفيذ الأمر واستقبال النتيجة
        $output = [];
        $returnVar = null;
        exec($command, $output, $returnVar);

        // لو الأمر فشل
        if ($returnVar !== 0) {
            dd('فشل التصدير. تفاصيل الخطأ من النظام:', $output, 'الأمر المستخدم:', $command);
        }

        // لو نجح، تحميل الملف وبعدين مسحه
        return response()->download($path)->deleteFileAfterSend(true);
    }
}
