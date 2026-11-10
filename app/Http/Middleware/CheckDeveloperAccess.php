<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDeveloperAccess
{
    /**
     * السماح بالدخول لحساب المطور (Developer) فقط، وإظهار شاشة "قيد التنفيذ سوف تتاح قريباً" لباقي المستخدمين.
     */
    public function handle(Request $request, Closure $next, string $module = '')
    {
        if (function_exists('isDeveloperUser') && isDeveloperUser()) {
            return $next($request);
        }

        $msg = 'الشاشة قيد التنفيذ سوف تتاح قريباً';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error'   => $msg,
                'message' => $msg,
            ], 403);
        }

        if ($request->isMethod('GET')) {
            $isContracting = $module === 'contracting' || str_starts_with($request->path(), 'contracting');
            $screenTitle = $isContracting ? 'المقاولات والتشطيبات' : 'منظومة التدفقات النقدية (الجمعيات)';
            $screenIcon  = $isContracting ? 'fa-hard-hat' : 'fa-hand-holding-dollar';

            return response()->view('coming_soon', [
                'screenTitle' => $screenTitle,
                'screenIcon'  => $screenIcon,
            ]);
        }

        return redirect()->back()->with('error', $msg);
    }
}
