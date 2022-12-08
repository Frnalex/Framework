<?php

namespace App\Blog;

use App\Blog\Actions\CategoryCrudAction;
use App\Blog\Actions\CategoryShowAction;
use App\Blog\Actions\PostCrudAction;
use App\Blog\Actions\PostIndexAction;
use App\Blog\Actions\PostShowAction;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Psr\Container\ContainerInterface;

class BlogModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    public function __construct(ContainerInterface $container)
    {
        $container->get(RendererInterface::class)->addPath('blog', __DIR__ . '/views');

        /** @var Router */
        $router =  $container->get(Router::class);

        $blogPrefix = $container->get('blog.prefix');
        $router->get($blogPrefix, PostIndexAction::class, 'blog.index');
        $router->get("{$blogPrefix}/category/[*:slug]", CategoryShowAction::class, 'blog.category');
        $router->get("{$blogPrefix}/[*:slug]-[i:id]", PostShowAction::class, 'blog.show');

        if ($container->has('admin.prefix')) {
            $adminPrefix = $container->get('admin.prefix');
            $router->crud("{$adminPrefix}/posts", PostCrudAction::class, 'blog.admin');
            $router->crud("{$adminPrefix}/categories", CategoryCrudAction::class, 'blog.category.admin');
        }
    }
}
