<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\AmazonFbaShippingService;
use App\Client\AmazonSpApiClient;
use Symfony\Component\Dotenv\Dotenv;

echo "=== Amazon FBA Shipping Service Demo ===\n\n";

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

$apiClient = new AmazonSpApiClient($credentials, true, true); // sandbox mode
$shippingService = new AmazonFbaShippingService($apiClient);

// Example 1: Single item shipment
echo "Example 1: Shipping single item order\n";
echo str_repeat('-', 50) . "\n";

try {
    $orderId = 'ORDER-' . time();
    $items = [
        ['sku' => 'WIDGET-001', 'quantity' => 2]
    ];
    $address = [
        'name' => 'John Doe',
        'line1' => '123 Main Street',
        'line2' => 'Apt 4B',
        'city' => 'New York',
        'state' => 'NY',
        'postal_code' => '10001',
        'country' => 'US',
        'phone' => '+1-555-0123'
    ];

    echo "Order ID: {$orderId}\n";
    echo "Items: " . json_encode($items, JSON_PRETTY_PRINT) . "\n";
    echo "Shipping to: {$address['name']}, {$address['city']}, {$address['state']}\n\n";

    $trackingNumber = $shippingService->ship($orderId, $items, $address);

    echo "✓ Shipment created successfully!\n";
    echo "Tracking Number: {$trackingNumber}\n\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Multiple items shipment
echo "Example 2: Shipping multi-item order\n";
echo str_repeat('-', 50) . "\n";

try {
    $orderId = 'ORDER-' . (time() + 1);
    $items = [
        ['sku' => 'BOOK-001', 'quantity' => 3],
        ['sku' => 'PEN-042', 'quantity' => 5],
        ['sku' => 'NOTEBOOK-99', 'quantity' => 1]
    ];
    $address = [
        'name' => 'Jane Smith',
        'line1' => '456 Oak Avenue',
        'city' => 'Los Angeles',
        'state' => 'CA',
        'postal_code' => '90001',
        'country' => 'US'
    ];

    echo "Order ID: {$orderId}\n";
    echo "Items count: " . count($items) . "\n";
    echo "Shipping to: {$address['name']}, {$address['city']}, {$address['state']}\n\n";

    $trackingNumber = $shippingService->ship($orderId, $items, $address);

    echo "✓ Shipment created successfully!\n";
    echo "Tracking Number: {$trackingNumber}\n\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Invalid shipment (missing required field)
echo "Example 3: Handling validation errors\n";
echo str_repeat('-', 50) . "\n";

try {
    $orderId = 'ORDER-INVALID';
    $items = [
        ['sku' => 'TEST-001', 'quantity' => 1]
    ];
    $address = [
        'name' => 'Test User',
        'line1' => '789 Test St'
        // Missing required fields: city, state, postal_code, country
    ];

    echo "Attempting to ship with incomplete address...\n";
    $trackingNumber = $shippingService->ship($orderId, $items, $address);

} catch (\Exception $e) {
    echo "✓ Validation working correctly!\n";
    echo "Error caught: " . $e->getMessage() . "\n\n";
}

echo "=== Demo completed ===\n";