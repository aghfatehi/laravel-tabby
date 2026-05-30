<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tabby Pay in 4 Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Tabby is a Buy Now Pay Later (BNPL) solution enabling customers to
    | split payments into 4 installments. Available in Saudi Arabia,
    | UAE, and Kuwait.
    |
    */

    'sandbox' => env('TABBY_SANDBOX_MODE', true),

    'secret_key' => env('TABBY_SECRET_KEY', ''),

    'merchant_code' => env('TABBY_MERCHANT_CODE', ''),

    'currency' => env('TABBY_CURRENCY', 'SAR'),

    'region' => env('TABBY_REGION', 'sa'),

    'api_urls' => [
        'sa' => 'https://api.tabby.sa',
        'ae' => 'https://api.tabby.ai',
        'kw' => 'https://api.tabby.ai',
    ],

    'sandbox_url' => 'https://api.tabby.ai',

    'routes' => [
        'prefix' => env('TABBY_ROUTE_PREFIX', 'tabby'),
        'middleware' => ['web'],
    ],

    'language' => env('TABBY_LANGUAGE', 'en'),

    'logging' => env('TABBY_LOGGING', true),
];
