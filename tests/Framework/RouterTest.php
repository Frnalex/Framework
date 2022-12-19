<?php

namespace Tests\Framework;

use Framework\Router;
use Framework\Router\Route;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    public function setUp(): void
    {
        $this->router = new Router();
    }

    public function testGetMethod(): void
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

    public function testCrudMethod(): void
    {
        $this->router->crud('/blog', function () {
        }, 'blog');
        $this->assertEquals('blog.index', $this->router->match(new ServerRequest('GET', '/blog'))->getName());
        $this->assertEquals('blog.create', $this->router->match(new ServerRequest('GET', '/blog/new'))->getName());
        $this->assertInstanceOf(Route::class, $this->router->match(new ServerRequest('POST', '/blog/new')));
        $this->assertEquals('blog.edit', $this->router->match(new ServerRequest('GET', '/blog/1'))->getName());
        $this->assertInstanceOf(Route::class, $this->router->match(new ServerRequest('POST', '/blog/1')));
        $this->assertInstanceOf(Route::class, $this->router->match(new ServerRequest('DELETE', '/blog/1')));
    }

    public function testGetMethodIfUrlDoesNotExists(): void
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

    public function testGetMethodWithParameters(): void
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

    public function testGenerateUri(): void
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

    public function testGenerateUriWithQueryParams(): void
    {
        $this->router->get('/blog', function () {
            return 'azeaze';
        }, 'posts');
        $this->router->get('/blog/[*:slug]-[i:id]', function () {
            return 'test';
        }, 'post.show');

        $uri = $this->router->generateUri(
            'post.show',
            ['slug' => 'mon-article', 'id' => '18'],
            ['page' => 2]
        );

        $this->assertEquals('/blog/mon-article-18?page=2', $uri);
    }
}
