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

    public function test_getRoutes_buildsNameCorrectlyWhenOutsideGroup()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $this->assertTrue($routes->exists('a'));
    }

    public function test_getRoutes_buildsNameCorrectlyWhenOutsideGroupWithAsParameter()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $this->assertTrue($routes->exists('name1'));
    }

    public function test_getRoutes_buildsAGetRouteCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('a');
        $this->assertEquals(['GET'], $route->methods());
        $this->assertEquals('/a', $route->uri());
        $this->assertEquals('controllerA', $route->controller());
        $this->assertEquals('actionA', $route->action());
        $this->assertEquals(0, sizeof($route->middlewares()));
    }
}