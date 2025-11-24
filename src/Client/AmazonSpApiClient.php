<?php
declare(strict_types=1);

namespace App\Client;

use App\DTO\ShipmentRequest;
use App\DTO\ShipmentResponse;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AmazonSpApiClient
{
    private Client $httpClient;
    private string $apiEndpoint;
    private array $credentials;
    private bool $sandboxMode;
    private bool $useMocks;

    public function __construct(
        array $credentials,
        bool $sandboxMode = true,
        bool $useMocks = true,
        ?Client $httpClient = null
    ) {
        $this->credentials = $credentials;
        $this->sandboxMode = $sandboxMode;
        $this->useMocks = $useMocks;
        $this->apiEndpoint = $sandboxMode
            ? 'https://sandbox.sellingpartnerapi-na.amazon.com'
            : 'https://sellingpartnerapi-na.amazon.com';

        $this->httpClient = $httpClient ?? new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Create fulfillment order via Amazon SP-API
     *
     * API Reference: https://developer-docs.amazon.com/sp-api/docs/fulfillment-outbound-api-v2020-07-01-reference
     */
    public function createFulfillmentOrder(ShipmentRequest $request): ShipmentResponse
    {
        if ($this->useMocks) {
            return $this->mockCreateFulfillmentOrder($request);
        }

        try {
            $payload = $this->buildFulfillmentOrderPayload($request);

            $response = $this->httpClient->post(
                "{$this->apiEndpoint}/fba/outbound/2020-07-01/fulfillmentOrders",
                [
                    'json' => $payload,
                    'headers' => $this->getAuthHeaders()
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->parseShipmentResponse($data);

        } catch (GuzzleException $e) {
            throw new ApiException("Amazon API request failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Mock implementation for testing without real API credentials
     */
    private function mockCreateFulfillmentOrder(ShipmentRequest $request): ShipmentResponse
    {
        // Simulate API processing delay
        usleep(100000); // 100ms

        // Generate realistic tracking number
        $trackingNumber = $this->generateTrackingNumber();

        // Simulate successful fulfillment order creation
        $responseData = [
            'fulfillmentOrderId' => 'FBA' . strtoupper(uniqid()),
            'trackingNumber' => $trackingNumber,
            'carrier' => 'UPS',
            'shippingSpeed' => 'Standard',
            'status' => 'PLANNING',
            'estimatedDeliveryDate' => date('Y-m-d', strtotime('+5 days')),
            'createdAt' => date('c')
        ];

        return new ShipmentResponse($responseData);
    }

    /**
     * Build payload according to Amazon SP-API specification
     */
    private function buildFulfillmentOrderPayload(ShipmentRequest $request): array
    {
        return [
            'sellerFulfillmentOrderId' => $request->getOrderId(),
            'displayableOrderId' => $request->getOrderId(),
            'displayableOrderDate' => date('c'),
            'displayableOrderComment' => 'Order from seller',
            'shippingSpeedCategory' => 'Standard',
            'destinationAddress' => [
                'name' => $request->getShippingAddress()['name'],
                'addressLine1' => $request->getShippingAddress()['line1'],
                'addressLine2' => $request->getShippingAddress()['line2'] ?? null,
                'city' => $request->getShippingAddress()['city'],
                'stateOrRegion' => $request->getShippingAddress()['state'],
                'postalCode' => $request->getShippingAddress()['postal_code'],
                'countryCode' => $request->getShippingAddress()['country'],
                'phone' => $request->getShippingAddress()['phone'] ?? null
            ],
            'items' => array_map(function($item) {
                return [
                    'sellerSku' => $item['sku'],
                    'sellerFulfillmentOrderItemId' => uniqid('item_'),
                    'quantity' => $item['quantity']
                ];
            }, $request->getItems())
        ];
    }

    /**
     * Get authentication headers for Amazon SP-API
     */
    private function getAuthHeaders(): array
    {
        // In real implementation, this would use AWS Signature V4
        // and LWA (Login with Amazon) access token
        return [
            'x-amz-access-token' => $this->credentials['access_token'] ?? 'mock_token',
            'x-amz-date' => gmdate('Ymd\THis\Z')
        ];
    }

    /**
     * Parse API response into ShipmentResponse DTO
     */
    private function parseShipmentResponse(array $data): ShipmentResponse
    {
        return new ShipmentResponse($data);
    }

    /**
     * Generate realistic tracking number
     */
    private function generateTrackingNumber(): string
    {
        // UPS tracking number format: 1Z + 6 chars + 2 digits + 7 digits
        return '1Z' . strtoupper(substr(md5(uniqid()), 0, 6)) . rand(10, 99) . rand(1000000, 9999999);
    }
}