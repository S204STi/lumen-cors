<?php

namespace Vluzrmos\LumenCors;

/**
 * Class CorsMiddlewareTest.
 */
class CorsMiddlewareTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testShouldHandlePreflightRequest()
    {
        $middleware = $this->createCorsMiddleware();

        $requestedMethods = ['get', 'post', 'delete', 'put', 'patch', 'head', 'options', 'anyMethod'];

        $verbs = ['GET', 'PUT', 'PATCH', 'POST', 'DELETE', 'HEAD', 'ANYOTHER'];

        foreach ($requestedMethods as $method) {
            /** @var \Illuminate\Http\Request $request */
            $request = $this->createPreflightRequest($method);

            $response = $middleware->handle($request, function ($request) {
                return response('Welcome!');
            });

            $this->assertEquals('OK', $response->getContent());
        }

        foreach ($verbs as $verb) {
            $request = $this->createPreflightRequest('get', $verb);

            $response = $middleware->handle($request, function ($request) {
                return response('Welcome!');
            });

            $this->assertEquals('Welcome!', $response->getContent());
        }
    }

    /**
     * @return void
     */
    public function testShouldSeeWelcomeWithCorsHeaders()
    {
        $middleware = $this->createCorsMiddleware();

        $verbs = ['GET', 'PUT', 'PATCH', 'POST', 'DELETE', 'OPTIONS', 'HEAD', 'ANYOTHER'];

        $cors = $this->createCorsService();

        foreach ($verbs as $http) {
            /** @var \Illuminate\Http\Request $request */
            $request = $this->createRequest($http);

            $response = $middleware->handle($request, function ($request) {
                return response('Welcome!');
            });

            foreach ($cors->getCorsHeaders() as $key => $value) {
                $this->assertEquals($value, $response->headers->get($key));
            }

            $this->assertEquals('Welcome!', $response->getContent());
        }
    }

    /**
     * @return void
     */
    public function testShouldDownloadWithCorsHeaders()
    {
        $middleware = $this->createCorsMiddleware();

        /** @var \Illuminate\Http\Request $request */
        $request = $this->createRequest();

        $response = $middleware->handle($request, function ($request) {
            return response()->download(__DIR__.'/stubs/download.txt');
        });

        $cors = $middleware->getCorsService();

        foreach ($cors->getCorsHeaders() as $key => $value) {
            $this->assertEquals($value, $response->headers->get($key));
        }

        $this->assertStringMatchesFormat('File was downloaded!', file_get_contents($response->getFile()));
    }
}
