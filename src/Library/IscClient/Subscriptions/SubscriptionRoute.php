<?php

namespace Library\IscClient\Subscriptions;

class SubscriptionRoute
{
    private $class;

    private $method
    ;

    private $topic;

    private $type;

    private $action;

    private $middlewares;

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