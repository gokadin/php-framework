<?php

namespace Library\IscClient\Controllers;

use Library\IscClient\IscClient;
use Library\IscClient\IscConstants;
use Library\IscClient\Subscriptions\SubscriptionRoute;

abstract class IscController
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @var IscClient
     */
    private $isc;

    /**
     * @var SubscriptionRoute
     */
    private $route;

    /**
     * @param string $topic
     * @param string $action
     * @param array $payload
     */
    protected function dispatchEvent(string $topic, string $action, array $payload = [])
    {
        $this->isc->dispatchEvent($topic, $action, $payload);
    }

    /**
     * @param string $topic
     * @param string $action
     * @param array $payload
     */
    protected function dispatchCommand(string $topic, string $action, array $payload = [])
    {
        $this->isc->dispatchCommand($topic, $action, $payload);
    }

    /**
     * @param string $topic
     * @param string $action
     * @param array $payload
     */
    protected function dispatchQuery(string $topic, string $action, array $payload = [])
    {
        $this->isc->dispatchQuery($topic, $action, $payload);
    }

    protected function returnOk(array $payload = [])
    {
        $this->isc->dispatchResult($this->route->topic(), $this->route->action(), IscConstants::STATUS_OK, $payload, $this->route->requestId());
    }

    private function returnResult()
    {

    }
}