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

    public function __construct()
    {
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

    public function subscribe(array $subscriptions)
    {
        if (is_null($this->ps))
        {
            $this->ps = $this->predis->pubSubLoop();
        }

        foreach ($subscriptions as $subscription)
        {
            $this->ps->subscribe($subscription);
        }
    }

    public function run(\Closure $closure)
    {
        foreach ($this->ps as $request)
        {
            $this->processRequest($closure, $request);
        }
    }

    private function processRequest(\Closure $closure, $request)
    {
        if ($request->kind != 'message')
        {
            return;
        }

        $action = substr($request->channel, strrpos($request->channel, '.') + 1);
        $type = str_replace('.'.$action, '', $request->channel);
        $type = substr($type, strrpos($type, '.') + 1);
        $topic = str_replace('.'.$type.'.'.$action, '', $request->channel);
        $payload = $this->decodePayload($request->payload);

        $closure($topic, $type, $action, $payload);
    }

    private function decodePayload($payload)
    {
        $decoded = json_decode($payload, true);

        return is_null($decoded) ? [] : $decoded;
    }

    public function stop()
    {
        //$this->ps->stop();
    }

    public function dispatch(string $channel, array $payload)
    {
        $predis2 = new Client('tcp://isc-redis:6379');
        $predis2->publish($channel, json_encode($payload));
    }
}