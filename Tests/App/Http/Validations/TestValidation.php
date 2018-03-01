<?php

namespace Tests\App\Http\Validations;

use Library\Http\Request;
use Library\Validation\ValidationBase;
use Library\Validation\ValidationResult;
use Library\Validation\Validator;
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

    public function validationHasRequest()
    {
        return !is_null($this->request) && $this->request instanceof Request;
    }

    public function validationHasValidator()
    {
        return !is_null($this->validator) && $this->validator instanceof Validator;
    }

    public function validationCtorParameters()
    {
        if (!($this->one instanceof ResolvableOne))
        {
            return false;
        }

        return true;
    }

    public function validationMethodParameters(ResolvableOne $one)
    {
        if (!($one instanceof ResolvableOne))
        {
            return false;
        }

        return true;
    }

    public function validationReturnsFalse()
    {
        return false;
    }

    public function validationReturnsValidValidationResult()
    {
        return new ValidationResult(true);
    }

    public function validationReturnsInvalidValidationResult()
    {
        return new ValidationResult(false);
    }
}