<?php

namespace Framework\Middleware;

use ArrayAccess;
use Framework\Exception\CsrfInvalidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(
        private array|ArrayAccess &$session,
        private int $limit = 50,
        private string $formKey = "_csrf",
        private string $sessionKey = "csrf"
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ["POST", "PUT", "DELETE"])) {
            $params = $request->getParsedBody() ?: [];
            $csrfList = $this->session[$this->sessionKey] ?? [];

            if (!array_key_exists($this->formKey, $params) || !in_array($params[$this->formKey], $csrfList)) {
                $this->reject();
            }

            $this->useToken($params[$this->formKey]);
            return $handler->handle($request);
        }

        return $handler->handle($request);
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $csrfList = $this->session[$this->sessionKey] ?? [];
        $csrfList[] = $token;
        $this->session[$this->sessionKey] = $csrfList;
        $this->limitTokens();
        return $token;
    }

    private function reject(): void
    {
        throw new CsrfInvalidException();
    }

    private function useToken(string $token): void
    {
        $tokens = array_filter(
            $this->session[$this->sessionKey],
            fn ($t) => $token !== $t
        );

        $this->session[$this->sessionKey] = $tokens;
    }

    private function limitTokens(): void
    {
        $tokens = $this->session[$this->sessionKey] ?? [];
        if (count($tokens) > $this->limit) {
            array_shift($tokens);
        }
        $this->session[$this->sessionKey] = $tokens;
    }

    public function getFormKey(): string
    {
        return $this->formKey;
    }
}
