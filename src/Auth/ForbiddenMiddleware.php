<?php

namespace App\Auth;

use Exception;
use Framework\Auth\ForbiddenException;
use Framework\Response\RedirectResponse;
use Framework\Session\FlashService;
use Framework\Session\SessionInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

class ForbiddenMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $loginPath,
        private SessionInterface $session
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $exception) {
            return $this->redirectLogin($request);
        } catch (TypeError $error) {
            if (strpos($error->getMessage(), \Framework\Auth\User::class)) {
                return $this->redirectLogin($request);
            }
        }

        throw new ForbiddenException();
    }

    private function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error("Vous devez posséder un compte pour accéder à cette page");
        return new RedirectResponse($this->loginPath);
    }
}
