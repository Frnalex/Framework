<?php

namespace Framework\Router;

use Closure;

class Route
{
    public function __construct(
        private string $name,
        private Closure|string|array $callback,
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
     * @return Closure|string|array
     */
    public function getCallback(): Closure|string|array
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
