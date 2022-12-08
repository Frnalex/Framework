<?php

namespace App\Blog;

use App\Admin\AdminWidgetInterface;
use App\Blog\Table\PostTable;
use Framework\Renderer\RendererInterface;

class BlogWidget implements AdminWidgetInterface
{
    public function __construct(
        private RendererInterface $renderer,
        private PostTable $postTable
    ) {
    }

    public function render(): string
    {
        $count = $this->postTable->count();
        return $this->renderer->render('@blog/admin/widget', ['count' => $count]);
    }

    public function renderMenu(): string
    {
        return $this->renderer->render('@blog/admin/menu');
    }
}
