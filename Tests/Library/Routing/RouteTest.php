<?php

namespace Tests\LibraryRouting;

use Library\Routing\Route;
use Tests\BaseTest;

class RouteTest extends BaseTest
{
    public function test_match_withSimpleRequestWhenValid()
    {
        // Arrange
        $route = new Route(['GET'], '/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matches('GET', '/test'));
    }

    public function test_match_withParameters()
    {
        // Arrange
        $route = new Route(['GET'], '/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matches('GET', '/test?one=abc'));

        // Act
        $route = new Route(['GET'], '/some/test2', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matches('GET', '/some/test2?one=abc&two=def'));
    }

    public function test_match_withParametersWhenItShouldNotMatch()
    {
        // Act
        $route = new Route(['GET'], '/some/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue(!$route->matches('GET', '/some/other?one=abc&two=def'));
    }

    public function test_matchCatchAll_withDifferentUris()
    {
        // Act
        $route = new Route(['GET'], '/some/test', 'controller', 'action', [], 'name1');

        // Assert
        $this->assertTrue($route->matchesCatchAll('GET'));
    }
}