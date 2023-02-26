<?php

namespace Framework\Middleware;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CombinedMiddlewareHandler implements RequestHandlerInterface
{
    private int $index = 0;

    public function __construct(
        private ContainerInterface $container,
        private RequestHandlerInterface $handler,
        private array $middlewares = []
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getMiddleware();
        if (is_null($middleware)) {
            return $this->handler->handle($request);
        } elseif (is_callable($middleware)) {
            $response = call_user_func_array($middleware, [$request, [$this, 'handle']]);
            if (is_string($response)) {
                return new Response(200, [], $response);
            }
            return $response;
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }

        throw new Exception("Vous ne devriez pas arriver ici");
    }

    private function getMiddleware(): ?object
    {
        if (array_key_exists($this->index, $this->middlewares)) {
            if (is_string($this->middlewares[$this->index])) {
                $middleware = $this->container->get($this->middlewares[$this->index]);
            } else {
                $middleware = $this->middlewares[$this->index];
            }
            $this->index++;
            return $middleware;
        }
        return null;
    }
}
