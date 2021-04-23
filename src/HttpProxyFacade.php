<?php

namespace Firevel\HttpProxy;

use Firevel\HttpProxy\HttpProxy;
use Illuminate\Support\Facades\Facade;

class HttpProxyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return HttpProxy::class;
    }
}
