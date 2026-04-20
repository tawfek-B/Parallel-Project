<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOrderJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        Log::info('ORDER REQUEST RECEIVED', [
            'user_id' => optional(auth()->user())->id,
            'payload' => $request->all(),
            'time' => now()->toDateTimeString(),
        ]);

        $job = ProcessOrderJob::dispatch(
            auth()->id() ?? 1, // fallback for testing
            $request->items ?? [
                ['product_id' => 1, 'quantity' => 1],
                ['product_id' => 2, 'quantity' => 1],
            ]
        );

        Log::info('JOB DISPATCHED', [
            'job_id' => class_basename($job),
            'user_id' => auth()->id() ?? 1,
        ]);

        return response()->json([
            'message' => 'Order queued successfully',
        ]);
    }
}