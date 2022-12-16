<?php

use App\Auth\AuthTwigExtension;
use App\Auth\DatabaseAuth;
use App\Auth\ForbiddenMiddleware;
use Framework\Auth;
use Framework\Auth\User;

return [
    'auth.login' => '/login',
    'twig.extensions' => DI\add([
       DI\get(AuthTwigExtension::class)
    ]),
    User::class => DI\factory(fn (Auth $auth) => $auth->getUser())->parameter('auth', DI\get(Auth::class)),
    Auth::class => DI\get(DatabaseAuth::class),
    ForbiddenMiddleware::class => DI\autowire()->constructorParameter('loginPath', DI\get('auth.login'))
];
