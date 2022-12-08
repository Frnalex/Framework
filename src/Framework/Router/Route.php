<?php

namespace Framework\Router;

use Closure;

class Route
{
    public function __construct(
        private string $name,
        private Closure|string $callback,
        private array $parameters
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Closure|string
     */
    public function getCallback(): Closure|string
    {
        return $this->callback;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->parameters;
    }
}
