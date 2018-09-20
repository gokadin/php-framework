<?php

namespace Library\IscClient\Controllers;

use Library\IscClient\IscClient;
use Library\IscClient\IscConstants;
use Library\IscClient\Subscriptions\SubscriptionRoute;
use Library\Http\Response;

abstract class IscController
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @var IscClient
     */
    protected $isc;

    /**
     * @var SubscriptionRoute
     */
    protected $route;

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
        $result = $this->isc->dispatchCommand($topic, $action, $payload);
        return new Response($result['statusCode'], $result['payload']);
    }

    /**
     * @param string $topic
     * @param string $action
     * @param array $payload
     */
    protected function dispatchQuery(string $topic, string $action, array $payload = [])
    {
        $result = $this->isc->dispatchQuery($topic, $action, $payload);
        return new Response($result['statusCode'], $result['payload']);
    }

    /**
     * @param array $payload
     */
    protected function respondOk(array $payload = [])
    {
        $this->respond(IscConstants::STATUS_OK, $payload);
    }

    /**
     * @param array $payload
     */
    protected function respondBadRequest(array $payload = [])
    {
        $this->respond(IscConstants::STATUS_BAD_REQUEST, $payload);
    }

    /**
     * @param array $payload
     */
    protected function respondInternalServerError(array $payload = [])
    {
        $this->respond(IscConstants::STATUS_INTERNAL_SERVER_ERROR, $payload);
    }

    /**
     * @param array $payload
     */
    protected function respondUnauthorized(array $payload = [])
    {
        $this->respond(IscConstants::STATUS_BAD_UNAUTHORIZED, $payload);
    }

    /**
     * @param array $payload
     */
    protected function respondNotFound(array $payload = [])
    {
        $this->respond(IscConstants::STATUS_NOT_FOUND, $payload);
    }

    /**
     * @param int $statusCode
     * @param array $payload
     */
    private function respond(int $statusCode, array $payload)
    {
        $this->isc->dispatchResult($this->route->topic(), $this->route->action(), $statusCode, $payload, $this->route->requestId());
    }
}