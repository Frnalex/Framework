<?php

namespace Framework\Router;

use Framework\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterTwigExtension extends AbstractExtension
{
    public function __construct(
        private Router $router
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'pathFor']),
            new TwigFunction('is_subpath', [$this, 'isSubpath']),
        ];
    }

    public function pathFor(string $path, array $params = []): string
    {
        return $this->router->generateUri($path, $params);
    }

    public function isSubpath(string $path): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $expectedUri = $this->router->generateUri($path);
        return strpos($uri, $expectedUri) !== false;
    }
}
