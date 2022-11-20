<?php

namespace Tests\Framework;

use Framework\Renderer;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    private Renderer $renderer;

    public function setUp(): void
    {
        $this->renderer = new Renderer();
        $this->renderer->addPath(__DIR__ . '/views');
    }

    public function testRenderTheRightPath()
    {
        $this->renderer->addPath('blog', __DIR__ . '/views');
        $content = $this->renderer->render('@blog/test');
        $this->assertEquals('Texte de test', $content);
    }

    public function testRenderTheDefaultPath()
    {
        $content = $this->renderer->render('test');
        $this->assertEquals('Texte de test', $content);
    }

    public function testRenderWithParams()
    {
        $content = $this->renderer->render('testparams', ["param" => "World"]);
        $this->assertEquals('Hello World', $content);
    }

    public function testGlobalParameters()
    {
        $this->renderer->addGlobal("param", "World");
        $content = $this->renderer->render('testparams');
        $this->assertEquals('Hello World', $content);
    }
}
