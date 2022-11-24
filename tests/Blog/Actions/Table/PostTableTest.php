<?php

namespace App\Blog\Table;

use App\Blog\Entity\Post;
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

    public function testUpdate()
    {
        $this->seedDatabase();

        $editedTitle = 'Edited Title';
        $editedSlug = 'edited-title';
        $this->postTable->update(1, ['name' => $editedTitle,'slug' => $editedSlug]);
        $post = $this->postTable->find(1);

        $this->assertEquals($editedTitle, $post->name);
        $this->assertEquals($editedSlug, $post->slug);
    }

    public function testInsert()
    {
        $newTitle = 'New Title';
        $newSlug = 'new-title';
        $this->postTable->insert(['name' => $newTitle,'slug' => $newSlug]);
        $post = $this->postTable->find(1);

        $this->assertEquals($newTitle, $post->name);
        $this->assertEquals($newSlug, $post->slug);
    }

    public function testDelete()
    {
        $newTitle = 'New Title';
        $newSlug = 'new-title';
        $this->postTable->insert(['name' => $newTitle,'slug' => $newSlug]);

        $count = $this->pdo->query('SELECT COUNT(id) from posts')->fetchColumn();
        $this->assertEquals(1, $count);

        $this->postTable->delete($this->pdo->lastInsertId());

        $count = $this->pdo->query('SELECT COUNT(id) from posts')->fetchColumn();
        $this->assertEquals(0, $count);
    }
}
