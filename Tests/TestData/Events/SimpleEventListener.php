<?php

namespace Tests\TestData\Events;

use Library\Events\Listener;

class SimpleEventListener extends Listener
{
    public function handle(SimpleEvent $event)
    {
        $event->fired();
    }
}