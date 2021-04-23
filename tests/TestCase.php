<?php

namespace Firevel\HttpProxy\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Firevel\HttpProxy\HttpProxyServiceProvider;

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
