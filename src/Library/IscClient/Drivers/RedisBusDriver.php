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

        $this->redis = new Redis();
        $this->redis->connect($host, $port, 5, NULL, 100, 5);
    }

    public function run(array $subscriptions, \Closure $closure)
    {
        $this->redis->psubscribe($subscriptions, function($redis, $subscription, $channel, $payload) use ($closure) {
            echo 'PROCESSING REQUEST FROM '.$channel.PHP_EOL;
            $channelParts = explode('.', $channel);
            $partCount = sizeof($channelParts);
            $requestId = $channelParts[$partCount - 1];
            $action = $channelParts[$partCount - 2];
            $type = $channelParts[$partCount - 3];
            $topic = substr($channel, 0, strpos($channel, '.'.$type));
            $payload = $this->decodePayload($payload);

            echo 'requestId: '.$requestId.' - action: '.$action.' - type: '.$type.' - topic: '.$topic.PHP_EOL;
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
        $channel = str_replace(IscConstants::QUERY_TYPE, IscConstants::RESULT_TYPE, $channel);
        $channel = str_replace(IscConstants::COMMAND_TYPE, IscConstants::RESULT_TYPE, $channel);
        $channel .= '.*';

        echo 'LISTENING ON '.$channel.PHP_EOL;

        try
        {
            $result = [];
            $this->redis->psubscribe($channel, function($redis, $channel, $subscription, $payload) use (&$result) {
                var_dump($payload);

                $result = [
                    'statusCode' => 200,
                    'payload' => $payload
                ];

                $redis->close();
                return false;
            });

            return $result;
        }
        catch (\RedisException $e)
        {
            echo 'IN EXCEPTION'.PHP_EOL;
            return [
                'statusCode' => 500,
                'payload' => ['error' => 'Isc request timed out.']
            ];
        }
    }
}