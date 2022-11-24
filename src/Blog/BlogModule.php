<?php

namespace App\Blog;

use App\Blog\Actions\AdminBlogAction;
use App\Blog\Actions\BlogAction;
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
        $router->get($blogPrefix, BlogAction::class, 'blog.index');
        $router->get("{$blogPrefix}/[*:slug]-[i:id]", BlogAction::class, 'blog.show');

        if ($container->has('admin.prefix')) {
            $adminPrefix = $container->get('admin.prefix');
            $router->crud("{$adminPrefix}/posts", AdminBlogAction::class, 'blog.admin');
        }
    }
}
