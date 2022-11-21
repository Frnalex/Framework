<?php

namespace Framework;

use AltoRouter;
use Framework\Router\Route;
use Psr\Http\Message\RequestInterface;

/**
 * Register and match router
 */
class Router
{
    /**
     * @var AltoRouter
     */
    private $router;

    public function __construct()
    {
        $this->router = new AltoRouter();
    }

    /**
     * @param string $path
     * @param callable $callable
     * @param string $name
     */
    public function get(string $path, callable|string $callable, string $name)
    {
        $this->router->map('GET', $path, $callable, $name);
    }

    /**
     * @param RequestInterface $request
     *
     * @return Route|null
     */
    public function match(RequestInterface $request): ?Route
    {
        $result = $this->router->match($request->getUri()->getPath());

        if ($result) {
            return new Route($result['name'], $result['target'], $result['params']);
        }

        return null;
    }

    public function generateUri(string $name, array $params): ?string
    {
        return $this->router->generate($name, $params);
    }
}
