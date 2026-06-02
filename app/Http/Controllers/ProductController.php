<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    public function fetch($id, ProductService $service)
    {
        $start = microtime(true);

        $product = $service->getProduct($id);

        $time = (microtime(true) - $start) * 1000;

        return response()->json([
            'product' => $product,
            'execution_time_ms' => round($time, 2)
        ]);
    }

    public function fetchAll(ProductService $service) {
        $start = microtime(true);

        $products = $service->getProducts();

        $time = (microtime(true) - $start) * 1000;

        return response()->json([
            'products' => $products,
            'execution_time_ms' => round($time, 2)
        ]);
    }
}
