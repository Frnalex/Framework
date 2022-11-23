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
     * @return Post
     */
    public function find(int $id): Post
    {
        $query = $this->pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $query->execute([$id]);
        $query->setFetchMode(PDO::FETCH_CLASS, Post::class);
        return $query->fetch();
    }
}
