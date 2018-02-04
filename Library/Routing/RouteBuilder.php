<?php

namespace Library\Routing;

class RouteBuilder
{
    /**
     * @var string
     */
    private $routesFile;

    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var array
     */
    private $namespaces = [];

    /**
     * @var array
     */
    private $prefixes = [];

    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @var Route
     */
    private $catchAll;

    /**
     * RouteBuilder constructor.
     *
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->routesFile = $basePath.'/App/Http/routes.php';

        $this->routes = new RouteCollection();
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection
    {
        $this->group(['namespace' => 'App\\Http\\Controllers'], function() {
            require $this->routesFile;
        });

        return $this->routes;
    }

    /**
     * @param string $uri
     * @param string $description
     */
    private function get(string $uri, string $description): void
    {
        $this->addRoute(['GET'], $uri, $description);
    }

    /**
     * @param string $uri
     * @param string $description
     */
    private function post(string $uri, string $description): void
    {
        $this->addRoute(['POST'], $uri, $description);
    }

    /**
     * @param string $uri
     * @param string $description
     */
    private function put(string $uri, string $description): void
    {
        $this->addRoute(['PUT'], $uri, $description);
    }

    /**
     * @param string $uri
     * @param string $description
     */
    private function patch(string $uri, string $description): void
    {
        $this->addRoute(['PATCH'], $uri, $description);
    }

    /**
     * @param string $uri
     * @param string $description
     */
    private function delete(string $uri, string $description): void
    {
        $this->addRoute(['DELETE'], $uri, $description);
    }

    /**
     * @param array $methods
     * @param string $uri
     * @param string $description
     */
    private function many(array $methods, string $uri, string $description): void
    {
        $this->addRoute($methods, $uri, $description);
    }

    /**
     * @param string $controller
     * @param array $actions
     */
    private function resource(string $controller, array $actions): void
    {
        foreach ($actions as $action)
        {
            switch ($action)
            {
                case 'fetch':
                    $this->addRoute(['GET'], '/', $controller.'@fetch');
                    break;
                case 'show':
                    $this->addRoute(['GET'], '/{id}', $controller.'@show');
                    break;
                case 'create':
                    $this->addRoute(['GET'], '/create', $controller.'@create');
                    break;
                case 'store':
                    $this->addRoute(['POST'], '/', $controller.'@store');
                    break;
                case 'edit':
                    $this->addRoute(['GET'], '/{id}/edit', $controller.'@edit');
                    break;
                case 'update':
                    $this->addRoute(['PUT'], '/{id}', $controller.'@update');
                    break;
                case 'destroy':
                    $this->addRoute(['DELETE'], '/{id}', $controller.'@destroy');
                    break;
            }
        }
    }

    /**
     * @param string $description
     */
    private function catchAll(string $description): void
    {
        $this->catchAll = $this->addRoute(['GET'], '/', $description);
    }

    /**
     * @param array $params
     * @param $builderMethod
     */
    private function group(array $params, $builderMethod): void
    {
        if (isset($params['namespace'])) { array_push($this->namespaces, $params['namespace']); }
        if (isset($params['prefix'])) { array_push($this->prefixes, $params['prefix']); }
        if (isset($params['middleware'])) { array_push($this->middlewares, $params['middleware']); }

        $builderMethod($this);

        if (isset($params['namespace'])) { array_pop($this->namespaces); }
        if (isset($params['prefix'])) { array_pop($this->prefixes); }
        if (isset($params['middleware'])) { array_pop($this->middlewares); }
    }

    /**
     * @param array $methods
     * @param string $uri
     * @param string $description
     */
    private function addRoute(array $methods, string $uri, string $description): void
    {
        $uri = $this->buildPrefix().$uri;

        list($controller, $action) = explode('@', $description);
        $controller = $this->buildNamespace().$controller;

        $this->routes->add(new Route($methods, $uri, $controller, $action, $this->buildMiddlewares()));
    }

    /**
     * @return string
     */
    private function buildNamespace(): string
    {
        return implode('\\', $this->namespaces).sizeof($this->namespaces) > 0 ? '\\' : '';
    }

    /**
     * @return string
     */
    private function buildPrefix(): string
    {
        return implode('', $this->prefixes);
    }

    /**
     * @return array
     */
    private function buildMiddlewares(): array
    {
        $middlewares = [];
        foreach ($this->middlewares as $middleware)
        {
            if (is_string($middleware))
            {
                $middlewares[] = $middleware;
                continue;
            }

            if (is_array($middleware))
            {
                $middlewares = array_merge($middlewares, $middleware);
            }
        }

        return $middlewares;
    }
}