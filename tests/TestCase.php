<?php

namespace Firevel\HttpProxy\Tests;

use Firevel\HttpProxy\HttpProxyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            HttpProxyServiceProvider::class,
        ];
    }
}
