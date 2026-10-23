<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAhmedPartnerAccess
{
    /**
     * موديول الشركاء مخصص لمديري النظام (role = admin) بنفس بيانات الدخول المعتادة.
     * أي مستخدم آخر (موظف، مدير فرع، مشاهد) لن يتمكن من الوصول وسيظهر له خطأ 404
     * حتى لا يعلم أصلاً بوجود هذا الرابط في النظام.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = session('auth_user');

        if ($user && ($user->role ?? null) === 'admin') {
            return $next($request);
        }

        abort(404);
    }
}
