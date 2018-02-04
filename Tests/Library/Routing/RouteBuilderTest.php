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
        $this->assertTrue($routes->exists('simple-get'));
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
        $route = $routes->get('simple-get');
        $this->assertEquals(['GET'], $route->methods());
        $this->assertEquals('/simple-get', $route->uri());
        $this->assertEquals('controllerA', $route->controller());
        $this->assertEquals('actionA', $route->action());
        $this->assertEquals(0, sizeof($route->middlewares()));
    }

    public function test_getRoutes_buildsAPostRouteCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('simple-post');
        $this->assertEquals(['POST'], $route->methods());
    }

    public function test_getRoutes_buildsAPutRouteCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('simple-put');
        $this->assertEquals(['PUT'], $route->methods());
    }

    public function test_getRoutes_buildsAPatchRouteCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('simple-patch');
        $this->assertEquals(['PATCH'], $route->methods());
    }

    public function test_getRoutes_buildsADeleteRouteCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('simple-delete');
        $this->assertEquals(['DELETE'], $route->methods());
    }

    public function test_getRoutes_buildsAManyRouteCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('simple-many');
        $this->assertEquals(['GET', 'DELETE'], $route->methods());
    }

    public function test_getRoutes_buildsGroupGetCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('groupA.group-get');
        $this->assertEquals(['GET'], $route->methods());
        $this->assertEquals('prefixA/group-get', $route->uri());
        $this->assertEquals('controllerA', $route->controller());
        $this->assertEquals('actionA', $route->action());
        $this->assertEquals(1, sizeof($route->middlewares()));
        $this->assertEquals('m1', $route->middlewares()[0]);
    }

    public function test_getRoutes_buildsMultiGroupGetCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('groupA.groupB.name1');
        $this->assertNotNull($route);
        $this->assertEquals(['GET'], $route->methods());
        $this->assertEquals('controllerA', $route->controller());
        $this->assertEquals('actionA', $route->action());
    }

    public function test_getRoutes_buildsGroupGetMiddlewaresCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('groupA.group-get-with-middleware');
        $this->assertEquals(2, sizeof($route->middlewares()));
        $this->assertEquals('m1', $route->middlewares()[0]);
        $this->assertEquals('m2', $route->middlewares()[1]);
    }

    public function test_getRoutes_multipleMiddlewareOrderIsCorrect()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->get('groupA.groupB.middlewareOrder');
        $this->assertEquals(5, sizeof($route->middlewares()));
        $this->assertEquals('m1', $route->middlewares()[0]);
        $this->assertEquals('m2', $route->middlewares()[1]);
        $this->assertEquals('m3', $route->middlewares()[2]);
        $this->assertEquals('m4', $route->middlewares()[3]);
        $this->assertEquals('m5', $route->middlewares()[4]);
    }

    public function test_getRoutes_buildCatchAllCorrectly()
    {
        // Act
        $routes = $this->routebuilder->getRoutes();

        // Assert
        $route = $routes->getCatchAll('catchAll');
        $this->assertNotNull($route);
        $this->assertEquals(['GET', 'POST'], $route->methods());
        $this->assertEquals('controllerA', $route->controller());
        $this->assertEquals('actionA', $route->action());
        $this->assertEquals(1, sizeof($route->middlewares()));
        $this->assertEquals('m1', $route->middlewares()[0]);
    }
}