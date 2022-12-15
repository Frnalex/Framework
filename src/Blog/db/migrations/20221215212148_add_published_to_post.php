<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPublishedToPost extends AbstractMigration
{
    public function change(): void
    {
        $this->table('posts')
            ->addColumn('published', 'boolean', ['default' => false])
            ->update();
    }
}
