<?php

namespace App\Data;

use App\JsonLoader;

class Order extends AbstractOrder
{
    protected function loadOrderData(int $id): array
    {
        return JsonLoader::load(sprintf('mock/order.%d.json', $id));
    }
}