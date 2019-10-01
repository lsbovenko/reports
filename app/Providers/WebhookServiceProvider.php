<?php

namespace App\Providers;

use App\Service\Webhook;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Webhook::class, function ($app) {

            $encrypter = new Encrypter(config('app.webhook.' . config('app.env') . '.secret_key'), 'AES-256-CBC');
            return new Webhook($encrypter);
        });
    }
}
