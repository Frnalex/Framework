<?php

namespace App\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminTwigExtension extends AbstractExtension
{
    public function __construct(private array $widgets)
    {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('admin_menu', [$this, 'renderMenu'], ['is_safe' => ['html']]),
        ];
    }

    public function renderMenu(): string
    {
        return array_reduce(
            $this->widgets,
            fn (string $html, AdminWidgetInterface $widget) => $html . $widget->renderMenu(),
            ''
        );
    }
}
