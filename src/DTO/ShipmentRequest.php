<?php
declare(strict_types=1);

namespace App\DTO;

class ShipmentRequest
{
    private string $orderId;
    private array $items;
    private array $shippingAddress;

    public function __construct(string $orderId, array $items, array $shippingAddress)
    {
        $this->orderId = $orderId;
        $this->items = $items;
        $this->shippingAddress = $shippingAddress;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getShippingAddress(): array
    {
        return $this->shippingAddress;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'items' => $this->items,
            'shipping_address' => $this->shippingAddress
        ];
    }
}