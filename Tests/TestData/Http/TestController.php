<?php

namespace Tests\TestData\Http;

use Library\Http\Controller;

class TestController extends Controller
{
    public function testRespondOk()
    {
        return $this->respondOk();
    }

    public function testRespondBadRequest()
    {
        return $this->respondBadRequest();
    }

    public function testRespondNotFound()
    {
        return $this->respondNotFound();
    }

    public function testRespondInternalServerError()
    {
        return $this->respondInternalServerError();
    }

    public function testRespondUnauthorized()
    {
        return $this->respondUnauthorized();
    }
}