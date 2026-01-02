<?php

namespace Tests;

use PiovezanFernando\LaravelApiVueForge\LaravelApiVueForgeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelApiVueForgeServiceProvider::class,
        ];
    }
}
