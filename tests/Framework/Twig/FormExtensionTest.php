<?php

namespace Tests\Framework\Twig;

use Framework\Twig\FormExtension;
use PHPUnit\Framework\TestCase;

class FormExtensionTest extends TestCase
{
    private FormExtension $formExtension;

    public function setUp(): void
    {
        $this->formExtension = new FormExtension();
    }

    public function testField(): void
    {
        $html = $this->formExtension->field([], 'name', 'test', 'Titre test');
        $this->assertSimilar('
            <div class="mb-3">
                <label class="form-label" for="name">Titre test</label>
                <input name="name" id="name" type="text" value="test">
            </div>
        ', $html);
    }

    public function testFieldWithClass(): void
    {
        $html = $this->formExtension->field(
            [],
            'name',
            'test',
            'Titre test',
            ['class' => 'test']
        );
        $this->assertSimilar('
            <div class="mb-3">
                <label class="form-label" for="name">Titre test</label>
                <input class="test" name="name" id="name" type="text" value="test">
            </div>
        ', $html);
    }

    public function testTextarea(): void
    {
        $html = $this->formExtension->field([], 'name', 'test', 'Titre test', ['type' => 'textarea']);
        $this->assertSimilar('
            <div class="mb-3">
                <label class="form-label" for="name">Titre test</label>
                <textarea name="name" id="name">test</textarea>
            </div>
        ', $html);
    }

    public function testFieldWithErrors(): void
    {
        $context = ["errors" => ['name' => 'erreur']];
        $html = $this->formExtension->field($context, 'name', 'test', 'Titre test');
        $this->assertSimilar('
            <div class="mb-3 has-danger">
                <label class="form-label" for="name">Titre test</label>
                <input class="is-invalid" name="name" id="name" type="text" value="test">
                <div class="invalid-feedback">erreur</div>
            </div>
        ', $html);
    }

    public function testSelect(): void
    {
        $html = $this->formExtension->field(
            [],
            'name',
            2,
            'Label',
            [
                'options' => [1 => 'Test', 2 => 'Test2'],
                "class" => "form-select"
            ]
        );

        $this->assertSimilar('
            <div class="mb-3">
                <label class="form-label" for="name">Label</label>
                <select class="form-select" name="name" id="name">
                    <option value="1">Test</option>
                    <option value="2" selected>Test2</option>
                </select>
            </div>
        ', $html);
    }

    private function trim(string $string): string
    {
        $lines = explode("\n", $string);
        $lines = array_map('trim', $lines);
        return implode('', $lines);
    }

    private function assertSimilar(string $expected, string $actual): void
    {
        $this->assertEquals($this->trim($expected), $this->trim($actual));
    }
}
