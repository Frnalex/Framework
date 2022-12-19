<?php

namespace Framework\Database;

use ArrayAccess;
use Exception;
use Iterator;
use stdClass;

class QueryResult implements ArrayAccess, Iterator
{
    private int $index = 0;
    private array $hydratedRecords = [];

    public function __construct(
        private array $records,
        private ?string $entity = null
    ) {
    }

    public function get(int $index): mixed
    {
        if ($this->entity) {
            if (!isset($this->hydratedRecords[$index])) {
                $this->hydratedRecords[$index] = Hydrator::hydrate($this->records[$index], $this->entity);
            }
            return $this->hydratedRecords[$index];
        } else {
            return $this->entity;
        }
    }


    public function current(): mixed
    {
        return $this->get($this->index);
    }

    public function next(): void
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->records[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->records[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception("Can't alter records");
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Exception("Can't alter records");
    }
}
