<?php

namespace App\Http\Controllers;
use App\Jobs\ProcessDailySalesJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Jobs\ProcessOrderJob;
use App\Models\User;
use App\Services\LoadBalancerService;
use Illuminate\Http\Request;
use App\Jobs\GenerateInvoiceJob;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;


class OrderController extends Controller
{
    //old version
    // public function store(Request $request) {
    //     $userId = User::find(rand(1, User::max('id')))->id;

    //     $executed = RateLimiter::attempt(
    //         'orders:' . $userId,
    //         10,
    //         function () use ($userId, $request) {

    //             ProcessOrderJob::dispatch(
    //                 $userId,
    //                 $request->items ?? [
    //                     ['product_id' => 1, 'quantity' => 1],
    //                     ['product_id' => 2, 'quantity' => 1],
    //                 ]
    //             );
    //         }
    //     );
    //     if (!$executed) {
    //         Log::info("Rate limit exceeded for user: $userId");
    //     }

    //     Log::info("Order request received for user: $userId");
    // }

    public function store(Request $request)
    {
        $userId = User::inRandomOrder()->value('id');

        $executed = RateLimiter::attempt(
            "orders:$userId",
            10,
            function () use ($userId, $request) {

                ProcessOrderJob::dispatch(
                    $userId,
                    $request->items ?? [
                        ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
                        ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
                    ]
                );
            },
            60
        );

        if (!$executed) {
            Log::warning("Rate limit exceeded for user: $userId");
            return response()->json([
                'message' => 'Too many orders, please try again later.'
            ], 429);
        }

        Log::info("Order request received for user: $userId");

        return response()->json([
            'message' => 'Order queued'
        ]);
    }

    // اصدار فاتورة بدون مزامنة
    public function createOrderWithInvoiceSync(Request $request)
    {
        $service = app(OrderService::class);

        $start = microtime(true);

        $order = $service->createOrder(
            User::find(rand(1, User::max('id')))->id,
            $request->items ?? [
                ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
                ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
            ]
        );

        // usleep(200000); //0.2 seconds

        $time = microtime(true) - $start;

        return response()->json([
            'message' => 'Order created, thank you for waiting while we generate your invoice',
            'order_id' => $order->id,
            'execution_time_seconds' => round($time, 2)

        ]);
    }
    // اصدار الفاتورة مع مزامنة
    public function createOrderWithInvoiceAsync(Request $request)
    {
        $service = app(OrderService::class);

        $start = microtime(true);

        $order = $service->createOrder(
            User::find(rand(1, User::max('id')))->id,
            $request->items ?? [
                ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
                ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
            ]
        );

        // إصدار الفاتورة بالخلفية
        GenerateInvoiceJob::dispatch($order->id);

        $time = microtime(true) - $start;

        return response()->json([
            'message' => 'Your order has been placed successfully, and your invoice is being generated. Thank you for your patience!',
            'order_id' => $order->id,
            'execution_time_seconds' => round($time, 2)

        ]);
    }
    // الحل الفعلي
    public function dailySalesBatch()
    {
        $start = microtime(true);

        ProcessDailySalesJob::dispatch();

        $time = microtime(true) - $start;

        return response()->json([
            'message' => 'Daily sales processing queued',
            'execution_time_seconds' => $time
        ]);
    }

    public function dailySalesBruteForce()
    {
        $start = microtime(true);

        $orders = Order::with('items')->get();

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $sales[$item->product_id] = ($sales[$item->product_id] ?? 0) + ($item->quantity * $item->price);
            }
        }

        $time = microtime(true) - $start;

        return response()->json([
            'message' => 'Daily sales summary',
            'sales' => $sales,
            'execution_time_seconds' => $time
        ]);

    }

    public function simulateLoad()
    {
        $result = [];

        for ($i = 1; $i <= 100; $i++) {

            $server = app(LoadBalancerService::class)
                ->distribute($i);

            $result[$server] =
                ($result[$server] ?? 0) + 1;
        }

        return $result;
    }

}