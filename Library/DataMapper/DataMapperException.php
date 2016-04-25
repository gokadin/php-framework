<?php

namespace Library\DataMapper;

use Exception;

class DataMapperException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}