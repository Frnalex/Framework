<?php

namespace Tests\Framework\Database;

class FakeClass
{
    private string $slug;

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug . 'test';
    }
}
