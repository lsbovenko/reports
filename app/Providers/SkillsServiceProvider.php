<?php

namespace App\Providers;

use App\Service\Skills;
use GuzzleHttp\Client;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\ServiceProvider;

/**
 * Class SkillsServiceProvider
 * @package App\Providers
 */
class SkillsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Skills::class, function ($app) {
            $encrypter = new Encrypter(config('app.webhook.' . env('APP_ENV') . '.secret_key'), 'AES-256-CBC');
            return new Skills(new Client(), $encrypter);
        });
    }
}
