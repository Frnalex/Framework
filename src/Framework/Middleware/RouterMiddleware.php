<?php

namespace Framework\Middleware;

use Framework\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class RouterMiddleware
{
    public function __construct(
        private Router $router
    ) {
    }

    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route = $this->router->match($request);

        if (is_null($route)) {
            return $next($request);
        }

        $params = $route->getParams();
        $request = array_reduce(
            array_keys($params),
            fn ($request, $key) => $request->withAttribute($key, $params[$key]),
            $request
        );

        $request = $request->withAttribute(get_class($route), $route);
        return $next($request);
    }
}
