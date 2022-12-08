<?php

namespace Framework\Twig;

use Framework\Router;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap5View;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PagerFantaExtension extends AbstractExtension
{
    public function __construct(
        private Router $router
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('paginate', [$this, 'paginate'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Génère la pagination
     * @param Pagerfanta $paginatedResults
     * @param string $route
     * @param array $routerParams
     * @param array $queryArgs
     *
     * @return string
     */
    public function paginate(
        Pagerfanta $paginatedResults,
        string $route,
        array $routerParams = [],
        array $queryArgs = []
    ): string {
        $view = new TwitterBootstrap5View();
        return $view->render(
            $paginatedResults,
            function (int $page) use ($route, $routerParams, $queryArgs) {
                if ($page > 1) {
                    $queryArgs['page'] = $page;
                }
                return $this->router->generateUri($route, $routerParams, $queryArgs);
            }
        );
    }
}
