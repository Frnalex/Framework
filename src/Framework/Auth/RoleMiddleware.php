<?php

namespace Framework\Auth;

use Framework\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Auth $auth,
        private string $role
    ) {
        # code...
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->getUser();

        if ($user === null || !in_array($this->role, $user->getRoles())) {
            throw new ForbiddenException();
        }

        return $handler->handle($request);
    }
}
