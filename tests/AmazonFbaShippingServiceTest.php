<?php

declare(strict_types=1);

namespace Tests;

use App\AmazonFbaShippingService;
use App\Client\AmazonSpApiClient;
use App\Exceptions\ShippingException;
use PHPUnit\Framework\TestCase;

class AmazonFbaShippingServiceTest extends TestCase
{
    private AmazonFbaShippingService $service;

    protected function setUp(): void
    {
        $apiClient = new AmazonSpApiClient(
            ['access_token' => 'test_token'],
            true // sandbox mode
        );

        $this->service = new AmazonFbaShippingService($apiClient);
    }

    public function testShipReturnsTrackingNumber(): void
    {
        $orderId = 'TEST-ORDER-001';
        $items = [
            ['sku' => 'SKU-123', 'quantity' => 2],
            ['sku' => 'SKU-456', 'quantity' => 1]
        ];
        $address = [
            'name' => 'John Doe',
            'line1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US'
        ];

        $trackingNumber = $this->service->ship($orderId, $items, $address);

        $this->assertIsString($trackingNumber);
        $this->assertNotEmpty($trackingNumber);
        $this->assertStringStartsWith('1Z', $trackingNumber); // UPS format
    }

    public function testShipThrowsExceptionForEmptyOrderId(): void
    {
        $this->expectException(ShippingException::class);
        $this->expectExceptionMessage('Order ID cannot be empty');

        $this->service->ship('', [], []);
    }

    public function testShipThrowsExceptionForEmptyItems(): void
    {
        $this->expectException(ShippingException::class);
        $this->expectExceptionMessage('Items array cannot be empty');

        $address = [
            'name' => 'John Doe',
            'line1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US'
        ];

        $this->service->ship('ORDER-001', [], $address);
    }

    public function testShipThrowsExceptionForInvalidItemFormat(): void
    {
        $this->expectException(ShippingException::class);
        $this->expectExceptionMessage('Each item must have "sku" and "quantity"');

        $items = [
            ['sku' => 'SKU-123'] // missing quantity
        ];
        $address = [
            'name' => 'John Doe',
            'line1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US'
        ];

        $this->service->ship('ORDER-001', $items, $address);
    }

    public function testShipThrowsExceptionForMissingAddressField(): void
    {
        $this->expectException(ShippingException::class);
        $this->expectExceptionMessageMatches('/Shipping address field .* is required/');

        $items = [
            ['sku' => 'SKU-123', 'quantity' => 1]
        ];
        $address = [
            'name' => 'John Doe',
            'line1' => '123 Main St'
            // missing required fields
        ];

        $this->service->ship('ORDER-001', $items, $address);
    }

    public function testMultipleShipmentsGenerateDifferentTrackingNumbers(): void
    {
        $items = [['sku' => 'SKU-123', 'quantity' => 1]];
        $address = [
            'name' => 'John Doe',
            'line1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US'
        ];

        $tracking1 = $this->service->ship('ORDER-001', $items, $address);
        $tracking2 = $this->service->ship('ORDER-002', $items, $address);

        $this->assertNotEquals($tracking1, $tracking2);
    }
}