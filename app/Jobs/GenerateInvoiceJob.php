<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Jobs\Middleware\LogJobExecution;
use App\Jobs\Middleware\MeasureJobPerformance;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public $orderId)
    {
    }

    public function handle(): void
    {

        usleep(500000); //0.5 seconds

        Log::info("Invoice generated for Order ID: " . $this->orderId);
    }

    public function middleware()
{
    return [
        new LogJobExecution(),
        new MeasureJobPerformance(),
    ];
}
}
