<?php

namespace Tests\TestData\Events;

use Library\Events\Listener;

class ListenerWithResolvableConstructor extends Listener
{
    protected $simpleEvent;

    public function __construct(SimpleEvent $simpleEvent)
    {
        $this->simpleEvent = $simpleEvent;
    }

    public function handle(EventTestingResolvableConstructor $event)
    {
        $event->fired();
        $event->setResolvedParameter($this->simpleEvent);
    }
}