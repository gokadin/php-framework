<?php

namespace Library\IscClient\Drivers;

use Library\IscClient\IscConstants;
use Library\IscClient\IscException;
use RedisClient\RedisClient;

class RedisBusDriver implements IBusDriver
{
    private const ISC_REDIS_HOST_KEY = 'ISC_REDIS_HOST';
    private const ISC_REDIS_PORT_KEY = 'ISC_REDIS_PORT';

    /**
     * @var RedisClient
     */
    private $publishRedis;

    public function __construct()
    {
        $this->publishRedis = $this->connect();
    }

    private function connect(int $timeout = 0)
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

        return new RedisClient(['server' => $host.':'.$port, 'timeout' => $timeout]);
    }

    public function run(array $subscriptions, \Closure $closure)
    {
        $subscribeRedis = $this->connect();
        $subscribeRedis->psubscribe($subscriptions, function($type, $pattern, $channel, $payload) use ($closure) {
            if ($type != 'pmessage')
            {
                return true;
            }

            $channelParts = explode('.', $channel);
            $partCount = sizeof($channelParts);
            $requestId = $channelParts[$partCount - 1];
            $action = $channelParts[$partCount - 2];
            $type = $channelParts[$partCount - 3];
            $topic = substr($channel, 0, strpos($channel, '.'.$type));
            $payload = $this->decodePayload($payload);

            $closure($topic, $type, $action, $payload, $requestId);

            return true;
        });
    }

    private function decodePayload($payload)
    {
        $decoded = json_decode($payload, true);

        return is_null($decoded) ? [] : $decoded;
    }

    public function dispatch(string $channel, array $payload)
    {
        $this->publishRedis->publish($channel, json_encode($payload));
    }

    public function publishAndListenResult(string $dispatchChannel, array $dispatchPayload)
    {
        $redis = $this->connect(1);
        $resultChannel = str_replace(IscConstants::QUERY_TYPE, IscConstants::RESULT_TYPE, $dispatchChannel);
        $resultChannel = str_replace(IscConstants::COMMAND_TYPE, IscConstants::RESULT_TYPE, $resultChannel);
        $resultChannel .= '.*';

        $result = [
            'statusCode' => 500,
            'payload' => ['error' => 'Isc request timed out.']
        ];

        $redis->psubscribe([$resultChannel], function($type, $pattern, $channel, $payload) use (&$result, $dispatchChannel, $dispatchPayload) {
            switch ($type)
            {
                case 'psubscribe':
                    $this->dispatch($dispatchChannel, $dispatchPayload);
                    return true;
                case 'pmessage':
                    if (is_string($payload))
                    {
                        $payload = json_decode($payload, true);
                    }
                    $result = [
                        'statusCode' => 200,
                        'payload' => $payload
                    ];
                    return false;
            }

            return true;
        });

        return $result;
    }
}