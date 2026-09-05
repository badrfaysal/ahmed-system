<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PreventDoubleSubmission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            $transactionCode = $request->input('transaction_code');

            if ($transactionCode) {
                $cacheKey = 'transaction_' . $transactionCode;

                if (Cache::has($cacheKey)) {
                    throw ValidationException::withMessages([
                        'transaction_code' => ['تم التسجيل مسبقاً. هذه العملية مكررة.'],
                    ]);
                }

                // Lock for 60 seconds
                Cache::put($cacheKey, true, 60);

                try {
                    $response = $next($request);

                    // If response is not successful, remove the lock so user can try again
                    if (!$response->isSuccessful() && !$response->isRedirection()) {
                        Cache::forget($cacheKey);
                    }

                    return $response;

                } catch (\Exception $e) {
                    // Check if it's a unique constraint violation (code 23000 in MySQL)
                    if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[0] == '23000') {
                        throw ValidationException::withMessages([
                            'transaction_code' => ['تم التسجيل مسبقاً. هذه العملية مكررة (من قاعدة البيانات).'],
                        ]);
                    }

                    Cache::forget($cacheKey);
                    throw $e;
                }
            }
        }

        return $next($request);
    }
}
