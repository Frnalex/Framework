<?php

namespace App\Blog\Actions;

use App\Blog\Entity\Post;
use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use DateTime;
use Framework\Actions\CrudAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class PostCrudAction extends CrudAction
{
    protected string $viewPath = "@blog/admin/posts";
    protected string $routePrefix = "blog.admin";

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        private PostTable $table,
        FlashService $flash,
        private CategoryTable $categoryTable
    ) {
        parent::__construct($renderer, $router, $table, $flash);
    }

    protected function formParams(array $params): array
    {
        $params['categories'] = $this->categoryTable->findList();
        return $params;
    }

    protected function getNewEntity(): Post
    {
        $post = new Post();
        $post->created_at = new DateTime();
        return $post;
    }

    protected function getParams(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            throw new RuntimeException("body must be an array");
        }

        $params = array_filter(
            $body,
            fn ($key) => in_array($key, ['name', 'slug', 'content', 'created_at', 'category_id']),
            ARRAY_FILTER_USE_KEY
        );

        return [
         ...$params,
         'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return parent::getValidator($request)
            ->required('name', 'content', 'slug', 'created_at', 'category_id')
            ->length('content', 10)
            ->length('name', 2, 250)
            ->length('slug', 2, 50)
            ->exists('category_id', $this->categoryTable->getTable(), $this->table->getPdo())
            ->dateTime('created_at')
            ->slug('slug')
        ;
    }
}
