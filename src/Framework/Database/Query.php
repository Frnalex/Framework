<?php

namespace Framework\Database;

use PDO;
use PDOStatement;

class Query
{
    private ?array $select = null;
    private array $from;
    private array $where = [];
    private array $group;
    private array $order;
    private array $limit;
    private ?array $params = null;


    public function __construct(
        private ?PDO $pdo = null
    ) {
    }

    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->from[$alias] = $table;
        } else {
            $this->from[] = $table;
        }
        return $this;
    }

    public function select(string ...$fields): self
    {
        $this->select = $fields;
        return $this;
    }

    public function where(string ...$condition): self
    {
        $this->where = [...$this->where, ...$condition];
        return $this;
    }

    public function count(): int
    {
        $this->select("COUNT(id)");
        return $this->execute()->fetchColumn();
    }

    public function params(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function __toString()
    {
        $parts = ['SELECT'];

        if ($this->select) {
            $parts[] = join(', ', $this->select);
        } else {
            $parts[] = "*";
        }

        $parts[] = 'FROM';
        $parts[] = $this->buildFrom();

        if (!empty($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = "(" . join(") AND (", $this->where) . ")";
        }


        return join(' ', $parts);
    }

    private function buildFrom(): string
    {
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$value as $key";
            } else {
                $from[] = $value;
            }
        }

        return join(', ', $from);
    }

    private function execute(): PDOStatement
    {
        $query = $this->__toString();
        if ($this->params) {
            $statement = $this->pdo->prepare($query);
            $statement->execute($this->params);
            return $statement;
        }
        return $this->pdo->query($query);
    }
}
