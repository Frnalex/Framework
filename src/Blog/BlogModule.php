<?php

namespace App\Blog;

use Framework\Renderer\RendererInterface;
use Framework\Router;
use Psr\Http\Message\ServerRequestInterface;

class BlogModule
{
    private RendererInterface $renderer;

    public function __construct(Router $router, RendererInterface $renderer)
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
