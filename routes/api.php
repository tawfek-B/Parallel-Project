<?php
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Jobs\GenerateInvoiceJob;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('orders', [OrderController::class, 'store']);

// العمل بالخلفية للمهام الغير اساسية

Route::get('/invoice-without', [OrderController::class, 'createOrderWithInvoiceSync']);
Route::get('/invoice-with', [OrderController::class, 'createOrderWithInvoiceAsync']);

Route::get('/daily-sales-batch', [OrderController::class, 'dailySalesBatch']);
Route::get('/daily-sales-batch-brute', [OrderController::class, 'dailySalesBruteForce']);

Route::get('/product/{id}', [\App\Http\Controllers\ProductController::class, 'fetch']);

Route::get('/products', [\App\Http\Controllers\ProductController::class, 'fetchAll']);

Route::get('/load-balancer', [OrderController::class, 'simulateLoad']);