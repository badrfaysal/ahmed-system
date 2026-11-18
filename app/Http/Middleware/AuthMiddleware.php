<?php
// ══════════════════════════════════════════════════════════
// FILE: app/Http/Middleware/AuthMiddleware.php
// سجّله في Kernel.php:
//   'auth.custom' => \App\Http\Middleware\AuthMiddleware::class,
// ══════════════════════════════════════════════════════════
namespace App\Http\Middleware;

use App\Services\ScreenAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('auth_user')) {
            return redirect()->route('login')
                ->with('error', 'يرجى تسجيل الدخول أولاً.');
        }

        $user = session('auth_user');

        if (!$user->is_active) {
            session()->forget('auth_user');
            return redirect()->route('login')
                ->with('error', 'حسابك موقوف. تواصل مع الإدارة.');
        }

        // مزامنة بيانات المستخدم (الصلاحيات، الدور، الحالة، الشاشات المسموحة) فورياً من قاعدة البيانات
        if (!empty($user->id) && Schema::hasTable('users')) {
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            if (!$freshUser || !$freshUser->is_active) {
                session()->flush();
                return redirect()->route('login')
                    ->with('error', 'تم إيقاف حسابك. تواصل مع الإدارة.');
            }
            $user = $freshUser;
            session(['auth_user' => $freshUser, 'auth_last_check' => time()]);
        }

        if (!ScreenAccessService::canAccessRequest($user, $request)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error'   => '⛔ غير مصرح لك بالدخول لهذه الشاشة.',
                    'message' => '⛔ غير مصرح لك بالدخول لهذه الشاشة.',
                ], 403);
            }

            $homeRoute = ScreenAccessService::homeRouteForUser($user);
            if ($homeRoute && $request->route()?->getName() !== $homeRoute) {
                return redirect()->route($homeRoute)
                    ->with('error', '⛔ غير مصرح لك بالدخول لهذه الشاشة.');
            }

            abort(403, '⛔ غير مصرح لك بالدخول لأي شاشة. يرجى التواصل مع الإدارة.');
        }

        return $next($request);
    }
}