<?php

namespace Tests\Framework\Auth;

use Framework\Auth;
use Framework\Auth\ForbiddenException;
use Framework\Auth\RoleMiddleware;
use Framework\Auth\User;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

class RoleMiddlewareTest extends TestCase
{
    private RoleMiddleware $middleware;
    private MockObject $auth;

    public function setUp(): void
    {
        $this->auth = $this->getMockBuilder(Auth::class)->getMock();

        /** @var Auth $auth */
        $auth = $this->auth;
        $this->middleware = new RoleMiddleware($auth, "admin");
    }

    public function testWithUnauthenticatedUser(): void
    {
        $this->auth->method('getUser')->willReturn(null);

        /** @var RequestHandlerInterface $handler */
        $handler =  $this->makeHandler();

        $this->expectException(ForbiddenException::class);
        $this->middleware->process(new ServerRequest('GET', '/demo'), $handler);
    }

    public function testWithBadRole(): void
    {
        $user = $this->getMockBuilder(User::class)->getMock();
        $user->method('getRoles')->willReturn(['user']);
        /** @var User $user */
        $this->auth->method('getUser')->willReturn($user);
        /** @var RequestHandlerInterface $handler */
        $handler =  $this->makeHandler();

        $this->expectException(ForbiddenException::class);
        $this->middleware->process(new ServerRequest('GET', '/demo'), $handler);
    }

    public function testWithGoodRole(): void
    {
        $user = $this->getMockBuilder(User::class)->getMock();
        $user->method('getRoles')->willReturn(['admin']);
        /** @var User $user */
        $this->auth->method('getUser')->willReturn($user);

        $handler =  $this->makeHandler();
        $handler->expects($this->once())->method('handle')->willReturn(new Response());

        /** @var RequestHandlerInterface $handler */
        $this->middleware->process(new ServerRequest('GET', '/demo'), $handler);
    }

    public function makeHandler(): MockObject
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $handler->method('handle')->willReturn(new Response());

        return $handler;
    }
}
