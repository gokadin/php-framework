<?php

namespace Tests\LibraryRouting;

use Library\Container\Container;
use Library\Http\Request;
use Library\Http\Response;
use Library\IscClient\IscClient;
use Library\Routing\Route;
use Library\Routing\RouteCollection;
use Library\Routing\Router;
use Tests\BaseTest;
use Tests\TestData\Router\ResolvableOne;

class RouterTest extends BaseTest
{
    private const TEST_CONTROLLERS_ROOT_NAMESPACE = 'Tests\\App\\Http\\Controllers\\';
    private const TEST_MIDDLEWARES_ROOT_NAMESPACE = 'Tests\\App\\Http\\Middlewares\\';

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
        $this->router = new Router($this->container);
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

    public function test_dispatch_controllerHasRequest()
    {
        // Arrange
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'controllerHasRequest', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_controllerHasValidator()
    {
        // Arrange
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'controllerHasValidator', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_validationHasRequest()
    {
        // Arrange
        $this->router->enableValidation();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'validationHasRequest', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_validationHasValidator()
    {
        // Arrange
        $this->router->enableValidation();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'validationHasValidator', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_validationCtorParametersAreResolved()
    {
        // Arrange
        $this->router->enableValidation();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'validationCtorParameters', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_validationMethodParametersAreResolved()
    {
        // Arrange
        $this->router->enableValidation();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'validationMethodParameters', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_whenValidationReturnsFalse()
    {
        // Arrange
        $this->router->enableValidation();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'validationReturnsFalse', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_BAD_REQUEST, $response->statusCode());
    }

    public function test_dispatch_whenValidationReturnsValidValidationResult()
    {
        // Arrange
        $this->router->enableValidation();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'validationReturnsValidValidationResult', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_whenValidationReturnsInvalidValidationResult()
    {
        // Arrange
        $this->router->enableValidation();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'validationReturnsInvalidValidationResult', [], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_BAD_REQUEST, $response->statusCode());
    }

    public function test_dispatch_middlewareIsProcessed()
    {
        // Arrange
        $this->router->enableMiddlewares();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'middlewaresSimplest', [
            self::TEST_MIDDLEWARES_ROOT_NAMESPACE.'MidOne'
        ], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_middlewareIsProcessedWhenNotTrue()
    {
        // Arrange
        $this->router->enableMiddlewares();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'middlewaresSimplest', [
            self::TEST_MIDDLEWARES_ROOT_NAMESPACE.'MidTwo'
        ], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_UNAUTHORIZED, $response->statusCode());
    }

    public function test_dispatch_middlewareHasRequest()
    {
        // Arrange
        $this->router->enableMiddlewares();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'middlewareHasRequest', [
            self::TEST_MIDDLEWARES_ROOT_NAMESPACE.'MidThree'
        ], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_middlewareCtorParametersAreResolved()
    {
        // Arrange
        $this->router->enableMiddlewares();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'middlewareCtorParameters', [
            self::TEST_MIDDLEWARES_ROOT_NAMESPACE.'MidFour'
        ], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_middlewareMethodParametersAreResolved()
    {
        // Arrange
        $this->router->enableMiddlewares();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'middlewareMethodParameters', [
            self::TEST_MIDDLEWARES_ROOT_NAMESPACE.'MidFive'
        ], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_dispatch_middlewareOrder()
    {
        // Arrange
        $this->router->enableMiddlewares();
        $routes = new RouteCollection();
        $routes->add(new Route(['GET'], '/', self::TEST_CONTROLLERS_ROOT_NAMESPACE . 'TestController', 'middlewareOrder', [
            self::TEST_MIDDLEWARES_ROOT_NAMESPACE.'OrderTests\\MidOrderOne',
            self::TEST_MIDDLEWARES_ROOT_NAMESPACE.'OrderTests\\MidOrderTwo'
        ], 'name1'));
        $request = new Request('GET', '/', [], [], []);
        $this->container->registerInstance('request', $request);

        // Act
        $response = $this->router->dispatch($routes, $request);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }
}