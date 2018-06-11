<?php

namespace Library\Routing;

use Library\Container\Container;
use Library\Container\ContainerException;
use Library\Core\Paths;
use Library\Engine\Engine;
use Library\Http\Controller;
use Library\Http\Middleware;
use Library\Http\Request;
use Library\Http\Response;
use Library\Validation\ValidationBase;
use Library\Validation\ValidationResult;
use Library\Validation\Validator;
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
     * @var Engine
     */
    private $engine;

    /**
     * @var bool
     */
    private $middlewaresEnabled;

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
        $this->engine = $this->container->resolveInstance('engine');
    }

    public function enableMiddlewares(bool $value = true)
    {
        $this->middlewaresEnabled = $value;
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

        $response = $this->handleHttpRequest($routes, $request);

        if ($this->isCorsEnabled())
        {
            $this->setCorsHeaders($response);
        }

        return $response;
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
        if (!$this->isCorsEnabled())
        {
            return new Response(Response::STATUS_UNAUTHORIZED);
        }

        $response = new Response(Response::STATUS_OK);
        $this->setCorsHeaders($response);

        return $response;
    }

    private function isCorsEnabled()
    {
        return getenv(self::ALLOW_CORS_REQUESTS_KEY);
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
        if ($this->engineEnabled && $this->isEngineRoute($request))
        {
            return $this->executeEngineRoute($request);
        }

        try
        {
            $route = $this->findRoute($routes, $request);
        }
        catch (RouterException $e)
        {
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
     * @param Request $request
     * @return bool
     */
    private function isEngineRoute(Request $request): bool
    {
        return $request->uri() == $this->engine->getUri() && $request->method() == Engine::ROUTE_METHOD;
    }

    /**
     * @param Request $request
     * @return Response
     */
    private function executeEngineRoute(Request $request): Response
    {
        $result = $this->engine->processData($request->data('data'));
        return new Response($result['status'], $result['content']);
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
            $resolvedParameters = $this->container->resolveMethodParameters($controller, $action);
            $controller = $this->resolveController($controller, $request);
            return call_user_func_array([$controller, $action], $resolvedParameters);
        };
    }

    /**
     * @param Closure $closure
     * @param Request $request
     * @param array $middlewares
     * @return Response
     */
    private function executeActionClosure(Closure $closure, Request $request, array $middlewares): Response
    {
        if ($this->middlewaresEnabled)
        {
            $result = $this->executeMiddlewares($request, $middlewares);
            if ($result !== true)
            {
                return $result;
            }
        }

        return $closure();
    }

    /**
     * @param Request $request
     * @param array $middlewares
     * @return mixed
     * @throws RouterException
     */
    private function executeMiddlewares(Request $request, array $middlewares)
    {
        foreach ($middlewares as $middleware)
        {
            $result = $this->executeMiddleware($request, $middleware);
            if ($result !== true)
            {
                if (!($result instanceof Response))
                {
                    throw new RouterException('Middlewares should either return true or a response object.');
                }

                return $result;
            }
        }

        return true;
    }

    /**
     * @param Request $request
     * @param string $middleware
     * @return mixed
     */
    private function executeMiddleware(Request $request, string $middleware)
    {
        $resolvedParameters = $this->container->resolveMethodParameters($middleware, 'handle');
        $middleware = $this->resolveMiddleware($middleware, $request);
        return call_user_func_array([$middleware, 'handle'], $resolvedParameters);
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
     * @param string $middleware
     * @param Request $request
     * @return Middleware
     */
    private function resolveMiddleware(string $middleware, Request $request): Middleware
    {
        $middleware = $this->container->resolve($middleware);

        $this->container->resolveObjectProperty($middleware, 'request', $request);

        return $middleware;
    }
}