<?php

namespace Tests\Framework;

use Framework\Router;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    public function setUp(): void
    {
        $this->router = new Router();
    }

    public function testGetMethod()
    {
        $request = new Request('GET', '/blog');
        $this->router->get(
            '/blog',
            function () {
                return 'test';
            },
            'blog'
        );
        $route = $this->router->match($request);

        $this->assertEquals('blog', $route->getName());
        $this->assertEquals('test', call_user_func_array($route->getCallback(), [$request]));
    }

    public function testGetMethodIfUrlDoesNotExists()
    {
        $request = new Request('GET', '/blog');
        $this->router->get(
            '/blog-url-not-exists',
            function () {
                return 'test';
            },
            'blog'
        );
        $route = $this->router->match($request);

        $this->assertEquals(null, $route);
    }

    public function testGetMethodWithParameters()
    {
        $request = new Request('GET', '/blog/mon-slug-8');
        $this->router->get('/blog', function () {
            return 'azeaze';
        }, 'posts');
        $this->router->get('/blog/[*:slug]-[i:id]', function () {
            return 'test';
        }, 'post.show');
        $route = $this->router->match($request);

        $this->assertEquals('post.show', $route->getName());
        $this->assertEquals('test', call_user_func_array($route->getCallback(), [$request]));
        $this->assertEquals(['slug' => 'mon-slug', 'id' => '8'], $route->getParams());
    }

    public function testGenerateUri()
    {
        $this->router->get('/blog', function () {
            return 'azeaze';
        }, 'posts');
        $this->router->get('/blog/[*:slug]-[i:id]', function () {
            return 'test';
        }, 'post.show');

        $uri = $this->router->generateUri('post.show', ['slug' => 'mon-article', 'id' => '18']);

        $this->assertEquals('/blog/mon-article-18', $uri);
    }
}
