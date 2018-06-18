<?php

namespace Library\IscClient\Drivers;

use Library\IscClient\IscEntity;
use Library\IscClient\IscException;
use Library\IscClient\RequestHandlers\EntityHandler;
use Predis\Client;

class RedisBusDriver implements IBusDriver
{
    private const ISC_REDIS_HOST_KEY = 'ISC_REDIS_HOST';
    private const ISC_REDIS_PORT_KEY = 'ISC_REDIS_PORT';

    /**
     * @var PredisClient
     */
    private $predis;

    private $ps;

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->connect();
    }

    private function connect()
    {
        $host = getenv(self::ISC_REDIS_HOST_KEY);
        if (is_null($host) || $host == '')
        {
            throw new IscException('Redis hostname is not set.');
        }

        $port = getenv(self::ISC_REDIS_PORT_KEY);
        if (is_null($port) || $port == '')
        {
            throw new IscException('Redis port is not set.');
        }

        $this->predis = new Client('tcp://'.$host.':'.$port.'?read_write_timeout=0');
    }

    public function subscribe()
    {
        if (is_null($this->ps))
        {
            $this->ps = $this->predis->pubSubLoop();
        }

        if (isset($this->config['subscriptions']) && isset($this->config['subscriptions']['events']))
        {
            $this->subscribeEvents($this->config['subscriptions']['events']);
        }
    }

    private function subscribeEvents(array $allEvents)
    {
        foreach ($allEvents as $topic => $events)
        {
            foreach ($events as $event)
            {
                $subscriptionString = $topic.'.'.$event;
                $this->ps->subscribe($subscriptionString);
            }
        }
    }

    public function run()
    {
        $handler = new EntityHandler();

        foreach ($this->ps as $request)
        {
            $handler->handle($request);
        }
    }

    public function stop()
    {
        //$this->ps->stop();
    }

    public function dispatch(string $channel, IscEntity $entity)
    {
        $this->predis->publish($channel, json_encode($entity));
    }
}