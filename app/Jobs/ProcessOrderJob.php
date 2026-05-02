<?php

namespace App\Jobs;


use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;
class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public $userId, public $items)
    {
    }

    public function handle(OrderService $service): void
    {
        $service->createOrder($this->items);
    }

    public function middleware()
    {
        return [
            new \App\Jobs\Middleware\LogJobExecution,
            new \App\Jobs\Middleware\MeasureJobPerformance,
            new \Illuminate\Queue\Middleware\RateLimited('orders'),
        ];
    }
}