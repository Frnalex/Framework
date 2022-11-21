<?php

namespace Framework\Router;

use Closure;

class Route
{
    private string $name;
    private Closure|string $callback;
    private array $parameters;

    public function __construct(
        string $name,
        callable|string $callback,
        array $parameters
    ) {
        $this->name = $name;
        $this->callback = $callback;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return callable|string
     */
    public function getCallback(): callable|string
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
