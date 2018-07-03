<?php

namespace Library\IscClient;

use Library\Container\Container;
use Library\Core\Application;
use Library\IscClient\Actions\ActionHandler;
use Library\IscClient\Drivers\IBusDriver;
use Library\IscClient\Drivers\RedisBusDriver;
use Library\IscClient\Subscriptions\SubscriptionDiscovery;

class IscClient
{
    private const ISC_ROOT_KEY = 'isc_root';
    private const DEFAULT_ISC_ROOT = 'App/Isc';

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var IBusDriver
     */
    private $driver;

    /**
     * @var string
     */
    private $iscRoot;

    /**
     * @var SubscriptionDiscovery
     */
    private $subscriptionDiscovery;

    public function __construct(string $basePath, array $config)
    {
        $this->basePath = $basePath;

        $this->connectToBus();

        $this->readConfig($config);

        $this->subscriptionDiscovery = new SubscriptionDiscovery($this->basePath, $this->iscRoot);
    }

    private function readConfig(array $config)
    {
        $this->iscRoot = isset($config[self::ISC_ROOT_KEY]) ? $config[self::ISC_ROOT_KEY] : self::DEFAULT_ISC_ROOT;
    }

    private function connectToBus()
    {
        $this->driver = new RedisBusDriver();
    }

    public function run()
    {
        $app = new Application($this->basePath);
        $app->container()->registerInstance('iscClient', $this);
        $actionHandler = new ActionHandler($app->container());

        $this->driver->run($this->subscriptionDiscovery->getSubscriptionStrings(), function(string $topic, string $type, string $action, array $payload, string $requestId) use ($actionHandler)
        {
            $route = $this->subscriptionDiscovery->findSubscriptionRoute($topic, $type, $action);
            if (!is_null($route))
            {
                $route->setRequestId($requestId);
                $actionHandler->handle($route, $payload);
            }
        });
    }

    public function stop()
    {
        $this->driver->stop();
    }

    public function dispatchEvent(string $topic, string $action, array $payload)
    {
        $this->driver->dispatch($this->buildChannelString($topic, IscConstants::EVENT_TYPE, $action, uniqid()), $payload);
    }

    public function dispatchCommand(string $topic, string $action, array $payload)
    {
        $this->driver->dispatch($this->buildChannelString($topic, IscConstants::COMMAND_TYPE, $action, uniqid()), $payload);
    }

    public function dispatchQuery(string $topic, string $action, array $payload)
    {
        $requestId = uniqid();
        $channel = $this->buildChannelString($topic, IscConstants::QUERY_TYPE, $action, $requestId);

        //$this->driver->dispatch($channel, $payload);

        return $this->driver->listenToResult($channel);
    }

    public function dispatchResult(string $topic, string $action, int $statusCode, array $payload, string $requestId)
    {
        $this->driver->dispatch($this->buildChannelString($topic, IscConstants::RESULT_TYPE, $action, $requestId, $statusCode), $payload);
    }

    private function buildChannelString(string $topic, string $type, string $action, $requestId, $statusCode = null): string
    {
        $channel = implode('.', [$topic, $type, $action, $requestId]);

        if (!is_null($statusCode))
        {
            $channel .= '.'.$statusCode;
        }

        return $channel;
    }
}