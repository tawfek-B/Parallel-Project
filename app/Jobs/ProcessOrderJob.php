<?php

namespace App\Jobs;

use App\Jobs\Middleware\LogJobExecution;
use App\Jobs\Middleware\MeasureJobPerformance;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 3;
    public $backoff = 5;

    public function __construct(
        public $userId,
        public $items
    ) {}

    public function handle(OrderService $service): void
    {
        $service->createOrder(
            $this->userId,
            $this->items
        );
    }

    public function middleware()
    {
        return [
            new LogJobExecution(),
            new MeasureJobPerformance(),
            new RateLimited('orders'),
        ];
    }
}