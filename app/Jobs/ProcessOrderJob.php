<?php

namespace App\Jobs;


use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public $userId, public $items)
    {
    }

    public function handle(OrderService $service): void
    {
        Log::info('Job started', ['user_id' => $this->userId]);

        try {
            $service->createOrder($this->items);

            Log::info('Order processed', ['user_id' => $this->userId]);

        } catch (\Throwable $e) {
            Log::error('Order failed', [
                'error' => $e->getMessage()
            ]);

            throw $e; // important → marks job as failed
        }
    }
}