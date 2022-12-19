<?php

namespace Tests\Framework\Middleware;

use Framework\Middleware\TrailingSlashMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlashMiddlewareTest extends TestCase
{
    private MockObject $handler;

    public function setUp(): void
    {
        $this->handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
    }

    public function testRedirectIfSlash(): void
    {
        $request = (new ServerRequest('GET', '/test/'));
        $middleware = new TrailingSlashMiddleware();

        $this->handler
            ->expects($this->never())
            ->method('handle')
            ->will($this->returnCallback(function () {
            }));

        /** @var RequestHandlerInterface $handler */
        $handler = $this->handler;
        $response = $middleware->process($request, $handler);
        $this->assertEquals(['/test'], $response->getHeader('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testCallNextIfNoSlash(): void
    {
        $request = (new ServerRequest('GET', '/test'));
        $response = new Response();
        $middleware = new TrailingSlashMiddleware();

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(fn () => $response));

        /** @var RequestHandlerInterface $handler */
        $handler = $this->handler;
        $this->assertEquals($response, $middleware->process($request, $handler));
    }
}
