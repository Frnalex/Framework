<?php

namespace Tests\Framework\Middleware;

use Framework\Middleware\MethodMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MethodMiddlewareTest extends TestCase
{
    private MethodMiddleware $middleware;

    public function setUp(): void
    {
        $this->middleware = new MethodMiddleware();
    }

    public function testAddMethod()
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)->onlyMethods(['handle'])->getMock();

        $requestHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn (ServerRequestInterface $request) => $request->getMethod() === "DELETE"))
        ;

        $request = (new ServerRequest('POST', "/test"))
        ->withParsedBody(['_method' => 'DELETE']);

        /** @var RequestHandlerInterface $requestHandler */
        $this->middleware->process($request, $requestHandler);
    }
}
