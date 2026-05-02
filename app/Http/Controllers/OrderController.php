<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOrderJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
}