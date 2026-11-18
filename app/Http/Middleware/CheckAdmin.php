<?php
// ══════════════════════════════════════════════════════════
// FILE: app/Http/Middleware/CheckAdmin.php
// سجّله في Kernel.php:
//   'admin.only' => \App\Http\Middleware\CheckAdmin::class,
// ══════════════════════════════════════════════════════════
namespace App\Http\Middleware;

use App\Services\ScreenAccessService;
use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // النظام يستخدم session('auth_user') وليس auth() المدمج في Laravel
        $user = session('auth_user');

        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        if ($user) {
            $allowedForRequest = ScreenAccessService::allowedScreensForRequest($request);
            $isSettingsRoute   = $allowedForRequest && in_array('settings', $allowedForRequest, true);
            if (!$isSettingsRoute && ScreenAccessService::hasExplicitAccessForRequest($user, $request)) {
                return $next($request);
            }
        }

        $homeRoute = $user ? ScreenAccessService::homeRouteForUser($user) : 'login';
        if (!$homeRoute) {
            abort(403, 'عذراً! هذه الشاشة محمية وصلاحيتها مخصصة للإدارة فقط.');
        }

        return redirect()->route($homeRoute)
            ->with('error', 'عذراً! هذه الشاشة محمية وصلاحيتها مخصصة للإدارة فقط.');
    }
}