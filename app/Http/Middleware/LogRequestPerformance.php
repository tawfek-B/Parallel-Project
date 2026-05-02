<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogRequestPerformance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        Log::info('AOP REQUEST PERFORMANCE', [
            'path' => $request->path(),
            'duration_ms' => (microtime(true) - $start) * 1000,
        ]);

        return $response;
    }
}
