<?php

namespace Tests\Framework\Middleware;

use Framework\Middleware\NotFoundMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundMiddlewareTest extends TestCase
{
    public function testSendNotFound(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $handler->expects($this->never())->method('handle')->willReturn($response);

        $middleware = new NotFoundMiddleware();
        $request = (new ServerRequest('GET', '/test'));

        /** @var RequestHandlerInterface $handler */
        $response = $middleware->process($request, $handler);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
