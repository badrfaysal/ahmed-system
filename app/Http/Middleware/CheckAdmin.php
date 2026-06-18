<?php
// ══════════════════════════════════════════════════════════
// FILE: app/Http/Middleware/CheckAdmin.php
// سجّله في Kernel.php:
//   'admin.only' => \App\Http\Middleware\CheckAdmin::class,
// ══════════════════════════════════════════════════════════
namespace App\Http\Middleware;

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

        return redirect()->route('treasury.index')
            ->with('error', 'عذراً! هذه الشاشة محمية وصلاحيتها مخصصة للإدارة فقط.');
    }
}