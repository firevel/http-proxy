<?php

namespace Firevel\HttpProxy;

use Firevel\HttpProxy\Commands\HttpProxyCommand;
use Firevel\HttpProxy\HttpProxy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class HttpProxyServiceProvider  extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/proxy.php', 'proxy');

        $this->app->singleton(HttpProxy::class, function ($app) {
            return new HttpProxy();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/../config/proxy.php' => config_path('proxy.php')
            ],
            'config'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [HttpProxy::class];
    }
}

