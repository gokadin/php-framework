<?php

namespace Library\Routing;

use Library\Http\Request;
use Exception;

class Route
{
    /**
     * @var array
     */
    private $methods;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Route constructor.
     *
     * @param array $methods
     * @param string $uri
     * @param string $controller
     * @param string $action
     * @param array $middlewares
     */
    public function __construct(array $methods, string $uri, string $controller, string $action, array $middlewares)
    {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->controller = $controller;
        $this->action = $action;
        $this->middlewares = $middlewares;
        $this->parameters = [];
    }

    /**
     * @return array
     */
    public function methods(): array
    {
        return $this->methods;
    }

    /**
     * @param string $method
     * @return bool
     */
    public function hasMethod(string $method): bool
    {
        return in_array($method, $this->methods);
    }

    /**
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function controller(): string
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function action(): string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @return array
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function matches(Request $request): bool
    {
        if (!in_array($request->method(), $this->methods))
        {
            return false;
        }

        $pattern = '({[a-zA-Z0-9]+})';
        $substituteUrl = preg_replace($pattern, '([^\/]*)', $this->uri, -1, $parameterCount);

        if (preg_match('`^'.strtolower($substituteUrl).'(\?.*)?$`', strtolower($request->uri()), $valueMatches) != 1)
        {
            return false;
        }

        $pattern = '/{([a-zA-Z0-9]+)}/';
        preg_match_all($pattern, $this->uri, $varMatches);

        if (sizeof($varMatches[1]) != $parameterCount) {
            throw new Exception('Route arguments do not match.');
        }

        for ($i = 0; $i < $parameterCount; $i++)
        {
            $this->parameters[$varMatches[1][$i]] = $valueMatches[$i + 1];
        }

        $this->populateGetArray();

        return true;
    }

    /**
     * Populates the $_GET array from its parameters
     */
    private function populateGetArray(): void
    {
        $_GET = array_merge($_GET, $this->parameters);
    }
}