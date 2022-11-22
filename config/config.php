<?php

use Framework\Renderer\RendererInterface;
use Framework\Renderer\TwigRendererFactory;
use Framework\Router;
use Framework\Router\RouterTwigExtension;
use Psr\Container\ContainerInterface;

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
    RendererInterface::class => DI\factory(TwigRendererFactory::class),
    PDO::class => function (ContainerInterface $container) {
        return new PDO(
            "mysql:host={$container->get('database.host')};dbname={$container->get('database.name')}",
            $container->get('database.username'),
            $container->get('database.password'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
];
