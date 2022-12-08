<?php

namespace App\Blog\Actions;

use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use Framework\Actions\RouterAwareAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostShowAction
{
    use RouterAwareAction;

    public function __construct(
        private RendererInterface $renderer,
        private Router $router,
        private PostTable $postTable,
    ) {
    }


    public function __invoke(ServerRequestInterface $request)
    {
        $slug = $request->getAttribute('slug');
        $post = $this->postTable->findWithCategory($request->getAttribute('id'));

        if ($post->slug !== $slug) {
            return $this->redirect('blog.show', [
                'slug' => $post->slug,
                'id' => $post->id,
            ]);
        }

        return  $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }
}
