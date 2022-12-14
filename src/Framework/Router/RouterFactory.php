<?php

namespace Framework\Router;

use Framework\Router;

class RouterFactory
{
    public function __invoke(): Router
    {
        return new Router();
    }
}
