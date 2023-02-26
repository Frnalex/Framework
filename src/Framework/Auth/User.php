<?php

namespace Framework\Auth;

interface User
{
    public function getId(): int;
    public function getUsername(): string;
    public function getRoles(): array;
}
