<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAhmedPartnerAccess
{
    /**
     * موديول الشركاء سري للغاية ومخصص حصرياً للمدير العام أحمد (المستخدم رقم 1).
     * أي مستخدم آخر، حتى لو كان أدمن أو مطور أو موظف، لن يتمكن من الوصول وسيظهر له خطأ 404
     * حتى لا يعلم أصلاً بوجود هذا الرابط في النظام.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = session('auth_user');

        if ($user && (int) $user->id === 1) {
            return $next($request);
        }

        abort(404);
    }
}
