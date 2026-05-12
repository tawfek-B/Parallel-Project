<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public $orderId)
    {
    }

    public function handle(): void
    {

        sleep(10);

        Log::info("Invoice generated for Order ID: " . $this->orderId);
    }
}
