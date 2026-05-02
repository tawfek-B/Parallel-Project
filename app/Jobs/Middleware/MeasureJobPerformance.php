<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Log;

class MeasureJobPerformance
{
    public function handle($job, $next)
    {
        $start = microtime(true);

        $next($job);

        $duration = (microtime(true) - $start) * 1000;

        Log::info('AOP JOB PERFORMANCE', [
            'job' => get_class($job),
            'duration_ms' => $duration,
        ]);
    }
}