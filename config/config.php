<?php

use Framework\MailerFactory;
use Framework\Middleware\CsrfMiddleware;
use Framework\Renderer\RendererInterface;
use Framework\Renderer\TwigRendererFactory;
use Framework\Router;
use Framework\Router\RouterFactory;
use Framework\Router\RouterTwigExtension;
use Framework\Session\PHPSession;
use Framework\Session\SessionInterface;
use Framework\Twig\CsrfExtension;
use Framework\Twig\FlashExtension;
use Framework\Twig\FormExtension;
use Framework\Twig\PagerFantaExtension;
use Framework\Twig\TextExtension;
use Framework\Twig\TimeExtension;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;

return [
    'env' => $_ENV['ENV'],
    'database.host'=> 'localhost',
    'database.username'=> 'root',
    'database.password'=> '',
    'database.name'=> 'framework_php',
    'views.path' => dirname(__DIR__) . '/views',
    'twig.extensions' => [
        DI\get(RouterTwigExtension::class),
        DI\get(PagerFantaExtension::class),
        DI\get(TextExtension::class),
        DI\get(TimeExtension::class),
        DI\get(FlashExtension::class),
        DI\get(FormExtension::class),
        DI\get(CsrfExtension::class)
    ],
    SessionInterface::class => DI\autowire(PHPSession::class),
    CsrfMiddleware::class => DI\autowire()->constructor(DI\get(SessionInterface::class)),
    Router::class => DI\factory(RouterFactory::class),
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
    },

    // MAILER
    'mail.to' => 'admin@admin.fr',
    MailerInterface::class => \DI\factory(MailerFactory::class),
];
