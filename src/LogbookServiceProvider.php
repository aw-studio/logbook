<?php

namespace AwStudio\Logbook;

use Illuminate\Support\ServiceProvider;

class LogbookServiceProvider extends ServiceProvider
{
    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/logbook.php', 'logbook');

        $this->app->singleton('logbook', function () {
            return new Logbook();
        });
    }
}
