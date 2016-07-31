<?php

namespace Library\Queue;

use Library\Events\Handler;
use Library\Queue\Drivers\SyncQueueDriver;
use Library\Queue\Drivers\DatabaseQueueDriver;

class Queue
{
    protected $asyncDriver;
    protected $syncDriver;
    protected $syncOnly;

    public function __construct($config)
    {
        $this->syncOnly = false;

        $this->setDrivers($config);
    }

    protected function setDrivers($config)
    {
        $this->syncDriver = new SyncQueueDriver();

        switch ($config['use'])
        {
            case 'database':
                $this->asyncDriver = new DatabaseQueueDriver($config['connections']['database']);
                break;
            default:
                $this->syncOnly = true;
                $this->asyncDriver = null;
                break;
        }
    }

    public function push(Handler $handler, $event = null)
    {
        if ($this->syncOnly || env('CONSOLE'))
        {
            $this->syncDriver->push($handler, $event);

            return;
        }

        $this->asyncDriver->push($handler, $event);
    }
}