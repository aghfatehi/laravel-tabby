# Laravel Tabby Pay in 4 Payment Gateway

[![Latest Version](https://img.shields.io/packagist/v/aghfatehi/laravel-tabby.svg)](https://packagist.org/packages/aghfatehi/laravel-tabby)
[![Laravel](https://img.shields.io/badge/Laravel-10~13-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/github/license/aghfatehi/laravel-tabby)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/aghfatehi/laravel-tabby.svg)](https://packagist.org/packages/aghfatehi/laravel-tabby)

A professional Laravel package for integrating **Tabby Pay in 4** - the leading Buy Now Pay Later (BNPL) solution in the Middle East. Supports Saudi Arabia, UAE, and Kuwait.

Customers can split their payments into 4 interest-free installments, increasing conversion and average order value.

## Features

- ✅ Full Tabby Checkout flow (Create Session, Callback, Cancel, Failure)
- ✅ Capture authorized payments
- ✅ Refund payments (partial or full)
- ✅ Payment details retrieval
- ✅ Webhook management (authorized, captured, failed, rejected)
- ✅ Sandbox & Production environments
- ✅ Multi-region support (SA, AE, KW)
- ✅ Multi-currency support (SAR, AED, KWD)
- ✅ Arabic & English language support
- ✅ Guzzle HTTP client (no raw cURL)
- ✅ Configurable routes prefix & middleware
- ✅ Transaction logging migration
- ✅ Laravel 10, 11, 12 & 13 compatible
- ✅ PHP 8.1+

## Requirements

| Laravel | PHP   | Package Version |
|---------|-------|-----------------|
| 10.x    | ^8.1  | ^1.0            |
| 11.x    | ^8.2  | ^1.0            |
| 12.x    | ^8.2  | ^1.0            |
| 13.x    | ^8.2  | ^1.0            |

## Installation

```bash
composer require aghfatehi/laravel-tabby
```

## Configuration

### 1. Publish Configuration

```bash
php artisan vendor:publish --tag=tabby-config
```

### 2. Publish Migration (Optional)

```bash
php artisan vendor:publish --tag=tabby-migrations
php artisan migrate
```

### 3. Environment Variables

Add these to your `.env` file:

```env
TABBY_SANDBOX_MODE=true
TABBY_SECRET_KEY=sk_test_your_secret_key_here
TABBY_MERCHANT_CODE=ae
TABBY_REGION=ae
TABBY_CURRENCY=AED
TABBY_LANGUAGE=en
TABBY_LOGGING=true
TABBY_ROUTE_PREFIX=tabby
```

### 4. Service Provider

The package auto-discovers via Laravel's package discovery. If you disable discovery, register manually in `config/app.php`:

```php
'providers' => [
    Aghfatehi\Tabby\TabbyServiceProvider::class,
],
```

### 5. Facade (Optional)

```php
'aliases' => [
    'Tabby' => Aghfatehi\Tabby\Facades\Tabby::class,
],
```

## Usage

### Quick Start - Frontend Checkout

```php
use Aghfatehi\Tabby\Facades\Tabby;

// Build the checkout payload
$response = Tabby::createCheckout([
    'payment' => [
        'amount' => '500.00',
        'currency' => 'SAR',
        'description' => 'Order #1234 payment',
        'buyer' => [
            'phone' => '500000001',
            'email' => 'customer@example.com',
            'name' => 'Ahmed Ali',
            'dob' => '1990-01-01',
        ],
        'order' => [
            'reference_id' => 'ORD-' . uniqid(),
            'items' => [
                [
                    'title' => 'Product Name',
                    'quantity' => 1,
                    'unit_price' => '500.00',
                ],
            ],
        ],
    ],
    'merchant_code' => config('tabby.merchant_code'),
    'merchant_urls' => [
        'success' => route('tabby.callback'),
        'cancel' => route('tabby.cancel'),
        'failure' => route('tabby.failure'),
    ],
]);

// Redirect customer to Tabby checkout
$webUrl = $response['configuration']['available_products']['installments'][0]['web_url']
    ?? $response['configuration']['available_products']['pay_by_installments']['web_url']
    ?? null;

if ($webUrl) {
    return redirect()->away($webUrl);
}
```

### Using Routes

The package registers these routes under the configured prefix (`/tabby` by default):

| Method | URI                    | Name                  | Description               |
|--------|------------------------|-----------------------|---------------------------|
| POST   | `/tabby/pay`           | `tabby.pay`           | Initiate checkout         |
| ANY    | `/tabby/callback`      | `tabby.callback`      | Payment callback          |
| GET    | `/tabby/cancel`        | `tabby.cancel`        | Cancel handler            |
| GET    | `/tabby/failure`       | `tabby.failure`       | Failure handler           |
| POST   | `/tabby/webhook`       | `tabby.webhook`       | Webhook receiver          |
| POST   | `/tabby/capture`       | `tabby.capture`       | Capture payment           |
| POST   | `/tabby/refund`        | `tabby.refund`        | Refund payment            |
| GET    | `/tabby/payment/{id}`  | `tabby.payment.details` | Payment details         |

### API Methods

```php
use Aghfatehi\Tabby\Facades\Tabby;

// Create Checkout Session
$checkout = Tabby::createCheckout($data);

// Retrieve Payment
$payment = Tabby::getPayment('pay_xxxxx');

// Update Payment (e.g., order reference)
$updated = Tabby::updatePayment('pay_xxxxx', [
    'order' => ['reference_id' => 'NEW-ORD-123'],
]);

// Capture Payment (after authorization)
$captured = Tabby::capturePayment('pay_xxxxx', '500.00');

// Refund Payment
$refunded = Tabby::refundPayment('pay_xxxxx', '100.00');

// List Payments with filters
$payments = Tabby::listPayments([
    'created_at__gte' => '2025-03-01',
    'limit' => 20,
]);

// Webhook Management
$webhook = Tabby::webhookRegister('https://example.com/webhook', [
    'title' => 'Authorization',
    'value' => 'Bearer xxx',
]);
$list = Tabby::webhookList();
$detail = Tabby::webhookGet('webhook-id');
Tabby::webhookUpdate('webhook-id', 'https://example.com/webhook-new');
Tabby::webhookDelete('webhook-id');
```

### Amount Formatting

```php
use Aghfatehi\Tabby\Facades\Tabby;

$formatted = Tabby::formatAmount(100.5); // "100.50"
```

## Regions & API Endpoints

| Region      | Code | Sandbox URL              | Production URL          |
|-------------|------|--------------------------|--------------------------|
| Saudi Arabia| sa   | `https://api.tabby.ai`   | `https://api.tabby.sa`   |
| UAE         | ae   | `https://api.tabby.ai`   | `https://api.tabby.ai`   |
| Kuwait      | kw   | `https://api.tabby.ai`   | `https://api.tabby.ai`   |

## Webhook Events

The webhook endpoint handles these events:

- `payment_authorized` - Payment has been authorized (ready to capture)
- `payment_captured` - Payment has been captured (funds collected)
- `payment_failed` - Payment failed
- `payment_rejected` - Payment was rejected

## Customising Routes

Publish the config and modify the `routes` section:

```php
// config/tabby.php
'routes' => [
    'prefix' => 'payment/tabby',     // Custom prefix
    'middleware' => ['web', 'auth'],   // Custom middleware
],
```

## Test Cards (Sandbox)

| Card Type  | Number             | Expiry | CVV  |
|------------|--------------------|--------|------|
| Visa       | 4508750015741019   | 07/39  | 100  |
| Mastercard | 5123450000000008   | 07/39  | 100  |
| AMEX       | 345678901234564    | 07/39  | 1000 |

### Test Customer

```
Email: test@example.com
Phone: +966500000000
```

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Security

If you discover security issues, please email fathi.a.n2002@gmail.com instead of using the issue tracker.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- **Issues**: [GitHub Issues](https://github.com/aghfatehi/laravel-tabby/issues)
- **Tabby Docs**: [https://docs.tabby.ai](https://docs.tabby.ai)
- **Author**: Abdelghafour Fatehi ([fathi.a.n2002@gmail.com](mailto:fathi.a.n2002@gmail.com))

## Comparison with Tamara

| Feature         | Tabby (this package) | Tamara |
|-----------------|----------------------|--------|
| Payment Model   | Pay in 4             | Installments 3/4/6 |
| Capture         | ✅ After authorization | ✅ After authorization |
| Refund          | ✅ Full & Partial    | ✅ Full & Partial    |
| Webhooks        | ✅ Managed           | ✅ Managed           |
| KSA Support     | ✅                   | ✅                   |
| UAE Support     | ✅                   | ✅                   |
| Kuwait Support  | ✅                   | ✅                   |
| API Version     | v2                   | v1                   |
