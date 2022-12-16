<?php

namespace App\Auth;

use App\Auth\Actions\LoginAction;
use App\Auth\Actions\LoginAttemptAction;
use App\Auth\Actions\LogoutAction;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Psr\Container\ContainerInterface;

class AuthModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    public function __construct(ContainerInterface $container, Router $router, RendererInterface $renderer)
    {
        $renderer->addPath('auth', __DIR__ . '/views');
        $router->get($container->get('auth.login'), LoginAction::class, 'auth.login');
        $router->post($container->get('auth.login'), LoginAttemptAction::class);
        $router->post('/logout', LogoutAction::class, 'auth.logout');
    }
}
