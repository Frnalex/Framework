<?php

namespace Framework\Middleware;

use Framework\Router\Route;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispacherMiddleware
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route = $request->getAttribute(Route::class);

        if (is_null($route)) {
            return $next($request);
        }

        $callback = $route->getCallback();
        if (is_string($callback)) {
            $callback = $this->container->get($route->getCallback());
        }
        $response = call_user_func_array($callback, [$request]);

        if (is_string($response)) {
            return new Response(200, [], $response);
        } elseif ($response instanceof ResponseInterface) {
            return $response;
        } else {
            throw new \Exception("The response is not a string or an instance of ResponseInterface");
        }
    }
}
