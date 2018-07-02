<?php

namespace Library\IscClient\Drivers;

use Library\IscClient\IscConstants;
use Library\IscClient\IscException;
use \Redis;

class RedisBusDriver implements IBusDriver
{
    private const ISC_REDIS_HOST_KEY = 'ISC_REDIS_HOST';
    private const ISC_REDIS_PORT_KEY = 'ISC_REDIS_PORT';

    /**
     * @var Redis
     */
    private $redis;

    public function __construct()
    {
        $this->redis = $this->connect();
    }

    private function connect(int $readTimeout = 0)
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

        $redis = new Redis();
        $redis->connect($host, $port, 0, NULL, 100, $readTimeout);

        return $redis;
    }

    public function run(array $subscriptions, \Closure $closure)
    {
        $this->redis->psubscribe($subscriptions, function($redis, $subscription, $channel, $payload) use ($closure) {
            $channelParts = explode('.', $channel);
            $partCount = sizeof($channelParts);
            $requestId = $channelParts[$partCount - 1];
            $action = $channelParts[$partCount - 2];
            $type = $channelParts[$partCount - 3];
            $topic = substr($channel, 0, strpos($channel, '.'.$type));
            $payload = $this->decodePayload($payload);

            $closure($topic, $type, $action, $payload, $requestId);
        });
    }

    private function decodePayload($payload)
    {
        $decoded = json_decode($payload, true);

        return is_null($decoded) ? [] : $decoded;
    }

    public function stop()
    {
        // ...
    }

    public function dispatch(string $channel, array $payload)
    {
        $this->redis->publish($channel, json_encode($payload));
        echo 'DISPATCHING ON '.$channel.PHP_EOL;
    }

    public function listenToResult(string $channel)
    {
        $resultRedis = $this->connect(5);
        $channel = str_replace(IscConstants::QUERY_TYPE, IscConstants::RESULT_TYPE, $channel);
        $channel = str_replace(IscConstants::COMMAND_TYPE, IscConstants::RESULT_TYPE, $channel);
        $channel .= '.*';

        echo 'LISTENING ON '.$channel.PHP_EOL;

        try
        {
            $result = [];
            $resultRedis->psubscribe($channel, function($redis, $channel, $subscription, $payload) use ($resultRedis, &$result) {
                $result = [
                    'statusCode' => 200,
                    'payload' => $payload
                ];

                $resultRedis->close();
                return false;
            });

            return $result;
        }
        catch (\RedisException $e)
        {
            return [
                'statusCode' => 500,
                'payload' => ['error' => 'Isc request timed out.']
            ];
        }
    }
}