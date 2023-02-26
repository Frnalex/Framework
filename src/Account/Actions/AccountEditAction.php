<?php

namespace App\Account\Actions;

use App\Auth\UserTable;
use Framework\Auth;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountEditAction
{
    public function __construct(
        private RendererInterface $renderer,
        private Auth $auth,
        private FlashService $flashService,
        private UserTable $userTable
    ) {
    }

    public function __invoke(ServerRequestInterface $request): string|ResponseInterface
    {
        $user = $this->auth->getUser();
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->confirm('password')
            ->required('firstname', 'lastname');

        if ($validator->isValid()) {
            $userParams = [
                'firstname' => $params['firstname'],
                'lastname' => $params['lastname']
            ];
            if (!empty($params['password'])) {
                $userParams['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
            }
            $this->userTable->update($user->getId(), $userParams);
            $this->flashService->success('Votre compte a bien été mis à jour');
            return new RedirectResponse($request->getUri()->getPath());
        }

        $errors = $validator->getErrors();
        return $this->renderer->render('@account/account', [
            'user' => $user,
            'errors' => $errors,
        ]);
    }
}
