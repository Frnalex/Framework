<?php

namespace Tests\Framework;

use App\Blog\BlogModule;
use Framework\App;
use Framework\Renderer;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tests\Framework\Modules\ErroredModule;
use Tests\Framework\Modules\StringModule;

class AppTest extends TestCase
{
    private $renderer;

    public function setUp(): void
    {
        $this->renderer = new Renderer();
        $this->renderer->addPath(dirname(__DIR__) . '/views');
    }

    public function testRedirectTrailingSlash()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/testslash/');
        $response = $app->run($request);
        $this->assertContains('/testslash', $response->getHeader('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testBlog()
    {
        $app = new App([BlogModule::class], ['renderer' => $this->renderer]);

        $request = new ServerRequest('GET', '/blog');
        $response = $app->run($request);
        $this->assertStringContainsString('<h1>Bienvenue sur le blog</h1>', $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());

        $requestSingle = new ServerRequest('GET', '/blog/article-de-test');
        $responseSingle = $app->run($requestSingle);
        $this->assertStringContainsString(
            "<h1>Bienvenue sur l'article article-de-test</h1>",
            $responseSingle->getBody()
        );
    }

    public function testThrowExceptionIfNoResponseSent()
    {
        $app = new App([ErroredModule::class], ['renderer' => $this->renderer]);
        $request = new ServerRequest('GET', '/demo');
        $this->expectException(\Exception::class);
        $app->run($request);
    }

    public function testConvertStringToResponse()
    {
        $app = new App([StringModule::class], ['renderer' => $this->renderer]);
        $request = new ServerRequest('GET', '/demo');
        $response = $app->run($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('DEMO', $response->getBody());
    }

    public function testError404()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/not-exists');
        $response = $app->run($request);
        $this->assertStringContainsString('<h1>Erreur 404</h1>', $response->getBody());
        $this->assertEquals(404, $response->getStatusCode());
    }
}
