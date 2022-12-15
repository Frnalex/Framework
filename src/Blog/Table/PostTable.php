<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
use Framework\Database\Query;
use Framework\Database\Table;

class PostTable extends Table
{
    protected string $entity = Post::class;

    protected string $table = "posts";

    public function findAll(): Query
    {
        $category = new CategoryTable($this->getPdo());
        return $this->makeQuery()
            ->select('p.*, c.name as category_name, c.slug as category_slug')
            ->join($category->getTable() . ' as c', 'c.id = p.category_id')
            ->order('p.created_at DESC');
    }

    public function findPublic(): Query
    {
        return $this->findAll()
            ->where('p.created_at < NOW()')
            ->where('p.published = 1');
    }

    public function findPublicForCategory(int $id): Query
    {
        return $this->findPublic()->where("p.category_id = $id");
    }

    public function findWithCategory(int $postId): Post
    {
        return $this->findPublic()->where("p.id = $postId")->fetch();
    }
}
