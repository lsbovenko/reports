<?php

namespace App\Providers;

use App\Service\Statistics;
use Illuminate\Support\ServiceProvider;

class StatisticsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Statistics::class, function($app){
            return new Statistics();
        });
    }
}
