<?php

namespace App\Account\Actions;

use Framework\Auth;
use Framework\Renderer\RendererInterface;

class AccountAction
{
    public function __construct(
        private RendererInterface $renderer,
        private Auth $auth
    ) {
    }

    public function __invoke(): string
    {
        $user = $this->auth->getUser();
        return $this->renderer->render('@account/account', [
            'user' => $user
        ]);
    }
}
