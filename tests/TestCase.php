<?php

namespace Tests;

use AwStudio\Logbook\LogbookServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LogbookServiceProvider::class,
        ];
    }
}
