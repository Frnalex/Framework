<?php

namespace Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class MethodMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

        if (!is_array($parsedBody)) {
            throw new RuntimeException("parsedBody must be an array");
        }

        if (
            array_key_exists('_method', $parsedBody)
            && in_array($parsedBody['_method'], ['DELETE', 'PUT'])
        ) {
            $request = $request->withMethod($parsedBody['_method']);
        }

        return $next->handle($request);
    }
}
