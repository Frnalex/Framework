<?php

namespace App\Blog\Actions;

use App\Blog\Table\CategoryTable;
use Framework\Actions\CrudAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class CategoryCrudAction extends CrudAction
{
    protected string $viewPath = "@blog/admin/categories";
    protected string $routePrefix = "blog.category.admin";

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        private CategoryTable $table,
        FlashService $flash,
    ) {
        parent::__construct($renderer, $router, $table, $flash);
    }

    protected function getParams(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            throw new RuntimeException("body must be an array");
        }

        return array_filter(
            $body,
            fn ($key) => in_array($key, ['name', 'slug']),
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return parent::getValidator($request)
            ->required('name', 'slug')
            ->length('name', 2, 250)
            ->length('slug', 2, 50)
            ->unique('slug', $this->table->getTable(), $this->table->getPdo(), $request->getAttribute('id'))
            ->slug('slug')
        ;
    }
}
