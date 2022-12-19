<?php

namespace Tests\Framework\Auth;

use App\Auth\User;
use Framework\Auth;
use Framework\Auth\ForbiddenException;
use Framework\Auth\LoggedInMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggedInMiddlewareTest extends TestCase
{
    public function makeMiddleware(?User $user): LoggedInMiddleware
    {
        $auth = $this->getMockBuilder(Auth::class)->getMock();
        $auth->method('getUser')->willReturn($user);

        /** @var Auth $auth */
        return new LoggedInMiddleware($auth);
    }

    public function makeHandler(InvocationOrder $calls): RequestHandlerInterface
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $handler->expects($calls)->method('handle')->willReturn($response);


        /** @var RequestHandlerInterface $handler */
        return $handler;
    }

    public function testThrowIfNoUser(): void
    {
        $request = (new ServerRequest('GET', '/test/'));
        $this->expectException(ForbiddenException::class);


        $this->makeMiddleware(null)->process(
            $request,
            $this->makeHandler($this->never())
        );
    }

    public function testNextIfUser(): void
    {
        /** @var User $user */
        $user = $this->getMockBuilder(User::class)->getMock();
        $request = (new ServerRequest('GET', '/test/'));
        $this->makeMiddleware($user)->process(
            $request,
            $this->makeHandler($this->once())
        );
    }
}
