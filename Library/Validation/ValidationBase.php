<?php

namespace Library\Validation;

use Library\Http\Request;

class ValidationBase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Validator
     */
    protected $validator;

    protected function validate(array $rules)
    {
        // ...
    }
}