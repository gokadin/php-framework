<?php

namespace Tests\Routing;

use Library\Http\Request;
use Tests\BaseTest;
use Library\Routing\Route;

class RouteTest extends BaseTest
{
    public function testMatchWithSimpleRequestWhenValid()
    {
        // Arrange
        $request = new Request('GET', '/test', []);
        $route = new Route(['GET'], '/test', 'controller@action', '', []);

        // Assert
        $this->assertTrue($route->matches($request));
    }

    public function testMatchWithParameters()
    {
        // Arrange
        $request = new Request('GET', '/test?one=abc', []);
        $route = new Route(['GET'], '/test', 'controller@actions', '', []);

        // Assert
        $this->assertTrue($route->matches($request));

        // Act
        $request = new Request('GET', '/some/test2?one=abc&two=def', []);
        $route = new Route(['GET'], '/some/test2', 'controller@actions', '', []);

        // Assert
        $this->assertTrue($route->matches($request));
    }

    public function testMatchWithParametersWhenItShouldNotMatch()
    {
        // Act
        $request = new Request('GET', '/some/other?one=abc&two=def', []);
        $route = new Route(['GET'], '/some/test', 'controller@actions', '', []);

        // Assert
        $this->assertTrue(!$route->matches($request));
    }
}