<?php
require __DIR__ . '/vendor/autoload.php';

$client = new MongoDB\Client('mongodb://localhost:27017');

$result = $client
    ->selectDatabase('restaurant_system')
    ->command(['ping' => 1])
    ->toArray();

print_r($result);
