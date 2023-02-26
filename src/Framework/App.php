<?php

namespace Framework;

use DI\ContainerBuilder;
use Exception;
use Framework\Middleware\CombinedMiddleware;
use Framework\Middleware\RoutePrefixedMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class App implements RequestHandlerInterface
{
    /**
     * Liste des modules
     * @var array
     */
    private array $modules = [];

    private ?ContainerInterface $container = null;

    /**
     * @var string[]
     */
    private array $middlewares = [];

    private int $index = 0;

    public function __construct(
        private string|array|null $definition = null
    ) {
    }


    /**
     * Rajoute un module à l'application
     * @param string $module
     * @return self
     */
    public function addModule(string $module): self
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * Ajoute un middleware
     * @param string $routePrefix
     * @param string|null $middleware
     * @return self
     */
    public function pipe(
        string|callable|MiddlewareInterface $routePrefix,
        null|string|callable|MiddlewareInterface $middleware = null
    ): self {
        if ($middleware === null) {
            $this->middlewares[] = $routePrefix;
        } else {
            $this->middlewares[] = new RoutePrefixedMiddleware($this->getContainer(), $routePrefix, $middleware);
        }

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->index++;

        if ($this->index > 1) {
            throw new Exception();
        }
        $middleware = new CombinedMiddleware($this->getContainer(), $this->middlewares);
        return $middleware->process($request, $this);
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        foreach ($this->modules as $module) {
            $this->getContainer()->get($module);
        }

        return $this->handle($request);
    }

    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $builder = new ContainerBuilder();
            $env = $_ENV['ENV'] ?? 'prod';

            if ($env === 'prod') {
                $builder->writeProxiesToFile(true, 'tmp/proxies');
            }

            if ($this->definition) {
                $builder->addDefinitions($this->definition);
            }

            foreach ($this->modules as $module) {
                if ($module::DEFINITIONS !== null) {
                    $builder->addDefinitions($module::DEFINITIONS);
                }
            }
            $this->container = $builder->build();
        }

        return $this->container;
    }

    public function getModules(): array
    {
        return $this->modules;
    }
}
