# ING WebPay for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/andreighioc/ingwebpay.svg)](https://packagist.org/packages/andreighioc/ingwebpay)
[![Total Downloads](https://img.shields.io/packagist/dt/andreighioc/ingwebpay.svg)](https://packagist.org/packages/andreighioc/ingwebpay)
[![License](https://img.shields.io/packagist/l/andreighioc/ingwebpay.svg)](LICENSE)
[![PHP Quality](https://github.com/andreighioc/ingwebpay/actions/workflows/run-tests.yml/badge.svg)](https://github.com/andreighioc/ingwebpay/actions)

A Laravel package for processing card payments through **ING WebPay** — the e-commerce payment gateway provided by ING Bank Romania.

Supports **Laravel 10, 11, 12, and 13**.

## Features

- **Full API coverage** — register (sale), registerPreAuth, getOrderStatus, getOrderStatusExtended, reverse, deposit, refund
- **Strongly-typed DTOs** — all responses are mapped to clean data objects
- **PHP 8.1+ enums** — `PaymentStatus`, `OrderStatus`, `Currency`, `Environment`
- **Plug-and-play** — `php artisan ingwebpay:install` scaffolds controller, routes, and views
- **Database support** — Optional migrations to automatically store transactions
- **Facade support** — use `IngWebPay::register(...)` anywhere
- **3D Secure v2** — enforced by default via `FORCE_3DS2`
- **Test & Production** environments with separate URL configuration
- **Comprehensive tests** — unit + feature tests with faked HTTP
- **Testing Mocks** — `IngWebPay::fake()` built-in for asserting payments
- **Advanced Exceptions** — `IngAuthenticationException`, `IngEndpointUnreachableException`, etc.
- **Auto-Retry Mechanism** — Prevents transaction loss on random network failures

---

## Requirements

- PHP ≥ 8.1
- Laravel 10, 11, 12, or 13
- An ING WebPay merchant account (API username & password)

## Installation

```bash
composer require andreighioc/ingwebpay
```

The service provider and facade are auto-discovered. Then run the install command:

```bash
php artisan ingwebpay:install
```

This publishes:
- `config/ingwebpay.php` — Configuration
- `app/Http/Controllers/IngWebPayController.php` — Payment controller
- `routes/ingwebpay.php` — Route definitions
- `resources/views/ingwebpay/` — Checkout, success, and failure views

After configuration, you can optionally publish migrations to automatically log your payments in the database:
```bash
php artisan vendor:publish --tag=ingwebpay-migrations
php artisan migrate
```

Verify your credentials easily using the built-in checker:
```bash
php artisan ingwebpay:check-connection
```

## Configuration

Add your credentials to `.env`:

```env
INGWEBPAY_ENVIRONMENT=test
INGWEBPAY_USERNAME=your_api_username
INGWEBPAY_PASSWORD=your_api_password
INGWEBPAY_CURRENCY=946
INGWEBPAY_RETURN_URL=/ingwebpay/callback
INGWEBPAY_LANGUAGE=ro
INGWEBPAY_ORDER_NUMBER_MODE=merchant
```

| Variable | Description | Default |
|---|---|---|
| `INGWEBPAY_ENVIRONMENT` | `test` or `production` | `test` |
| `INGWEBPAY_USERNAME` | API user code from ING Bank | — |
| `INGWEBPAY_PASSWORD` | API password | — |
| `INGWEBPAY_CURRENCY` | `946` (RON) or `978` (EUR) | `946` |
| `INGWEBPAY_RETURN_URL` | Callback URL after payment | `/ingwebpay/callback` |
| `INGWEBPAY_LANGUAGE` | Payment page language (`ro` / `en`) | `ro` |
| `INGWEBPAY_ORDER_NUMBER_MODE` | `merchant` or `auto` | `merchant` |

> **Important:** When operating in multiple currencies, ING Bank provides separate credentials for each currency. You'll need to configure accordingly.

## Include the Routes

Add this to your `routes/web.php`:

```php
require __DIR__ . '/ingwebpay.php';
```

## Usage

### Quick Start (Facade)

```php
use AndreighioC\IngWebPay\Facades\IngWebPay;

// Register a sale transaction (amount in minor units: 102.31 RON = 10231)
$response = IngWebPay::register(
    amount: 10231,
    orderNumber: 'ORD-0001',
    description: 'Premium subscription',
    email: 'client@example.com',
);

if ($response->isSuccessful()) {
    // Redirect the customer to the ING payment page
    return redirect()->away($response->formUrl);
}

// Handle error
echo $response->errorMessage;
```

### Register a Pre-Authorization

```php
$response = IngWebPay::registerPreAuth(
    amount: 50000, // 500.00 RON
    orderNumber: 'PRE-0001',
);

if ($response->isSuccessful()) {
    return redirect()->away($response->formUrl);
}
```

### Check Transaction Status

```php
// Basic status
$status = IngWebPay::getOrderStatus($orderId);

if ($status->isSuccessful()) {
    echo "Paid: {$status->getAmountInMajorUnits()} RON";
    echo "Card: {$status->pan}";
}

// Extended status (more details)
$extended = IngWebPay::getOrderStatusExtended($orderId);

echo $extended->actionCodeDescription;
echo $extended->getCardholderName();
echo $extended->getApprovalCode();
```

### Reverse (Cancel) a Transaction

```php
// Before batch settlement (COT 22:00)
$result = IngWebPay::reverse($orderId);
```

### Complete a Pre-Authorization (Deposit)

```php
// Full amount
$result = IngWebPay::deposit($orderId);

// Partial amount (300.00 RON)
$result = IngWebPay::deposit($orderId, 30000);
```

### Refund a Transaction

```php
// After batch settlement
$result = IngWebPay::refund($orderId, 10231); // 102.31 RON
```

### Currency Helpers

```php
use AndreighioC\IngWebPay\IngWebPay;

$minor = IngWebPay::toMinorUnits(102.31); // 10231
$major = IngWebPay::toMajorUnits(10231);  // 102.31
```

### Using the Enum Directly

```php
use AndreighioC\IngWebPay\Enums\OrderStatus;

$status = OrderStatus::DEPOSITED;

$status->isSuccessful();   // true
$status->isFailed();       // false
$status->isPending();      // false
$status->label();          // "Deposited (authorized)"
```

## Payment Flow

```
1. Customer clicks "Pay" on your site
2. Your app calls IngWebPay::register(...) → receives formUrl
3. Customer is redirected to formUrl (ING WebPay payment page)
4. Customer enters card data + 3D Secure authentication
5. ING WebPay redirects back to your returnUrl with orderId
6. Your callback handler calls IngWebPay::getOrderStatusExtended($orderId)
7. Based on the status, redirect to success or failure page
```

## Order Statuses

| Value | Enum | Description |
|---|---|---|
| 0 | `REGISTERED` | Order registered but not paid |
| 1 | `PREAUTHORIZED` | Pre-authorized (awaiting completion) |
| 2 | `DEPOSITED` | Authorized & deposited ✅ |
| 3 | `CANCELLED` | Cancelled |
| 4 | `REVERSED` | Reversed |
| 5 | `ACS_INITIATED` | Initiated by issuer ACS |
| 6 | `DECLINED` | Declined / Rejected ❌ |

## API Endpoints Reference

| Operation | Test URL | Production URL |
|---|---|---|
| Register (Sale) | `securepay-uat.ing.ro/mpi_uat/rest/register.do` | `securepay.ing.ro/mpi/rest/register.do` |
| Register PreAuth | `securepay-uat.ing.ro/mpi_uat/rest/registerPreAuth.do` | `securepay.ing.ro/mpi/rest/registerPreAuth.do` |
| Get Order Status | `securepay-uat.ing.ro/mpi_uat/rest/getOrderStatus.do` | `securepay.ing.ro/mpi/rest/getOrderStatus.do` |
| Get Status Extended | `securepay-uat.ing.ro/mpi_uat/rest/getOrderStatusExtended.do` | `securepay.ing.ro/mpi/rest/getOrderStatusExtended.do` |
| Reverse | `securepay-uat.ing.ro/mpi_uat/rest/reverse.do` | `securepay.ing.ro/mpi/rest/reverse.do` |
| Deposit | `securepay-uat.ing.ro/mpi_uat/rest/deposit.do` | `securepay.ing.ro/mpi/rest/deposit.do` |

## Testing

Testing your own Application implementing ING WebPay:
```php
use AndreighioC\IngWebPay\Facades\IngWebPay;

public function test_user_can_checkout()
{
    IngWebPay::fake();

    $this->post('/checkout', ['product_id' => 1])
         ->assertRedirect();
         
    IngWebPay::assertPaymentInitiated(10231); // Verify payment of 102.31 RON was sent!
}
```

Testing the package directly:
```bash
composer test
```

Or directly:
```bash
./vendor/bin/phpunit
```

## Test Environment Credentials

ING provides shared test credentials (from the official guide):

- **API User:** `TEST_API` / Password: `q1w2e3r4Q!`
- **Admin User:** `TEST_ADMINISTRARE` / Password: `Ing.12345!`
- **Console:** `https://securepay-uat.ing.ro/consola/index.html`
- **Test Card (Visa):** `4256031168525366`, Exp: `05/21`, CVV: `865`, 3DS: `test123!`

> ⚠️ Do not modify these shared test passwords.

## Security

- API credentials are **never** exposed to the browser (all calls are server-to-server)
- 3D Secure v2 is enforced by default
- Card data is handled exclusively by ING WebPay (PCI DSS compliant)
- Passwords are excluded from debug logs

## License

MIT — see [LICENSE](LICENSE).

## Support

For ING WebPay technical support: `SupportWebPay@ing.ro` or call `+40 21 403 83 04`.
