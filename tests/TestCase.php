<?php

namespace Aghfatehi\Tabby\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \Aghfatehi\Tabby\TabbyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('tabby.sandbox', true);
        config()->set('tabby.secret_key', 'sk_test_000000000');
        config()->set('tabby.merchant_code', 'ae');
        config()->set('tabby.currency', 'AED');
    }
}
