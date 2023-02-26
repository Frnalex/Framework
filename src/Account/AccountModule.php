<?php

namespace App\Account;

use App\Account\Actions\AccountAction;
use App\Account\Actions\AccountEditAction;
use App\Account\Actions\SignupAction;
use Framework\Auth\LoggedInMiddleware;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Router;

class AccountModule extends Module
{
    public const MIGRATIONS = __DIR__ . '/db/migrations';
    public const DEFINITIONS = __DIR__ . '/config.php';

    public function __construct(Router $router, RendererInterface $renderer)
    {
        $renderer->addPath('account', __DIR__ . '/views');
        $router->get('/inscription', SignupAction::class, 'account.signup');
        $router->post('/inscription', SignupAction::class);
        $router->get('/profil', [LoggedInMiddleware::class, AccountAction::class], 'account');
        $router->post('/profil', [LoggedInMiddleware::class, AccountEditAction::class]);
    }
}
