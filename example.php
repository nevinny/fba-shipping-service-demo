<?php
require_once 'vendor/autoload.php';

use App\AmazonFbaShippingService;
use App\Client\AmazonSpApiClient;

use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Prepare credentials
$credentials = [
    'access_token'  => $_ENV['SPAPI_ACCESS_TOKEN'],
    'refresh_token' => $_ENV['SPAPI_REFRESH_TOKEN'],
    'client_id'     => $_ENV['SPAPI_CLIENT_ID'],
    'client_secret' => $_ENV['SPAPI_CLIENT_SECRET'],
];

$sandbox = filter_var($_ENV['SPAPI_SANDBOX'], FILTER_VALIDATE_BOOL);

$apiClient = new AmazonSpApiClient($credentials, true, true);
$shippingService = new AmazonFbaShippingService($apiClient);

// Prepare shipment data
$orderId = 'ORDER-12345';
$items = [
    ['sku' => 'PRODUCT-001', 'quantity' => 2],
    ['sku' => 'PRODUCT-002', 'quantity' => 1]
];
$shippingAddress = [
    'name' => 'John Doe',
    'line1' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'postal_code' => '10001',
    'country' => 'US'
];

// Ship the order
try {
    $trackingNumber = $shippingService->ship($orderId, $items, $shippingAddress);
    echo "Tracking Number: {$trackingNumber}\n";
} catch (\Exception $e) {
    echo "Shipping failed: " . $e->getMessage() . "\n";
}