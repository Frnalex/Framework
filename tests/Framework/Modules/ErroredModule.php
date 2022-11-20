<?php

namespace Tests\Framework\Modules;

use Framework\Router;

class ErroredModule
{
    public function __construct(Router $router)
    {
        $router->get('/demo', function () {
            // Returns something that is neither a string nor a ResponseInterface
            return new \stdClass();
        }, 'demo');
    }
}
