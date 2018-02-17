<?php

namespace Library\Routing;

use Library\Container\Container;
use Library\Engine\EngineRequestExecutor;
use Library\Http\Controller;
use Library\Http\Request;
use Library\Http\Response;
use Library\Validation\ValidationBase;
use Library\Validation\ValidationResult;
use Library\Validation\Validator;
use Symfony\Component\Yaml\Exception\RuntimeException;
use ReflectionMethod;
use Closure;

class Router
{
    private const ALLOW_CORS_REQUESTS_KEY = 'ALLOW_CORS_REQUESTS';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var bool
     */
    private $shouldValidate;

    /**
     * @var bool
     */
    private $engineEnabled;

    /**
     * Router constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param bool $value
     */
    public function enableValidation(bool $value = true)
    {
        $this->shouldValidate = $value;
    }

    /**
     * @param bool $value
     */
    public function enableEngine(bool $value = true)
    {
        $this->engineEnabled = $value;
    }

    /**
     * @param RouteCollection $routes
     * @param Request $request
     * @return Response
     */
    public function dispatch(RouteCollection $routes, Request $request): Response
    {
        if ($this->isCorsRequest($request))
        {
            return $this->handleCorsRequest();
        }

        return $this->handleHttpRequest($routes, $request);
    }

    private function isCorsRequest(Request $request)
    {
        return $request->method() == 'OPTIONS';
    }

    /**
     * @return Response
     */
    private function handleCorsRequest(): Response
    {
        if (!getenv(self::ALLOW_CORS_REQUESTS_KEY))
        {
            return new Response(Response::STATUS_UNAUTHORIZED);
        }

        $response = new Response(Response::STATUS_OK);
        $this->setCorsHeaders($response);

        return $response;
    }

    /**
     * @param Response $response
     */
    private function setCorsHeaders(Response $response): void
    {
        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Access-Control-Allow-Credentials', true);
        $response->addHeader('Access-Control-Max-Age', 86400);
        $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
        $response->addHeader('Access-Control-Allow-Headers', "{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    /**
     * @param RouteCollection $routes
     * @param Request $request
     * @return Response
     */
    private function handleHttpRequest(RouteCollection $routes, Request $request): Response
    {
        try
        {
            $route = $this->findRoute($routes, $request);
        }
        catch (RouterException $e)
        {
            if ($this->engineEnabled)
            {
                return $this->container->resolve(EngineRequestExecutor::class)->execute($request->data('data'));
            }

            return new Response(Response::STATUS_NOT_FOUND, 'Route not found.');
        }

        return $this->executeRouteAction($route, $request);
    }

    /**
     * Matches a route with the request.
     *
     * @param RouteCollection $routes
     * @param Request $request
     * @return Route
     */
    private function findRoute(RouteCollection $routes, Request $request): Route
    {
        try
        {
            return $routes->match($request);
        }
        catch (RouterException $e)
        {
            return $routes->matchCatchAll($request);
        }
    }

    /**
     * @param Route $route
     * @param Request $request
     * @return Response
     */
    private function executeRouteAction(Route $route, Request $request): Response
    {
        if ($this->shouldValidate)
        {
            $result = $this->executeValidation($route->controller(), $route->action(), $request);
            if (!$result)
            {
                return new Response(Response::STATUS_BAD_REQUEST);
            }
            else if ($result instanceof ValidationResult && !$result->isValid())
            {
                return new Response(Response::STATUS_BAD_REQUEST, $result->errors());
            }
        }

        $actionClosure = $this->getControllerClosure($route->controller(), $route->action(), $route->parameters(), $request);

        return $this->executeActionClosure($actionClosure, $request, $route->middlewares());
    }

    private function executeValidation(string $controller, string $action, Request $request)
    {
        $validation = str_replace('Http\\Controllers', 'Http\\Validations', $controller);
        $validation = substr($validation, 0, strlen($validation) - 10).'Validation';
        if (!class_exists($validation))
        {
            throw new RouterException('Could not find validation class.');
        }

        $validation = $this->resolveValidation($validation, $request);
        $resolvedParameters = $this->container->resolveMethodParameters($validation, $action);
        $result = call_user_func_array([$validation, $action], $resolvedParameters);
        return $result;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param array $routeParameters
     * @param Request $request
     * @return Closure
     */
    private function getControllerClosure(string $controller, string $action, array $routeParameters, Request $request): Closure
    {
        return function() use ($controller, $action, $routeParameters, $request) {
            $resolvedParameters = $this->getResolvedParameters($controller, $action, $routeParameters);
            $controller = $this->resolveController($controller, $request);
            return call_user_func_array([$controller, $action], $resolvedParameters);
        };
    }

    /**
     * @param string $controller
     * @param Request $request
     * @return Controller
     */
    private function resolveController(string $controller, Request $request): Controller
    {
        $controller = $this->container->resolve($controller);

        $this->container->resolveObjectProperty($controller, 'request', $request);
        $this->container->resolveObjectProperty($controller, 'validator', new Validator());

        return $controller;
    }

    /**
     * @param string $validation
     * @param Request $request
     * @return ValidationBase
     */
    private function resolveValidation(string $validation, Request $request): ValidationBase
    {
        $validation = $this->container->resolve($validation);

        $this->container->resolveObjectProperty($validation, 'request', $request);
        $this->container->resolveObjectProperty($validation, 'validator', new Validator());

        return $validation;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param array $routeParameters
     * @return array
     */
    private function getResolvedParameters(string $controller, string $action, array $routeParameters): array
    {
        $resolvedParameters = [];
        $r = new ReflectionMethod($controller, $action);

        foreach ($r->getParameters() as $parameter)
        {
            $class = $parameter->getClass();
            if (!is_null($class))
            {
                $resolvedParameters[] = $this->container->resolve($class->getName());
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

            throw new RuntimeException('Could not resolve parameter '.$parameter->getName().' for route method '.$action);
        }

        return $resolvedParameters;
    }

    /**
     * @param Closure $closure
     * @param Request $request
     * @param array $middlewares
     * @return Response
     */
    private function executeActionClosure(Closure $closure, Request $request, array $middlewares): Response
    {
        if (sizeof($middlewares) == 0)
        {
            return $closure();
        }

        $closure = $this->getActionClosureWithMiddlewares($closure, $request, sizeof($middlewares) - 1);

        return $closure();
    }

    protected function getActionClosureWithMiddlewares(Closure $closure, Request $request, $index)
    {
        $middlewareName = '\\App\\Http\\Middleware\\'.$this->currentRoute->middlewares()[$index];
        $middleware = $this->container->resolve($middlewareName);

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
}