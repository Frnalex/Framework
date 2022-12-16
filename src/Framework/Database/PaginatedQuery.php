<?php

namespace Framework\Database;

use Pagerfanta\Adapter\AdapterInterface;
use PDO;
use Traversable;

class PaginatedQuery implements AdapterInterface
{
    public function __construct(
        private Query $query
    ) {
    }

    /**
     * Returns the number of results for the list.
     * @return int
     */
    public function getNbResults(): int
    {
        return $this->query->count();
    }

    /**
     * Returns an slice of the results representing the current page of items in the list.
     * @param int $offset
     * @param int $length
     * @return QueryResult
     */
    public function getSlice(int $offset, int $length): QueryResult
    {
        $query = clone $this->query;
        return $query->limit($length, $offset)->fetchAll();
    }
}
