<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    // ════════════════════════════════════════════════════════
    // شاشة الإشعارات (أدمن فقط)
    // ════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        if (session('auth_user')?->role !== 'admin') abort(403);

        $userFilter   = $request->input('user', '');
        $moduleFilter = $request->input('module', '');
        $actionFilter = $request->input('action', '');
        $dateFrom     = $request->input('date_from', '');
        $dateTo       = $request->input('date_to', '');

        $query = DB::table('activity_logs')
            ->orderBy('id', 'desc');

        if ($userFilter)   $query->where('user_name', 'LIKE', "%{$userFilter}%");
        if ($moduleFilter) $query->where('module', $moduleFilter);
        if ($actionFilter) $query->where('action', $actionFilter);
        if ($dateFrom)     $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)       $query->whereDate('created_at', '<=', $dateTo);

        $logs = $query->paginate(50)->withQueryString();

        // إحصائيات سريعة
        $stats = [
            'unread'         => DB::table('activity_logs')->where('is_read', 0)->count(),
            'today'          => DB::table('activity_logs')->whereDate('created_at', today())->count(),
            'total'          => DB::table('activity_logs')->count(),
            'unique_users'   => DB::table('activity_logs')->distinct('user_name')->count('user_name'),
        ];

        // قوائم الفلاتر
        $users   = DB::table('activity_logs')->distinct()->pluck('user_name');
        $modules = ['installments','sales','gas','inventory','finance','treasury','settings','auth'];
        $actions = ['create','update','delete','login','logout','cancel','payment','transfer'];

        // تأشير كلها كـ مقروءة
        DB::table('activity_logs')->where('is_read', 0)->update(['is_read' => 1]);

        return view('notifications', compact(
            'logs', 'stats', 'users', 'modules', 'actions',
            'userFilter', 'moduleFilter', 'actionFilter', 'dateFrom', 'dateTo'
        ));
    }

    // ── حذف سجل واحد ──
    public function destroy($id)
    {
        if (session('auth_user')?->role !== 'admin') abort(403);
        DB::table('activity_logs')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف السجل.');
    }

    // ── حذف كل السجلات القديمة (أكثر من 30 يوم) ──
    public function clearOld()
    {
        if (session('auth_user')?->role !== 'admin') abort(403);
        $deleted = DB::table('activity_logs')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
        return back()->with('success', "تم حذف {$deleted} سجل قديم.");
    }
}