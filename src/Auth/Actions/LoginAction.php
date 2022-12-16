<?php

namespace App\Auth\Actions;

use Framework\Renderer\RendererInterface;

class LoginAction
{
    public function __construct(
        private RendererInterface $renderer
    ) {
    }

    public function __invoke(): string
    {
        return $this->renderer->render('@auth/login');
    }
}
