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
- ✅ Native PHP cURL client (no external HTTP dependencies)
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
# ─── Tabby Payment Gateway Environment Variables ──────────────────────────────

TABBY_SANDBOX_MODE=true
# bool | Sandbox mode (testing) when true, Production mode when false

TABBY_SECRET_KEY="sk_test_your_secret_key_here"
# string | Your Tabby secret key from Tabby dashboard
# Prefix: sk_test_ for sandbox | sk_live_ for production

TABBY_MERCHANT_CODE="TABBY_MERCHANT_CODE"
# string | Your merchant code from Tabby dashboard

TABBY_REGION="sa"
# string | Region determines API endpoint
# "sa" = https://api.tabby.sa | "ae" = https://api.tabby.ai | "kw" = https://api.tabby.ai

TABBY_CURRENCY="SAR"
# string (3 chars) | Currency code
# SAR = Saudi Riyal | AED = UAE Dirham | KWD = Kuwaiti Dinar

TABBY_LANGUAGE="ar"
# string | Checkout widget language
# "en" = English | "ar" = Arabic

TABBY_LOGGING=true
# bool | Log all API requests/responses to laravel.log
# true = enabled | false = disabled

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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

/**
 * Example controller method showing the full Tabby checkout payload
 * with explicit default values, data types, and field descriptions.
 */
public function initiateTabbyPayment(Request $request)
{
    // ═══════════════════════════════════════════════════════════════════
    //  1.  PAYMENT DATA
    // ═══════════════════════════════════════════════════════════════════

    $amount   = 500.00;
    // float | Order total (e.g. 500.00)
    // Tabby requires minimum 50 AED or equivalent in other currencies

    $currency = 'SAR';
    // string (3 chars) | Payment currency
    // 'SAR' = Saudi Riyal  |  'AED' = UAE Dirham  |  'KWD' = Kuwaiti Dinar
    // Must match the currency registered in your Tabby account

    $user     = $request->user();
    // ?\Illuminate\Foundation\Auth\User | Authenticated user (may be null)

    // ─── Buyer Information ───────────────────────────────────────────

    $firstName = $user?->name ?? 'Ahmed';
    // string | Buyer's first name - falls back to default if user not logged in

    $lastName  = $user?->name ?? 'Ali';
    // string | Buyer's last name

    $fullName  = trim("$firstName $lastName") ?: 'Ahmed Ali';
    // string | Full name (max 255 chars)

    $email     = $user?->email ?? 'customer@example.com';
    // string | Email address (must be valid for payment notifications)
    // Test: use otp.success@tabby.ai to always succeed

    $phone     = $user?->phone ?? '500000001';
    // string | Phone number WITHOUT country prefix (+)
    // e.g. "500000001" for Saudi Arabia, "500000001" for UAE
    // Test: "500000001" succeeds, "500000000" fails

    $dob       = $user?->dob ?? '1990-01-01';
    // string (YYYY-MM-DD) | Buyer's date of birth (optional, helps approval)

    // ─── Order References ────────────────────────────────────────────

    $orderReferenceId = 'ORD-' . uniqid();
    // string | Your unique order reference (max 255 chars)
    // Used to link your order with Tabby's payment

    $orderId          = uniqid('ord_');
    // string | Order ID displayed in Tabby's checkout UI

    // ═══════════════════════════════════════════════════════════════════
    //  2.  CHECKOUT PAYLOAD
    // ═══════════════════════════════════════════════════════════════════

    $requestBody = [

        // ─── Payment ────────────────────────────────────────────────────
        'payment' => [

            'amount'      => (string) $amount,
            // string | Total amount as string - "500.00"
            // IMPORTANT: Tabby requires amount as string, NOT float
            // Must be >= 50 AED or equivalent

            'currency'    => $currency,
            // string (3 chars) | Currency - "SAR" | "AED" | "KWD"

            'description' => 'Payment for order #1234',
            // string | Short payment description (max 255 chars)
            // Displayed to the buyer in Tabby's checkout

            // ── Buyer ─────────────────────────────────────────────────────
            'buyer' => [
                'phone' => $phone,
                // string | Phone number - "500000001"
                // WITHOUT country prefix (+966)

                'email' => $email,
                // string | Email - "customer@example.com"

                'name'  => $fullName,
                // string | Full name - "Ahmed Ali"

                'dob'   => $dob,
                // string (YYYY-MM-DD) | Date of birth - "1990-01-01"
            ],

            // ── Buyer History (optional, improves approval rate) ───────
            'buyer_history' => [
                'registered_since' => $user?->created_at?->format('Y-m-d\TH:i:s\Z')
                                         ?? '2024-01-01T00:00:00Z',
                // string (ISO8601) | Customer registration date
                // Older registration = higher approval chance

                'loyalty_level' => 0,
                // int | Loyalty tier (0 = new, 1 = regular, 2 = VIP)

                'wishlist_count' => 3,
                // int | Number of items in wishlist

                'is_social_networks_connected' => false,
                // bool | Does customer have social accounts linked?

                'is_phone_number_verified' => !empty($phone),
                // bool | Is phone number verified?

                'is_email_verified' => !empty($email),
                // bool | Is email verified?
            ],

            // ── Order ───────────────────────────────────────────────────
            'order' => [
                'tax_amount'      => '0.00',
                // string | Tax amount - "0.00" or actual tax

                'shipping_amount' => '0.00',
                // string | Shipping cost - "0.00" or actual cost

                'discount_amount' => '0.00',
                // string | Discount amount - "0.00" or actual discount

                'updated_at' => now()->format('Y-m-d\TH:i:s\Z'),
                // string (ISO8601) | Order last updated timestamp

                'reference_id' => $orderReferenceId,
                // string | Your order reference - "ORD-abc123"

                // ── Order Items ─────────────────────────────────────────
                'items' => [
                    [
                        'title'           => 'Wireless Headphones',
                        // string | Product name (required)

                        'description'     => 'Bluetooth 5.0',
                        // string | Product description (optional)

                        'quantity'        => 1,
                        // int | Quantity (must be >= 1)

                        'unit_price'      => '500.00',
                        // string | Unit price as string

                        'discount_amount' => '0.00',
                        // string | Discount on this item

                        'reference_id'    => 'SKU-001',
                        // string | SKU or product ID in your system

                        'image_url'       => 'https://example.com/headphones.jpg',
                        // string (URL) | Product image URL (optional)

                        'product_url'     => 'https://example.com/products/1',
                        // string (URL) | Product page URL (optional)

                        'category'        => 'Electronics',
                        // string | Product category (optional)
                    ],
                ],
            ],

            // ── Shipping Address ────────────────────────────────────────
            'shipping_address' => [
                'city'    => 'Riyadh',
                // string | City - e.g. "Riyadh" or "Dubai"

                'address' => '3764 Al Urubah Rd',
                // string | Street address

                'zip'     => '12345',
                // string | ZIP / postal code
            ],

            // ── Meta ────────────────────────────────────────────────────
            'meta' => [
                'order_id' => $orderId,
                // string | Your internal order ID

                'customer' => (string) ($user?->id ?? 'guest'),
                // string | Customer ID in your system
            ],
        ],

        // ─── Session Configuration ─────────────────────────────────────
        'lang' => config('tabby.language', 'en'),
        // string | Checkout language - "en" = English | "ar" = Arabic

        'merchant_code' => config('tabby.merchant_code', 'sa'),
        // string | Merchant code in Tabby - "ae" | "sa" | "kw"
        // Must match the code registered in your Tabby account

        // ─── Merchant Callback URLs ───────────────────────────────────
        'merchant_urls' => [
            'success' => route('tabby.callback'),
            // string (URL) | Redirect browser here after successful payment
            // Receives payment_id in query string

            'cancel'  => route('tabby.cancel'),
            // string (URL) | Redirect browser here if user cancels

            'failure' => route('tabby.failure'),
            // string (URL) | Redirect browser here if payment fails
        ],
    ];

    // ═══════════════════════════════════════════════════════════════════
    //  3.  SEND REQUEST  (Submit to Tabby API)
    // ═══════════════════════════════════════════════════════════════════

    Log::info('Tabby Checkout Request:', $requestBody);

    try {
        $response = Tabby::createCheckout($requestBody);
        // array | API response contains:
        //   ['payment']['id']          => "pay_xxxxxxxx"     (payment ID)
        //   ['id']                     => "uuid-string"      (session ID)
        //   ['configuration']['available_products']['installments'][0]['web_url'] => checkout URL

        Log::info('Tabby Checkout Response:', $response);

        // ═══════════════════════════════════════════════════════════════
        //  4.  HANDLE RESPONSE
        // ═══════════════════════════════════════════════════════════════

        if (isset($response['error']) || isset($response['errors'])) {
            // Tabby returned an error - show it to the user
            $error = $response['message']
                  ?? $response['errors'][0]['message']
                  ?? 'Payment initialization failed';
            return back()->withErrors(['error' => $error]);
        }

        $paymentId = $response['payment']['id'] ?? null;
        // string|null | "pay_xxxxxxxx" - Tabby payment ID (store in DB)

        $sessionId = $response['id'] ?? null;
        // string|null | UUID - checkout session ID

        // Extract checkout URL from available products
        $webUrl = $response['configuration']['available_products']['installments'][0]['web_url']
               ?? $response['configuration']['available_products']['pay_by_installments']['web_url']
               ?? null;
        // string|null | URL to redirect buyer to Tabby checkout
        // e.g. "https://checkout.tabby.ai/..."

        // Store in session for callback verification
        session([
            'tabby_payment_id' => $paymentId,
            // string|null | Used in callback to verify payment status
            'tabby_session_id' => $sessionId,
            // string|null | Used in webhook to match the session
        ]);

        if ($webUrl) {
            return Redirect::away($webUrl);
            // Redirect buyer to Tabby checkout page
        }

        return back()->withErrors(['error' => 'No checkout URL returned']);
        // Unexpected: no checkout URL in response

    } catch (\Throwable $e) {
        // API connection error (Network, Timeout, etc.)
        Log::error('Tabby Checkout Exception: ' . $e->getMessage());
        return back()->withErrors(['error' => $e->getMessage()]);
    }
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
| Saudi Arabia| sa   | `https://api.tabby.sa`   | `https://api.tabby.sa`   |
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

## Tabby Testing Credentials

UAE: otp.success@tabby.ai, phone: +971500000001
KSA: otp.success@tabby.ai, phone: +966500000001
Kuwait: otp.success@tabby.ai, phone: +96590000001

otp test: 8888

## Test Cards (Sandbox)

| Card Type  | Number             | Expiry | CVV  |
|------------|--------------------|--------|------|
| Visa       | 4508750015741019   | 07/39  | 100  |
| Mastercard | 5123450000000008   | 07/39  | 100  |
| AMEX       | 345678901234564    | 07/39  | 1000 |


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
- **Author**: AL-AGHBARI Fatehi ([fathi.a.n2002@gmail.com](mailto:fathi.a.n2002@gmail.com))

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
