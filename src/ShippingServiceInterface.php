<?php
declare(strict_types=1);

namespace App;

interface ShippingServiceInterface
{
    public function ship(string $orderId, array $items, array $shippingAddress): string;
}