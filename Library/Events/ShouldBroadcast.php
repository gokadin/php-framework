<?php

namespace Library\Events;

interface ShouldBroadcast
{
    function broadcastOn();
}