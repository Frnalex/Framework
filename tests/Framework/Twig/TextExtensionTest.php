<?php

namespace Tests\Framework\Twig;

use Framework\Twig\TextExtension;
use PHPUnit\Framework\TestCase;

class TextExtensionTest extends TestCase
{
    private TextExtension $textExtension;

    public function setUp(): void
    {
        $this->textExtension = new TextExtension();
    }

    public function testExcerptWithShortText(): void
    {
        $text = 'Texte';
        $excerpt = $this->textExtension->excerpt($text, 10);
        $this->assertEquals($text, $excerpt);
    }

    public function testExcerptWithLongText(): void
    {
        $text = 'Texte plus long que ce qui va être demandé';
        $this->assertEquals("Texte...", $this->textExtension->excerpt($text, 7));
        $this->assertEquals("Texte plus long...", $this->textExtension->excerpt($text, 18));
    }
}
