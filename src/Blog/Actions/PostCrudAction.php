<?php

namespace App\Blog\Actions;

use App\Blog\Entity\Post;
use App\Blog\PostUpload;
use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use DateTime;
use Framework\Actions\CrudAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostCrudAction extends CrudAction
{
    protected string $viewPath = "@blog/admin/posts";
    protected string $routePrefix = "blog.admin";

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        private PostTable $table,
        FlashService $flash,
        private CategoryTable $categoryTable,
        private PostUpload $postUpload
    ) {
        parent::__construct($renderer, $router, $table, $flash);
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $post = $this->table->find($request->getAttribute('id'));
        $this->postUpload->delete($post->image);
        return parent::delete($request);
    }

    protected function formParams(array $params): array
    {
        $params['categories'] = $this->categoryTable->findList();
        return $params;
    }

    protected function getNewEntity(): Post
    {
        $post = new Post();
        $post->setCreatedAt(new DateTime());
        return $post;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Post $post
     *
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, object $post): array
    {
        $params = [...$request->getParsedBody(), ...$request->getUploadedFiles()];
        $image = $this->postUpload->upload($params['image'], $post->image);

        if ($image) {
            $params['image'] = $image;
        } else {
            unset($params['image']);
        }

        $params = array_filter(
            $params,
            fn ($key) => in_array($key, ['name', 'slug', 'content', 'created_at', 'category_id', 'image', 'published']),
            ARRAY_FILTER_USE_KEY
        );

        return [...$params, 'updated_at' => date('Y-m-d H:i:s')];
    }

    protected function getValidator(ServerRequestInterface $request): Validator
    {
        $validator = parent::getValidator($request)
            ->required('name', 'content', 'slug', 'created_at', 'category_id')
            ->length('content', 10)
            ->length('name', 2, 250)
            ->length('slug', 2, 50)
            ->exists('category_id', $this->categoryTable->getTable(), $this->table->getPdo())
            ->dateTime('created_at')
            ->slug('slug')
            ->extension('image', ['jpg', 'png']);

        if (is_null($request->getAttribute('id'))) {
            $validator->uploaded('image');
        }

        return $validator;
    }
}
