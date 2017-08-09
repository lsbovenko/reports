<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use GuzzleHttp\Client;

/**
 * Class AuthApiClientServiceProvider
 * @package App\Providers
 */
class AuthApiClientServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('auth.api.client', function ($app) {
            return new \App\Service\AuthApiClient(new Client());
        });
    }
}