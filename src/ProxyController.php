<?php

namespace Firevel\HttpProxy;

use App\Http\Controllers\Controller;
use Firevel\HttpProxy\HttpProxy;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class ProxyController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get proxy service.
     *
     * @return HttpProxy
     */
    public function getHttpProxy()
    {
        return app(HttpProxy::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function proxy(Request $request)
    {
        return $this
            ->getHttpProxy()
            ->forwardRequest(
                $request->path(),
                $request
            );
    }
}
