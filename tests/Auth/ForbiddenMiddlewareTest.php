<?php

namespace Tests\Auth;

use App\Auth\ForbiddenMiddleware;
use Framework\Auth\ForbiddenException;
use Framework\Auth\User;
use Framework\Session\ArraySession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

class ForbiddenMiddlewareTest extends TestCase
{
    private ArraySession $session;

    public function setUp(): void
    {
        $this->session = new ArraySession();
    }

    public function makeRequest(string $path = '/'): ServerRequestInterface
    {
        $uri = $this->getMockBuilder(UriInterface::class)->getMock();
        $uri->method('getPath')->willReturn($path);
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getUri')->willReturn($uri);

        /** @var ServerRequestInterface $request */
        return $request;
    }

    public function makeHandler(): MockObject
    {
        return $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
    }

    public function makeMiddleware(): ForbiddenMiddleware
    {
        return new ForbiddenMiddleware('/login', $this->session);
    }

    public function testCatchForbiddenException(): void
    {
        $handler = $this->makeHandler();
        $handler->expects($this->once())->method('handle')->willThrowException(new ForbiddenException());

        /** @var RequestHandlerInterface $handler */
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testCatchTypeErrorException(): void
    {
        $handler = $this->makeHandler();
        $handler->expects($this->once())->method('handle')->willReturnCallback(fn (User $user) => true);

        /** @var RequestHandlerInterface $handler */
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testBubbleError(): void
    {
        $handler = $this->makeHandler();
        $handler->expects($this->once())->method('handle')->willReturnCallback(function () {
            throw new TypeError("test", 200);
        });

        try {
            /** @var RequestHandlerInterface $handler */
            $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);
        } catch (\Throwable $e) {
            $this->assertEquals(200, $e->getCode());
            $this->assertEquals("test", $e->getMessage());
        }
    }

    public function testProcessValidRequest(): void
    {
        /** @var ResponseInterface $response */
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler = $this->makeHandler();
        $handler->expects($this->once())->method('handle')->willReturn($response);

        /** @var RequestHandlerInterface $handler */
        $this->assertSame($response, $this->makeMiddleware()->process($this->makeRequest('/test'), $handler));
    }
}
