<?php

namespace Framework\Twig;

use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Des extensions concernant les dates
 */
class TimeExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('ago', [$this, 'ago'], ['is_safe' => ['html']]),
        ];
    }

    public function ago(DateTime $date, string $format = 'd/m/Y H:i'): string
    {
        return "<span class='timeago' datetime='{$date->format(DateTime::ISO8601)}'>{$date->format($format)}</span>";
    }
}
