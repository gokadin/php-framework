<?php

namespace Library\IscClient\Controllers;

use Library\IscClient\IscClient;

abstract class IscController
{
    /**
     * @var string
     */
    protected $payload;

    /**
     * @var IscClient
     */
    private $isc;

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
}