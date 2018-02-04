<?php

namespace Library\Http;

class Request
{
    protected $method;
    protected $uri;
    private $data;

    public function __construct($method = null, $uri = null, $data = null)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->data = $data;
    }

    protected function getDataFromSource()
    {
        if ($this->data != null)
        {
            return $this->data;
        }

        if ($this->isJson())
        {
            $decodedJson = $this->getDecodedJson();
            $this->data = array_merge(is_array($decodedJson) ? $decodedJson : [], $this->retrieveGetData());

            return $this->data;
        }

        switch ($this->method())
        {
            case 'GET':
                $this->data = $this->retrieveGetData();
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $this->data = array_merge($_POST, $this->retrieveGetData());
                break;
        }

        return $this->data;
    }

    private function retrieveGetData()
    {
        $result = [];
        foreach ($_GET as $key => $value)
        {
            if ($key == 'json')
            {
                $result[$key] = json_decode($value);

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    public function get($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    public function isJson()
    {
        if (!strpos($this->header('Content-Type'), '/json'))
        {
            return false;
        }

        return true;
    }

    protected function getDecodedJson()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    public function __get($key)
    {
        $data = $this->getDataFromSource();
        return isset($data[$key]) ? $data[$key] : null;
    }

    public function all()
    {
        return $this->excludeFrameworkVariablesFromAll($this->getDataFromSource());
    }

    protected function excludeFrameworkVariablesFromAll($arr)
    {
        $results = array();
        foreach ($arr as $key => $value)
        {
            if ($key != '_method' && $key != '_token')
                $results[$key] = $value;
        }

        return $results;
    }

    public function cookieData($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    public function cookieExists($key)
    {
        return isset($_COOKIE[$key]);
    }

    public function data($key)
    {
        $data = $this->getDataFromSource();
        return isset($data[$key]) ? $data[$key] : null;
    }

    public function dataExists($key)
    {
        $data = $this->getDataFromSource();
        return isset($data[$key]);
    }

    public function method()
    {
        if ($this->method != null)
        {
            return $this->method;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
            return $this->method = 'GET';

        if (isset($_POST['_method']))
        {
            return $this->method = strtoupper($_POST['_method']);
        }

        return $this->method = $_SERVER['REQUEST_METHOD'];
    }
    
    public function fileData($key)
    {
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }
    
    public function fileExists($key)
    {
        return isset($_FILES[$key]);
    }

    public function uri()
    {
        if (!is_null($this->uri))
        {
            return $this->uri;
        }

        return $this->uri = $_SERVER['REQUEST_URI'];
    }

    public function header($key)
    {
        $headers = apache_request_headers();

        if (isset($headers[$key]))
        {
            return $headers[$key];
        }

        $key = strtolower($key);

        return isset($headers[$key]) ? $headers[$key] : null;
    }
}
