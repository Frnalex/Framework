<?php

namespace Framework\Actions;

use Framework\Database\Hydrator;
use Framework\Database\Table;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CrudAction
{
    use RouterAwareAction;

    protected string $viewPath;
    protected string $routePrefix;
    protected array $messages = [
        "create" => "L'élément a bien été créé",
        "edit" => "L'élément a bien été modifié",
    ];

    public function __construct(
        private RendererInterface $renderer,
        private Router $router,
        private Table $table,
        private FlashService $flash,
    ) {
    }


    public function __invoke(ServerRequestInterface $request): string|ResponseInterface
    {
        $this->renderer->addGlobal('viewPath', $this->viewPath);
        $this->renderer->addGlobal('routePrefix', $this->routePrefix);
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

    /**
     * Affiche la liste des éléments
     * @param ServerRequestInterface $request
     * @return string
     */
    public function index(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        $items = $this->table->findAll()->paginate(12, $params['page'] ?? 1);
        return  $this->renderer->render("{$this->viewPath}/index", [
            'items' => $items,
        ]);
    }

    /**
     * Edite un élément
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function edit(ServerRequestInterface $request): ResponseInterface|string
    {
        $item = $this->table->find($request->getAttribute('id'));

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);

            if ($validator->isValid()) {
                $this->table->update($item->id, $this->getParams($request, $item));
                $this->flash->success($this->messages['edit']);
                return $this->redirect("{$this->routePrefix}.index");
            }

            $errors = $validator->getErrors();
            Hydrator::hydrate($request->getParsedBody(), $item);
            // $item = ['id' => $item->id, ...$request->getParsedBody()];
        }

        return $this->renderer->render("{$this->viewPath}/edit", $this->formParams([
            "item" => $item,
            "errors" => $errors ?? []
        ]));
    }

    /**
     * Créé un nouvel élément
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function create(ServerRequestInterface $request): ResponseInterface|string
    {
        $item = $this->getNewEntity();
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);

            if ($validator->isValid()) {
                $this->table->insert($this->getParams($request, $item));
                $this->flash->success($this->messages['create']);
                return $this->redirect("{$this->routePrefix}.index");
            }

            Hydrator::hydrate($request->getParsedBody(), $item);
            $errors = $validator->getErrors();
        }

        return $this->renderer->render("{$this->viewPath}/create", $this->formParams([
            "item" => $item,
            "errors" => $errors ?? []
        ]));
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $this->table->delete($request->getAttribute('id'));
        return $this->redirect("{$this->routePrefix}.index");
    }

    protected function getParams(ServerRequestInterface $request, object $item): array
    {
        $body = $request->getParsedBody() ?: [];

        return array_filter(
            $body,
            fn ($key) => in_array($key, []),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Génère le validateur pour valider les données
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return new Validator([...$request->getParsedBody(), ...$request->getUploadedFiles()]);
    }

    /**
     * Génère une nouvelle entité pour l'action de création
     * @return mixed
     */
    protected function getNewEntity(): mixed
    {
        return [];
    }

    /**
     * Permet de traiter les paramètres à envoyer à la vue
     * @param array $params
     * @return array
     */
    protected function formParams(array $params): array
    {
        return $params;
    }
}
