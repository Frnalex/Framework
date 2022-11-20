<?php

namespace App\Blog;

use Framework\Renderer;
use Framework\Router;
use Psr\Http\Message\ServerRequestInterface;

class BlogModule
{
    private Renderer $renderer;

    public function __construct(Router $router, Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->renderer->addPath('blog', __DIR__ . '/views');
        $router->get('/blog', [$this, 'index'], 'blog.index');
        $router->get('/blog/[*:slug]', [$this, 'show'], 'blog.show');
    }

    public function index(ServerRequestInterface $request): string
    {
        return  $this->renderer->render('@blog/index');
    }

    public function show(ServerRequestInterface $request): string
    {
        return  $this->renderer->render('@blog/show', [
            'slug' => $request->getAttribute(('slug'))
        ]);
    }
}
