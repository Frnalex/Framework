<?php

namespace Framework\Router;

use Closure;

class Route
{
    private string $name;
    private Closure $callback;
    private array $parameters;

    public function __construct(
        string $name,
        callable $callback,
        array $parameters
    ) {
        $this->name = $name;
        $this->callback = Closure::fromCallable($callback);
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
     * @return callable
     */
    public function getCallback(): callable
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
