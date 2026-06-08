<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function getProduct($id)
    {
        Log::info('CACHE CHECK', [
            'product_id' => $id
        ]);

        return Cache::remember(
            "product:$id",
            300,
            function () use ($id) {

                $start = microtime(true);

                $product = Product::findOrFail($id);

                usleep(500000); // 0.5 seconds

                Log::info('DB QUERY TIME', [
                    'ms' => round((microtime(true) - $start) * 1000, 2)
                ]);

                return $product;
            }
        );
    }

    public function getProducts()
    {
        Log::info('CACHE CHECK ALL');

        return Cache::remember(
            "products:all",
            300,
            function () {
                usleep(500000); // 0.5 seconds

                Log::info("DATABASE QUERY EXECUTED FOR ALL");

                return Product::all()->toArray();
            }
        );
    }
}