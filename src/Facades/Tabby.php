<?php

namespace Aghfatehi\Tabby\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array createCheckout(array $data)
 * @method static array getPayment(string $paymentId)
 * @method static array updatePayment(string $paymentId, array $data)
 * @method static array capturePayment(string $paymentId, string $amount, string $referenceId = '')
 * @method static array refundPayment(string $paymentId, string $amount, string $referenceId = '')
 * @method static array listPayments(array $filters = [])
 * @method static array webhookRegister(string $url, array $header = [])
 * @method static array webhookList()
 * @method static array webhookGet(string $webhookId)
 * @method static array webhookUpdate(string $webhookId, string $url)
 * @method static array webhookDelete(string $webhookId)
 *
 * @see \Aghfatehi\Tabby\Services\TabbyService
 */
class Tabby extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tabby';
    }
}
