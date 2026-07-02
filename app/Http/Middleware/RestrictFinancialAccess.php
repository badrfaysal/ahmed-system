<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictFinancialAccess
{
    // 🔒 الموظف اللي الأدمن فعّل له إخفاء الأرقام المالية (users.hide_financials) ممنوع كمان
    // من صفحات لوحة التحكم والتقارير والأهداف بالكامل — مش بس إخفاء أرقام جواها.
    protected array $blockedPrefixes = ['dashboard.', 'reports.', 'goals.'];
    protected array $blockedExact = ['send.daily.report'];

    public function handle(Request $request, Closure $next)
    {
        $user = session('auth_user');
        $routeName = $request->route()?->getName();

        if ($user && (int) ($user->hide_financials ?? 0) === 1 && $routeName) {
            $blocked = in_array($routeName, $this->blockedExact, true)
                || collect($this->blockedPrefixes)->contains(fn ($p) => str_starts_with($routeName, $p));

            if ($blocked) {
                return redirect()->route('treasury.index')->with('error', '⛔ غير مصرح لك بالدخول لهذه الصفحة.');
            }
        }

        return $next($request);
    }
}
