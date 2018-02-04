<?php

namespace Library\Routing;

class RouteBuilder
{
    private $file;
    protected $namespaces = [];
    protected $prefixes = [];
    protected $middlewares = [];
    protected $names = [];
    private $catchAll;

    public function __construct($customFile = null)
    {
        $this->routes = new RouteCollection();

        if (!is_null($customFile))
        {
            $this->file = $customFile;
        }
        else
        {
            $this->file = __DIR__.'/../../App/Http/routes.php';
        }
    }

    public function getRoutes()
    {
        $this->group(['namespace' => 'App\\Http\\Controllers'], function() {
            require $this->file;
        });

        return $this->routes;
    }

    private function get($uri, $action)
    {
        $this->addRoute(['GET'], $uri, $action);
    }

    private function post($uri, $action)
    {
        $this->addRoute(['POST'], $uri, $action);
    }

    private function put($uri, $action)
    {
        $this->addRoute(['PUT'], $uri, $action);
    }

    private function patch($uri, $action)
    {
        $this->addRoute(['PATCH'], $uri, $action);
    }

    private function delete($uri, $action)
    {
        $this->addRoute(['DELETE'], $uri, $action);
    }

    private function many($methods, $uri, $action)
    {
        $this->addRoute($methods, $uri, $action);
    }

    private function resource($controller, $actions)
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

    private function catchAll($action)
    {
        $this->catchAll = $this->addRoute(['GET'], '/', $action);
    }

    private function group($params, $action)
    {
        if (isset($params['namespace'])) { array_push($this->namespaces, $params['namespace']); }
        if (isset($params['prefix'])) { array_push($this->prefixes, $params['prefix']); }
        if (isset($params['middleware'])) { array_push($this->middlewares, $params['middleware']); }
        if (isset($params['as'])) { array_push($this->names, $params['as']); }

        $action($this);

        if (isset($params['namespace'])) { array_pop($this->namespaces); }
        if (isset($params['prefix'])) { array_pop($this->prefixes); }
        if (isset($params['middleware'])) { array_pop($this->middlewares); }
        if (isset($params['as'])) { array_pop($this->names); }
    }

    protected function addRoute($methods, $uri, $action)
    {
        if (sizeof($this->namespaces) > 0)
        {
            $namespaceString = '';
            for ($i = 0; $i < sizeof($this->namespaces); $i++)
            {
                $namespaceString .= $this->namespaces[$i].'\\';
            }

            if (is_string($action))
            {
                $action = $namespaceString.$action;
            }
            else if (is_array($action) && isset($action['uses']))
            {
                $action['uses'] = $namespaceString.$action['uses'];
            }
        }

        if (sizeof($this->prefixes) > 0)
        {
            $prefixString = '';
            for ($i = 0; $i < sizeof($this->prefixes); $i++)
            {
                $prefixString .= $this->prefixes[$i];
            }

            $uri = $prefixString.$uri;
        }

        $middlewares = array();
        if (sizeof($this->middlewares) > 0)
        {
            foreach ($this->middlewares as $middleware)
            {
                if (!is_array($middleware))
                {
                    $middlewares[] = $middleware;
                    continue;
                }

                foreach ($middleware as $m)
                {
                    $middlewares[] = $m;
                }
            }
        }

        $name = null;
        $namePrefix = '';
        if (sizeof($this->names) > 0)
        {
            $namePrefix = implode('.', $this->names).'.';
        }

        if (!is_callable($action))
        {
            if (is_array($action))
            {
                if (isset($action['as']))
                {
                    $name = $namePrefix.$action['as'];
                }
                else
                {
                    $name = $this->generateRouteNameFromController($action['uses'], $namePrefix);
                }
            }
            else
            {
                $name = $this->generateRouteNameFromController($action, $namePrefix);
            }
        }

        $route = new Route($methods, $uri, $action, $name, $middlewares);
        $this->routes->add($route);

        return $route;
    }

    protected function generateRouteNameFromController($controllerAndAction, $prefix)
    {
        $name = explode('@', $controllerAndAction)[1];

        if ($prefix != '' || sizeof($this->namespaces) == 0)
        {
            return $prefix.$name;
        }

        foreach ($this->namespaces as $namespace)
        {
            if ($namespace == 'App\\Http\\Controllers')
            {
                continue;
            }

            $ns = explode('\\', $namespace);
            foreach ($ns as $n)
            {
                $prefix .= lcfirst($n).'.';
            }
        }

        return $prefix.$name;
    }
}