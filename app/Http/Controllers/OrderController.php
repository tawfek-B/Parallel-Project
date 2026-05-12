<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Jobs\ProcessOrderJob;
use Illuminate\Http\Request;
use App\Jobs\GenerateInvoiceJob;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
use App\Models\OrderItem;
use App\Models\Product;


class OrderController extends Controller
{
    public function store(Request $request)
    {
        ProcessOrderJob::dispatch(
            auth()->id() ?? 1,
            $request->items ?? [
                ['product_id' => 1, 'quantity' => 1],
                ['product_id' => 2, 'quantity' => 1],
            ]
        );

        return response()->json([
            'message' => 'Order queued successfully',
        ]);
    }
    // اصدار فاتورة بدون مزامنة
public function createOrderWithInvoiceSync(Request $request)
{
    $service = app(OrderService::class);

    $start = microtime(true);

    $order = $service->createOrder(
        $request->items ?? [
            ['product_id' => 1, 'quantity' => 1],
            ['product_id' => 2, 'quantity' => 1],
        ]
    );

    sleep(10);

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

    OrderItem::chunk(2, function ($items) use (&$sales) {

        foreach ($items as $item) {

            // إذا المنتج مو موجود بالمصفوفة
            if (!isset($sales[$item->product_id])) {
                $product = Product::find($item->product_id);

                $sales[$item->product_id] = [
                    'product_name' => $product->name,
                    'total_quantity' => 0,
                    'total_revenue' => 0,
                ];
            }

            // جمع الكميات
            $sales[$item->product_id]['total_quantity']
                += $item->quantity;

            // جمع الإيرادات
            $sales[$item->product_id]['total_revenue']
                += $item->quantity * $item->price;
        }

    });
    $time = microtime(true) - $start;

    return response()->json([
        'message' => 'Daily sales processed in batches',
        'execution_time' => round($time, 2) . ' sec',
        'sales_summary' => array_values($sales),
    ]);
}
}




