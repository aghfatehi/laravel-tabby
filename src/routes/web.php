<?php

use Aghfatehi\Tabby\Controllers\TabbyController;
use Illuminate\Support\Facades\Route;

$prefix = config('tabby.routes.prefix', 'tabby');
$middleware = config('tabby.routes.middleware', ['web']);

Route::middleware($middleware)->prefix($prefix)->group(function () {
    Route::post('/pay', [TabbyController::class, 'pay'])
        ->name('tabby.pay');

    Route::any('/callback', [TabbyController::class, 'callback'])
        ->name('tabby.callback');

    Route::get('/cancel', [TabbyController::class, 'cancel'])
        ->name('tabby.cancel');

    Route::get('/failure', [TabbyController::class, 'failure'])
        ->name('tabby.failure');

    Route::post('/webhook', [TabbyController::class, 'webhook'])
        ->name('tabby.webhook');

    Route::post('/capture', [TabbyController::class, 'capture'])
        ->name('tabby.capture');

    Route::post('/refund', [TabbyController::class, 'refund'])
        ->name('tabby.refund');

    Route::get('/payment/{paymentId}', [TabbyController::class, 'getPaymentDetails'])
        ->name('tabby.payment.details');
});
