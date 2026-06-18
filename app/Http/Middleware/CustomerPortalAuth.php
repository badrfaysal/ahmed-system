<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerPortalAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('portal_customer')) {
            return redirect()->route('portal.login');
        }
        return $next($request);
    }
}
