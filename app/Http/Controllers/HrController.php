<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HrController extends SystemController
{
    // عرض شاشة الموظفين وحساب الرواتب
    public function index()
    {
        $employees = DB::table('employees')->where('status', 'active')->get();
        
        foreach ($employees as $emp) {
            $transactions = DB::table('employee_transactions')
                ->where('employee_id', $emp->id)
                ->where('status', 'pending')
                ->get();

            $emp->advances   = $transactions->where('type', 'advance')->sum('amount');
            $emp->deductions = $transactions->where('type', 'deduction')->sum('amount');
            $emp->bonuses    = $transactions->where('type', 'bonus')->sum('amount');
            
            // معادلة الصافي
            $emp->net_salary = $emp->basic_salary + $emp->bonuses - $emp->advances - $emp->deductions;

            // ✅ جلب تاريخ آخر مرة تم صرف راتب لهذا الموظف
            $lastSalaryTx = DB::table('financial_transactions')
                ->where('type', 'expense')
                ->where('notes', 'LIKE', "صرف راتب الموظف: {$emp->name}%")
                ->orderBy('created_at', 'desc')
                ->first();
                
            $emp->last_salary_date = $lastSalaryTx ? Carbon::parse($lastSalaryTx->created_at)->format('Y-m-d') : 'لم يتم الصرف من قبل';

            // ✅ جلب كل الحركات (pending) لعرضها في البوب أب
            $emp->transactions_details = DB::table('employee_transactions')
                ->where('employee_id', $emp->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get(['type', 'amount', 'notes', 'created_at']);
        }

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        return view('hr', compact('employees', 'accounts'));
    }

    // ✅ إضافة موظف جديد (فاليديشن مريح وواضح)
    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20', // خليناه يقبل أي رقم ومش إجباري
            'basic_salary' => 'required|numeric|min:0',
        ], [
            'name.required'         => '⚠️ اسم الموظف مطلوب.',
            'basic_salary.required' => '⚠️ الراتب الأساسي مطلوب.',
            'basic_salary.numeric'  => '⚠️ الراتب الأساسي يجب أن يكون رقماً.'
        ]);

        try {
            // التحقق من رقم الهاتف لو تم إدخاله عشان ميتكررش
            if ($request->phone) {
                $exists = DB::table('employees')->where('phone', $request->phone)->exists();
                if ($exists) throw new \Exception('⚠️ رقم الهاتف مسجل لموظف آخر بالفعل.');
            }

            DB::table('employees')->insert([
                'name'         => trim($request->name),
                'phone'        => $request->phone ? trim($request->phone) : null,
                'basic_salary' => floatval($request->basic_salary),
                'hiring_date'  => $request->hiring_date ?? now()->toDateString(),
                'status'       => 'active',
                'created_at'   => now()
            ]);

            if (method_exists($this, 'logActivity')) {
                $this->logActivity('create', 'hr', "👥 إضافة موظف جديد: {$request->name}");
            }

            return back()->with('success', '✅ تم تسجيل الموظف الجديد بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage())->withInput();
        }
    }

    // ✅ تسجيل حركة (سلفة، خصم، مكافأة)
    public function addTransaction(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:advance,deduction,bonus',
            'amount'      => 'required|numeric|min:1',
            'account_id'  => 'required_if:type,advance'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $emp = DB::table('employees')->where('id', $request->employee_id)->first();
                
                // لو سلفة، لازم نخصمها من الخزنة
                if ($request->type === 'advance') {
                    $acc = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
                    if (!$acc || $acc->balance < $request->amount) {
                        throw new \Exception("رصيد الخزنة غير كافٍ لصرف السلفة.");
                    }
                    
                    DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $request->amount);
                    
                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $request->amount,
                        'from_account_id' => $request->account_id,
                        'notes'           => "صرف سلفة للموظف: {$emp->name}",
                        'status'          => 'active',
                        'created_at'      => now()
                    ]);
                }

                DB::table('employee_transactions')->insert([
                    'employee_id' => $request->employee_id,
                    'type'        => $request->type,
                    'amount'      => $request->amount,
                    'notes'       => $request->notes,
                    'status'      => 'pending',
                    'created_at'  => now()
                ]);
            });

            return back()->with('success', '✅ تم تسجيل الحركة بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    // ✅ صرف الراتب
   public function paySalary(Request $request)
{
    $request->validate(['employee_id' => 'required', 'amount' => 'required|numeric|min:1', 'account_id' => 'required']);
    try {
        DB::transaction(function () use ($request) {
            $empId = $request->employee_id;
            $amountPaid = floatval($request->amount);

            // 💡 حساب الصافي الحقيقي في الباك إند للحماية من التلاعب
            $emp = DB::table('employees')->where('id', $empId)->first();
            $transactions = DB::table('employee_transactions')->where('employee_id', $empId)->where('status', 'pending')->get();
            $advances   = $transactions->where('type', 'advance')->sum('amount');
            $deductions = $transactions->where('type', 'deduction')->sum('amount');
            $bonuses    = $transactions->where('type', 'bonus')->sum('amount');
            $realNetSalary = $emp->basic_salary + $bonuses - $advances - $deductions;

            // 🛡️ منع صرف الراتب مرتين في نفس الشهر
            $alreadyPaid = DB::table('financial_transactions')
                ->where('type', 'salary_expense')
                ->where('notes', 'LIKE', "صرف راتب الموظف: {$emp->name}%")
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->exists();

            if ($alreadyPaid) {
                throw new \Exception("⚠️ تم صرف راتب {$emp->name} بالفعل هذا الشهر ولا يمكن الصرف مرة أخرى.");
            }

            // خصم الراتب من الخزنة
            $acc = DB::table('accounts')->where('id', $request->account_id)->lockForUpdate()->first();
            if (!$acc || $acc->balance < $amountPaid) throw new \Exception('رصيد الخزنة لا يكفي لصرف الراتب.');
            DB::table('accounts')->where('id', $request->account_id)->decrement('balance', $amountPaid);

            DB::table('financial_transactions')->insert([
                'type' => 'salary_expense', 'amount' => $amountPaid, 'from_account_id' => $request->account_id,
                'notes' => "صرف راتب الموظف: {$emp->name}", 'status' => 'active', 'created_at' => now()
            ]);

            // 💡 المقارنة بالصافي الحقيقي
            if ($amountPaid < $realNetSalary) {
                $diff = $realNetSalary - $amountPaid;
                DB::table('employee_transactions')->insert(['employee_id' => $empId, 'type' => 'bonus', 'amount' => $diff, 'notes' => 'باقي مستحق من راتب ' . date('Y-m'), 'status' => 'pending', 'created_at' => now()]);
            } elseif ($amountPaid > $realNetSalary) {
                $diff = $amountPaid - $realNetSalary;
                DB::table('employee_transactions')->insert(['employee_id' => $empId, 'type' => 'advance', 'amount' => $diff, 'notes' => 'زيادة منصرفة عن الراتب تسجل سلفة', 'status' => 'pending', 'created_at' => now()]);
            }

            DB::table('employee_transactions')->where('employee_id', $empId)->where('status', 'pending')->update(['status' => 'paid', 'updated_at' => now()]);

            if (method_exists($this, 'logActivity')) $this->logActivity('create', 'hr', "💰 صرف راتب للموظف {$emp->name} بمبلغ {$amountPaid} ج");
        });
        return back()->with('success', '✅ تم صرف الراتب وتسوية الحسابات بنجاح.');
    } catch (\Exception $e) { return back()->withInput()->with('error', $e->getMessage()); }
}
    // ✅ تحديث موظف
    public function updateEmployee(Request $request)
    {
        $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'basic_salary' => 'required|numeric|min:0',
        ]);

        try {
            DB::table('employees')
                ->where('id', $request->employee_id)
                ->update([
                    'name'         => trim($request->name),
                    'phone'        => trim($request->phone),
                    'basic_salary' => floatval($request->basic_salary),
                    'updated_at'   => now()
                ]);

            return back()->with('success', '✅ تم تحديث بيانات الموظف بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage())->withInput();
        }
    }

    // ✅ حذف موظف
    public function deleteEmployee(Request $request)
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);

        try {
            $employee = DB::table('employees')->where('id', $request->employee_id)->first();
            
            $pendingTransactions = DB::table('employee_transactions')
                ->where('employee_id', $request->employee_id)
                ->where('status', 'pending')
                ->count();

            if ($pendingTransactions > 0) {
                throw new \Exception("❌ لا يمكن حذف الموظف! يوجد معاملات معلقة ({$pendingTransactions}) لم يتم تسويتها بعد.");
            }

            DB::table('employees')
                ->where('id', $request->employee_id)
                ->update(['status' => 'inactive', 'updated_at' => now()]);

            if (method_exists($this, 'logActivity')) {
                $this->logActivity('delete', 'hr', "🗑️ حذف موظف: {$employee->name}");
            }

            return back()->with('success', '✅ تم حذف الموظف بنجاح.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}