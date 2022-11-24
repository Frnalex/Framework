<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
use Framework\Database\PaginatedQuery;
use Pagerfanta\Pagerfanta;
use PDO;
use stdClass;

class PostTable
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Pagine les articles
     * @param int $perPage
     *
     * @return Pagerfanta
     */
    public function findPaginated(int $perPage, int $currentpage): Pagerfanta
    {
        $query = new PaginatedQuery(
            $this->pdo,
            'SELECT * FROM posts ORDER BY created_at DESC',
            'SELECT count(id) FROM posts',
            Post::class
        );

        $pagerFanta = (new Pagerfanta($query))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentpage)
        ;

        return $pagerFanta;
    }

    /**
     * Récupère un article à partir de son id
     * @param int $id
     * @return Post|null
     */
    public function find(int $id): ?Post
    {
        $query = $this->pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $query->execute([$id]);
        $query->setFetchMode(PDO::FETCH_CLASS, Post::class);
        return $query->fetch() ?: null;
    }

    /**
     * Met à jour un enregistrement au niveau de la base de donnée
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function update(int $id, array $params): bool
    {
        $fieldQuery = $this->buildFieldQuery($params);
        $statement = $this->pdo->prepare("UPDATE posts SET {$fieldQuery} WHERE id = :id");
        return $statement->execute([...$params, 'id' => $id]);
    }

    /**
     * Créé un nouvel enregistrement
     * @param array $params
     * @return bool
     */
    public function insert(array $params): bool
    {
        $fields = array_keys($params);
        $values = array_map(fn ($field) => ":{$field}", $fields);

        $statement = $this->pdo->prepare(
            "INSERT INTO posts (" . join(',', $fields) . ")
            VALUES (" . join(',', $values) . ")"
        );
        return $statement->execute($params);
    }

    /**
     * Supprime un enregistrement
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    private function buildFieldQuery(array $params)
    {
        return join(', ', array_map(fn ($field) => "{$field} = :{$field}", array_keys($params)));
    }
}
