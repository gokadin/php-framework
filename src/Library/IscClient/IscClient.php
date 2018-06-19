<?php

namespace Library\IscClient;

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

        $this->connectToBus($config);

        $this->readConfig($config);

        $this->subscriptionDiscovery = new SubscriptionDiscovery($this->basePath, $this->iscRoot);
    }

    private function readConfig(array $config)
    {
        $this->iscRoot = isset($config[self::ISC_ROOT_KEY]) ? $config[self::ISC_ROOT_KEY] : self::DEFAULT_ISC_ROOT;
    }

    private function connectToBus(array $config)
    {
        $this->driver = new RedisBusDriver($config);
    }

    public function run()
    {
        $this->driver->subscribe($this->subscriptionDiscovery->getSubscriptionStrings());

        $this->driver->run(function(string $topic, string $type, string $action, $payload)
        {
            echo 'RECEIVED REQUEST: '.$topic.' - '.$type.' - '.$action.' - '.PHP_EOL;
            var_dump($payload).PHP_EOL;
        });
    }

    public function stop()
    {
        $this->driver->stop();
    }

    public function dispatch(string $channel, IscEntity $entity)
    {
        $this->driver->dispatch($channel, $entity);
    }
}