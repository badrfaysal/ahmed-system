<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends SystemController
{
    public function index(Request $request)
    {
        // فلاتر
        $module    = $request->input('module', 'all');
        $action    = $request->input('action', 'all');
        $severity  = $request->input('severity', 'all');
        $userId    = $request->input('user_id', 'all');
        $search    = trim((string) $request->input('search', ''));
        $period    = $request->input('period', 'today');
        $fromDate  = $request->input('from_date');
        $toDate    = $request->input('to_date');

        [$start, $end] = $this->resolvePeriod($period, $fromDate, $toDate);

        $q = DB::table('audit_logs');

        if ($module !== 'all')   $q->where('module', $module);
        if ($action !== 'all')   $q->where('action', $action);
        if ($severity !== 'all') $q->where('severity', $severity);
        if ($userId !== 'all')   $q->where('user_id', $userId);
        if ($start && $end)      $q->whereBetween('created_at', [$start, $end]);
        if ($search !== '') {
            $q->where(function($x) use ($search) {
                $x->where('summary', 'LIKE', "%{$search}%")
                  ->orWhere('user_name', 'LIKE', "%{$search}%");
            });
        }

        $logs = $q->orderByDesc('created_at')->limit(500)->get();

        $modules = DB::table('audit_logs')->distinct()->pluck('module');
        $actions = DB::table('audit_logs')->distinct()->pluck('action');
        $users   = DB::table('users')->select('id', 'name')->get();

        // إحصائيات سريعة على الفلتر الحالي
        $statsQ = DB::table('audit_logs');
        if ($start && $end) $statsQ->whereBetween('created_at', [$start, $end]);
        $stats = [
            'total'    => (clone $statsQ)->count(),
            'critical' => (clone $statsQ)->where('severity', 'critical')->count(),
            'warning'  => (clone $statsQ)->where('severity', 'warning')->count(),
            'today'    => DB::table('audit_logs')->whereDate('created_at', today())->count(),
        ];

        return view('audit_log', compact(
            'logs', 'modules', 'actions', 'users', 'stats',
            'module', 'action', 'severity', 'userId', 'search', 'period', 'fromDate', 'toDate'
        ));
    }

    public function show($id)
    {
        $log = DB::table('audit_logs')->where('id', $id)->first();
        if (!$log) {
            return response()->json(['error' => 'سجل غير موجود'], 404);
        }
        $log->old_values = $log->old_values ? json_decode($log->old_values, true) : null;
        $log->new_values = $log->new_values ? json_decode($log->new_values, true) : null;
        return response()->json($log);
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
