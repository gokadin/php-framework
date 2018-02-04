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
     * @var array
     */
    private $catchAllRoutes = [];

    /**
     * @var array
     */
    private $catchAllRoutesByMethod = [];

    /**
     * @var array
     */
    private $catchAllRoutesByName = [];

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->allRoutes;
    }

    /**
     * @return array
     */
    public function catchAllToArray(): array
    {
        return $this->catchAllRoutes;
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
     * @param Route $route
     */
    public function addCatchAll(Route $route): void
    {
        $this->catchAllRoutes[] = $route;
        $this->catchAllRoutesByName[$route->name()] = $route;

        foreach ($route->methods() as $method)
        {
            $this->catchAllRoutesByMethod[$method][] = $route;
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
     * @param string $name
     * @return Route
     * @throws RouterException
     */
    public function getCatchAll(string $name): Route
    {
        if (isset($this->catchAllRoutesByName[$name]))
        {
            return $this->catchAllRoutesByName[$name];
        }

        throw new RouterException('Catch all route with name '.$name.' not found.');
    }

    /**
     * @param string $name
     * @return bool
     */
    public function catchAllExists(string $name): bool
    {
        return isset($this->catchAllRoutesByName[$name]);
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

    public function matchCatchAll(Request $request): Route
    {
        foreach ($this->catchAllRoutesByMethod[$request->method()] as $route)
        {
            if ($route->matchesCatchAll($request))
            {
                return $route;
            }
        }

        throw new RouterException('Catch all route for uri '.$request->uri().' and method '.$request->method().' not found.');
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->allRoutes);
    }

    /**
     * @return int
     */
    public function catchAllCount(): int
    {
        return count($this->catchAllRoutes);
    }
}