<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
use Framework\Database\PaginatedQuery;
use Framework\Database\Table;
use Pagerfanta\Pagerfanta;

class PostTable extends Table
{
    protected ?string $entity = Post::class;

    protected string $table = "posts";

    public function findPaginatedPublic(int $perPage, int $currentpage): Pagerfanta
    {
        $query = new PaginatedQuery(
            $this->pdo,
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM posts as p 
            LEFT JOIN categories as c ON c.id = p.category_id 
            ORDER BY p.created_at DESC",
            "SELECT count(id) FROM {$this->table}",
            $this->entity
        );

        $pagerFanta = (new Pagerfanta($query))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentpage)
        ;

        return $pagerFanta;
    }

    public function findPaginatedPublicForCategory(int $perPage, int $currentpage, int $categoryId): Pagerfanta
    {
        $query = new PaginatedQuery(
            $this->pdo,
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM posts as p 
            LEFT JOIN categories as c ON c.id = p.category_id 
            WHERE p.category_id = :categoryId
            ORDER BY p.created_at DESC",
            "SELECT count(id) FROM {$this->table} WHERE category_id = :categoryId",
            $this->entity,
            ['categoryId' => $categoryId]
        );

        $pagerFanta = (new Pagerfanta($query))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentpage)
        ;

        return $pagerFanta;
    }

    public function findWithCategory(int $id): Post
    {
        return $this->fetchOrFail(
            'SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM posts as p
            LEFT JOIN categories as c ON c.id = p.category_id
            WHERE p.id = :id',
            ['id' => $id]
        );
    }

    protected function paginationQuery(): string
    {
        return
            "SELECT p.id, p.name, c.name category_name
            FROM {$this->table} as p
            LEFT JOIN categories as c ON p.category_id = c.id
            ORDER BY created_at DESC"
        ;
    }
}
