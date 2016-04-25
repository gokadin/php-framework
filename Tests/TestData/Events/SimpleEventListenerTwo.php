<?php

namespace Tests\TestData\Events;

use Library\Events\Listener;

class SimpleEventListenerTwo extends Listener
{
    public function handle(SimpleEvent $event)
    {
        $event->secondFired();
    }
}