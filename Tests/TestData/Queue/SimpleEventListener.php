<?php

namespace Tests\TestData\Queue;

use App\Listeners\Listener;

class SimpleEventListener extends Listener
{
    protected $wasRun;
    protected $eventValue;

    public function __construct()
    {
        $this->wasRun = false;
        $this->eventValue = null;
    }

    public function handle(SimpleEvent $event)
    {
        $this->wasRun = true;
        $this->eventValue = $event->eventValue();
    }

    public function wasRun()
    {
        return $this->wasRun;
    }

    public function eventValue()
    {
        return $this->eventValue;
    }
}