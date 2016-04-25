<?php

namespace Library\Scheduler;

use Closure;

class Scheduler
{
    protected $events = [];

    public function add($name, Closure $closure)
    {
        return $events[] = new Event($name, $closure);
    }

    public function events()
    {
        return $this->events;
    }

    public function dueEvents()
    {
        $dueEvents = [];

        foreach ($this->events as $event)
        {
            if ($event->isDue())
            {
                $dueEvents[] = $event;
            }
        }

        return $dueEvents;
    }
}