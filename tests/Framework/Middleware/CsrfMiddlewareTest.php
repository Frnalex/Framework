<?php

namespace Tests\Framework\Middleware;

use Framework\Exception\CsrfInvalidException;
use Framework\Middleware\CsrfMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddlewareTest extends TestCase
{
    private CsrfMiddleware $middleware;
    private array $session;

    public function setUp(): void
    {
        $this->session = [];
        $this->middleware = new CsrfMiddleware($this->session);
    }

    public function testLetGetRequestPass(): void
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)->onlyMethods(['handle'])->getMock();
        $requestHandler->expects($this->once())->method('handle');

        $request = (new ServerRequest('GET', "/test"));

        /** @var RequestHandlerInterface $requestHandler */
        $this->middleware->process($request, $requestHandler);
    }

    public function testBlockPostRequestWithoutCsrf(): void
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)->onlyMethods(['handle'])->getMock();
        $requestHandler->expects($this->never())->method('handle');

        $request = (new ServerRequest('POST', "/test"));

        $this->expectException(CsrfInvalidException::class);
        /** @var RequestHandlerInterface $requestHandler */
        $this->middleware->process($request, $requestHandler);
    }

    public function testBlockPostRequestWithInvalidCsrf(): void
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)->onlyMethods(['handle'])->getMock();
        $requestHandler->expects($this->never())->method('handle');

        $this->middleware->generateToken();
        $request = (new ServerRequest('POST', "/test"));
        $request = $request->withParsedBody(["_csrf" => 'invalid_token']);

        $this->expectException(CsrfInvalidException::class);
        /** @var RequestHandlerInterface $requestHandler */
        $this->middleware->process($request, $requestHandler);
    }

    public function testLetPostWithTokenPass(): void
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)->onlyMethods(['handle'])->getMock();
        $requestHandler->expects($this->once())->method('handle');

        $token = $this->middleware->generateToken();
        $request = (new ServerRequest('POST', "/test"));
        $request = $request->withParsedBody(["_csrf" => $token]);

        /** @var RequestHandlerInterface $requestHandler */
        $this->middleware->process($request, $requestHandler);
    }

    public function testLetPostWithTokenPassOnce(): void
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)->onlyMethods(['handle'])->getMock();
        $requestHandler->expects($this->once())->method('handle');

        $token = $this->middleware->generateToken();
        $request = (new ServerRequest('POST', "/test"));
        $request = $request->withParsedBody(["_csrf" => $token]);

        /** @var RequestHandlerInterface $requestHandler */
        $this->middleware->process($request, $requestHandler);
        $this->expectException(CsrfInvalidException::class);
        $this->middleware->process($request, $requestHandler);
    }

    public function testLimitTheTokenNumber(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $token = $this->middleware->generateToken();
        }

        $this->assertCount(50, $this->session['csrf']);
        $this->assertEquals($token, $this->session['csrf'][49]);
    }
}
