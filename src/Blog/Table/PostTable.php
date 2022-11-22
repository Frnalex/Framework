<?php

namespace App\Blog\Table;

use PDO;
use stdClass;

class PostTable
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Pagine les articles
     * @return \stdClass
     */
    public function findPaginated(): array
    {
        return $this->pdo->query('SELECT * FROM posts ORDER BY created_at LIMIT 10')->fetchAll();
    }

    /**
     * Récupère un article à partir de son id
     * @param int $id
     * @return stdClass
     */
    public function find(int $id): stdClass
    {
        $query = $this->pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch();
    }
}
