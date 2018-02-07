<?php

namespace Tests\LibraryRouting;

use Library\Routing\Route;
use Library\Routing\RouteCollection;
use Tests\BaseTest;

class RouteCollectionTest extends BaseTest
{
    public function test_add_routeIsAddedToCollection()
    {
        // Arrange
        $collection = new RouteCollection();

        // Act
        $collection->add(new Route(['GET'], '/test', 'controller', 'action', [], 'name1'));

        // Assert
        $this->assertEquals(1, count($collection));
    }

    public function test_add_routeIsAddedByName()
    {
        // Arrange
        $collection = new RouteCollection();

        // Act
        $collection->add(new Route(['GET'], '/test', 'controller', 'action', [], 'name1'));

        // Assert
        $this->assertTrue($collection->exists('name1'));
    }
}