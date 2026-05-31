<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function getProduct($id)
    {
        return Cache::remember(
            "product:$id",
            300, // 5 minutes
            function () use ($id) {

                Log::info("DATABASE QUERY EXECUTED");

                return Product::findOrFail($id);
            }
        );
    }
}