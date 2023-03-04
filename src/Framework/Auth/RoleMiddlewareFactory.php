<?php

namespace Framework\Auth;

use Framework\Auth;

class RoleMiddlewareFactory
{
    public function __construct(private Auth $auth)
    {
        # code...
    }

    public function makeForRole(string $role): RoleMiddleware
    {
        return new RoleMiddleware($this->auth, $role);
    }
}
