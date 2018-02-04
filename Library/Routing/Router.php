<?php

namespace Library\Routing;

use Library\Container\Container;
use Library\Http\Request;
use Library\Http\Response;
use Library\Validation\Validator;
use Symfony\Component\Yaml\Exception\RuntimeException;
use ReflectionMethod;

class Router
{
    const ENGINE_METHOD = 'POST';
    const ENGINE_URI = '/api/engine';

    protected $container;
    protected $validator;
    protected $routes;
    protected $currentRoute;

    public function __construct(Container $container, Validator $validator)
    {
        $this->container = $container;
        $this->validator = $validator;
        $this->routes = new RouteCollection();
    }

    public function setRoutes(RouteCollection $routes)
    {
        $this->routes = $routes;
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

    public function dispatch($routes, Request $request)
    {
        if ($request->method() == 'OPTIONS' && env('ALLOW_CORS_HEADERS'))
        {
            return $this->handleCorsRequest($request);
        }

        if ($request->method() == self::ENGINE_METHOD && $request->uri() == self::ENGINE_URI)
        {
            if (env('ALLOW_CORS_HEADERS'))
            {
                header('Access-Control-Allow-Origin: *');
            }

            return $this->dispatchEngineRequest($request);
        }

        $this->currentRoute = $this->findRoute($request);

        return $this->executeRouteAction($request);
    }

    private function dispatchEngineRequest(Request $request)
    {
        if (!$request->dataExists('data'))
        {
            return new Response(Response::STATUS_BAD_REQUEST, 'No data was passed to engine request.');
        }

        $engine = $this->container->resolveInstance('engine');
        $result = $engine->run($request->data('data'));

        return new Response($result['status'], $result['content']);
    }

    private function handleCorsRequest($request)
    {
        $response = new Response(Response::STATUS_OK);

        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Access-Control-Allow-Credentials', true);
        $response->addHeader('Access-Control-Max-Age', 86400);
        $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
        $response->addHeader('Access-Control-Allow-Headers', "{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        return $response;
    }

    /**
     * Matches a route with the request.
     *
     * @param Request $request
     * @return Route
     */
    private function findRoute(Request $request): Route
    {
        try
        {
            return $this->routes->match($request);
        }
        catch (RouterException $e)
        {
            try
            {
                return $this->routes->matchCatchAll($request);
            }
            catch (RouterException $e)
            {
                $response = new Response(Response::STATUS_NOT_FOUND, 'Route not found.');
                $response->executeResponse();
            }
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
                $response = new Response(Response::STATUS_NOT_FOUND, $this->validator->errors());
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