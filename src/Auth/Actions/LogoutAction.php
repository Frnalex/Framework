<?php

namespace App\Auth\Actions;

use App\Auth\DatabaseAuth;
use Framework\Response\RedirectResponse;
use Framework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;

class LogoutAction
{
    public function __construct(
        private DatabaseAuth $auth,
        private FlashService $flash
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $this->auth->logout();
        $this->flash->success("Vous êtes maintenant déconnecté");
        return new RedirectResponse('/');
    }
}
