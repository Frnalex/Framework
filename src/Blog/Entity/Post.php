<?php

namespace App\Blog\Entity;

use DateTime;

class Post
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $content;
    public string|DateTime|null $created_at = '';
    public string|DateTime|null $updated_at = '';
    public string $category_name;

    public function __construct()
    {
        if ($this->created_at && is_string($this->created_at)) {
            $this->created_at = new DateTime($this->created_at);
        }
        if ($this->updated_at && is_string($this->updated_at)) {
            $this->updated_at = new DateTime($this->updated_at);
        }
    }
}
