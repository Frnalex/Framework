<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
use Framework\Database\NoRecordException;
use Tests\DatabaseTestCase;

class PostTableTest extends DatabaseTestCase
{
    private PostTable $postTable;

    public function setUp(): void
    {
        parent::setUp();
        $pdo = $this->getPDO();
        $this->migrateDatabase($pdo);
        $this->postTable = new PostTable($pdo);
    }

    public function testFind(): void
    {
        $this->seedDatabase($this->postTable->getPdo());
        $post = $this->postTable->find(1);
        $this->assertInstanceOf(Post::class, $post);
    }

    public function testFindNotFoundRecord(): void
    {
        $this->expectException(NoRecordException::class);
        $idNotExisting = 999999;
        $this->postTable->find($idNotExisting);
    }

    public function testUpdate(): void
    {
        $this->seedDatabase($this->postTable->getPdo());

        $editedTitle = 'Edited Title';
        $editedSlug = 'edited-title';
        $this->postTable->update(1, ['name' => $editedTitle,'slug' => $editedSlug]);
        $post = $this->postTable->find(1);

        $this->assertEquals($editedTitle, $post->name);
        $this->assertEquals($editedSlug, $post->slug);
    }

    public function testInsert(): void
    {
        $newTitle = 'New Title';
        $newSlug = 'new-title';
        $this->postTable->insert(['name' => $newTitle,'slug' => $newSlug]);
        $post = $this->postTable->find(1);

        $this->assertEquals($newTitle, $post->name);
        $this->assertEquals($newSlug, $post->slug);
    }

    public function testDelete(): void
    {
        $newTitle = 'New Title';
        $newSlug = 'new-title';
        $this->postTable->insert(['name' => $newTitle,'slug' => $newSlug]);

        $count = $this->postTable->getPdo()->query('SELECT COUNT(id) from posts')->fetchColumn();
        $this->assertEquals(1, $count);

        $this->postTable->delete((int) $this->postTable->getPdo()->lastInsertId());

        $count = $this->postTable->getPdo()->query('SELECT COUNT(id) from posts')->fetchColumn();
        $this->assertEquals(0, $count);
    }
}
