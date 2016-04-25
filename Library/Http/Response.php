<?php

namespace Library\Http;

use Library\Facades\Page;
use Library\Routing\Router;
use Library\Session\Session;

class Response
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $args = [];

    public function __construct(Router $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
        $this->action = '';
    }

    public function setCookie($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = false, $http_only = true)
    {
        set_cookie($name, $value, $expire, $path, $domain, $secure, $http_only);

        return $this;
    }

    public function withFlash($message, $type = 'success')
    {
        $this->session->setFlash($message, $type);

        return $this;
    }

    public function withErrors(array $errors)
    {
        $this->session->setErrors($errors);

        return $this;
    }

    public function back()
    {
        $this->action = 'Back';

        return $this;
    }

    private function executeBack()
    {
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit();
    }

    public function route($name, $params = [])
    {
        $this->action = 'Route';
        $this->args[] = $name;
        $this->args[] = $params;

        return $this;
    }

    private function executeRoute()
    {
        $uri = $this->router->getUri($this->args[0], $this->args[1]);
        header('Location: '.$uri);
        exit();
    }

    public function redirect($uri)
    {
        $this->action = 'Redirect';
        $this->args[] = $uri;

        return $this;
    }

    private function executeRedirect()
    {
        header('Location: '.$this->args[0]);
        exit();
    }

    public function redirect404()
    {
        $this->action = 'Redirect404';

        return $this;
    }

    private function executeRedirect404()
    {
        header('HTTP/1.0 404 Not Found');
        echo '404';
        exit();
    }

    public function json($data, $statusCode)
    {
        $this->action = 'Json';
        $this->args[] = $data;
        $this->args[] = $statusCode;

        return $this;
    }

    private function executeJson()
    {
        http_response_code($this->args[1]);
        echo json_encode($this->args[0]);
        exit();
    }

    public function executeResponse()
    {
        if ($this->action == '')
        {
            return;
        }

        $functionName = 'execute'.$this->action;
        return $this->$functionName();
    }
}
