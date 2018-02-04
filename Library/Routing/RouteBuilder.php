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
     * @var array
     */
    private $names = [];

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
     * @param array $parameters
     */
    private function get(string $uri, string $description, array $parameters = []): void
    {
        $this->addRoute(['GET'], $uri, $description, $parameters);
    }

    /**
     * @param string $uri
     * @param string $description
     * @param array $parameters
     */
    private function post(string $uri, string $description, array $parameters = []): void
    {
        $this->addRoute(['POST'], $uri, $description, $parameters);
    }

    /**
     * @param string $uri
     * @param string $description
     * @param array $parameters
     */
    private function put(string $uri, string $description, array $parameters = []): void
    {
        $this->addRoute(['PUT'], $uri, $description, $parameters);
    }

    /**
     * @param string $uri
     * @param string $description
     * @param array $parameters
     */
    private function patch(string $uri, string $description, array $parameters = []): void
    {
        $this->addRoute(['PATCH'], $uri, $description, $parameters);
    }

    /**
     * @param string $uri
     * @param string $description
     * @param array $parameters
     */
    private function delete(string $uri, string $description, array $parameters = []): void
    {
        $this->addRoute(['DELETE'], $uri, $description, $parameters);
    }

    /**
     * @param array $methods
     * @param string $uri
     * @param string $description
     * @param array $parameters
     */
    private function many(array $methods, string $uri, string $description, array $parameters = []): void
    {
        $this->addRoute($methods, $uri, $description, $parameters);
    }

    /**
     * @param string $controller
     * @param array $actions
     * @param array $parameters
     */
    private function resource(string $controller, array $actions, array $parameters = []): void
    {
        foreach ($actions as $action)
        {
            switch ($action)
            {
                case 'fetch':
                    $this->addRoute(['GET'], '/', $controller.'@fetch', $parameters);
                    break;
                case 'show':
                    $this->addRoute(['GET'], '/{id}', $controller.'@show', $parameters);
                    break;
                case 'create':
                    $this->addRoute(['GET'], '/create', $controller.'@create', $parameters);
                    break;
                case 'store':
                    $this->addRoute(['POST'], '/', $controller.'@store', $parameters);
                    break;
                case 'edit':
                    $this->addRoute(['GET'], '/{id}/edit', $controller.'@edit', $parameters);
                    break;
                case 'update':
                    $this->addRoute(['PUT'], '/{id}', $controller.'@update', $parameters);
                    break;
                case 'destroy':
                    $this->addRoute(['DELETE'], '/{id}', $controller.'@destroy', $parameters);
                    break;
            }
        }
    }

    /**
     * @param string $description
     * @param array $parameters
     */
    private function catchAll(string $description, array $parameters = []): void
    {
        $this->catchAll = $this->addRoute(['GET'], '/', $description, $parameters);
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
        if (isset($params['as'])) { array_push($this->names, $params['as']); }

        $builderMethod($this);

        if (isset($params['namespace'])) { array_pop($this->namespaces); }
        if (isset($params['prefix'])) { array_pop($this->prefixes); }
        if (isset($params['middleware'])) { array_pop($this->middlewares); }
        if (isset($params['as'])) { array_pop($this->names); }
    }

    /**
     * @param array $methods
     * @param string $uri
     * @param string $description
     * @param array $parameters
     * @return Route
     */
    private function addRoute(array $methods, string $uri, string $description, array $parameters): Route
    {
        $uri = $this->buildPrefix().$uri;

        list($controller, $action) = explode('@', $description);
        $controller = $this->buildNamespace().$controller;

        $middlewares = $this->buildMiddlewares($parameters);

        $name = $this->buildName($parameters, $uri);

        $route = new Route($methods, $uri, $controller, $action, $middlewares, $name);
        $this->routes->add($route);
        return $route;
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
     * @param array $parameters
     * @return array
     */
    private function buildMiddlewares(array $parameters): array
    {
        $results = [];
        $middlewares = $this->middlewares;
        if (isset($parameters['middleware']))
        {
            if (is_array($parameters['middleware']))
            {
                $middlewares = array_merge($middlewares, $parameters['middleware']);
            }
            else
            {
                $middlewares[] = $parameters['middleware'];
            }
        }

        foreach ($middlewares as $middleware)
        {
            if (is_string($middleware))
            {
                $results[] = $middleware;
                continue;
            }

            if (is_array($middleware))
            {
                $results = array_merge($results, $middleware);
            }
        }

        return $results;
    }

    /**
     * @param array $parameters
     * @param string $uri
     * @return string
     */
    private function buildName(array $parameters, string $uri): string
    {
        $name = implode('.', $this->names);
        if (isset($parameters['as']))
        {
            return ($name == '' ? '' : $name.'.').$parameters['as'];
        }

        return ($name == '' ? '' : $name.'.').substr($uri, strrpos($uri, '/') + 1);
    }
}