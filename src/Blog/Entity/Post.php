<?php

namespace App\Blog\Entity;

use DateTime;

class Post
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $content;
    public string|DateTime|null $createdAt = '';
    public string|DateTime|null $updatedAt = '';
    public string $category_name;

    public function setCreatedAt(string|Datetime $datetime): void
    {
        if (is_string($datetime)) {
            $this->createdAt = new DateTime($datetime);
        }
    }

    public function setUpdatedAt(string|Datetime $datetime): void
    {
        if (is_string($datetime)) {
            $this->updatedAt = new DateTime($datetime);
        }
    }
}
