<?php

namespace Tests\TestData\Queue;

use Tests\TestData\Container\ConcreteNoConstructor;

class ResolvableEventListener
{
    protected $wasRun;
    protected $eventValue;
    protected $resolvedConcreteNoConstructor;

    public function __construct(ConcreteNoConstructor $resolvedConcreteNoConstructor)
    {
        $this->wasRun = false;
        $this->eventValue = null;
        $this->resolvedConcreteNoConstructor = $resolvedConcreteNoConstructor;
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

    public function resolvedConcreteNoConstructor()
    {
        return $this->resolvedConcreteNoConstructor;
    }
}