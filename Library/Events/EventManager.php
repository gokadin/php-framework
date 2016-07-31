<?php

namespace Library\Events;

use Library\Container\Container;
use Library\Queue\Queue;

class EventManager
{
    private $container;
    private $queue;
    private $listeners = [];

    public function __construct($config, Container $container, Queue $queue)
    {
        $this->container = $container;
        $this->queue = $queue;

        $this->registerListeners($config);
    }

    private function registerListeners($config)
    {
        foreach ($config as $eventClass => $listeners)
        {
            foreach ($listeners as $listenerClass)
            {
                $this->listeners[$eventClass][] = $listenerClass;
            }
        }
    }

    /**
     * Returns all listeners class names
     * associated with the event
     *
     * @param $event class name
     * @return array
     */
    public function getListeners($event)
    {
        if (!isset($this->listeners[$event]))
        {
            return [];
        }

        return $this->listeners[$event];
    }

    /**
     * Fires an event
     *
     * @param $event
     * @return void
     */
    public function fire(Event $event)
    {
        if ($event instanceof ShouldBroadcast)
        {
            $this->broadcastEvent($event);
        }

        $eventClass = get_class($event);
        if (!isset($this->listeners[$eventClass]))
        {
            return;
        }

        $this->executeListeners($this->listeners[$eventClass], $event);
    }

    private function executeListeners(array $listeners, Event $event)
    {
        foreach ($listeners as $listenerClass)
        {
            $this->executeListener($this->container->resolve($listenerClass), $event);
        }
    }

    private function executeListener(Listener $listener, Event $event)
    {
        if ($listener instanceof ShouldQueue)
        {
            $this->executeAsyncListener($listener, $event);

            return;
        }

        $this->executeSyncListener($listener, $event);
    }

    private function executeSyncListener(Listener $listener, Event $event)
    {
        $listener->handle($event);
    }

    private function executeAsyncListener(Listener $listener, Event $event)
    {
        $this->queue->push($listener, $event);
    }

    // *** TO REMOVE
    /**
     * Broadcasts the event
     *
     * @param $event
     */
    private function broadcastEvent($event)
    {
        $data = $event->broadcastOn();

        foreach ($data as $channel => $payload)
        {
            //$this->redis->publish($channel, json_encode($payload));
        }
    }
}