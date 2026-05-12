<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;


class DemoDataSeeder extends Seeder
{
    public function run(): void
{
    OrderItem::truncate();
    Order::truncate();
    Product::truncate();

    // 1) 10 Products
    Product::insert([
        ['name' => 'Laptop', 'stock' => 100, 'price' => 5000],
        ['name' => 'Phone', 'stock' => 100, 'price' => 3000],
        ['name' => 'Mouse', 'stock' => 100, 'price' => 100],
        ['name' => 'Keyboard', 'stock' => 100, 'price' => 200],
        ['name' => 'Monitor', 'stock' => 100, 'price' => 1500],
        ['name' => 'Headphones', 'stock' => 100, 'price' => 400],
        ['name' => 'Charger', 'stock' => 100, 'price' => 150],
        ['name' => 'USB Cable', 'stock' => 100, 'price' => 50],
        ['name' => 'Tablet', 'stock' => 100, 'price' => 2500],
        ['name' => 'Smart Watch', 'stock' => 100, 'price' => 1200],
    ]);

    $products = Product::all();

    // 2) 10 Orders
    for ($i = 1; $i <= 10; $i++) {

        $order = Order::create([
            'user_id' => rand(1, 2),
            'total' => 0,
        ]);

        $total = 0;

        // 3) 10 OrderItems لكل Order
        for ($j = 1; $j <= 10; $j++) {

            $product = $products->random();
            $qty = rand(1, 5);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $product->price,
            ]);

            $total += $qty * $product->price;
        }

        $order->update([
            'total' => $total,
        ]);
    }
}
}
