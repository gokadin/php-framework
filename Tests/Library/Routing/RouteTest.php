<?php

namespace Tests\LibraryRouting;

use Library\Http\Request;
use Library\Routing\Route;
use Tests\BaseTest;

class RouteTest extends BaseTest
{
    public function test_match_withSimpleRequestWhenValid()
    {
        // Arrange
        $request = new Request('GET', '/test', []);
        $route = new Route(['GET'], '/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matches($request));
    }

    public function test_match_withParameters()
    {
        // Arrange
        $request = new Request('GET', '/test?one=abc', []);
        $route = new Route(['GET'], '/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matches($request));

        // Act
        $request = new Request('GET', '/some/test2?one=abc&two=def', []);
        $route = new Route(['GET'], '/some/test2', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matches($request));
    }

    public function test_match_withParametersWhenItShouldNotMatch()
    {
        // Act
        $request = new Request('GET', '/some/other?one=abc&two=def', []);
        $route = new Route(['GET'], '/some/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue(!$route->matches($request));
    }
}