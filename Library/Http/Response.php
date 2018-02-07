<?php

namespace Library\Http;

class Response
{
    const STATUS_OK = 200;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_NOT_FOUND = 401;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var mixed
     */
    private $data;

    public function __construct($statusCode = self::STATUS_OK, $data = [])
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    public function statusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function data()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function addHeader($key, $value)
    {
        header($key.': '.$value);
    }

    public function isSuccess()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function executeResponse()
    {
        http_response_code($this->statusCode);

        echo json_encode($this->data);

        exit();
    }
}