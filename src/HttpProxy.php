<?php

namespace Firevel\HttpProxy;

use Exception;
use Config;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class HttpProxy
{
    /**
     * Guzzle client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Get Guzzle client.
     *
     * @return Client
     */
    public function getClient()
    {
        if (empty($this->client)) {
            $this->client = new Client();
        }
        return $this->client;
    }

    /**
     * Set Guzzle client.
     *
     * @param Client $client
     */
    public function setClient($client)
    {
        return $this->client = $client;
    }

    /**
     * Get endpoint configuration by route name.
     *
     * @param  string $routeName
     * @return array
     */
    public function getEndpointConfigurationByRouteName($routeName)
    {
    	$endpoint = config('proxy.default');

    	if (! empty($routeName) && (strpos($routeName, ':') !== false)) {
    		$endpoint = strtok($routeName, ':');
    	}

    	if (! Config::has("proxy.endpoints.{$endpoint}")) {
    		throw new Exception("Missing {$endpoint} endpoint configuration.");
    	}

    	return Config::get("proxy.endpoints.{$endpoint}");
    }

    /**
     * Forward http request.
     *
     * @return Response
     */
    public function forwardRequest($url, Request $request)
    {
        $routeName =  $request->route()->getName();

        $endpointConfiguration = $this->getEndpointConfigurationByRouteName($routeName);

        $options = [];
        $options['http_errors'] = false;

        $options['headers'] =  array_intersect_key(
            $request->header(),
            array_fill_keys($endpointConfiguration['allowed_headers'], '')
        );

        switch ($request->method()) {
            case 'GET':
                $options['query'] = http_build_query($request->all());
                break;
            case 'POST':
            case 'DELETE':
            case 'PATCH':
            case 'PUT':
                $options['query'] = http_build_query($request->query());
                $options['json'] = $request->post();
                break;
        }

        if (!empty($routeName)) {
            event('proxy.request: '. $routeName, [$request]);
        }

        $response = $this->getClient()->request(
            $request->method(),
            rtrim($endpointConfiguration['url'], '/') . '/' . ltrim($url, '/'),
            $options
        );

        $response->getBody()->rewind();

        if (!empty($routeName)) {
            event('proxy.response: '. $routeName, [$response, $request]);
        }

        if ($response->getStatusCode() >= 300 || $response->getStatusCode() < 200) {
            event('proxy.error: '. ($routeName ?? $request->url()), [$response, $request]);
        }

        $response->getBody()->rewind();

        return response($response->getBody()->getContents(), $response->getStatusCode())
                  ->withHeaders($response->getHeaders());
    }
}
