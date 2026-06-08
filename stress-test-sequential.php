<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$url = "http://parallel.test/api/orders";

$start = microtime(true);

$requests = 100;

$success = 0;
$rateLimited = 0;
$failed = 0;
$errors = 0;


for ($i = 0; $i < $requests; $i++) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "items" => [
            ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
            ['product_id' => Product::inRandomOrder()->value('id'), 'quantity' => rand(1, 3)],
        ]
    ]));

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    if (!empty($curlError)) {
        $errors++;
        echo "Request #" . ($index + 1) . " ERROR $curlError\n";
        curl_multi_remove_handle($mh, $ch);
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        $success++;
        echo "Success (HTTP Code: $httpCode): $response\n";
    } elseif ($httpCode == 429) {
        $rateLimited++;
        echo "Rate Limited (HTTP Code: $httpCode): $response\n";
    } else {
        $failed++;
        echo "Failed (HTTP Code: $httpCode): $response\n";
    }
}

$time = microtime(true) - $start;

echo "\n";

echo "Done {$requests} requests in {$time} seconds\n";
echo "Average execution time: " . round(($time / $requests), 3) . " seconds\n";
echo "Successful requests: $success\n";
echo "Rate limited requests: $rateLimited\n";
echo "Failed requests: $failed\n\n";

if ($failed == 0 && $errors == 0) {
    echo "SYSTEM SURVIVED THE STRESS TEST.\n";
} else {
    echo "SYSTEM EXPERIENCED FAILURES DURING THE STRESS TEST.\n";
}