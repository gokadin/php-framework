<?php

namespace Tests\App\Http\Controllers;

use Library\Http\Request;
use Library\Http\Response;
use Library\Validation\Validator;
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

    public function controllerHasRequest()
    {
        if (is_null($this->request) || !($this->request instanceof Request))
        {
            return new Response(Response::STATUS_BAD_REQUEST);
        }

        return new Response(Response::STATUS_OK);
    }

    public function controllerHasValidator()
    {
        if (is_null($this->validator) || !($this->validator instanceof Validator))
        {
            return new Response(Response::STATUS_BAD_REQUEST);
        }

        return new Response(Response::STATUS_OK);
    }

    public function validationHasRequest()
    {
        return new Response(Response::STATUS_OK);
    }

    public function validationHasValidator()
    {
        return new Response(Response::STATUS_OK);
    }

    public function validationCtorParameters()
    {
        return new Response(Response::STATUS_OK);
    }

    public function validationMethodParameters()
    {
        return new Response(Response::STATUS_OK);
    }

    public function validationReturnsFalse()
    {
        return new Response(Response::STATUS_OK);
    }

    public function validationReturnsValidValidationResult()
    {
        return new Response(Response::STATUS_OK);
    }

    public function validationReturnsInvalidValidationResult()
    {
        return new Response(Response::STATUS_OK);
    }

    public function middlewaresSimplest()
    {
        return new Response(Response::STATUS_OK);
    }

    public function middlewareHasRequest()
    {
        return new Response(Response::STATUS_OK);
    }

    public function middlewareCtorParameters()
    {
        return new Response(Response::STATUS_OK);
    }

    public function middlewareMethodParameters()
    {
        return new Response(Response::STATUS_OK);
    }
}