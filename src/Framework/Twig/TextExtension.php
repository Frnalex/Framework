<?php

namespace Framework\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Des extensions concernant les textes
 */
class TextExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt']),
        ];
    }

    /**
     * Créé un extrait du contenu et le retourne
     *
     * @param string $content
     * @param int $maxLength
     *
     * @return string
     */
    public function excerpt(?string $content, int $maxLength = 100): string
    {
        if (is_null($content)) {
            return '';
        }
        if (mb_strlen($content) < $maxLength) {
            return $content;
        };

        $excertp = mb_substr($content, 0, $maxLength);
        $lastSpacePosition = mb_strrpos($excertp, ' ');
        return $excertp = mb_substr($excertp, 0, $lastSpacePosition) . '...';
    }
}
