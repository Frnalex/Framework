<?php

namespace Tests\Framework\Database;

use Framework\Database\Table;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class TableTest extends TestCase
{
    private Table $table;

    public function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]);
        $pdo->exec('CREATE TABLE test(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255)   
        )');

        $this->table = new Table($pdo);
        $reflection = new ReflectionClass($this->table);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        $property->setValue($this->table, 'test');
    }

    public function testFind()
    {
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 1")');
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 2")');
        $test = $this->table->find(1);

        $this->assertInstanceOf(stdClass::class, $test);
        $this->assertEquals('value 1', $test->name);
    }

    public function testFindList()
    {
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 1")');
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 2")');

        $this->assertEquals([
            '1' => "value 1",
            '2' => "value 2",
        ], $this->table->findList());
    }

    public function testFindAll()
    {
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 1")');
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 2")');
        $categories = $this->table->findAll();

        $this->assertCount(2, $categories);
        $this->assertInstanceOf(stdClass::class, $categories[0]);
        $this->assertEquals("value 1", $categories[0]->name);
        $this->assertEquals("value 2", $categories[1]->name);
    }

    public function testFindBy()
    {
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 1")');
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 2")');
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 1")');
        $category = $this->table->findBy('name', 'value 1');

        $this->assertInstanceOf(stdClass::class, $category);
        $this->assertEquals(1, $category->id);
    }

    public function testExists()
    {
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 1")');
        $this->table->getPdo()->exec('INSERT INTO test (name) VALUES ("value 2")');

        $this->assertTrue($this->table->exists(1));
        $this->assertTrue($this->table->exists(2));
        $this->assertFalse($this->table->exists(9999));
    }
}
