<?php

namespace Tests\Framework\Middleware;

use Framework\Middleware\DispatcherMiddleware;
use Framework\Router\Route;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatcherMiddlewareTest extends TestCase
{
    public function testDispatchTheCallback(): void
    {
        $callback = function () {
            return 'Test';
        };
        $route = new Route('test', $callback, []);
        $request = (new ServerRequest('GET', '/test'))->withAttribute(Route::class, $route);
        /** @var ContainerInterface $container */
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $dispatcher = new DispatcherMiddleware($container);
        /** @var RequestHandlerInterface $handler */
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $dispatcher->process($request, $handler);
        $this->assertEquals('Test', (string)$response->getBody());
    }

    public function testCallNextIfNotRoutes(): void
    {
        /** @var ContainerInterface $container */
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $handler->expects($this->once())->method('handle')->willReturn($response);

        $request = (new ServerRequest('GET', '/test'));
        $dispatcher = new DispatcherMiddleware($container);

        /** @var RequestHandlerInterface $handler */
        $this->assertEquals($response, $dispatcher->process($request, $handler));
    }
}
