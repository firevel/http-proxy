<?php

namespace Firevel\HttpProxy\Tests;

use Config;
use Firevel\HttpProxy\HttpProxy;
use Firevel\HttpProxy\ProxyController;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Route;

class ExampleTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     *
     * @return void
     */
    protected function defineRoutes($router)
    {
        Route::get('/phpunit-get-proxy-test', [ProxyController::class, 'proxy'])->name('get-test');
        Route::post('/phpunit-post-proxy-test', [ProxyController::class, 'proxy']);
        Route::patch('/phpunit-post-proxy-test', [ProxyController::class, 'proxy']);
        Route::get('/phpunit-endpoint-get-proxy-test', [ProxyController::class, 'proxy'])->name('alternative:test');
    }

    /**
     * Test GET call.
     *
     * @return void
     */
    public function testGetRoute()
    {
        Event::fake();

        Config::set('proxy.endpoints.default.url', 'http://test.com//');

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        app(HttpProxy::class)->setClient($client);
        $response = $this->call('GET', '/phpunit-get-proxy-test');
        $request = $mock->getLastRequest();

        $this->assertEquals(200, $response->status());
        $this->assertEquals('test.com', $request->getUri()->getHost());
        $this->assertEquals('GET', $request->getMethod());
        Event::assertDispatched('proxy.request: get-test');
        Event::assertDispatched('proxy.response: get-test');
    }

    /**
     * Test GET call.
     *
     * @return void
     */
    public function testAlternativeEndpoint()
    {
        $alternativeConfig = Config::get('proxy.endpoints.default');
        $alternativeConfig['url'] = 'http://test2.com/';

        Config::set('proxy.endpoints.alternative', $alternativeConfig);

        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        app(HttpProxy::class)->setClient($client);

        $response = $this->call('GET', '/phpunit-endpoint-get-proxy-test');
        $request = $mock->getLastRequest();

        $this->assertEquals(200, $response->status());
        $this->assertEquals('test2.com', $request->getUri()->getHost());
    }

    /**
     * Test GET call returning error.
     *
     * @return void
     */
    public function testGetRouteError()
    {
        Event::fake();
        // 501 to avoid positive on regular 500
        $mock = new MockHandler([
            new Response(501, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        app(HttpProxy::class)->setClient($client);

        $response = $this->call('GET', '/phpunit-get-proxy-test');

        $this->assertEquals(501, $response->status());
        Event::assertDispatched('proxy.error: get-test');
    }

    /**
     * Test POST call.
     *
     * @return void
     */
    public function testPostRoute()
    {
        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        app(HttpProxy::class)->setClient($client);

        $response = $this->call('POST', '/phpunit-post-proxy-test');

        $this->assertEquals(200, $response->status());
    }

    /**
     * Test PATCH call.
     *
     * @return void
     */
    public function testPatchRoute()
    {
        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        app(HttpProxy::class)->setClient($client);

        $response = $this->call('PATCH', '/phpunit-post-proxy-test');

        $this->assertEquals(200, $response->status());
    }

    /**
     * Test allowed allowed_headers.
     *
     * @return void
     */
    public function testHeadersForwarding()
    {
        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0, 'Referer' => 'http://referer.com/', ['forbidden' => 'foo']], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        app(HttpProxy::class)->setClient($client);

        $response = $this->call('POST', '/phpunit-post-proxy-test');

        $this->assertTrue($response->headers->has('referer'));
        $this->assertFalse($response->headers->has('forbidden'));
    }

    /**
     * Test missing routes handling.
     *
     * @return void
     */
    public function testMissingRoute()
    {
        $response = $this->call('GET', '/phpunit-proxy-missing-route');

        $this->assertEquals(404, $response->status());
    }
}
