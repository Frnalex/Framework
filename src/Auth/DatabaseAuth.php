<?php

namespace App\Auth;

use Framework\Auth;
use Framework\Database\NoRecordException;
use Framework\Session\SessionInterface;

class DatabaseAuth implements Auth
{
    private ?User $user = null;

    public function __construct(
        private UserTable $userTable,
        private SessionInterface $session
    ) {
    }

    public function login(string $username, string $password): ?User
    {
        if (empty($username) || empty($password)) {
            return null;
        }

        /** @var ?User $user */
        $user = $this->userTable->findBy('username', $username);

        if (!$user || !password_verify($password, $user->password)) {
            return null;
        }

        $this->session->set('auth.user', $user->id);
        return $user;
    }

    public function logout(): void
    {
        $this->session->delete('auth.user');
    }

    public function getUser(): ?User
    {
        if ($this->user) {
            return $this->user;
        }

        $userId = $this->session->get('auth.user');

        if (!$userId) {
            $this->session->delete('auth.user');
            return null;
        }

        try {
            $this->user = $this->userTable->find($userId);
            return $this->user;
        } catch (NoRecordException $exception) {
            $this->session->delete('auth.user');
            return null;
        }
    }
}
