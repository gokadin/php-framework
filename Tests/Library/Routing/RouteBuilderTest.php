<?php

namespace Tests\LibraryRouting;

use Library\Routing\RouteBuilder;
use Tests\BaseTest;

class RouteBuilderTest extends BaseTest
{
    /**
     * @var RouteBuilder
     */
    private $routebuilder;

    public function setUp()
    {
        parent::setUp();

        $this->routebuilder = new RouteBuilder($this->basePath());
    }

    public function test_getRoutes_buildsAGetRouteCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $this->assertEquals(1, $routes->count());
        $this->assertEquals(['GET'], $routes->toArray()[0]->methods());
        $this->assertEquals('/a', $routes->toArray()[0]->uri());
        $this->assertEquals('controllerA', $routes->toArray()[0]->controller());
        $this->assertEquals('actionA', $routes->toArray()[0]->action());
        $this->assertEquals(0, sizeof($routes->toArray()[0]->middlewares()));
    }
}