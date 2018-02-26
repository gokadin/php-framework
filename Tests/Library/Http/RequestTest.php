<?php

namespace Tests\Library\Http;

use Library\Http\Request;
use Tests\BaseTest;

class RequestTest extends BaseTest
{
    public function tearDown()
    {
        parent::tearDown();

        $_GET = [];
        $_POST = [];
    }

    public function test_ctor_methodIsOverridenIfPassedInCtor()
    {
        // Arrange
        $request = new Request('someMethod');

        // Assert
        $this->assertEquals('someMethod', $request->method());
    }

    public function test_ctor_methodIsSetFromServerGlobal()
    {
        // Arrange
        $_SERVER['REQUEST_METHOD'] = 'patch';
        $request = new Request();

        // Assert
        $this->assertEquals('PATCH', $request->method());
    }

    public function test_ctor_methodIsOverridenIfSetInPostHiddenVariable()
    {
        // Arrange
        $_SERVER['REQUEST_METHOD'] = 'patch';
        $_POST['_method'] = 'put';
        $request = new Request();

        // Assert
        $this->assertEquals('PUT', $request->method());
    }

    public function test_ctor_uriIsOverridenIfPassedInCtor()
    {
        // Arrange
        $request = new Request(null, 'someUri');

        // Assert
        $this->assertEquals('someUri', $request->uri());
    }

    public function test_ctor_uriIsSetFromServerGloal()
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/some';
        $request = new Request();

        // Assert
        $this->assertEquals('/some', $request->uri());
    }

    public function test_ctor_headersAreOverridenIfSetInCtor()
    {
        // Arrange
        $request = new Request(null, null, null, null, ['a' => 'b']);

        // Assert
        $this->assertTrue($request->headerExists('a'));
        $this->assertEquals('b', $request->header('a'));
    }

    public function test_ctor_getIsOverridenIfPassedInCtor()
    {
        // Arrange
        $request = new Request(null, null, ['a' => 'b']);

        // Assert
        $this->assertTrue($request->getExists('a'));
        $this->assertEquals('b', $request->get('a'));
    }

    public function test_ctor_getIsSetFromGlobal()
    {
        // Arrange
        $_GET = ['c' => 'd'];
        $request = new Request();

        // Assert
        $this->assertTrue($request->getExists('c'));
        $this->assertEquals('d', $request->get('c'));
    }

    public function test_ctor_dataIsOverridenIfPassedInCtor()
    {
        // Arrange
        $request = new Request(null, null, null, ['a' => 'b']);

        // Assert
        $this->assertTrue($request->dataExists('a'));
        $this->assertEquals('b', $request->data('a'));
    }

    public function test_ctor_dataIsSetFromGlobal()
    {
        // Arrange
        $_POST = ['c' => 'd'];
        $request = new Request();

        // Assert
        $this->assertTrue($request->dataExists('c'));
        $this->assertEquals('d', $request->data('c'));
    }

    public function test_ctor_dataIsAnEmptyArrayIfMethodIsGet()
    {
        // Arrange
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = ['c' => 'd'];
        $request = new Request();

        // Assert
        $this->assertFalse($request->dataExists('c'));
    }

    public function test_all_whenHavingBothGetAndDataValues()
    {
        // Arrange
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = ['a' => 'b'];
        $_POST = ['c' => 'd'];
        $request = new Request();

        // Assert
        $this->assertEquals(2, sizeof($request->all()));
        $this->assertEquals('b', $request->all()['a']);
        $this->assertEquals('d', $request->all()['c']);
    }
}