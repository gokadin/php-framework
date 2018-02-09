<?php

namespace Library\Validation;

class ValidationResult
{
    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var array
     */
    private $errors;

    /**
     * ValidationResult constructor.
     *
     * @param bool $isValid
     * @param array $errors
     */
    public function __construct(bool $isValid, array $errors = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }
}