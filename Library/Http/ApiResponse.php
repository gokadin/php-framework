<?php

namespace Library\Http;

class ApiResponse
{
    const STATUS_OK = 200;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_BAD_REQUEST = 400;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var mixed
     */
    private $data;

    public function __construct($statusCode = self::STATUS_BAD_REQUEST, $data = [])
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function setData($data)
    {
        $this->data = $data;
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