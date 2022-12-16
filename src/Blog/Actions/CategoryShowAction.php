<?php

namespace App\Blog\Actions;

use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use Framework\Auth\User;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class CategoryShowAction
{
    public function __construct(
        private RendererInterface $renderer,
        private PostTable $postTable,
        private CategoryTable $categoryTable,
        private readonly User $user // @phpstan-ignore-line
    ) {
    }

    public function __invoke(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        $category = $this->categoryTable->findBy('slug', $request->getAttribute('slug'));
        $posts = $this->postTable->findPublicForCategory($category->id)->paginate(12, $params['page'] ?? 1);
        $categories = $this->categoryTable->findAll();
        $page  = $params['page'] ?? 1;

        return  $this->renderer->render('@blog/index', [
            'posts' => $posts,
            'categories' => $categories,
            'category' => $category,
            'page' => $page
        ]);
    }
}
