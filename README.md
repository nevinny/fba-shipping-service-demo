# Amazon FBA Shipping Service (Demo)

**Описание:**  
Пример реализации сервиса для отправки заказов через Amazon FBA (Fulfillment by Amazon) с использованием мок-данных.

Сервис реализует интерфейс `ShippingServiceInterface` и работает с объектами `AbstractOrder` и `BuyerInterface`.

Вместо реального обращения к Amazon SP-API используется мок-клиент, который возвращает фиктивный трекинг-номер.

---

## Структура проекта
```
src/
├─ Data/
│ ├─ AbstractOrder.php
│ ├─ BuyerInterface.php
│ ├─ Buyer.php
│ └─ Order.php
├─ mock/
│ ├─ buyer.29664.json
│ └─ order.16400.json
├─ Exceptions/
│ └─ ShippingException.php
├─ AmazonFbaShippingService.php
└─ ShippingServiceInterface.php
demo.php
.env
composer.json
.gitignore
```

- `mock` — содержит тестовые JSON-файлы для покупателя и заказа.
- `demo.php` — демонстрационный скрипт для запуска сервиса.
- `src/Data` — сущности заказов и покупателей.
- `src/Exceptions` — исключения для обработки ошибок доставки.

---

## Установка

1. Клонируйте репозиторий:

```bash
git clone https://github.com/nevinny/fba-shipping-service-demo
cd amazon-fba-test
```
2. Установите зависимости через Composer:

```bash
composer install
```
3. Настройте .env (можно оставить фиктивные значения для демо):

```bash
SPAPI_ACCESS_TOKEN=demo
SPAPI_REFRESH_TOKEN=demo
SPAPI_CLIENT_ID=demo
SPAPI_CLIENT_SECRET=demo
SPAPI_SANDBOX=true
```
---
## Использование
Запуск демо:
```bash
php demo.php
```
Ожидаемый вывод:

```bash
Tracking number: AMZ-16400-8F3A1C
```
