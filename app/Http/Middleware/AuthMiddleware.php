<?php
// ══════════════════════════════════════════════════════════
// FILE: app/Http/Middleware/AuthMiddleware.php
// سجّله في Kernel.php:
//   'auth.custom' => \App\Http\Middleware\AuthMiddleware::class,
// ══════════════════════════════════════════════════════════
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // تحديث بيانات المستخدم من DB كل 5 دقائق للتأكد من التغييرات الحية
        $lastCheck = session('auth_last_check', 0);
        if (time() - $lastCheck > 300) {
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            if (!$freshUser || !$freshUser->is_active) {
                session()->flush();
                return redirect()->route('login')
                    ->with('error', 'تم إيقاف حسابك. تواصل مع الإدارة.');
            }
            session(['auth_user' => $freshUser, 'auth_last_check' => time()]);
        }

        return $next($request);
    }
}