<?php

namespace Tests\LibraryRouting;

use Library\Container\Container;
use Library\Http\Request;
use Library\Http\Response;
use Library\Routing\Route;
use Library\Routing\RouteCollection;
use Library\Routing\Router;
use Library\Validation\Validator;
use Tests\BaseTest;
use Tests\TestData\Router\ResolvableOne;

class RouterTest extends BaseTest
{
    private const TEST_CONTROLLERS_ROOT_NAMESPACE = 'Tests\\App\\Http\\Controllers\\';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();
        $this->router = new Router($this->container, new Validator());
    }

    public function test_dispatch_handlesCorsRequestCorrectlyWhenCorsIsDeactivated()
    {
        // Arrange
        $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] = 'x';
        $routes = new RouteCollection();
        $routes->add(new Route(['OPTIONS'], '/', 'controllerA', 'actionA', [], 'name1'));
        $request = new Request('OPTIONS', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertNotEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_httpRequestWhenRouteIsNotFound()
    {
        // Arrange
        $routes = new RouteCollection();
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_NOT_FOUND, $response->statusCode());
    }

    public function test_dispatch_simplestHttpRequest()
    {
        // Arrange
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE.'TestController', 'simpleAction', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_simplestHttpRequestWithRequestParameter()
    {
        // Arrange
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'simpleActionWithRequest', [], 'name1'));
        $request = new Request('GET', '/', ['a' => 'b'], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
        $this->assertEquals('b', $response->data()['a']);
    }

    public function test_dispatch_controllerActionParametersAreResolved()
    {
        // Arrange
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'actionParameters', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
        $this->assertTrue($response->data()['resolvableOne'] instanceof ResolvableOne);
    }

    public function test_dispatch_controllerCtorParametersAreResolved()
    {
        // Arrange
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'actionParameters', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
        $this->assertTrue($response->data()['resolvableOne'] instanceof ResolvableOne);
    }
}