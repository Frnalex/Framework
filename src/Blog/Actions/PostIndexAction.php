<?php

namespace App\Blog\Actions;

use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostIndexAction
{
    public function __construct(
        private RendererInterface $renderer,
        private PostTable $postTable,
        private CategoryTable $categoryTable
    ) {
    }


    public function __invoke(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        $posts = $this->postTable->findPublic()->paginate(12, $params['page'] ?? 1);
        $categories = $this->categoryTable->findAll();

        return  $this->renderer->render('@blog/index', [
            'posts' => $posts,
            'categories' => $categories
        ]);
    }
}
