<?php

namespace Library\IscClient;

use Library\IscClient\Drivers\IBusDriver;
use Library\IscClient\Drivers\RedisBusDriver;

class IscClient
{
    /**
     * @var IBusDriver
     */
    private $driver;

    public function __construct(array $config)
    {
        $this->connectToBus($config);
    }

    private function connectToBus(array $config)
    {
        $this->driver = new RedisBusDriver($config);

        $this->driver->subscribe();
    }

    public function run()
    {
        $this->driver->run();
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