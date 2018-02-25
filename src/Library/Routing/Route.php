<?php

namespace Library\Routing;

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
     * @var string
     */
    private $name;

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
     * @param string $name
     */
    public function __construct(array $methods, string $uri, string $controller, string $action, array $middlewares, string $name)
    {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->controller = $controller;
        $this->action = $action;
        $this->middlewares = $middlewares;
        $this->name = $name;
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
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param string $method
     * @param string $uri
     * @return bool
     * @throws Exception
     */
    public function matches(string $method, string $uri): bool
    {
        if (!in_array($method, $this->methods))
        {
            return false;
        }

        $pattern = '({[a-zA-Z0-9]+})';
        $substituteUrl = preg_replace($pattern, '([^\/]*)', $this->uri, -1, $parameterCount);
        if (preg_match('`^'.strtolower($substituteUrl).'(\?.*)?$`', strtolower($uri), $valueMatches) != 1)
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
     * @param $method
     * @return bool
     */
    public function matchesCatchAll($method): bool
    {
        if (!in_array($method, $this->methods))
        {
            return false;
        }

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