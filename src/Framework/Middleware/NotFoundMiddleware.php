<?php

namespace Framework\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class NotFoundMiddleware
{
    public function __invoke(): ResponseInterface
    {
        return new Response(404, [], 'Erreur 404');
    }
}
