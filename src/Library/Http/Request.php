<?php

namespace Library\Http;

class Request
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $getData = [];

    /**
     * @var array
     */
    private $postData = [];

    /**
     * @var array
     */
    private $headers;

    /**
     * Request constructor.
     *
     * @param mixed $method
     * @param mixed $uri
     * @param array|null $getData
     * @param array|null $postData
     * @param array|null $headers
     * @internal param array|null $data
     */
    public function __construct($method = null, $uri = null, array $getData = null, array $postData = null, array $headers = null)
    {
        $this->setUpHeaders($headers);
        $this->setUpMethod($method);
        $this->setUpUri($uri);
        $this->setUpGetData($getData);
        $this->setUpPostData($postData);
    }

    /**
     * @param mixed $method
     */
    private function setUpMethod($method): void
    {
        if (!is_null($method))
        {
            $this->method = $method;
            return;
        }

        $this->method = array_key_exists('REQUEST_METHOD', $_SERVER) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';

        if (isset($_POST['_method']))
        {
            $this->method = strtoupper($_POST['_method']);
        }
    }

    /**
     * @param mixed $uri
     */
    private function setUpUri($uri): void
    {
        if (!is_null($uri))
        {
            $this->uri = $uri;
            return;
        }

        $this->uri = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '';
    }

    /**
     * @param mixed $getData
     */
    public function setUpGetData($getData = null): void
    {
        if (!is_null($getData))
        {
            $this->getData = $getData;
            return;
        }

        foreach ($_GET as $key => $value)
        {
            if ($key == 'json')
            {
                $this->getData[$key] = json_decode($value);
                continue;
            }

            $this->getData[$key] = $value;
        }
    }

    /**
     * @param mixed $postData
     */
    private function setUpPostData($postData): void
    {
        if (!is_null($postData))
        {
            $this->postData = $postData;
            return;
        }

        if ($this->method == 'GET')
        {
            $this->postData = [];
            return;
        }

        if ($this->isJson())
        {
            $decodedJson = $this->getDecodedJson();
            $this->postData = is_array($decodedJson) ? $decodedJson : [];
            return;
        }

        $this->postData = $_POST;
    }

    /**
     * @param mixed $headers
     */
    private function setUpHeaders($headers): void
    {
        if (!is_null($headers))
        {
            $this->headers = $headers;
            return;
        }

        if (function_exists('apache_request_headers'))
        {
            $this->headers = apache_request_headers();
            return;
        }

        $this->headers = [];
    }

    /**
     * @return string
     */
    public function method(): string
    {
       return $this->method;
    }

    /**
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return strpos($this->header('Content-Type'), '/json');
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return array_key_exists($key, $this->getData) ? $this->getData[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function getExists(string $key): bool
    {
        return array_key_exists($key, $this->getData);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function data(string $key)
    {
        return array_key_exists($key, $this->postData) ? $this->postData[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function dataExists(string $key): bool
    {
        return array_key_exists($key, $this->postData);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function header(string $key)
    {
        if (array_key_exists($key, $this->headers))
        {
            return $this->headers[$key];
        }

        $key = strtolower($key);
        return array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function headerExists(string $key): bool
    {
        return array_key_exists($key, $this->headers);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->excludeFrameworkVariables(array_merge($this->getData, $this->postData));
    }

    /**
     * @param $key
     * @return mixed
     */
    public function cookie(string $key)
    {
        return array_key_exists($key, $_COOKIE) ? $_COOKIE[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function cookieExists(string $key): bool
    {
        return array_key_exists($key, $_COOKIE);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function file(string $key)
    {
        return array_key_exists($key, $_FILES) ? $_FILES[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function fileExists(string $key): bool
    {
        return array_key_exists($key, $_FILES);
    }

    /**
     * @return mixed
     */
    private function getDecodedJson()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    /**
     * @param array $arr
     * @return array
     */
    private function excludeFrameworkVariables(array $arr): array
    {
        $results = [];
        foreach ($arr as $key => $value)
        {
            if ($key != '_method' && $key != '_token')
            {
                $results[$key] = $value;
            }
        }

        return $results;
    }
}
