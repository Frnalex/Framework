<?php

namespace Framework;

class Renderer
{
    public const DEFAULT_NAMESPACE = '__MAIN';

    private array $paths = [];

    /**
     * Variables accessibles globalement dans toutes les vues
     *
     * @var array
     */
    private array $globals = [];

    /**
     * Permet de rajouter un chemin pour charger les vues
     *
     * @param string $namespace
     * @param string|null $path
     *
     * @return void
     */
    public function addPath(string $namespace, ?string $path = null): void
    {
        if (is_null($path)) {
            $this->paths[self::DEFAULT_NAMESPACE] = $namespace;
        } else {
            $this->paths[$namespace] = $path;
        }
    }

    /**
     * Permet de rendre une vue
     * Le chemin peut-être précisé avec des namespaces rajoutés avec la méthode addPath()
     * $this->render('@blog/view');
     * $this->render('view');
     *
     * @param string $view
     * @param array $params
     *
     * @return string
     */
    public function render(string $view, array $params = []): string
    {
        if ($this->hasNamespace($view)) {
            $path = $this->replaceNamespace($view) . '.php';
        } else {
            $path = $this->paths[self::DEFAULT_NAMESPACE] . DIRECTORY_SEPARATOR . $view . '.php';
        }
        ob_start();
        $renderer = $this;
        extract($this->globals);
        extract($params);
        require($path);
        return ob_get_clean();
    }

    /**
     * Permet de rajouter des variables globales à toutes les vues
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function addGlobal(string $key, mixed $value): void
    {
        $this->globals[$key] = $value;
    }

    private function hasNamespace(string $view): bool
    {
        return $view[0] === '@';
    }

    private function getNamespace(string $view): string
    {
        return substr($view, 1, strpos($view, '/') - 1);
    }

    private function replaceNamespace(string $view): string
    {
        $namespace = $this->getNamespace($view);
        return str_replace('@' . $namespace, $this->paths[$namespace], $view);
    }
}
