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
        $route = new Route(['GET'], '/test', 'controller', 'action', [], 'name1');
        $request = new Request('GET', '/test');

        // Assert
        $this->assertTrue($route->matches($request));
    }

    public function test_match_withParameters()
    {
        // Arrange
        $route = new Route(['GET'], '/test', 'controller', 'action', [], 'name1');
        $request1 = new Request('GET', '/test?one=abc');
        $request2 = new Request('GET', '/some/test2?one=abc&two=def');

        // Assert
        $this->assertTrue($route->matches($request1));

        // Act
        $route = new Route(['GET'], '/some/test2', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matches($request2));
    }

    public function test_match_withParametersWhenItShouldNotMatch()
    {
        // Arrange
        $request = new Request('GET', '/some/other?one=abc&two=def');

        // Act
        $route = new Route(['GET'], '/some/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue(!$route->matches($request));
    }

    public function test_matchCatchAll_withDifferentUris()
    {
        // Arrange
        $request = new Request('GET', '/');

        // Act
        $route = new Route(['GET'], '/some/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matchesCatchAll('GET'));
    }
}