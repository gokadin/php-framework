<?php

namespace Tests\Library\Http;

use Library\Http\Response;
use Tests\BaseTest;
use Tests\TestData\Http\TestController;

class ControllerTest extends BaseTest
{
    /**
     * @var TestController
     */
    private $controller;

    public function setUp()
    {
        parent::setUp();

        $this->controller = new TestController();
    }

    public function test_respondOk_respondsWithTheCorrectCode()
    {
        // Act
        $response = $this->controller->testRespondOk();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_respondBadRequest_respondsWithTheCorrectCode()
    {
        // Act
        $response = $this->controller->testRespondBadRequest();

        // Assert
        $this->assertEquals(Response::STATUS_BAD_REQUEST, $response->statusCode());
    }

    public function test_respondNotFound_respondsWithTheCorrectCode()
    {
        // Act
        $response = $this->controller->testRespondNotFound();

        // Assert
        $this->assertEquals(Response::STATUS_NOT_FOUND, $response->statusCode());
    }

    public function test_respondInternalServerError_respondsWithTheCorrectCode()
    {
        // Act
        $response = $this->controller->testRespondInternalServerError();

        // Assert
        $this->assertEquals(Response::STATUS_INTERNAL_SERVER_ERROR, $response->statusCode());
    }

    public function test_respondUnauthorized_respondsWithTheCorrectCode()
    {
        // Act
        $response = $this->controller->testRespondUnauthorized();

        // Assert
        $this->assertEquals(Response::STATUS_UNAUTHORIZED, $response->statusCode());
    }
}