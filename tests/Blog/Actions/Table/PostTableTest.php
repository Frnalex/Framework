<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
use PHPUnit\Framework\TestCase;
use Tests\DatabaseTestCase;

class PostTableTest extends DatabaseTestCase
{
    private PostTable $postTable;

    public function setUp(): void
    {
        parent::setUp();
        $this->postTable = new PostTable($this->pdo);
    }

    public function testFind()
    {
        $this->seedDatabase();
        $post = $this->postTable->find(1);
        $this->assertInstanceOf(Post::class, $post);
    }

    public function testFindNotFoundRecord()
    {
        $idNotExisting = 999999;
        $post = $this->postTable->find($idNotExisting);
        $this->assertNull($post);
    }
}
