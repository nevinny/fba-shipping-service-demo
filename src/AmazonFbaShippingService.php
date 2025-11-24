<?php
declare(strict_types=1);

namespace App;

use App\Client\AmazonSpApiClient;
use App\DTO\ShipmentRequest;
use App\Exceptions\ShippingException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AmazonFbaShippingService implements ShippingServiceInterface
{
    private AmazonSpApiClient $apiClient;
    private LoggerInterface $logger;

    public function __construct(
        AmazonSpApiClient $apiClient,
        ?LoggerInterface $logger = null
    ) {
        $this->apiClient = $apiClient;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Ship an order using Amazon FBA
     *
     * @param string $orderId Seller's order identifier
     * @param array $items Array of items to ship [['sku' => 'ABC123', 'quantity' => 2], ...]
     * @param array $shippingAddress ['name', 'line1', 'city', 'state', 'postal_code', 'country']
     * @return string Tracking number
     * @throws ShippingException
     */
    public function ship(string $orderId, array $items, array $shippingAddress): string
    {
        $this->logger->info('Starting FBA shipment process', [
            'order_id' => $orderId,
            'items_count' => count($items)
        ]);

        try {
            // Validate input
            $this->validateShipmentData($orderId, $items, $shippingAddress);

            // Create shipment request DTO
            $shipmentRequest = new ShipmentRequest($orderId, $items, $shippingAddress);

            // Call Amazon SP-API to create fulfillment order
            $response = $this->apiClient->createFulfillmentOrder($shipmentRequest);

            // Extract tracking number from response
            $trackingNumber = $response->getTrackingNumber();

            $this->logger->info('FBA shipment created successfully', [
                'order_id' => $orderId,
                'tracking_number' => $trackingNumber
            ]);

            return $trackingNumber;

        } catch (\Exception $e) {
            $this->logger->error('FBA shipment failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            throw new ShippingException(
                "Failed to ship order {$orderId}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Validate shipment data
     *
     * @throws ShippingException
     */
    private function validateShipmentData(string $orderId, array $items, array $shippingAddress): void
    {
        if (empty($orderId)) {
            throw new ShippingException('Order ID cannot be empty');
        }

        if (empty($items)) {
            throw new ShippingException('Items array cannot be empty');
        }

        foreach ($items as $item) {
            if (!isset($item['sku']) || !isset($item['quantity'])) {
                throw new ShippingException('Each item must have "sku" and "quantity"');
            }
        }

        $requiredAddressFields = ['name', 'line1', 'city', 'state', 'postal_code', 'country'];
        foreach ($requiredAddressFields as $field) {
            if (!isset($shippingAddress[$field]) || empty($shippingAddress[$field])) {
                throw new ShippingException("Shipping address field '{$field}' is required");
            }
        }
    }
}