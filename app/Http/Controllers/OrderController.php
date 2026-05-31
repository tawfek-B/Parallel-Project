<?php

namespace App\Http\Controllers;
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
    public function store(Request $request)
    {
        $userId = User::find(rand(1, User::max('id')))->id;

        $executed = RateLimiter::attempt(
            'orders:' . $userId,
            10,
            function () use ($userId, $request) {

                ProcessOrderJob::dispatch(
                    $userId,
                    $request->items ?? [
                        ['product_id' => 1, 'quantity' => 1],
                        ['product_id' => 2, 'quantity' => 1],
                    ]
                );
            }
        );

        if (!$executed) {
            Log::info("Rate limit exceeded for user: $userId");
        }

        Log::info("Order request received for user: $userId");
    }
    // اصدار فاتورة بدون مزامنة
    public function createOrderWithInvoiceSync(Request $request)
    {
        $service = app(OrderService::class);

        $start = microtime(true);

        $order = $service->createOrder(
            User::find(rand(1, User::max('id')))->id,
            $request->items ?? [
                ['product_id' => 1, 'quantity' => 1],
                ['product_id' => 2, 'quantity' => 1],
            ]
        );

        usleep(200000); //0.2 seconds

        $time = microtime(true) - $start;

        return response()->json([
            'message' => 'Order created, thank you for waiting while we generate your invoice',
            'order_id' => $order->id,

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
                ['product_id' => 1, 'quantity' => 1],
                ['product_id' => 2, 'quantity' => 1],
            ]
        );

        // إصدار الفاتورة بالخلفية
        GenerateInvoiceJob::dispatch($order->id);

        $time = microtime(true) - $start;

        return response()->json([
            'message' => 'your order created',
            'order_id' => $order->id,

        ]);
    }
    // الحل الفعلي
    public function dailySalesBatch()
    {
        $start = microtime(true);

        $sales = [];

        $products = Product::all()->keyBy('id');

        OrderItem::chunk(100, function ($items) use (&$sales, $products) {
            foreach ($items as $item) {

                $product = $products[$item->product_id] ?? null;
                if (!$product)
                    continue;

                if (!isset($sales[$item->product_id])) {
                    $sales[$item->product_id] = [
                        'product_name' => $product->name,
                        'total_quantity' => 0,
                        'total_revenue' => 0,
                    ];
                }

                $sales[$item->product_id]['total_quantity'] += $item->quantity;
                $sales[$item->product_id]['total_revenue'] += $item->quantity * $item->price;
            }
        });

        $time = microtime(true) - $start;

        return response()->json([
            'message' => 'Daily sales processed in batches',
            'execution_time' => round($time, 2) . ' sec',
            'sales_summary' => array_values($sales),
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