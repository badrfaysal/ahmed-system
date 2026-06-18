<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware عام للتحقق من دور المستخدم.
 * استخدام في routes:
 *   Route::post('/critical-action', ...)->middleware('role:admin');
 *   Route::post('/cashier-action', ...)->middleware('role:admin,cashier');
 *
 * لازم يكون مسجل في Kernel.php:
 *   'role' => \App\Http\Middleware\CheckRole::class,
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = session('auth_user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        if (empty($roles)) {
            // مفيش أدوار محددة — يكفي المصادقة
            return $next($request);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // لو AJAX/JSON request
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error'  => 'صلاحية غير كافية لهذا الإجراء.',
                'needed' => $roles,
                'have'   => $user->role,
            ], 403);
        }

        return redirect()->back()->with('error',
            'صلاحية غير كافية. هذا الإجراء يحتاج صلاحية: ' . implode(' أو ', $roles)
        );
    }
}
