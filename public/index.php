<?php

use App\Admin\AdminModule;
use App\Blog\BlogModule;
use Dotenv\Dotenv;
use Framework\App;
use Framework\Middleware\CsrfMiddleware;
use Framework\Middleware\DispacherMiddleware;
use Framework\Middleware\MethodMiddleware;
use Framework\Middleware\NotFoundMiddleware;
use Framework\Middleware\RouterMiddleware;
use Framework\Middleware\TrailingSlashMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Middlewares\Whoops;

use function Http\Response\send;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(getcwd());
$dotenv->load();

$modules = [
    AdminModule::class,
    BlogModule::class,
];

$app = (new App('config/config.php'))
    ->addModule(AdminModule::class)
    ->addModule(BlogModule::class)
    ->pipe(Whoops::class)
    ->pipe(TrailingSlashMiddleware::class)
    ->pipe(MethodMiddleware::class)
    ->pipe(CsrfMiddleware::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispacherMiddleware::class)
    ->pipe(NotFoundMiddleware::class)
;

if (php_sapi_name() !== 'cli') {
    $response = $app->run(ServerRequest::fromGlobals());
    send($response);
}
