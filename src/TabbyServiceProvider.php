<?php

namespace Aghfatehi\Tabby;

use Aghfatehi\Tabby\Services\TabbyService;
use Illuminate\Support\ServiceProvider;

class TabbyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/tabby.php' => config_path('tabby.php'),
        ], 'tabby-config');

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'tabby-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/tabby'),
            ], 'tabby-views');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tabby');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/tabby.php', 'tabby');

        $this->app->singleton('tabby', function ($app) {
            return new TabbyService();
        });

        $this->app->alias('tabby', TabbyService::class);
    }

    public function provides(): array
    {
        return ['tabby', TabbyService::class];
    }
}
