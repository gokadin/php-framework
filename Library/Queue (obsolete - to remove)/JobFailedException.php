<?php

namespace Library\Queue;

use Exception;

class JobFailedException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}