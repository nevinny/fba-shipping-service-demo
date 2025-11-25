<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\AmazonFbaShippingService;
use App\Data\Buyer;
use App\Data\Order;
use App\Exceptions\ShippingException;
use App\JsonLoader;


// Загружаем мок-данные
$buyerData = JsonLoader::load(__DIR__ . '/mock/buyer.29664.json');
$buyer = new Buyer($buyerData);

// Создаём заказ
$order = new Order(16400);

$service = new AmazonFbaShippingService();

try {
    $tracking = $service->ship($order, $buyer);
    echo "Tracking number: $tracking\n";
} catch (ShippingException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}