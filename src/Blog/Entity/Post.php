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
    public ?string $image;
    public string $category_name;

    public function setCreatedAt(string|Datetime|null $datetime): void
    {
        if (is_string($datetime)) {
            $this->createdAt = new DateTime($datetime);
        }
    }

    public function setUpdatedAt(string|Datetime|null $datetime): void
    {
        if (is_string($datetime)) {
            $this->updatedAt = new DateTime($datetime);
        }
    }

    public function getThumb(): string
    {
        ['filename' => $filename, 'extension' => $extension] = pathinfo($this->image);
        return DIRECTORY_SEPARATOR . 'uploads' .
            DIRECTORY_SEPARATOR . 'posts' .
            DIRECTORY_SEPARATOR . $filename . '_thumb.' . $extension;
    }

    public function getImageUrl()
    {
        return '/uploads/posts/' . $this->image;
    }
}
