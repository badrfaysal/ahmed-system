<?php

namespace App\Http\Middleware;

use App\Services\ScreenAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        if ($user && !empty($user->id)) {
            try {
                if (Schema::hasTable('users')) {
                    $freshUser = DB::table('users')->where('id', $user->id)->first();
                    if ($freshUser) {
                        $user = $freshUser;
                        session(['auth_user' => $freshUser]);
                    }
                }
            } catch (\Throwable $e) {
                // Keep session user fallback if DB is unavailable
            }
        }

        if ($user && (int) ($user->hide_financials ?? 0) === 1 && $routeName) {
            if (ScreenAccessService::getCustomAllowedScreens($user) !== null) {
                return $next($request);
            }

            $blocked = in_array($routeName, $this->blockedExact, true)
                || collect($this->blockedPrefixes)->contains(fn ($p) => str_starts_with($routeName, $p));

            if ($blocked) {
                $homeRoute = ScreenAccessService::homeRouteForUser($user) ?? 'treasury.index';
                return redirect()->route($homeRoute)->with('error', '⛔ غير مصرح لك بالدخول لهذه الصفحة.');
            }
        }

        return $next($request);
    }
}
