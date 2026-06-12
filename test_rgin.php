<?php

$client = app(\App\Services\AI\RGINClient::class);

echo "=== RGIN Ping ===" . PHP_EOL;
$ping = $client->ping();
var_export($ping);
echo PHP_EOL;

echo "=== Network Stats ===" . PHP_EOL;
$stats = $client->getNetworkStats();
var_export($stats);
echo PHP_EOL;
