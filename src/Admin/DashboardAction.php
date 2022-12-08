<?php

namespace App\Admin;

use Framework\Renderer\RendererInterface;

class DashboardAction
{
    /**
     * @param RendererInterface $renderer
     * @param AdminWidgetInterface[] $widgets
     */
    public function __construct(
        private RendererInterface $renderer,
        private array $widgets
    ) {
    }

    public function __invoke(): string
    {
        $widgets = array_reduce(
            $this->widgets,
            fn (string $html, AdminWidgetInterface $widget) => $html . $widget->render(),
            ''
        );
        return $this->renderer->render('@admin/dashboard', ['widgets' => $widgets]);
    }
}
