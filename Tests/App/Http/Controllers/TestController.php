<?php

namespace Tests\App\Http\Controllers;

use Library\Http\Response;
use Tests\TestData\Router\ResolvableOne;

class TestController extends Controller
{
    /**
     * @var ResolvableOne
     */
    private $one;

    public function __construct(ResolvableOne $one)
    {
        $this->one = $one;
    }

    public function simpleAction()
    {
        return new Response(Response::STATUS_OK);
    }

    public function simpleActionWithRequest()
    {
        return new Response(Response::STATUS_OK, ['a' => $this->request->get('a')]);
    }

    public function actionParameters(ResolvableOne $one)
    {
        return new Response(Response::STATUS_OK, ['resolvableOne' => $one]);
    }

    public function ctorParameters()
    {
        return new Response(Response::STATUS_OK, ['resolvableOne' => $this->one]);
    }

    public function validationCtorParameters()
    {
        return new Response(Response::STATUS_OK);
    }
}