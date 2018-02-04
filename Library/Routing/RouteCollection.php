<?php

namespace Library\Routing;

use Library\Http\Request;
use Countable;

class RouteCollection implements Countable
{
    /**
     * @var array
     */
    private $allRoutes = [];

    /**
     * @var array
     */
    private $routesByMethod = [];

    /**
     * @var array
     */
    private $routesByName = [];

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->allRoutes;
    }

    /**
     * @param Route $route
     */
    public function add(Route $route): void
    {
        $this->allRoutes[] = $route;
        $this->routesByName[$route->name()] = $route;

        foreach ($route->methods() as $method)
        {
            $this->routesByMethod[$method][] = $route;
        }
    }

    /**
     * @param string $name
     * @return Route
     * @throws RouterException
     */
    public function get(string $name): Route
    {
        if (isset($this->routesByName[$name]))
        {
            return $this->routesByName[$name];
        }

        throw new RouterException('Route with name '.$name.' not found.');
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->routesByName[$name]);
    }

    /**
     * @param Request $request
     * @return Route
     * @throws RouterException
     */
    public function match(Request $request): Route
    {
        foreach ($this->routesByMethod[$request->method()] as $route)
        {
            if ($route->matches($request))
            {
                return $route;
            }
        }

        throw new RouterException('Route for uri '.$request->uri().' and method '.$request->method().' not found.');
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->allRoutes);
    }
}