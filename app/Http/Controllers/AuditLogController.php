<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends SystemController
{
    public static function moduleLabels(): array
    {
        return [
            'inquiries'     => 'المهام',
            'installments'  => 'منظومة الأقساط',
            'finance'       => 'المالية والخزينة',
            'sales'         => 'المبيعات',
            'inventory'     => 'المخزون',
            'gas_station'   => 'محطة الوقود',
            'debts'         => 'مستحقات العملاء',
            'company_debts' => 'ديون الشركة',
            'expenses'      => 'المصروفات',
            'partners'      => 'الشركاء',
            'hr'            => 'شؤون الموظفين',
            'assets'        => 'الأصول الثابتة',
            'goals'         => 'الأهداف',
            'users'         => 'المستخدمين',
            'settings'      => 'الإعدادات',
            'customers'     => 'أرشيف العملاء',
            'ac'            => 'التكييفات والصيانة',
            'contracting'   => 'المقاولات والتشطيبات',
            'cashflows'     => 'التدفقات (الجمعيات)',
            'operations'    => 'سجل العمليات',
            'auth'          => 'الدخول والخروج',
            'profile'       => 'الملف الشخصي',
        ];
    }

    public static function actionLabels(): array
    {
        return [
            'create'       => 'إضافة',
            'update'       => 'تعديل',
            'delete'       => 'حذف',
            'complete'     => 'إنجاز (مهام سابقة)',
            'reopen'       => 'إرجاع لمهام اليوم',
            'bulk_update'  => 'تعديل جماعي',
            'toggle'       => 'تغيير الحالة',
            'login'        => 'تسجيل دخول',
            'logout'       => 'تسجيل خروج',
            'pay'          => 'تحصيل / سداد',
            'pay_bulk'     => 'تحصيل / سداد جماعي',
            'cancel'       => 'إلغاء',
            'reverse'      => 'عكس عملية',
            'delete_late'  => 'حذف قسط متأخر',
            'writeoff'     => 'إعدام دين',
            'sell'         => 'بيع',
            'return'       => 'مرتجع',
            'restock'      => 'إعادة تموين',
            'ret_supp'     => 'إرجاع للمورد',
            'adjust'       => 'تسوية',
            'transfer'     => 'تحويل',
            'discount'     => 'خصم',
            'distribute'   => 'توزيع أرباح',
            'withdraw'     => 'سحب',
            'reinvest'     => 'إعادة استثمار',
            'exit'         => 'خروج شريك',
            'tx'           => 'حركة موظف',
            'depreciate'   => 'إهلاك',
            'close'        => 'إغلاق',
            'reset_pwd'    => 'إعادة تعيين كلمة السر',
            'rename'       => 'إعادة تسمية',
            'commission'   => 'عمولات',
            'shift_close'  => 'تقفيل وردية',
            'snapshot'     => 'لقطة رأس المال',
            'update_bal'   => 'تعديل رصيد',
            'create_exp'   => 'تسجيل مصروف',
            'add_setting'  => 'إضافة إعداد',
            'edit_setting' => 'تعديل إعداد',
            'delete_set'   => 'حذف إعداد',
            'add_client'   => 'إضافة عميل',
            'show'         => 'عرض / تأكيد',
        ];
    }

    public static function roleLabels(): array
    {
        return [
            'admin'    => 'أدمن',
            'manager'  => 'مدير',
            'employee' => 'موظف',
            'viewer'   => 'مشاهد',
        ];
    }

    public function index(Request $request)
    {
        // فلاتر
        $module   = (string) $request->input('module', 'all');
        $action   = (string) $request->input('action', 'all');
        $severity = (string) $request->input('severity', 'all');
        $userId   = (string) $request->input('user_id', 'all');
        $search   = trim((string) $request->input('search', ''));
        $period   = (string) $request->input('period', 'today');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        [$start, $end] = $this->resolvePeriod($period, $fromDate, $toDate);

        $applyFilters = function ($query, bool $includeAction = true, bool $includeSeverity = true) use ($module, $action, $severity, $userId, $search, $start, $end) {
            if ($module !== 'all')                        $query->where('module', $module);
            if ($includeAction && $action !== 'all')      $query->where('action', $action);
            if ($includeSeverity && $severity !== 'all')  $query->where('severity', $severity);
            if ($userId !== 'all')                        $query->where('user_id', $userId);
            if ($start && $end)                           $query->whereBetween('created_at', [$start, $end]);
            if ($search !== '') {
                $this->applyArabicSearch($query, ['summary', 'user_name', 'old_values', 'new_values'], $search);
            }
        };

        $q = DB::table('audit_logs');
        $applyFilters($q);

        $logs = $q->orderByDesc('created_at')->orderByDesc('id')->limit(500)->get();

        $moduleLabels = self::moduleLabels();
        $actionLabels = self::actionLabels();
        $roleLabels   = self::roleLabels();

        foreach ($logs as $log) {
            $oldArr = $log->old_values ? json_decode($log->old_values, true) : null;
            $newArr = $log->new_values ? json_decode($log->new_values, true) : null;
            $log->old_array    = is_array($oldArr) ? $oldArr : null;
            $log->new_array    = is_array($newArr) ? $newArr : null;
            $log->changes      = $this->computeDiffRows($log->old_array, $log->new_array);
            $log->module_label = $moduleLabels[$log->module] ?? $log->module;
            $log->action_label = $actionLabels[$log->action] ?? $log->action;
            $log->role_label   = $roleLabels[$log->user_role ?? ''] ?? ($log->user_role ?: '-');
        }

        // ضمان وجود "المهام" دائماً في قائمة الأقسام مع كل الأقسام المسجلة في الداتا بيز
        $dbModules = DB::table('audit_logs')->distinct()->pluck('module')->filter()->values()->all();
        $modules   = collect(array_merge(['inquiries'], $dbModules))->unique()->values();

        // ضمان وجود عمليات المهام الأساسية في قائمة العمليات
        $dbActions = DB::table('audit_logs')->distinct()->pluck('action')->filter()->values()->all();
        $actions   = collect(array_merge(['create', 'update', 'delete', 'complete', 'reopen'], $dbActions))->unique()->values();

        $users = DB::table('users')->orderBy('name')->select('id', 'name', 'role')->get();

        // إحصائيات سريعة مطابقة للفلاتر النشطة
        $statsQ = DB::table('audit_logs');
        $applyFilters($statsQ);

        $todayQ = DB::table('audit_logs')->whereDate('created_at', today());
        if ($module !== 'all') {
            $todayQ->where('module', $module);
        }

        $stats = [
            'total'    => (clone $statsQ)->count(),
            'critical' => (clone $statsQ)->where('severity', 'critical')->count(),
            'warning'  => (clone $statsQ)->where('severity', 'warning')->count(),
            'today'    => $todayQ->count(),
        ];

        // عدادات سريعة لعمليات المهام عند اختيار قسم المهام
        $taskActionCounts = [];
        if ($module === 'inquiries') {
            $taskBase = DB::table('audit_logs');
            $applyFilters($taskBase, false, true);
            $taskActionCounts = [
                'all'      => (clone $taskBase)->count(),
                'update'   => (clone $taskBase)->where('action', 'update')->count(),
                'delete'   => (clone $taskBase)->where('action', 'delete')->count(),
                'create'   => (clone $taskBase)->where('action', 'create')->count(),
                'complete' => (clone $taskBase)->where('action', 'complete')->count(),
                'reopen'   => (clone $taskBase)->where('action', 'reopen')->count(),
            ];
        }

        return view('audit_log', compact(
            'logs', 'modules', 'actions', 'users', 'stats',
            'moduleLabels', 'actionLabels', 'roleLabels', 'taskActionCounts',
            'module', 'action', 'severity', 'userId', 'search', 'period', 'fromDate', 'toDate'
        ));
    }

    public function show($id)
    {
        $log = DB::table('audit_logs')->where('id', $id)->first();
        if (!$log) {
            return response()->json(['error' => 'سجل غير موجود'], 404);
        }

        $moduleLabels = self::moduleLabels();
        $actionLabels = self::actionLabels();
        $roleLabels   = self::roleLabels();

        $log->old_values           = $log->old_values ? json_decode($log->old_values, true) : null;
        $log->new_values           = $log->new_values ? json_decode($log->new_values, true) : null;
        $log->module_label         = $moduleLabels[$log->module] ?? $log->module;
        $log->action_label         = $actionLabels[$log->action] ?? $log->action;
        $log->role_label           = $roleLabels[$log->user_role ?? ''] ?? ($log->user_role ?: '-');
        $log->formatted_created_at = $log->created_at
            ? \Carbon\Carbon::parse($log->created_at)->format('Y/m/d - h:i:s A')
            : '-';

        return response()->json($log);
    }

    private function computeDiffRows(?array $old, ?array $new): array
    {
        if (!$old || !$new) {
            return [];
        }

        $keys = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
        $changes = [];
        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;
            if (json_encode($ov, JSON_UNESCAPED_UNICODE) !== json_encode($nv, JSON_UNESCAPED_UNICODE)) {
                $changes[] = [
                    'field' => (string) $k,
                    'old'   => $this->formatAuditScalar($ov),
                    'new'   => $this->formatAuditScalar($nv),
                ];
            }
        }
        return $changes;
    }

    private function formatAuditScalar($val): string
    {
        if ($val === null || $val === '') {
            return '—';
        }
        if (is_bool($val)) {
            return $val ? 'نعم' : 'لا';
        }
        if (is_array($val) || is_object($val)) {
            return (string) json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        return (string) $val;
    }

    private function resolvePeriod(string $period, ?string $from, ?string $to): array
    {
        switch ($period) {
            case 'today':     return [now()->startOfDay(), now()->endOfDay()];
            case 'yesterday': return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];
            case 'week':      return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
            case 'month':     return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
            case 'custom':
                if ($from && $to) {
                    return [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()];
                }
                return [null, null];
            case 'all':
            default:
                return [null, null];
        }
    }
}
