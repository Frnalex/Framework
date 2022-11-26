<?php

namespace Framework\Twig;

use Framework\Session\FlashService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashExtension extends AbstractExtension
{
    public function __construct(
        private FlashService $flashService
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('flash', [$this, 'getFlash']),
        ];
    }

    public function getFlash(string $type): ?string
    {
        return $this->flashService->get($type);
    }
}
