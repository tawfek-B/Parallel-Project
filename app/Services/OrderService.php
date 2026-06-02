<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Create a new class instance.
     */

    public function createOrder($userId, $items)
    {
        return DB::transaction(function () use ($userId, $items) {

            $order = Order::create([
                'user_id' => $userId,
                'total' => 0
            ]);

            $total = 0;

            foreach ($items as $item) {

                $lock = Cache::lock(
                    'product-stock-' . $item['product_id'],
                    10
                );

                try {

                    $lock->block(5);

                    Log::info('LOCK ACQUIRED', [
                        'product' => $item['product_id']
                    ]);

                    $product = Product::findOrFail(
                        $item['product_id']
                    );

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception(
                            "Not enough stock for {$product->name}"
                        );
                    }

                    $product->decrement(
                        'stock',
                        $item['quantity']
                    );

                    Cache::forget(
                        "product:{$product->id}"
                    );
                    Cache::forget('products:all');

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);

                    $total += $product->price * $item['quantity'];

                } finally {

                    optional($lock)->release();

                    Log::info('LOCK RELEASED', [
                        'product' => $item['product_id']
                    ]);
                }
            }

            $order->update([
                'total' => $total
            ]);

            return $order;
        });
    }

    public function __construct()
    {
        //
    }
}
