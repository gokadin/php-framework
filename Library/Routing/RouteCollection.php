<?php

namespace Library\Routing;

use Library\Http\Request;

class RouteCollection implements \Countable
{
    protected $allRoutes;
    protected $routesByMethod;

    public function __construct()
    {
        $this->allRoutes = array();
        $this->routesByMethod = array();
    }

    public function toArray()
    {
        return $this->allRoutes;
    }

    public function add(Route $route)
    {
        $this->allRoutes[] = $route;

        foreach ($route->methods() as $method)
        {
            $this->routesByMethod[$method][] = $route;
        }
    }

    public function match(Request $request)
    {
        $method = $request->method();

        foreach ($this->routesByMethod[$method] as $route)
        {
            if ($route->matches($request))
            {
                return $route;
            }
        }

        throw new RouteNotFoundException('Route for uri '.$request->uri().' and method '.$method.' not found.');
    }

    public function count()
    {
        return count($this->allRoutes);
    }
}