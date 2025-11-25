<?php
declare(strict_types=1);

namespace App;

use App\Data\AbstractOrder;
use App\Data\BuyerInterface;
use App\Exceptions\ShippingException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class AmazonFbaShippingService implements ShippingServiceInterface
{
    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Ship an order using Amazon FBA (mock implementation)
     *
     * @param AbstractOrder $order
     * @param BuyerInterface $buyer
     * @return string Tracking number
     * @throws ShippingException
     */
    public function ship(AbstractOrder $order, BuyerInterface $buyer): string
    {
        $order->load(); // загружаем данные заказа

        if (empty($order->data)) {
            throw new ShippingException('Order data is missing');
        }

        if (empty($buyer['shop_username'])) {
            throw new ShippingException('Buyer data is missing');
        }

        $this->logger->info('Starting FBA shipment process', [
            'order_id' => $order->getOrderId(),
        ]);

        try {
            // Извлекаем товары из заказа
            $items = array_map(
                fn($p) => ['sku' => $p['sku'], 'quantity' => (int)($p['ammount'] ?? 1)],
                $order->data['products'] ?? []
            );

            if (empty($items)) {
                throw new ShippingException('No items found in order');
            }

            // Формируем адрес доставки
            $shippingAddress = [
                'name'        => $buyer['name'] ?? 'Unknown',
                'line1'       => $this->extractLine1($buyer['address'] ?? ''),
                'city'        => $this->extractCity($buyer['address'] ?? ''),
                'state'       => $this->extractState($buyer['address'] ?? ''),
                'postal_code' => $this->extractPostal($buyer['address'] ?? ''),
                'country'     => $buyer['country_code'] ?? 'US'
            ];

            // В реальном сервисе здесь будет вызов API FBA
            // Пока мок: генерируем трекинг
            $trackingNumber = sprintf(
                'AMZ-%s-%s',
                substr((string)$order->getOrderId(), 0, 5),
                strtoupper(bin2hex(random_bytes(3)))
            );

            $this->logger->info('FBA shipment created successfully', [
                'order_id' => $order->getOrderId(),
                'tracking_number' => $trackingNumber
            ]);

            return $trackingNumber;

        } catch (\Exception $e) {
            $this->logger->error('FBA shipment failed', [
                'order_id' => $order->getOrderId(),
                'error' => $e->getMessage()
            ]);

            throw new ShippingException(
                "Failed to ship order {$order->getOrderId()}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function extractLine1(string $address): string
    {
        $lines = explode("\n", $address);
        return $lines[1] ?? $lines[0] ?? '';
    }

    private function extractCity(string $address): string
    {
        $lines = explode("\n", $address);
        return $lines[2] ?? '';
    }

    private function extractState(string $address): string
    {
        $lines = explode("\n", $address);
        $parts = explode(' ', $lines[3] ?? '');
        return $parts[0] ?? '';
    }

    private function extractPostal(string $address): string
    {
        $lines = explode("\n", $address);
        $parts = explode(' ', $lines[3] ?? '');
        return $parts[1] ?? '';
    }
}
