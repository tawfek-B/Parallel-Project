<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

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
                 $product = Product::where('id', $item['product_id'])
                     ->lockForUpdate()
                     ->first();

                 if ($product->stock < $item['quantity']) {
                     throw new \Exception("Not enough stock for product: " . $product->name);
                 }

                 $product->decrement('stock', $item['quantity']);

                 OrderItem::create([
                     'order_id' => $order->id,
                     'product_id' => $product->id,
                     'quantity' => $item['quantity'],
                     'price' => $product->price
                 ]);

                 $total += $product->price * $item['quantity'];
             }

             $order->update(['total' => $total]);

             return $order;
         });
     }

    public function __construct()
    {
        //
    }
}
