<?php

namespace App\Blog\Actions;

use App\Blog\Table\PostTable;
use Framework\Actions\RouterAwareAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BlogAction
{
    use RouterAwareAction;

    public function __construct(
        private RendererInterface $renderer,
        private Router $router,
        private PostTable $postTable
    ) {
    }


    public function __invoke(ServerRequestInterface $request)
    {
        if ($request->getAttribute('id')) {
            return $this->show($request);
        }
        return $this->index($request);
    }

    public function index(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        $posts = $this->postTable->findPaginated(12, $params['page'] ?? 1);
        return  $this->renderer->render('@blog/index', ['posts' => $posts]);
    }

    /**
     * Affiche les détails d'un article
     *
     * @param ServerRequestInterface $request
     *
     * @return string|ResponseInterface
     */
    public function show(ServerRequestInterface $request): string|ResponseInterface
    {
        $slug = $request->getAttribute('slug');
        $post = $this->postTable->find($request->getAttribute('id'));

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
