<?php

namespace Tests\Framework\Validator;

use Framework\Validator\ValidationError;
use PHPUnit\Framework\TestCase;

class ValidationErrorTest extends TestCase
{
    public function testString(): void
    {
        $error = new ValidationError('test', 'fakeRule', ['attribute 1', 'attribute 2']);
        $property = (new \ReflectionClass($error))->getProperty('messages');
        $property->setAccessible(true);
        $property->setValue($error, ['fakeRule' => 'problem %2$s %3$s']);
        $this->assertEquals('problem attribute 1 attribute 2', (string)$error);
    }

    public function testUnknownError(): void
    {
        $rule = 'fakeRule';
        $field = 'test';
        $error = new ValidationError($field, $rule, ['a1', 'a2']);
        $this->assertStringContainsString($field, (string)$error);
        $this->assertStringContainsString($rule, (string)$error);
    }
}
