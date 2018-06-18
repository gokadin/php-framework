<?php

namespace Library\IscClient\Drivers;

use Library\IscClient\IscException;
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

        $this->predis = new Client([
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port
        ]);

        $this->ps = $this->predis->pubSubLoop();
    }

    public function subscribe()
    {
        if (isset($this->config['events']))
        {
            $this->subscribeEvents($this->config['events']);
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
        echo 'ENTER RUN'.PHP_EOL;
        foreach ($this->ps as $message)
        {
            echo 'got message'.PHP_EOL;
            var_dump($message).PHP_EOL;
        }
        echo 'LEAVING RUN'.PHP_EOL;
    }

    public function stop()
    {
        //$this->ps->stop();
    }
}