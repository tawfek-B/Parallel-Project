<?php

$url = "http://127.0.0.1:8000/api/orders";

$mh = curl_multi_init();
$handles = [];

$users = 200;

for ($i = 0; $i < $users; $i++) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json"
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "items" => [
            ["product_id" => 1, "quantity" => 1],
            ["product_id" => 2, "quantity" => 1],
        ]
    ]));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}

// execute all requests in parallel
$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

// cleanup
foreach ($handles as $ch) {
    curl_multi_remove_handle($mh, $ch);
}

curl_multi_close($mh);

echo "Done sending 100 concurrent requests\n";