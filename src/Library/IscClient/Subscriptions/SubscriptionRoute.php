<?php

namespace Library\IscClient\Subscriptions;

class SubscriptionRoute
{
    private $requestId;

    private $class;

    private $method;

    private $topic;

    private $type;

    private $action;

    private $middlewares;

    public function __construct(string $class, string $method, string $topic, string $type, string $action)
    {
        $this->class = $class;
        $this->method = $method;
        $this->topic = $topic;
        $this->type = $type;
        $this->action = $action;
    }

    public function setRequestId(string $requestId)
    {
        $this->requestId = $requestId;
    }

    public function requestId()
    {
        return $this->requestId;
    }

    public function class()
    {
        return $this->class;
    }

    public function method()
    {
        return $this->method;
    }

    public function topic()
    {
        return $this->topic;
    }

    public function type()
    {
        return $this->type;
    }

    public function action()
    {
        return $this->action;
    }
}