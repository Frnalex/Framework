<?php

namespace Tests\Framework;

use Framework\Validator;
use Tests\DatabaseTestCase;

class ValidatorTest extends DatabaseTestCase
{
    private function makeValidator(array $params)
    {
        return new Validator($params);
    }

    public function testRequiredIfFail()
    {
        $validator = $this->makeValidator(['name' => 'test name']);

        $errors = $validator->required('name', 'content')->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals("Le champ content est requis", (string) $errors['content']);
    }

    public function testNotEmpty()
    {
        $validator = $this->makeValidator([
            'name' => 'test name',
            'content' => ''
        ]);

        $errors = $validator->notEmpty('content')->getErrors();

        $this->assertCount(1, $errors);
    }

    public function testRequiredIfSuccess()
    {
        $validator = $this->makeValidator([
            'name' => 'test name',
            'content' => 'test content'
        ]);

        $errors = $validator->required('name', 'content')->getErrors();

        $this->assertCount(0, $errors);
    }

    public function testSlugSuccess()
    {
        $validator = $this->makeValidator([
            'slug' => 'test-slug-test',
            'slug2' => 'slug',
        ]);

        $errors = $validator->slug('slug')->getErrors();

        $this->assertCount(0, $errors);
    }

    public function testSlugError()
    {
        $validator = $this->makeValidator([
            'slug' => 'teSt-Slug-MajusCule',
            'slug2' => 'test_slug_underscore',
            'slug3' => 'test-slug--double-tiret',
            'slug4' => 'test-slug-tiret-final-',
        ]);

        $errors = $validator
            ->slug('slug')
            ->slug('slug2')
            ->slug('slug3')
            ->slug('slug4')
            ->getErrors();

        $this->assertEquals(['slug', 'slug2', 'slug3', "slug4"], array_keys($errors));
    }

    public function testLength()
    {
        $params = ['test' => '123456789' ];

        $this->assertCount(0, $this->makeValidator($params)->length('test', 3)->getErrors());
        $this->assertCount(1, $this->makeValidator($params)->length('test', 3, 4)->getErrors());
        $this->assertCount(0, $this->makeValidator($params)->length('test', 3, 20)->getErrors());
        $this->assertCount(0, $this->makeValidator($params)->length('test', null, 20)->getErrors());
        $this->assertCount(1, $this->makeValidator($params)->length('test', null, 8)->getErrors());

        $errors = $this->makeValidator($params)->length('test', 12)->getErrors();
        $this->assertCount(1, $errors);
    }

    public function testDatetime()
    {
        $this->assertCount(0, $this->makeValidator(['date' => '2012-12-12 11:12:13'])->datetime('date')->getErrors());
        $this->assertCount(0, $this->makeValidator(['date' => '2012-12-12 00:00:00'])->datetime('date')->getErrors());
        $this->assertCount(1, $this->makeValidator(['date' => '2012-12-12'])->datetime('date')->getErrors());
        $this->assertCount(1, $this->makeValidator(['date' => '2012-21-12 11:12:13'])->datetime('date')->getErrors());
        $this->assertCount(1, $this->makeValidator(['date' => '2013-02-29 11:12:13'])->datetime('date')->getErrors());
    }

    public function testExists()
    {
        $pdo = $this->getPDO();
        $pdo->exec('CREATE TABLE test(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255)   
        )');
        $pdo->exec('INSERT INTO test (name) VALUES ("value 1")');
        $pdo->exec('INSERT INTO test (name) VALUES ("value 2")');

        $this->assertTrue($this->makeValidator(['category' => 1])->exists('category', 'test', $pdo)->isValid());
        $this->assertFalse($this->makeValidator(['category' => 9999])->exists('category', 'test', $pdo)->isValid());
    }
}