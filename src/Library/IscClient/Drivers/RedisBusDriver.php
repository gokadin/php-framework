<?php

namespace Library\IscClient\Drivers;

use Library\IscClient\IscConstants;
use Library\IscClient\IscException;
use Predis\Client;

class RedisBusDriver implements IBusDriver
{
    private const ISC_REDIS_HOST_KEY = 'ISC_REDIS_HOST';
    private const ISC_REDIS_PORT_KEY = 'ISC_REDIS_PORT';

    /**
     * @var PredisClient
     */
    private $predisSubscribe;

    /**
     * @var PredisClient
     */
    private $predisPublish;

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

        $this->predisSubscribe = new Client('tcp://'.$host.':'.$port.'?read_write_timeout=0');
        $this->predisPublish = new Client('tcp://'.$host.':'.$port);
    }

    public function subscribe(array $subscriptions)
    {
        if (is_null($this->ps))
        {
            $this->ps = $this->predisSubscribe->pubSubLoop();
        }

        foreach ($subscriptions as $subscription)
        {
            $this->ps->psubscribe($subscription);
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
        if ($request->kind != 'pmessage')
        {
            return;
        }

        $channelParts = explode('.', $request->channel);
        $partCount = sizeof($channelParts);
        $requestId = $channelParts[$partCount - 1];
        $action = $channelParts[$partCount - 2];
        $type = $channelParts[$partCount - 3];
        $topic = substr($request->channel, 0, strpos($request->channel, '.'.$type));
        $payload = $this->decodePayload($request->payload);

        $closure($topic, $type, $action, $payload, $requestId);
    }

    private function decodePayload($payload)
    {
        $decoded = json_decode($payload, true);

        return is_null($decoded) ? [] : $decoded;
    }

    public function stop()
    {
        $this->ps->unsubscribe();
    }

    public function dispatch(string $channel, array $payload)
    {
        $this->predisPublish->publish($channel, json_encode($payload));
    }

    public function listenToResult(string $channel)
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

        $this->predisSubscribe = new Client('tcp://'.$host.':'.$port.'?read_write_timeout=5');
        $this->ps = $this->predisSubscribe->pubSubLoop();
        $channel = str_replace(IscConstants::QUERY_TYPE, IscConstants::RESULT_TYPE, $channel);
        $channel = str_replace(IscConstants::COMMAND_TYPE, IscConstants::RESULT_TYPE, $channel);
        $this->ps->psubscribe($channel.'.*');

        try
        {
            foreach ($this->ps as $request)
            {
                if ($request->kind != 'pmessage')
                {
                    continue;
                }

                $this->ps->stop();

                return [
                    'statusCode' => substr($request->channel, strrpos($request->channel, '.') + 1),
                    'payload' => $request->payload
                ];
            }
        }
        catch (\Predis\Connection\ConnectionException $e)
        {
            // ...
        }

        return ['statusCode' => 500, 'payload' => ['error' => 'Isc request timed out.']];
    }
}