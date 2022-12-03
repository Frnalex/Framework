<?php

namespace App\Blog\Actions;

use App\Blog\Entity\Post;
use App\Blog\Table\PostTable;
use DateTime;
use Framework\Actions\RouterAwareAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminBlogAction
{
    use RouterAwareAction;

    public function __construct(
        private RendererInterface $renderer,
        private Router $router,
        private PostTable $postTable,
        private FlashService $flash,
    ) {
    }


    public function __invoke(ServerRequestInterface $request)
    {
        if ($request->getMethod() === "DELETE") {
            return $this->delete($request);
        }
        if (substr((string)$request->getUri(), -3) === 'new') {
            return $this->create($request);
        }
        if ($request->getAttribute('id')) {
            return $this->edit($request);
        }
        return $this->index($request);
    }

    public function index(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        $posts = $this->postTable->findPaginated(12, $params['page'] ?? 1);
        return  $this->renderer->render('@blog/admin/index', [
            'items' => $posts,
        ]);
    }

    /**
     * Edite un article
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function edit(ServerRequestInterface $request): ResponseInterface|string
    {
        $item = $this->postTable->find($request->getAttribute('id'));

        if ($request->getMethod() === 'POST') {
            $params = $this->getParams($request);
            $validator = $this->getValidator($request);

            if ($validator->isValid()) {
                $this->postTable->update($item->id, $params);
                $this->flash->success("L'article a bien été modifié");
                return $this->redirect("blog.admin.index");
            }
            $errors = $validator->getErrors();
            $params['id'] = $item->id;
            $item = $params;
        }

        return $this->renderer->render('@blog/admin/edit', [
            "item" => $item,
            "errors" => $errors ?? []
        ]);
    }

    /**
     * Créé un nouvel article
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function create(ServerRequestInterface $request): ResponseInterface|string
    {
        if ($request->getMethod() === 'POST') {
            $params = $this->getParams($request);
            $validator = $this->getValidator($request);

            if ($validator->isValid()) {
                $this->postTable->insert($params);
                $this->flash->success("L'article a bien été créé");
                return $this->redirect("blog.admin.index");
            }

            $errors = $validator->getErrors();
            $item = $params;
        }

        $item = new Post();
        $item->created_at = new DateTime();

        return $this->renderer->render('@blog/admin/create', [
            "item" => $item ?? [],
            "errors" => $errors ?? []
        ]);
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $this->postTable->delete($request->getAttribute('id'));
        return $this->redirect("blog.admin.index");
    }


    private function getParams(ServerRequestInterface $request): array
    {
        $params = array_filter(
            $request->getParsedBody(),
            fn ($key) => in_array($key, ['name', 'slug', 'content', 'created_at']),
            ARRAY_FILTER_USE_KEY
        );

        return [
         ...$params,
         'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function getValidator(ServerRequestInterface $request)
    {
        return (new Validator($request->getParsedBody()))
            ->required('name', 'content', 'slug', 'created_at')
            ->length('content', 10)
            ->length('name', 2, 250)
            ->length('slug', 2, 50)
            ->dateTime('created_at')
            ->slug('slug')
        ;
    }
}
