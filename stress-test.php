<?php

$url = "http://parallel.test/api/orders";

$start = microtime(true);
$mh = curl_multi_init();
$handles = [];

for ($i = 0; $i < 100; $i++) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "items" => [
            ["product_id" => 1, "quantity" => 1],
            ["product_id" => 2, "quantity" => 1],
        ]
    ]));

    curl_multi_add_handle($mh, $ch);

    $handles[] = $ch;
}

$running = null;

do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$time = microtime(true) - $start;

echo "Done in {$time} seconds\n";