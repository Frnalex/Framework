<?php

use Framework\Renderer\RendererInterface;
use Framework\Renderer\TwigRendererFactory;
use Framework\Router;
use Framework\Router\RouterTwigExtension;

use function DI\get;

return [
    'database.host'=> 'localhost',
    'database.username'=> 'root',
    'database.password'=> '',
    'database.name'=> 'framework_php',
    'views.path' => dirname(__DIR__) . '/views',
    'twig.extensions' => [
        get(RouterTwigExtension::class)
    ],
    Router::class => DI\autowire(),
    RendererInterface::class => DI\factory(TwigRendererFactory::class)
];
