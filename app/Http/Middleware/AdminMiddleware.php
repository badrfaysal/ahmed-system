<?php
 namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class AdminMiddleware {
  public function handle(Request $request, Closure $next) {
    if (session('auth_user')?->role !== 'admin') { abort(403); }
    return $next($request);  }
}