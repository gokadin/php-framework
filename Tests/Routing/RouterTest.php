<?php

namespace Tests\Routing;

use Library\Container\Container;
use Library\Database\Database;
use Library\Routing\Router;
use Library\Validation\Validator;
use Tests\BaseTest;

class RouterTest extends BaseTest
{
    /**
     * @var Router
     */
    private $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router(new Container(), new Validator(new Database([
            'driver' => 'mysql',
            'mysql' => [
                'host' => env('DATABASE_HOST'),
                'database' => env('DATABASE_NAME'),
                'username' => env('DATABASE_USERNAME'),
                'password' => env('DATABASE_PASSWORD')
            ]
        ])));
    }

    public function testThatGetRouteIsCorrectlyRegistered()
    {
        // Act
        $this->router->get('/test', ['as' => 'test', 'uses' => 'controller@index']);

        // Assert
        $this->assertTrue($this->router->has('test'));
    }

    public function testThatPostRouteIsCorrectlyRegistered()
    {
        // Act
        $this->router->post('/test', ['as' => 'test', 'uses' => 'controller@index']);

        // Assert
        $this->assertTrue($this->router->has('test'));
    }

    public function testThatPutRouteIsCorrectlyRegistered()
    {
        // Act
        $this->router->put('/test', ['as' => 'test', 'uses' => 'controller@index']);

        // Assert
        $this->assertTrue($this->router->has('test'));
    }

    public function testThatPatchRouteIsCorrectlyRegistered()
    {
        // Act
        $this->router->patch('/test', ['as' => 'test', 'uses' => 'controller@index']);

        // Assert
        $this->assertTrue($this->router->has('test'));
    }

    public function testThatDeleteRouteIsCorrectlyRegistered()
    {
        // Act
        $this->router->delete('/test', ['as' => 'test', 'uses' => 'controller@index']);

        // Assert
        $this->assertTrue($this->router->has('test'));
    }

    public function testThatManyRouteIsCorrectlyRegistered()
    {
        // Act
        $this->router->many(['GET', 'POST'], '/test', ['as' => 'test', 'uses' => 'controller@index']);

        // Assert
        $this->assertTrue($this->router->has('test'));
    }

    public function testResourceRouteIsCorrectlyRegistered()
    {
        // Act
        $this->router->resource('controller', ['fetch', 'store', 'update', 'destroy', 'show', 'edit']);

        // Assert
        $this->assertTrue($this->router->has('fetch'));
        $this->assertTrue($this->router->has('store'));
        $this->assertTrue($this->router->has('update'));
        $this->assertTrue($this->router->has('destroy'));
        $this->assertTrue($this->router->has('show'));
        $this->assertTrue($this->router->has('edit'));
    }
}