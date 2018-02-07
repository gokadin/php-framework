<?php

namespace Tests\App\Http\Validations;

use Library\Validation\ValidationBase;
use Tests\TestData\Router\ResolvableOne;

class TestValidation extends ValidationBase
{
    /**
     * @var ResolvableOne
     */
    private $one;

    public function __construct(ResolvableOne $one)
    {
        $this->one = $one;
    }

    public function validationCtorParameters()
    {
        if (!($this->one instanceof ResolvableOne))
        {
            return false;
        }

        return true;
    }
}