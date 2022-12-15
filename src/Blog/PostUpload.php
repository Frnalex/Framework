<?php

namespace App\Blog;

use Framework\Upload;

class PostUpload extends Upload
{
    protected ?string $path = 'public/uploads/posts';
}
