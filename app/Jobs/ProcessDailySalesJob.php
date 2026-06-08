<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\OrderItem;
use App\Models\Product;
use App\Jobs\Middleware\LogJobExecution;
use App\Jobs\Middleware\MeasureJobPerformance;

class ProcessDailySalesJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $sales = [];

        $products = Product::all()->keyBy('id');

        OrderItem::chunkById(100, function ($items) use (&$sales, $products) {

            foreach ($items as $item) {

                $product = $products[$item->product_id] ?? null;

                if (!$product) {
                    continue;
                }

                if (!isset($sales[$item->product_id])) {
                    $sales[$item->product_id] = [
                        'product_name' => $product->name,
                        'total_quantity' => 0,
                        'total_revenue' => 0,
                    ];
                }

                $sales[$item->product_id]['total_quantity']
                    += $item->quantity;

                $sales[$item->product_id]['total_revenue']
                    += $item->quantity * $item->price;
            }
        });

        Log::info('Daily sales summary', $sales);

        return $sales;
    }

    public function middleware()
{
    return [
        new LogJobExecution(),
        new MeasureJobPerformance(),
    ];
}
}
