<?php

namespace Library\Routing;

use Library\Container\Container;
use Library\Http\Request;
use Library\Validation\Validator;
use Symfony\Component\Yaml\Exception\RuntimeException;
use ReflectionMethod;

class Router
{
    protected $container;
    protected $validator;
    protected $routes;
    protected $currentRoute;
    protected $namespaces = [];
    protected $prefixes = [];
    protected $middlewares = [];
    protected $names = [];
    private $catchAll;

    public function __construct(Container $container, Validator $validator)
    {
        $this->container = $container;
        $this->validator = $validator;
        $this->routes = new RouteCollection();
    }

    public function get($uri, $action)
    {
        $this->addRoute(['GET'], $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute(['POST'], $uri, $action);
    }

    public function put($uri, $action)
    {
        $this->addRoute(['PUT'], $uri, $action);
    }

    public function patch($uri, $action)
    {
        $this->addRoute(['PATCH'], $uri, $action);
    }

    public function delete($uri, $action)
    {
        $this->addRoute(['DELETE'], $uri, $action);
    }

    public function many($methods, $uri, $action)
    {
        $this->addRoute($methods, $uri, $action);
    }

    public function resource($controller, $actions)
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

    public function catchAll($action)
    {
        $this->catchAll = $this->addRoute(['GET'], '/', $action);
    }

    public function group($params, $action)
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

    public function getUri($name, array $params = [])
    {
        $route = $this->routes->getNamedRoute($name);

        if (is_null($route))
        {
            return '';
        }

        if (sizeof($params) == 0)
        {
            return $route->uri();
        }

        return $this->resolveUriWithParameters($route->uri(), $params);
    }

    protected function resolveUriWithParameters($uri, $params)
    {
        return preg_replace('/({[a-zA-Z0-9]+})/', $params, $uri);
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

    public function has($name)
    {
        return $this->routes->hasNamedRoute($name);
    }

    public function current()
    {
        return $this->currentRoute;
    }

    public function currentNameContains($str)
    {
        return strpos($this->currentRoute->name(), $str) !== false;
    }

    public function dispatch(Request $request)
    {
        $this->currentRoute = $this->findRoute($request);

        return $this->executeRouteAction($request);
    }

    protected function findRoute(Request $request)
    {
        try
        {
            return $this->routes->match($request);
        }
        catch (RouteNotFoundException $e)
        {
            if (!is_null($this->catchAll) && $this->catchAll->hasMethod($request->method()))
            {
                return $this->catchAll;
            }

            $response = $this->container->resolveInstance('response');
            $response->redirect404();
            $response->executeResponse();
        }
    }

    protected function executeRouteAction(Request $request)
    {
        $action = $this->currentRoute->action();

        $actionClosure = function() { return ''; };

        if (is_string($action))
        {
            $actionClosure = $this->getControllerClosure($action);
        }

        if (is_array($action))
        {
            $actionClosure = $this->getArrayClosure($action);
        }

        if (is_callable($action))
        {
            $actionClosure = function() use ($action) {
                return call_user_func_array($action, $this->currentRoute->parameters());
            };
        }

        return $this->executeActionClosure($actionClosure, $request);
    }

    protected function executeActionClosure($closure, Request $request)
    {
        if (sizeof($this->currentRoute->middlewares()) == 0)
        {
            return $closure();
        }

        $closure = $this->getActionClosureWithMiddlewares($closure, $request, sizeof($this->currentRoute->middlewares()) - 1);

        return $closure();
    }

    protected function getActionClosureWithMiddlewares($closure, Request $request, $index)
    {
        $middlewareName = '\\App\\Http\\Middleware\\'.$this->currentRoute->middlewares()[$index];
        $middleware = $this->resolve($middlewareName);

        if ($index == 0)
        {
            return function() use ($middleware, $closure, $request) {
                return $middleware->handle($request, $closure);
            };
        }

        return $this->getActionClosureWithMiddlewares(function() use ($middleware, $closure, $request) {
            return $middleware->handle($request, $closure);
        }, $request, $index - 1);
    }

    protected function getArrayClosure($action)
    {
        if (isset($action['uses']))
        {
            return $this->getControllerClosure($action['uses']);
        }
    }

    protected function getControllerClosure($action)
    {
        return function() use ($action) {
            list($controllerName, $methodName) = explode('@', $action);
            $parameters = $this->getResolvedParameters($controllerName, $methodName, $this->currentRoute->parameters());
            $controller = $this->resolve($controllerName);
            return call_user_func_array([$controller, $methodName], $parameters);
        };
    }

    protected function getResolvedParameters($controllerName, $methodName, $routeParameters)
    {
        $resolvedParameters = [];
        $r = new ReflectionMethod($controllerName, $methodName);

        foreach ($r->getParameters() as $parameter)
        {
            $class = $parameter->getClass();
            if (!is_null($class))
            {
                $resolvedParameters[] = $this->resolve($class->getName());
                continue;
            }

            if (in_array($parameter->getName(), array_keys($routeParameters)))
            {
                $resolvedParameters[] = $routeParameters[$parameter->getName()];
                continue;
            }

            if ($parameter->isOptional())
            {
                continue;
            }

            throw new RuntimeException('Could not resolve parameter '.$parameter->getName().' for route method '.$methodName);
            return [];
        }

        return $resolvedParameters;
    }

    protected function resolve($class)
    {
        $instance = $this->container->resolve($class);

        if ($instance instanceof \App\Http\Requests\Request)
        {
            if (!$this->processRequest($instance))
            {
                $response = $this->container->resolveInstance('response');

                $instance->isJson()
                    ? $response->json($this->validator->errors(), 401)
                    : $response->back()->withErrors($this->validator->errors());

                $response->executeResponse();
            }
        }

        return $instance;
    }

    protected function processRequest($request)
    {
        if (!is_array($request->rules()))
        {
            if ($request->rules() && $request->authorize())
            {
                return true;
            }

            return false;
        }

        if (!$request->authorize() ||
            !$this->validator->make($request->all(), $request->rules()))
        {
            return false;
        }

        return true;
    }
}