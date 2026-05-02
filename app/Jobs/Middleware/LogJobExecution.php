<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Log;

class LogJobExecution
{
    public function handle($job, $next)
    {
        Log::info('AOP BEFORE JOB', [
            'job' => get_class($job),
        ]);

        $next($job);

        Log::info('AOP AFTER JOB', [
            'job' => get_class($job),
        ]);
    }
}