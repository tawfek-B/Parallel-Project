<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoadBalancerService
{
    protected array $servers = [
        'ServerA',
        'ServerB',
        'ServerC'
    ];

    public function distribute(int $requestNumber) {

        $server = $this->servers[
            $requestNumber % count($this->servers)
        ];
        Log::info('Request routed to server', [
            'server' => $server
        ]);

        return $server;
    }
    public function __construct()
    {
        //
    }
}
