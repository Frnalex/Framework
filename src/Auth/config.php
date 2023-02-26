<?php

use App\Auth\AuthTwigExtension;
use App\Auth\DatabaseAuth;
use App\Auth\ForbiddenMiddleware;
use App\Auth\UserTable;
use Framework\Auth;
use Framework\Auth\User;

return [
    'auth.login' => '/login',
    'auth.entity' => \App\Auth\User::class,
    'twig.extensions' => DI\add([
        DI\get(AuthTwigExtension::class)
    ]),
    User::class => DI\factory(fn (Auth $auth) => $auth->getUser())->parameter('auth', DI\get(Auth::class)),
    Auth::class => DI\get(DatabaseAuth::class),
    UserTable::class => DI\autowire()->constructorParameter('entity', DI\get('auth.entity')),
    ForbiddenMiddleware::class => DI\autowire()->constructorParameter('loginPath', DI\get('auth.login'))
];
