<?php

namespace App\Account\Actions;

use App\Auth\DatabaseAuth;
use App\Auth\User;
use App\Auth\UserTable;
use Framework\Database\Hydrator;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SignupAction
{
    public function __construct(
        private RendererInterface $renderer,
        private UserTable $userTable,
        private Router $router,
        private DatabaseAuth $auth,
        private FlashService $flashService
    ) {
    }

    public function __invoke(ServerRequestInterface $request): string|ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@account/signup');
        }

        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->required('username', 'email', 'password', 'password_confirm')
            ->length('username', 4)
            ->email('email')
            ->length('password', 4)
            ->confirm('password')
            ->unique('username', $this->userTable)
            ->unique('email', $this->userTable)
        ;

        if ($validator->isValid()) {
            $userParams = [
                'username' => $params['username'],
                'email' => $params['email'],
                'password' => password_hash($params['password'], PASSWORD_DEFAULT),
            ];
            $this->userTable->insert($userParams);
            $user = Hydrator::hydrate($userParams, User::class);
            $user->id = $this->userTable->getPdo()->lastInsertId();
            $this->auth->setUser($user);
            $this->flashService->success('Votre compte a bien été créé');
            return new RedirectResponse($this->router->generateUri('account.profile'));
        }

        $errors = $validator->getErrors();
        return $this->renderer->render('@account/signup', [
            'errors' => $errors,
            'user' => [
                'username' => $params['username'],
                'email' => $params['email'],
            ]
        ]);
    }
}
