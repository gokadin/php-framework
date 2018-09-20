<?php

namespace Library\IscClient\Controllers;

class IscResponse
{
    const STATUS_OK = 200;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_NOT_FOUND = 404;

    private $payload;

    private $statusCode;

    public function __construct($statusCode = self::STATUS_OK, $payload = [])
    {
        $this->statusCode = $statusCode;
        $this->payload = $payload;
    }

    public function statusCode()
    {
        return $this->statusCode;
    }

    public function payload()
    {
        return $this->payload;
    }
}