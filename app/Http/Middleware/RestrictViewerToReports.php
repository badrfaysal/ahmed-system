<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictViewerToReports
{
    // 👁️ دور "مشاهد" مقيّد بصفحة التقارير فقط (عرض + تصدير) + تسجيل الخروج — أي حاجة تانية تترفض
    protected array $allowed = [
        'reports.index',
        'reports.export',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = session('auth_user');
        $routeName = $request->route()?->getName();

        if ($user && $user->role === 'viewer' && (!$routeName || !in_array($routeName, $this->allowed, true))) {
            return redirect()->route('reports.index')->with('error', '⛔ صلاحيتك تسمح بعرض التقارير فقط.');
        }

        return $next($request);
    }
}
