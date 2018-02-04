<?php

namespace Library\Validation;

use Carbon\Carbon;

class Validator
{
    private $database;
    private $data = [];
    private $errors = [];

    public function make(array $data, array $rules)
    {
        $this->errors = [];
        $this->data = $data;

        if ($rules == null || sizeof($rules) == 0)
        {
            return true;
        }

        $isValid = true;

        foreach ($rules as $field => $constraints)
        {
            $customError = null;
            $value = isset($data[$field]) ? $data[$field]: null;

            if (!is_array($constraints))
            {
                if ($constraints != 'required' && is_null($value))
                {
                    continue;
                }

                $args = array();
                $temp = explode(':', $constraints);
                $functionName = $temp[0];
                if (sizeof($temp) > 1)
                    $args = explode(',', $temp[1]);

                if ($this->callProperFunction($functionName, $value, $args))
                    continue;

                if ($isValid)
                    $isValid = false;

                $this->errors[$field][] = $this->buildErrorString($field, $functionName, $args, $customError);

                continue;
            }

            foreach ($constraints as $key => $error)
            {
                $customError = null;
                $constraint = $error;

                if (is_string($key))
                {
                    $customError = $error;
                    $constraint = $key;
                }

                $args = array();
                $temp = explode(':', $constraint);
                $functionName = $temp[0];
                if (sizeof($temp) > 1)
                    $args = explode(',', $temp[1]);

                if ($this->callProperFunction($functionName, $value, $args))
                    continue;

                if ($isValid)
                    $isValid = false;

                $this->errors[$field][] = $this->buildErrorString($field, $functionName, $args, $customError);
            }
        }

        return $isValid;
    }

    public function hasErrors()
    {
        return sizeof($this->errors) > 0;
    }

    public function errors()
    {
        return $this->errors;
    }

    private function callProperFunction($functionName, $value, array $args)
    {
        switch (sizeof($args))
        {
            case 0:
                return $this->$functionName($value);
            case 1:
                return $this->$functionName($value, $args[0]);
            case 2:
                return $this->$functionName($value, $args[0], $args[1]);
            case 3:
                return $this->$functionName($value, $args[0], $args[1], $args[2]);
            case 4:
                return $this->$functionName($value, $args[0], $args[1], $args[2], $args[3]);
            case 5:
                return $this->$functionName($value, $args[0], $args[1], $args[2], $args[3], $args[4]);
            default:
                return false;
        }
    }

    private function buildErrorString($field, $functionName, array $args, $customError)
    {
        if ($customError != null)
            return $this->buildCustomErrorString($field, $args, $customError);

        switch ($functionName)
        {
            case 'required':
                return $field.' is required';
            case 'numeric':
                return $field.' must be numeric';
            case 'min':
                return $field.' must be bigger than '.$args[0];
            case 'max':
                return $field.' must be smaller than '.$args[0];
            case 'between':
                return $field.' must be between '.$args[0].' and '.$args[1];
            case 'boolean':
                return $field.' must be true or false';
            case 'email':
                return $field.' is already taken';
            case 'equalsField':
                return $field.' does not equal '.$args[0];
            case 'date':
                return $field.' is not a valid date';
            default:
                return '';
        }
    }

    /*
     * Example: '{field} is required'
     * Example: '{field} should be bigger than {0}'
     * Example: '{field} should be between {0} and {1}'
     */
    private function buildCustomErrorString($field, array $args, $customError)
    {
        $result = str_replace('{field}', $field, $customError);

        if (sizeof($args) == 0)
            return $result;

        for ($i = 0; $i < sizeof($args); $i++)
            $result = str_replace('{'.$i.'}', $args[$i], $result);

        return $result;
    }

    /* INDIVIDUAL VALIDATIONS */

    public function required($value)
    {
        if ($value == null || trim($value) === '')
        {
            return false;
        }

        return true;
    }

    public function numeric($value)
    {
        return is_numeric($value);
    }

    public function min($value, $min)
    {
        if (is_string($value))
        {
            return strlen($value) >= $min;
        }

        if (is_numeric($value)) {
            return $value >= $min;
        }

        return false;
    }

    public function max($value, $max)
    {
        if (is_string($value))
        {
            return strlen($value) <= $max;
        }

        if (is_numeric($value)) {
            return $value <= $max;
        }

        return false;
    }

    public function between($value, $min, $max)
    {
        return $this->numeric($value) && $value >= $min && $value <= $max;
    }

    public function boolean($value)
    {
        if (is_bool($value))
            return true;

        if (is_string($value))
            return $value == '1' || $value == '0';

        if (is_integer($value))
            return $value == 1 || $value == 0;

        return false;
    }

    public function email($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL))
            return false;

        return true;
    }

    public function equalsField($value, $fieldName)
    {
        return isset($this->data[$fieldName]) ? $value == $this->data[$fieldName] : false;
    }

    public function date($value)
    {
        return Carbon::parse($value);
    }
}