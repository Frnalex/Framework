<?php

namespace App\Auth;

use Framework\Auth\User as UserInterface;

class User implements UserInterface
{
    public int $id;
    public string $username;
    public string $email;
    public string $password;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return [];
    }
}
