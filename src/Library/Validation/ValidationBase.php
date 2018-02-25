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

    /**
     * @param array $rules
     * @return ValidationResult
     */
    protected function validate(array $rules): ValidationResult
    {
        return $this->validator->validate($this->request->all(), $rules);
    }
}