<?php

namespace Tests\Library\Http;

use Library\Http\Response;
use Tests\BaseTest;

class ResponseTest extends BaseTest
{
    public function test_ctor_defaultStatusIsSetToOK()
    {
        // Act
        $response = new Response();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $response->statusCode());
    }

    public function test_ctor_defaultDataIsEmptyArray()
    {
        // Act
        $response = new Response();

        // Assert
        $this->assertEquals([], $response->data());
    }
}