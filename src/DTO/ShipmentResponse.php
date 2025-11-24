<?php
declare(strict_types=1);

namespace App\DTO;

class ShipmentResponse
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getTrackingNumber(): string
    {
        return $this->data['trackingNumber'] ?? '';
    }

    public function getFulfillmentOrderId(): string
    {
        return $this->data['fulfillmentOrderId'] ?? '';
    }

    public function getCarrier(): ?string
    {
        return $this->data['carrier'] ?? null;
    }

    public function getStatus(): ?string
    {
        return $this->data['status'] ?? null;
    }

    public function getEstimatedDeliveryDate(): ?string
    {
        return $this->data['estimatedDeliveryDate'] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}