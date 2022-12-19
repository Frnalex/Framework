<?php

namespace Tests\Framework\Middleware;

use Framework\Middleware\RouterMiddleware;
use Framework\Router;
use Framework\Router\Route;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddlewareTest extends TestCase
{
    private MockObject $handler;

    public function setUp(): void
    {
        $this->handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
    }

    public function makeMiddleware(?Route $route): RouterMiddleware
    {
        $router = $this->getMockBuilder(Router::class)->getMock();
        $router->method('match')->willReturn($route);

        /** @var Router $router */
        return new RouterMiddleware($router);
    }

    public function testPassParameters(): void
    {
        $route = new Route('demo', 'trim', ['id' => 2]);
        $middleware = $this->makeMiddleware($route);

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function (ServerRequestInterface $request) use ($route) {
                $this->assertEquals(2, $request->getAttribute('id'));
                $this->assertEquals($route, $request->getAttribute(get_class($route)));
                return new Response();
            }));

        /** @var RequestHandlerInterface $handler */
        $handler = $this->handler;
        $middleware->process(new ServerRequest('GET', '/test'), $handler);
    }

    public function testCallNext(): void
    {
        $request = new ServerRequest('GET', '/test');
        $middleware = $this->makeMiddleware(null);
        $response = new Response();

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(fn () => $response));

        /** @var RequestHandlerInterface $handler */
        $handler = $this->handler;
        $this->assertEquals($response, $middleware->process($request, $handler));
    }
}
