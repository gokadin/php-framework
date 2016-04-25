<?php

namespace Library\Queue\Drivers;

use Library\Events\Handler;

class SyncQueueDriver
{
    public function push(Handler $handler, $event = null)
    {
        if (is_null($event))
        {
            $handler->handle();

            return;
        }

        $handler->handle($event);
    }
}