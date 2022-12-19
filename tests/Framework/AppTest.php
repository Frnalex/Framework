<?php

namespace Tests\Framework;

use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class AppTest extends TestCase
{
    private App $app;

    public function setUp(): void
    {
        $this->app = new App();
    }

    public function testApp(): void
    {
        $this->app->addModule(get_class($this));
        $this->assertEquals([get_class($this)], $this->app->getModules());
    }

    public function testAppWithArrayDefinition(): void
    {
        $app = new App(['key' => 'value']);
        $this->assertEquals('value', $app->getContainer()->get('key'));
    }

    public function testPipe(): void
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $middleware2 = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);
        $middleware2->expects($this->never())->method('process')->willReturn($response);

        /** @var MiddlewareInterface $middleware */
        /** @var ServerRequestInterface $request */
        $this->assertEquals($response, $this->app->pipe($middleware)->run($request));
    }

    public function testPipeWithClosure(): void
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);

        /** @var MiddlewareInterface $middleware */
        $this->app
        ->pipe(function ($request, $next) {
            return $next($request);
        })
        ->pipe($middleware);

        /** @var ServerRequestInterface $request */
        $this->assertEquals($response, $this->app->run($request));
    }

    public function testPipeWithoutMiddleware(): void
    {
        /** @var ServerRequestInterface $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();

        $this->expectException(\Exception::class);
        $this->app->run($request);
    }

    public function testPipeWithPrefix(): void
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);


        /** @var MiddlewareInterface $middleware */
        $this->app->pipe('/test', $middleware);
        $this->assertEquals($response, $this->app->run(new ServerRequest('GET', '/test/hello')));
        $this->expectException(\Exception::class);
        $this->assertEquals($response, $this->app->run(new ServerRequest('GET', '/hello')));
    }
}
