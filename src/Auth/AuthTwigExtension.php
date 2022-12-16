<?php

namespace App\Auth;

use Framework\Auth;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AuthTwigExtension extends AbstractExtension
{
    public function __construct(
        private Auth $auth
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_user', [$this->auth, 'getUser']),
        ];
    }
}
